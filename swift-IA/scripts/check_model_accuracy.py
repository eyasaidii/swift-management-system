import pickle, json, sys

with open('/app/model/training_stats.json') as f:
    stats = json.load(f)

with open('/app/model/xgb_regressor.pkl','rb') as f:
    reg = pickle.load(f)

with open('/app/model/xgb_classifier.pkl','rb') as f:
    clf = pickle.load(f)

with open('/app/model/isolation_forest.pkl','rb') as f:
    iso = pickle.load(f)

p = reg.get_params()
p2 = clf.get_params()

print("=== XGBoost Regresseur (score 0-100) ===")
print(f"  n_estimators  : {p.get('n_estimators')}")
print(f"  max_depth     : {p.get('max_depth')}")
print(f"  learning_rate : {p.get('learning_rate')}")
print(f"  subsample     : {p.get('subsample')}")
print()
print("=== XGBoost Classifieur (is_anomaly) ===")
print(f"  n_estimators  : {p2.get('n_estimators')}")
print(f"  max_depth     : {p2.get('max_depth')}")
print(f"  learning_rate : {p2.get('learning_rate')}")
print(f"  scale_pos_weight: {p2.get('scale_pos_weight')}")
print()
print("=== Isolation Forest (non-supervise) ===")
print(f"  n_estimators  : {iso.get_params().get('n_estimators')}")
print(f"  contamination : {iso.get_params().get('contamination')}")
print()
print("=== Dataset d'entrainement ===")
print(f"  Echantillons  : {stats['n_samples']:,}")
print(f"  Features      : {stats['feature_count']}")
print(f"  Score moyen   : {stats['score_mean']:.1f}/100")
print(f"  Ecart-type    : {stats['score_std']:.1f}")
print(f"  Taux anomalie : {stats['anomaly_rate']*100:.1f}%")
print(f"  Version       : v2.0-20260501092916")
print(f"  Entraine le   : {stats['trained_at']}")
print()

# Simulation sur les types de signaux connus
import sys
sys.path.insert(0, '/app')
from app.services.detector import detector

test_cases = [
    ("MT103 1.2M USD rejected BIC_MANQUANT", {"amount":1200000,"currency":"USD","status":"rejected","sender_bic":None,"type_message":"MT103","direction":"OUT","created_at":"2026-05-04 02:00:00"}),
    ("MT103 3500 EUR processed normal",       {"amount":3500,"currency":"EUR","status":"processed","sender_bic":"SOGEFRPP","type_message":"MT103","direction":"IN","created_at":"2026-05-05 09:00:00"}),
    ("MT103 0 EUR rejected probe",            {"amount":0,"currency":"EUR","status":"rejected","sender_bic":"BNPAFRPP","type_message":"MT103","direction":"IN","created_at":"2026-05-05 04:00:00"}),
    ("MT103 280k USD processed legit",        {"amount":280000,"currency":"USD","status":"processed","sender_bic":"DEUTDEDB","type_message":"MT103","direction":"IN","created_at":"2026-05-02 10:30:00"}),
]

print("=== Test rapide sur 4 cas ===")
for name, payload in test_cases:
    r = detector.predict(payload)
    score = int(round(r['score']*100))
    niveau = "HIGH" if score>=60 else ("MEDIUM" if score>=20 else "LOW")
    rules = [x['rule'] for x in r.get('reasons',[])]
    print(f"  [{niveau:6s} {score:3d}]  {name}")
    print(f"           Regles: {', '.join(rules) if rules else '(aucune)'}")
