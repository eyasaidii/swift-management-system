"""
Preprocessing & Feature Engineering pour les messages SWIFT BTL.

Transforme un SwiftMessageRequest (MESSAGES_SWIFT) en vecteur numérique
exploitable par les modèles ML.

Features alignées sur les 9 règles de AnomalyService.php +
features temporelles et contextuelles.

Gestion des NULL : tous les champs optionnels sont tolérés (pas d'erreur).
"""

import re
import json
import pandas as pd
import numpy as np
from datetime import datetime
from typing import List, Dict, Optional, Any

from app.models.schemas import SwiftMessageRequest


# ──────────────────────────────────────────────────────────────
# Constantes
# ──────────────────────────────────────────────────────────────

# Types de messages connus (MX ISO 20022 + MT legacy)
MESSAGE_TYPES = [
    "PACS.008", "PACS.009", "CAMT.053", "CAMT.054", "PAIN.001",
    "MT103", "MT202", "MT199", "MT900", "MT910", "MT940", "MT950",
    "OTHER",
]

# Types impliquant un virement (montant attendu > 0)
PAYMENT_TYPES = {"PACS.008", "PACS.009", "PAIN.001", "MT103", "MT202"}

DIRECTIONS = ["IN", "OUT"]

# Devises normales BTL (selon AnomalyService.php)
NORMAL_CURRENCIES = {"EUR", "USD", "TND", "GBP", "CHF"}
ALL_CURRENCIES    = ["EUR", "USD", "TND", "GBP", "CHF", "JPY", "CNY", "OTHER_CUR"]

# Statuts Oracle possibles
ALL_STATUSES = ["pending", "processed", "authorized", "rejected", "suspicious"]

# Catégories Oracle
ALL_CATEGORIES = ["PACS", "CAMT", "PAIN", "1", "2", "9", "OTHER_CAT"]


# ──────────────────────────────────────────────────────────────
# Helpers
# ──────────────────────────────────────────────────────────────

def _safe_str(val: Any, default: str = "") -> str:
    """Convertit une valeur en str, retourne default si None/NaN."""
    if val is None:
        return default
    try:
        if pd.isna(val):
            return default
    except (TypeError, ValueError):
        pass
    return str(val).strip()


def _safe_float(val: Any, default: float = 0.0) -> float:
    """Convertit une valeur en float, retourne default si None/NaN/invalide."""
    if val is None:
        return default
    try:
        f = float(val)
        return default if (np.isnan(f) or np.isinf(f)) else f
    except (TypeError, ValueError):
        return default


def _safe_parse_dt(dt_str: Optional[str]) -> Optional[datetime]:
    """Parse une date ISO 8601 ou YYYY-MM-DD, retourne None si invalide."""
    if not dt_str:
        return None
    for fmt in ("%Y-%m-%dT%H:%M:%S", "%Y-%m-%d %H:%M:%S", "%Y-%m-%d"):
        try:
            return datetime.strptime(str(dt_str)[:19], fmt)
        except ValueError:
            continue
    return None


def _has_translation_errors(te: Any) -> bool:
    """Retourne True si translation_errors est non-vide (comme AnomalyService.php)."""
    s = _safe_str(te)
    return bool(s) and s not in ("null", "[]", "{}", "None")


def _has_passport(receiver_name: Any) -> bool:
    """Détecte 'PASS NO <chiffres>' dans le nom bénéficiaire."""
    return bool(re.search(r"PASS\s*NO\s*\d+", _safe_str(receiver_name), re.IGNORECASE))


def _is_unusual_currency(currency: Any) -> bool:
    return _safe_str(currency).upper() not in NORMAL_CURRENCIES


def _is_type_error(reference: Any) -> bool:
    r = _safe_str(reference).upper()
    return "IMPORT-FAILED" in r or "ERROR" in r


# ──────────────────────────────────────────────────────────────
# Feature Extraction (un message → dict numérique)
# ──────────────────────────────────────────────────────────────

def extract_features(msg: SwiftMessageRequest) -> Dict[str, float]:
    """
    Extrait un vecteur de features numériques à partir d'un message SWIFT.
    Toutes les features sont floats.  Aucune KeyError possible (champs optionnels OK).
    """
    features: Dict[str, float] = {}

    # ── Helpers locaux pour ce message
    amount      = _safe_float(msg.amount)
    currency    = _safe_str(msg.currency).upper()
    direction   = _safe_str(msg.direction).upper()
    mtype       = _safe_str(msg.get_type())
    sbic        = _safe_str(msg.get_sender_bic())
    rbic        = _safe_str(msg.get_receiver_bic())
    status      = _safe_str(msg.status).lower()
    reference   = _safe_str(msg.reference)
    te          = msg.translation_errors
    rname       = _safe_str(msg.receiver_name)
    category    = _safe_str(msg.category).upper()

    # ── 1. Montant (raw + log)
    features["amount"]     = amount
    features["log_amount"] = float(np.log1p(amount))

    # ── 2. Flags des 9 règles AnomalyService.php  (0 / 1)
    features["rule_montant_zero"]       = 1.0 if (amount == 0 and mtype in PAYMENT_TYPES) else 0.0
    features["rule_montant_eleve"]      = 1.0 if amount > 100_000 else 0.0
    features["rule_statut_rejete"]      = 1.0 if status == "rejected" else 0.0
    features["rule_type_error"]         = 1.0 if _is_type_error(reference) else 0.0
    features["rule_translation_error"]  = 1.0 if _has_translation_errors(te) else 0.0
    features["rule_passport"]           = 1.0 if _has_passport(rname) else 0.0
    features["rule_bic_manquant"]       = 1.0 if (not sbic or not rbic) else 0.0
    features["rule_devise_inhabituelle"]= 1.0 if _is_unusual_currency(currency) else 0.0
    # rule_doublon_reference non calculable à la prédiction (dépend de la BD)
    features["rule_doublon"]            = 0.0  # placeholder, sera 0 à l'inférence

    # ── 3. Type de message (one-hot)
    mtype_norm = mtype if mtype in MESSAGE_TYPES else "OTHER"
    for t in MESSAGE_TYPES:
        features[f"mt_{t.replace('.', '_')}"] = 1.0 if t == mtype_norm else 0.0

    # ── 4. Direction (one-hot)
    features["dir_IN"]  = 1.0 if direction == "IN"  else 0.0
    features["dir_OUT"] = 1.0 if direction == "OUT" else 0.0

    # ── 5. Devise (one-hot)
    cur_norm = currency if currency in [c for c in ALL_CURRENCIES if c != "OTHER_CUR"] else "OTHER_CUR"
    for c in ALL_CURRENCIES:
        features[f"cur_{c}"] = 1.0 if c == cur_norm else 0.0

    # ── 6. Statut (one-hot)
    for s in ALL_STATUSES:
        features[f"status_{s}"] = 1.0 if status == s else 0.0
    features["status_known"] = 1.0 if status in ALL_STATUSES else 0.0

    # ── 7. Catégorie (one-hot)
    cat_norm = category if category in ALL_CATEGORIES else "OTHER_CAT"
    for cat in ALL_CATEGORIES:
        features[f"cat_{cat}"] = 1.0 if cat == cat_norm else 0.0

    # ── 8. Features temporelles
    dt = _safe_parse_dt(msg.created_at)
    if dt:
        features["hour"]        = float(dt.hour)
        features["day_of_week"] = float(dt.weekday())
        features["is_weekend"]  = 1.0 if dt.weekday() >= 5 else 0.0
        features["is_night"]    = 1.0 if (dt.hour < 6 or dt.hour >= 22) else 0.0
        features["is_morning"]  = 1.0 if (8 <= dt.hour < 12) else 0.0
    else:
        features["hour"]        = 12.0
        features["day_of_week"] = 2.0
        features["is_weekend"]  = 0.0
        features["is_night"]    = 0.0
        features["is_morning"]  = 1.0

    # ── 9. Features BIC / pays
    features["sender_bic_missing"]   = 1.0 if not sbic else 0.0
    features["receiver_bic_missing"] = 1.0 if not rbic else 0.0
    features["same_bic"]             = 1.0 if (sbic and rbic and sbic == rbic) else 0.0

    # Pays déduit du BIC (4 premiers caractères = institution + pays)
    sender_country   = _safe_str(msg.sender_country).upper()
    receiver_country = _safe_str(msg.receiver_country).upper()
    features["tn_sender"]   = 1.0 if (sender_country == "TN"   or sbic.startswith("BTL") or sbic.startswith("BIAT") or sbic.endswith("TT")) else 0.0
    features["tn_receiver"] = 1.0 if (receiver_country == "TN" or rbic.endswith("TT")) else 0.0

    # ── 10. Score partiel règles (estimation locale, 0-100)
    rule_scores = {
        "rule_montant_zero":        40,
        "rule_montant_eleve":       25,
        "rule_statut_rejete":       30,
        "rule_type_error":          35,
        "rule_translation_error":   25,
        "rule_passport":            30,
        "rule_bic_manquant":        15,
        "rule_devise_inhabituelle": 20,
    }
    estimated_rule_score = sum(
        pts for k, pts in rule_scores.items() if features.get(k, 0) == 1.0
    )
    features["estimated_rule_score"] = float(min(estimated_rule_score, 100))

    return features


def extract_features_batch(messages: list) -> pd.DataFrame:
    """
    Extrait les features pour une liste de SwiftMessageRequest → DataFrame.
    Gère les valeurs NaN en les remplissant par 0.
    """
    rows = [extract_features(m) for m in messages]
    df = pd.DataFrame(rows)
    # Remplir les éventuels NaN (colonnes one-hot absentes)
    df = df.fillna(0.0)
    return df


def get_feature_names() -> List[str]:
    """Retourne la liste ordonnée des noms de features."""
    dummy = SwiftMessageRequest(
        type_message="MT103",
        direction="OUT",
        amount=0.0,
        currency="EUR",
        sender_bic="BTLKTNTT",
        receiver_bic="BNPAFRPP",
    )
    return list(extract_features(dummy).keys())


# ──────────────────────────────────────────────────────────────
# Règles métier (pour raisons textuelles dans la réponse)
# ──────────────────────────────────────────────────────────────

def check_business_rules(msg: SwiftMessageRequest) -> List[Dict]:
    """
    Applique les 9 règles AnomalyService.php et retourne la liste
    des règles déclenchées avec description + severity.
    """
    reasons = []
    amount    = _safe_float(msg.amount)
    currency  = _safe_str(msg.currency).upper()
    direction = _safe_str(msg.direction).upper()
    mtype     = _safe_str(msg.get_type())
    sbic      = _safe_str(msg.get_sender_bic())
    rbic      = _safe_str(msg.get_receiver_bic())
    status    = _safe_str(msg.status).lower()
    reference = _safe_str(msg.reference)
    rname     = _safe_str(msg.receiver_name)
    te        = msg.translation_errors

    if amount == 0 and mtype in PAYMENT_TYPES:
        reasons.append({"rule": "MONTANT_ZERO",
                         "description": f"Montant nul pour un message de virement ({mtype})",
                         "severity": "high"})

    if amount > 100_000:
        reasons.append({"rule": "MONTANT_ELEVE",
                         "description": f"Montant élevé : {amount:,.2f} {currency}",
                         "severity": "medium"})

    if status == "rejected":
        reasons.append({"rule": "STATUT_REJETE",
                         "description": "Message rejeté (STATUS=rejected)",
                         "severity": "high"})

    if _is_type_error(reference):
        reasons.append({"rule": "TYPE_ERROR",
                         "description": f"Référence contient une erreur d'import : {reference[:60]}",
                         "severity": "high"})

    if _has_translation_errors(te):
        reasons.append({"rule": "TRANSLATION_ERROR",
                         "description": "Erreurs de parsing XML→MT détectées",
                         "severity": "medium"})

    if _has_passport(rname):
        reasons.append({"rule": "PASSPORT_DETECTE",
                         "description": f"Numéro de passeport dans le nom bénéficiaire : {rname[:80]}",
                         "severity": "high"})

    if not sbic or not rbic:
        missing = "sender_bic" if not sbic else "receiver_bic"
        reasons.append({"rule": "BIC_MANQUANT",
                         "description": f"BIC manquant : {missing}",
                         "severity": "low"})

    if _is_unusual_currency(currency):
        reasons.append({"rule": "DEVISE_INHABITUELLE",
                         "description": f"Devise inhabituelle pour BTL : {currency}",
                         "severity": "medium"})

    return reasons


# Alias conservé pour compatibilité avec detector.py (ancien code)
def check_missing_fields(msg: SwiftMessageRequest) -> List[Dict]:
    return [r for r in check_business_rules(msg)
            if r["rule"] in ("BIC_MANQUANT",)]


def check_high_risk_country(msg: SwiftMessageRequest) -> List[Dict]:
    return []  # Géré via features tn_sender/tn_receiver dans le ML
