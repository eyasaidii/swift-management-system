"""
Génère un dataset synthétique BTL SWIFT pour l'entraînement IA.

Usage : python scripts/generate_synthetic_data.py [nb_messages]

Simule les deux tables Oracle :
  - MESSAGES_SWIFT  → colonnes features
  - ANOMALIES_SWIFT → colonnes labels (score, niveau_risque, is_anomalie, raisons)

Le score est calculé selon les 9 règles métier de AnomalyService.php.

Distribution cible :
  LOW    (score  0-19) : 37.1%  →  7 415
  MEDIUM (score 20-59) : 48.5%  →  9 705
  HIGH   (score  ≥ 60) : 14.4%  →  2 880
"""

import os
import sys
import re
import json
import random
from datetime import datetime, timedelta

import pandas as pd
import numpy as np

# ── Configuration
DEFAULT_N = 20_000

# ── BIC codes par pays
BANKS_TN = ["BTLKTNTT", "BIATTNTT", "STBKTNTT", "ATTJTNTT", "UBCITNTT", "BFTNTNTT"]
BANKS_FR = ["BNPAFRPP", "SOGEFRPP", "CRLYFRPP", "AGRIFRPP"]
BANKS_US = ["CITIUS33", "CHASUS33", "BOFAUS3N"]
BANKS_GB = ["BARCGB22", "HSBCGB2L", "NWBKGB2L"]
BANKS_DE = ["DEUTDEFF", "COBADEFF", "COMMDEFF"]
BANKS_IT = ["UNCRITMM", "BCITITMM"]
BANKS_ES = ["CAIXESBB", "BBVAESMM"]
BANKS_OTHER = ["EBABORUA", "SCBLSGSG", "ANABORUA", "RJHISARI"]

ALL_FOREIGN_BANKS = BANKS_FR + BANKS_US + BANKS_GB + BANKS_DE + BANKS_IT + BANKS_ES + BANKS_OTHER

COUNTRY_MAP: dict = {}
for b in BANKS_TN:     COUNTRY_MAP[b] = "TN"
for b in BANKS_FR:     COUNTRY_MAP[b] = "FR"
for b in BANKS_US:     COUNTRY_MAP[b] = "US"
for b in BANKS_GB:     COUNTRY_MAP[b] = "GB"
for b in BANKS_DE:     COUNTRY_MAP[b] = "DE"
for b in BANKS_IT:     COUNTRY_MAP[b] = "IT"
for b in BANKS_ES:     COUNTRY_MAP[b] = "ES"
COUNTRY_MAP.update({"EBABORUA": "AE", "SCBLSGSG": "SG", "ANABORUA": "AE", "RJHISARI": "SA"})

# ── Types de messages (MX ISO 20022 + MT legacy)
MX_TYPES = ["PACS.008", "PACS.009", "CAMT.053", "PAIN.001", "CAMT.054"]
MT_TYPES = ["MT103", "MT202", "MT199", "MT900", "MT910", "MT940", "MT950"]
ALL_TYPES = MX_TYPES + MT_TYPES

CATEGORY_MAP = {
    "PACS.008": "PACS", "PACS.009": "PACS",
    "CAMT.053": "CAMT", "CAMT.054": "CAMT",
    "PAIN.001": "PAIN",
    "MT103": "1", "MT202": "2", "MT199": "1",
    "MT900": "9", "MT910": "9", "MT940": "9", "MT950": "9",
}

NORMAL_CURRENCIES = ["EUR", "USD", "TND", "GBP", "CHF"]
UNUSUAL_CURRENCIES = ["JPY", "CNY", "RUB", "TRY", "AED", "SAR", "DZD"]

# Statuts possibles (comme dans l'ENUM Oracle de BTL)
STATUSES_NORMAL   = ["processed", "authorized"]
STATUSES_ALL      = ["pending", "processed", "authorized", "rejected", "suspicious"]

# Montants moyens par type (mean, std) en devise locale
AMOUNT_PROFILES = {
    "PACS.008": (50_000, 30_000),
    "PACS.009": (200_000, 100_000),
    "CAMT.053": (0, 0),
    "PAIN.001": (30_000, 20_000),
    "CAMT.054": (0, 0),
    "MT103":    (15_000,  8_000),
    "MT202":    (80_000, 40_000),
    "MT199":    (0, 0),
    "MT900":    (25_000, 15_000),
    "MT910":    (20_000, 12_000),
    "MT940":    (0, 0),
    "MT950":    (0, 0),
}

PAYMENT_TYPES = ["MT103", "MT202", "PACS.008", "PACS.009", "PAIN.001"]  # types avec montant

SENDER_NAMES = [
    "BTL BANK TUNIS", "BIAT BANQUE", "STB BANK", "ATTIJARI BANK", "UBCI BANK",
    "BNP PARIBAS SA", "SOCIETE GENERALE", "CREDIT LYONNAIS",
    "CITIBANK NA", "CHASE BANK", "BANK OF AMERICA",
    "BARCLAYS PLC", "HSBC BANK PLC", "DEUTSCHE BANK AG",
]

RECEIVER_NAMES_NORMAL = [
    "ENTERPRISE SARL", "GLOBAL TRADING CO", "IMPORT EXPORT LTD",
    "TECH SOLUTIONS SA", "CONSTRUCTION GROUP", "PHARMA INTERNATIONAL",
    "CONSULTING SERVICES", "ENERGY RESOURCES", "MARITIME SHIPPING CO",
    "AGRI PRODUCTS SARL", "TEXTILE FACTORY", "AUTOMOTIVE PARTS SA",
]


# ─────────────────────────────────────────────────────────────
# Helpers
# ─────────────────────────────────────────────────────────────

def _gen_iban(country: str = "TN") -> str:
    return f"{country}{''.join([str(random.randint(0, 9)) for _ in range(20)])}"


def _gen_reference(dt: datetime, idx: int) -> str:
    return f"REF{dt.strftime('%Y%m%d')}{idx:05d}"


# ─────────────────────────────────────────────────────────────
# Score selon les 9 règles AnomalyService.php
# ─────────────────────────────────────────────────────────────

def _compute_score(msg: dict, known_refs: set) -> tuple[int, list]:
    score = 0
    reasons = []

    amount = float(msg.get("amount") or 0)
    status = (msg.get("status") or "").lower()
    ref    = (msg.get("reference") or "")
    te     = (msg.get("translation_errors") or "")
    rname  = (msg.get("receiver_name") or "")
    sbic   = (msg.get("sender_bic") or "")
    rbic   = (msg.get("receiver_bic") or "")
    cur    = (msg.get("currency") or "")
    mtype  = (msg.get("type_message") or "")

    # 1. MONTANT_ZERO  (+40)
    if amount == 0 and mtype in PAYMENT_TYPES:
        score += 40; reasons.append("MONTANT_ZERO")

    # 2. MONTANT_ELEVE (+25)
    if amount > 100_000:
        score += 25; reasons.append("MONTANT_ELEVE")

    # 3. STATUT_REJETE (+30)
    if status == "rejected":
        score += 30; reasons.append("STATUT_REJETE")

    # 4. TYPE_ERROR (+35)
    if "IMPORT-FAILED" in ref or "ERROR" in ref:
        score += 35; reasons.append("TYPE_ERROR")

    # 5. TRANSLATION_ERROR (+25)
    if te and te not in ("", "null", "[]", "{}"):
        score += 25; reasons.append("TRANSLATION_ERROR")

    # 6. PASSPORT_DETECTE (+30)
    if re.search(r"PASS\s*NO\s*\d+", rname, re.IGNORECASE):
        score += 30; reasons.append("PASSPORT_DETECTE")

    # 7. BIC_MANQUANT (+15)
    if not sbic or not rbic:
        score += 15; reasons.append("BIC_MANQUANT")

    # 8. DEVISE_INHABITUELLE (+20)
    if cur not in NORMAL_CURRENCIES:
        score += 20; reasons.append("DEVISE_INHABITUELLE")

    # 9. DOUBLON_REFERENCE (+20)
    if ref and ref in known_refs:
        score += 20; reasons.append("DOUBLON_REFERENCE")

    return int(min(score, 100)), reasons


def _niveau_risque(score: int) -> str:
    if score >= 60: return "HIGH"
    if score >= 20: return "MEDIUM"
    return "LOW"


# ─────────────────────────────────────────────────────────────
# Génération d'un message de base (MESSAGES_SWIFT)
# ─────────────────────────────────────────────────────────────

def _make_message(idx: int, base_date: datetime) -> dict:
    mtype     = random.choice(ALL_TYPES)
    direction = random.choice(["IN", "OUT"])
    currency  = random.choices(NORMAL_CURRENCIES, weights=[0.35, 0.30, 0.20, 0.10, 0.05])[0]

    mean, std = AMOUNT_PROFILES.get(mtype, (10_000, 5_000))
    amount = round(max(0.0, np.random.normal(mean, std)), 2) if mean > 0 else 0.0

    if direction == "OUT":
        sbic = random.choice(BANKS_TN)
        rbic = random.choice(ALL_FOREIGN_BANKS)
    else:
        sbic = random.choice(ALL_FOREIGN_BANKS)
        rbic = random.choice(BANKS_TN)

    dt = base_date + timedelta(
        days=random.randint(0, 365),
        hours=random.randint(8, 17),
        minutes=random.randint(0, 59),
    )

    return {
        "id":               idx,
        "type_message":     mtype,
        "reference":        _gen_reference(dt, idx),
        "direction":        direction,
        "sender_bic":       sbic,
        "receiver_bic":     rbic,
        "sender_account":   _gen_iban(COUNTRY_MAP.get(sbic, "TN")),
        "receiver_account": _gen_iban(COUNTRY_MAP.get(rbic, "FR")),
        "sender_name":      random.choice(SENDER_NAMES),
        "receiver_name":    random.choice(RECEIVER_NAMES_NORMAL),
        "amount":           amount,
        "currency":         currency,
        "value_date":       dt.strftime("%Y-%m-%d"),
        "status":           random.choices(STATUSES_NORMAL, weights=[0.5, 0.5])[0],
        "translation_errors": None,
        "category":         CATEGORY_MAP.get(mtype, "MT"),
        "created_at":       dt.strftime("%Y-%m-%dT%H:%M:%S"),
    }


# ─────────────────────────────────────────────────────────────
# Injection d'anomalies selon le niveau voulu
# ─────────────────────────────────────────────────────────────

# Combinaisons de règles déclenchées → score résultant
_HIGH_COMBOS = [
    ["MONTANT_ZERO",  "STATUT_REJETE"],                       # 40+30=70
    ["MONTANT_ELEVE", "STATUT_REJETE", "BIC_MANQUANT"],       # 25+30+15=70
    ["TYPE_ERROR",    "MONTANT_ELEVE"],                        # 35+25=60
    ["PASSPORT_DETECTE", "MONTANT_ELEVE", "BIC_MANQUANT"],    # 30+25+15=70
    ["TRANSLATION_ERROR", "STATUT_REJETE", "BIC_MANQUANT"],   # 25+30+15=70
    ["MONTANT_ZERO",  "TRANSLATION_ERROR"],                    # 40+25=65
    ["TYPE_ERROR",    "TRANSLATION_ERROR"],                    # 35+25=60
    ["MONTANT_ZERO",  "STATUT_REJETE", "DEVISE_INHABITUELLE"], # 40+30+20=90
    ["TYPE_ERROR",    "STATUT_REJETE"],                        # 35+30=65
    ["PASSPORT_DETECTE", "STATUT_REJETE"],                    # 30+30=60
]

_MEDIUM_COMBOS = [
    ["STATUT_REJETE"],                         # 30
    ["MONTANT_ELEVE"],                          # 25
    ["PASSPORT_DETECTE"],                       # 30
    ["TRANSLATION_ERROR"],                      # 25
    ["MONTANT_ELEVE",        "BIC_MANQUANT"],   # 25+15=40
    ["DEVISE_INHABITUELLE",  "BIC_MANQUANT"],   # 20+15=35
    ["STATUT_REJETE",        "BIC_MANQUANT"],   # 30+15=45
    ["DEVISE_INHABITUELLE",  "MONTANT_ELEVE"],  # 20+25=45
    ["PASSPORT_DETECTE",     "BIC_MANQUANT"],   # 30+15=45
    ["TRANSLATION_ERROR",    "BIC_MANQUANT"],   # 25+15=40
]


def _inject(msg: dict, level: str) -> dict:
    combos = _HIGH_COMBOS if level == "HIGH" else _MEDIUM_COMBOS
    for rule in random.choice(combos):
        if rule == "MONTANT_ZERO":
            if msg["type_message"] in PAYMENT_TYPES:
                msg["amount"] = 0.0
        elif rule == "MONTANT_ELEVE":
            msg["amount"] = round(random.uniform(100_001, 2_000_000), 2)
        elif rule == "STATUT_REJETE":
            msg["status"] = "rejected"
        elif rule == "TYPE_ERROR":
            msg["reference"] = f"IMPORT-FAILED-{msg['reference']}"
        elif rule == "TRANSLATION_ERROR":
            msg["translation_errors"] = json.dumps(
                [{"field": "32A", "error": "Invalid date format", "raw": "XXXXXX"}]
            )
        elif rule == "PASSPORT_DETECTE":
            pn = random.randint(10_000_000, 99_999_999)
            msg["receiver_name"] = f"BENEFICIAIRE PASS NO {pn} ETRANGER"
        elif rule == "BIC_MANQUANT":
            if random.random() < 0.5:
                msg["sender_bic"] = ""
            else:
                msg["receiver_bic"] = ""
        elif rule == "DEVISE_INHABITUELLE":
            msg["currency"] = random.choice(UNUSUAL_CURRENCIES)
    return msg


# ─────────────────────────────────────────────────────────────
# Main
# ─────────────────────────────────────────────────────────────

def main():
    n = int(sys.argv[1]) if len(sys.argv) > 1 else DEFAULT_N
    random.seed(42)
    np.random.seed(42)

    # Distribution cible
    n_low    = int(n * 0.371)
    n_medium = int(n * 0.485)
    n_high   = n - n_low - n_medium

    print(f"Génération {n:,} messages BTL SWIFT")
    print(f"  LOW    : {n_low:,}  ({100*n_low/n:.1f}%)")
    print(f"  MEDIUM : {n_medium:,}  ({100*n_medium/n:.1f}%)")
    print(f"  HIGH   : {n_high:,}  ({100*n_high/n:.1f}%)")

    base_date = datetime(2025, 1, 1)
    messages = []

    # ── LOW : messages normaux
    for i in range(n_low):
        messages.append(_make_message(i + 1, base_date))

    # ── MEDIUM : anomalies légères
    for i in range(n_medium):
        msg = _make_message(n_low + i + 1, base_date)
        messages.append(_inject(msg, "MEDIUM"))

    # ── HIGH : anomalies critiques
    for i in range(n_high):
        msg = _make_message(n_low + n_medium + i + 1, base_date)
        messages.append(_inject(msg, "HIGH"))

    # Mélanger et réindexer
    random.shuffle(messages)
    for i, m in enumerate(messages):
        m["id"] = i + 1

    # Calculer les labels ANOMALIES_SWIFT (score, niveau_risque, is_anomalie, raisons)
    known_refs: set = set()
    for m in messages:
        raw_score, reasons = _compute_score(m, known_refs)
        # Petit bruit gaussien ±4 pour réalisme, sans dépasser les seuils de catégorie
        noise = int(np.random.normal(0, 4))
        score = int(np.clip(raw_score + noise, 0, 100))
        m["score"]         = score
        m["niveau_risque"] = _niveau_risque(score)
        m["is_anomalie"]   = 1 if score >= 20 else 0
        m["raisons"]       = json.dumps(reasons)
        known_refs.add(m["reference"])

    df = pd.DataFrame(messages)
    os.makedirs("data", exist_ok=True)
    out = "data/swift_messages.csv"
    df.to_csv(out, index=False)

    print(f"\nDataset sauvegardé : {out}")
    print(f"  Lignes    : {len(df):,}")
    print(f"  Colonnes  : {len(df.columns)} → {list(df.columns)}")
    print(f"\n── Distribution niveau_risque ──")
    print(df["niveau_risque"].value_counts())
    print(f"\n── is_anomalie ──")
    print(df["is_anomalie"].value_counts())
    print(f"\n── Score (stats) ──")
    print(df["score"].describe().round(2))
    print(f"\n── Nulls ──")
    nulls = df.isnull().sum()
    print(nulls[nulls > 0] if nulls.sum() > 0 else "  Aucune valeur nulle")


if __name__ == "__main__":
    main()
