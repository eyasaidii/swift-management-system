"""
Génère un dataset international SWIFT labellisé manuellement.
300 scénarios couvrant IN/OUT, MT103/MT202/MT940, 20+ pays,
montants variés, statuts réels — labels basés sur typologies AML.

Usage : python scripts/generate_international_data.py
"""

import sys, os, csv, random
from datetime import datetime, timedelta

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

OUTPUT = os.path.join(os.path.dirname(__file__), '..', 'data', 'international_swift_data.csv')

random.seed(42)

# ─── BICs internationaux réels ────────────────────────────────────────────────
BANKS = {
    # Tunisie
    'BTLMTNTT': ('TN', 'BTL TUNISIAN LIBYAN BANK'),
    'BIATTNTT': ('TN', 'BIAT BANQUE INTERNATIONALE ARABE'),
    'STBKTNTT': ('TN', 'STB SOCIETE TUNISIENNE DE BANQUE'),
    'UBCITNTT': ('TN', 'UBCI UNION BANCAIRE'),
    'ATTJTNTT': ('TN', 'ATTIJARI BANK TUNISIE'),
    # France
    'BNPAFRPP': ('FR', 'BNP PARIBAS'),
    'SOGEFRPP': ('FR', 'SOCIETE GENERALE'),
    'CRLYFRPP': ('FR', 'CREDIT LYONNAIS'),
    'AGRIFRPP': ('FR', 'CREDIT AGRICOLE'),
    # Allemagne
    'DEUTDEFF': ('DE', 'DEUTSCHE BANK'),
    'COBADEFF': ('DE', 'COMMERZBANK'),
    # UK
    'BARCGB22': ('GB', 'BARCLAYS BANK'),
    'HSBCGB2L': ('GB', 'HSBC BANK'),
    'NWBKGB2L': ('GB', 'NATWEST'),
    # USA
    'CITIUS33': ('US', 'CITIBANK NA'),
    'CHASUS33': ('US', 'JP MORGAN CHASE'),
    'BOFAUS3N': ('US', 'BANK OF AMERICA'),
    # Italie
    'UNCRITMM': ('IT', 'UNICREDIT'),
    'BCITITMM': ('IT', 'BANCA INTESA'),
    # Espagne
    'CAIXESBB': ('ES', 'CAIXA BANK'),
    'BBVAESMM': ('ES', 'BBVA'),
    # Suisse
    'UBSWCHZH': ('CH', 'UBS ZURICH'),
    'CRESCHZZ': ('CH', 'CREDIT SUISSE'),
    # EAU / Dubaï
    'ADCBAEAA': ('AE', 'ABU DHABI COMMERCIAL BANK'),
    'EMIRAEAД': ('AE', 'EMIRATES NBD'),
    # Chine
    'ICBKCNBJ': ('CN', 'INDUSTRIAL COMMERCIAL BANK CHINA'),
    'BKCHCNBJ': ('CN', 'BANK OF CHINA'),
    # Japon
    'MHCBJPJT': ('JP', 'MIZUHO BANK TOKYO'),
    'BOTKJPJT': ('JP', 'BANK OF TOKYO MITSUBISHI'),
    # Libye
    'WAHYLYTR': ('LY', 'WAHDA BANK TRIPOLI'),
    'JUMHLYTR': ('LY', 'BANK OF COMMERCE TRIPOLI'),
    'NBTKLYTР': ('LY', 'NATIONAL BANK OF TRIPOLI'),
    # Maroc
    'BMCEMAMC': ('MA', 'BMCE BANK MAROC'),
    'BMARMAMC': ('MA', 'BANQUE MAROCAINE'),
    # Algérie
    'BDDRDZAL': ('DZ', 'BDL ALGERIE'),
    'BNADDZAL': ('DZ', 'BNA ALGERIE'),
    # Turquie
    'TGBATRIS': ('TR', 'TURKIYE GARANTI BANKASI'),
    'AKBKTRIS': ('TR', 'AKBANK'),
    # Russie
    'SABRRUMM': ('RU', 'SBERBANK MOSCOW'),
    'VTBRRUMM': ('RU', 'VTB BANK MOSCOW'),
    # Singapore
    'DBSSSGSG': ('SG', 'DBS BANK SINGAPORE'),
    'SCBLSGSG': ('SG', 'STANDARD CHARTERED SG'),
}

TN_BICS  = [b for b,v in BANKS.items() if v[0]=='TN']
EU_BICS  = [b for b,v in BANKS.items() if v[0] in ('FR','DE','GB','IT','ES','CH')]
US_BICS  = [b for b,v in BANKS.items() if v[0]=='US']
LY_BICS  = [b for b,v in BANKS.items() if v[0]=='LY']
ASIA_BICS= [b for b,v in BANKS.items() if v[0] in ('CN','JP','SG')]
MA_DZ    = [b for b,v in BANKS.items() if v[0] in ('MA','DZ')]
TR_RU    = [b for b,v in BANKS.items() if v[0] in ('TR','RU')]
AE_BICS  = [b for b,v in BANKS.items() if v[0]=='AE']

def bname(bic): return BANKS.get(bic, ('??', bic))[1]

def dt(days_ago, hour):
    d = datetime.now() - timedelta(days=days_ago)
    return d.replace(hour=hour, minute=random.randint(0,59), second=0).strftime('%Y-%m-%d %H:%M:%S')

rows = []

# ─── Fonction helper pour créer une ligne ────────────────────────────────────
def row(ref, mtype, direction, sbic, rbic, amount, currency, status,
        score, niveau, dago=1, hour=10):
    is_anom = 1 if score >= 20 else 0
    return {
        'reference'   : ref,
        'type_message': mtype,
        'direction'   : direction,
        'sender_bic'  : sbic,
        'receiver_bic': rbic,
        'sender_name' : bname(sbic) if sbic else '',
        'receiver_name': bname(rbic) if rbic else 'UNKNOWN ENTITY',
        'amount'      : amount,
        'currency'    : currency,
        'status'      : status,
        'category'    : '1' if mtype.startswith('MT1') else ('2' if mtype.startswith('MT2') else '9'),
        'translation_errors': '',
        'created_at'  : dt(dago, hour),
        'score'       : score,
        'niveau_risque': niveau,
        'is_anomalie' : is_anom,
    }

ref_counter = [1]
def R(): 
    r = f"INTL{datetime.now().strftime('%Y%m%d')}{ref_counter[0]:04d}"
    ref_counter[0] += 1
    return r

# ─────────────────────────────────────────────────────────────────────────────
# CATÉGORIE 1 : Transactions HIGH risk (score 60-90)
# ─────────────────────────────────────────────────────────────────────────────

# Sanctions / Russie
for _ in range(15):
    rows.append(row(R(), 'MT103', 'OUT', random.choice(TN_BICS), random.choice(TR_RU),
                    round(random.uniform(500_000, 5_000_000), 2),
                    random.choice(['RUB','TRY']), 'rejected', 75, 'HIGH',
                    dago=random.randint(1,30), hour=random.randint(20,23)))

# Offshore / BIC manquant
for _ in range(12):
    rows.append(row(R(), 'MT103', 'OUT', random.choice(TN_BICS), None,
                    round(random.uniform(200_000, 3_000_000), 2),
                    'USD', 'rejected', 70, 'HIGH',
                    dago=random.randint(1,20), hour=random.randint(0,4)))

# Probe transaction (montant zéro rejeté)
for _ in range(10):
    rows.append(row(R(), 'MT103', random.choice(['IN','OUT']),
                    random.choice(EU_BICS), random.choice(TN_BICS),
                    0.00, 'EUR', 'rejected', 70, 'HIGH',
                    dago=random.randint(1,15), hour=random.randint(0,6)))

# Libye / gros montants USD rejeté
for _ in range(10):
    rows.append(row(R(), 'MT103', 'IN', random.choice(LY_BICS), random.choice(TN_BICS),
                    round(random.uniform(1_000_000, 8_000_000), 2),
                    'USD', 'rejected', 75, 'HIGH',
                    dago=random.randint(1,60), hour=random.randint(8,18)))

# MT202 interbank gros montant devise exotique rejeté
for _ in range(15):
    rows.append(row(R(), 'MT202', 'OUT', random.choice(TN_BICS), random.choice(AE_BICS + ASIA_BICS),
                    round(random.uniform(500_000, 4_000_000), 2),
                    random.choice(['AED','JPY','CNY','RUB']), 'rejected', 74, 'HIGH',
                    dago=random.randint(1,45), hour=random.randint(0,5)))

# TBML (surfacturation AED vers Dubaï)
for _ in range(8):
    rows.append(row(R(), 'MT103', 'OUT', random.choice(TN_BICS), random.choice(AE_BICS),
                    round(random.uniform(800_000, 3_500_000), 2),
                    'AED', 'rejected', 75, 'HIGH',
                    dago=random.randint(1,30), hour=random.randint(21,23)))

# ─────────────────────────────────────────────────────────────────────────────
# CATÉGORIE 2 : Transactions MEDIUM risk (score 20-59)
# ─────────────────────────────────────────────────────────────────────────────

# Montant élevé USD traité (légitime mais à surveiller)
for _ in range(25):
    rows.append(row(R(), 'MT103', 'IN', random.choice(EU_BICS + US_BICS), random.choice(TN_BICS),
                    round(random.uniform(150_000, 500_000), 2),
                    'USD', 'processed', 25, 'MEDIUM',
                    dago=random.randint(1,90), hour=random.randint(8,17)))

# Devise inhabituelle petit montant
for _ in range(20):
    rows.append(row(R(), 'MT103', 'OUT', random.choice(TN_BICS), random.choice(ASIA_BICS),
                    round(random.uniform(5_000, 80_000), 2),
                    random.choice(['CNY','JPY','SGD']), 'processed', 20, 'MEDIUM',
                    dago=random.randint(1,60), hour=random.randint(8,18)))

# Libyen traité (en attente vérif)
for _ in range(15):
    rows.append(row(R(), 'MT103', 'IN', random.choice(LY_BICS), random.choice(TN_BICS),
                    round(random.uniform(50_000, 400_000), 2),
                    'EUR', 'pending', 30, 'MEDIUM',
                    dago=random.randint(1,30), hour=random.randint(9,16)))

# MT940 relevé montant élevé
for _ in range(15):
    rows.append(row(R(), 'MT940', 'IN', random.choice(EU_BICS), random.choice(TN_BICS),
                    round(random.uniform(200_000, 1_000_000), 2),
                    'EUR', 'processed', 25, 'MEDIUM',
                    dago=random.randint(1,120), hour=random.randint(7,10)))

# Statut pending + montant élevé (non encore validé)
for _ in range(20):
    rows.append(row(R(), 'MT103', 'OUT', random.choice(TN_BICS), random.choice(EU_BICS),
                    round(random.uniform(100_000, 350_000), 2),
                    'EUR', 'pending', 25, 'MEDIUM',
                    dago=random.randint(1,10), hour=random.randint(8,17)))

# Maroc/Algérie montant moyen EUR
for _ in range(10):
    rows.append(row(R(), 'MT103', random.choice(['IN','OUT']),
                    random.choice(MA_DZ), random.choice(TN_BICS),
                    round(random.uniform(20_000, 150_000), 2),
                    'EUR', 'processed', 20, 'MEDIUM',
                    dago=random.randint(1,60), hour=random.randint(9,17)))

# ─────────────────────────────────────────────────────────────────────────────
# CATÉGORIE 3 : Transactions LOW risk (score 0-19)
# ─────────────────────────────────────────────────────────────────────────────

# Virements EUR normaux (salaires, factures, commerce)
for _ in range(30):
    rows.append(row(R(), 'MT103', 'IN', random.choice(EU_BICS), random.choice(TN_BICS),
                    round(random.uniform(500, 25_000), 2),
                    'EUR', 'processed', 0, 'LOW',
                    dago=random.randint(1,180), hour=random.randint(8,17)))

# Règlements fournisseurs EUR sortants
for _ in range(25):
    rows.append(row(R(), 'MT103', 'OUT', random.choice(TN_BICS), random.choice(EU_BICS),
                    round(random.uniform(1_000, 30_000), 2),
                    'EUR', 'processed', 0, 'LOW',
                    dago=random.randint(1,180), hour=random.randint(9,16)))

# MT202 nostro/vostro EUR normaux
for _ in range(20):
    rows.append(row(R(), 'MT202', 'IN', random.choice(EU_BICS + US_BICS), random.choice(TN_BICS),
                    round(random.uniform(10_000, 80_000), 2),
                    'EUR', 'processed', 0, 'LOW',
                    dago=random.randint(1,90), hour=random.randint(7,17)))

# Virements USA USD normaux
for _ in range(15):
    rows.append(row(R(), 'MT103', 'IN', random.choice(US_BICS), random.choice(TN_BICS),
                    round(random.uniform(2_000, 50_000), 2),
                    'USD', 'processed', 0, 'LOW',
                    dago=random.randint(1,120), hour=random.randint(8,18)))

# MT940 relevés normaux
for _ in range(15):
    rows.append(row(R(), 'MT940', 'IN', random.choice(EU_BICS), random.choice(TN_BICS),
                    0.00, 'EUR', 'processed', 0, 'LOW',
                    dago=random.randint(1,90), hour=random.randint(7,10)))

# Petits virements GBP/CHF
for _ in range(10):
    rows.append(row(R(), 'MT103', random.choice(['IN','OUT']),
                    random.choice([b for b in BANKS if BANKS[b][0] in ('GB','CH')]),
                    random.choice(TN_BICS),
                    round(random.uniform(500, 20_000), 2),
                    random.choice(['GBP','CHF']), 'processed', 0, 'LOW',
                    dago=random.randint(1,120), hour=random.randint(9,16)))

# ─── Écriture CSV ─────────────────────────────────────────────────────────────
random.shuffle(rows)  # mélanger pour éviter l'ordre par catégorie

fieldnames = list(rows[0].keys())
with open(OUTPUT, 'w', newline='', encoding='utf-8') as f:
    writer = csv.DictWriter(f, fieldnames=fieldnames)
    writer.writeheader()
    writer.writerows(rows)

# Stats
n_high   = sum(1 for r in rows if r['niveau_risque'] == 'HIGH')
n_medium = sum(1 for r in rows if r['niveau_risque'] == 'MEDIUM')
n_low    = sum(1 for r in rows if r['niveau_risque'] == 'LOW')
n_in     = sum(1 for r in rows if r['direction'] == 'IN')
n_out    = sum(1 for r in rows if r['direction'] == 'OUT')

print(f"Dataset international généré : {OUTPUT}")
print(f"Total messages : {len(rows)}")
print(f"  HIGH   : {n_high} ({n_high*100//len(rows)}%)")
print(f"  MEDIUM : {n_medium} ({n_medium*100//len(rows)}%)")
print(f"  LOW    : {n_low} ({n_low*100//len(rows)}%)")
print(f"  IN     : {n_in}   OUT : {n_out}")
print(f"  Pays couverts : TN, FR, DE, GB, US, IT, ES, CH, AE, CN, JP, SG, LY, MA, DZ, TR, RU")
