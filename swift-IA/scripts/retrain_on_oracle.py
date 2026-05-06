"""
Ré-entraîne le modèle IA sur les vraies données Oracle exportées.

Étapes :
  1. Charge oracle_training_data.csv (export PHP depuis Oracle)
  2. Adapte les colonnes au format attendu par detector.py
  3. Fusionne avec les données synthétiques existantes (20 000 lignes)
  4. Entraîne XGBRegressor + XGBClassifier + IsolationForest
  5. Sauvegarde les nouveaux modèles
  6. Affiche les métriques de cross-validation

Usage :
  python scripts/retrain_on_oracle.py
"""

import sys
import os
import json
import numpy as np
import pandas as pd
from datetime import datetime

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from app.services.detector import detector

ORACLE_CSV  = os.path.join(os.path.dirname(__file__), '..', 'data', 'oracle_training_data.csv')
SYNTH_CSV   = os.path.join(os.path.dirname(__file__), '..', 'data', 'swift_messages.csv')
OUT_CSV     = os.path.join(os.path.dirname(__file__), '..', 'data', 'oracle_messages.csv')

print("=" * 60)
print("  RÉ-ENTRAÎNEMENT MODÈLE IA — DONNÉES ORACLE RÉELLES")
print("=" * 60)

# ─── 1. Charger et adapter le CSV Oracle ─────────────────────────────────────
print(f"\n[1/4] Chargement : {ORACLE_CSV}")
# anomaly_raisons contient du JSON avec virgules → on lit uniquement les colonnes utiles
usecols = ['id','reference','type_message','direction','sender_bic','receiver_bic',
           'sender_name','receiver_name','amount','currency','status','category',
           'translation_errors','created_at','anomaly_score','anomaly_niveau','is_anomaly']
df_oracle = pd.read_csv(ORACLE_CSV, usecols=lambda c: c in usecols, on_bad_lines='skip')
print(f"      {len(df_oracle)} messages Oracle chargés")

# Renommer les colonnes pour correspondre au format attendu par detector.py
rename_map = {
    'anomaly_score'  : 'score',
    'anomaly_niveau' : 'niveau_risque',
    'anomaly_raisons': 'raisons',
    'is_anomaly'     : 'is_anomalie',
    'category'       : 'categorie',
}
df_oracle = df_oracle.rename(columns=rename_map)

# Remplir les scores manquants à 0
df_oracle['score']       = pd.to_numeric(df_oracle['score'],       errors='coerce').fillna(0)
df_oracle['is_anomalie'] = pd.to_numeric(df_oracle['is_anomalie'], errors='coerce').fillna(0).astype(int)

# Stats des labels
n_labeled = (df_oracle['score'] > 0).sum()
n_high    = (df_oracle['score'] >= 60).sum()
n_medium  = ((df_oracle['score'] >= 20) & (df_oracle['score'] < 60)).sum()
n_low     = (df_oracle['score'] < 20).sum()

print(f"\n      Distribution des anomalies Oracle :")
print(f"        Avec score > 0 : {n_labeled}/{len(df_oracle)}")
print(f"        HIGH   (≥60)   : {n_high}")
print(f"        MEDIUM (20-59) : {n_medium}")
print(f"        LOW    (<20)   : {n_low}")

# ─── 2. Charger les données synthétiques ─────────────────────────────────────
print(f"\n[2/4] Chargement synthétique : {SYNTH_CSV}")
df_synth = pd.read_csv(SYNTH_CSV)
print(f"      {len(df_synth)} messages synthétiques")

# ─── 3. Fusionner et sauvegarder en oracle_messages.csv ──────────────────────
print(f"\n[3/4] Fusion et sauvegarde → oracle_messages.csv")

# S'assurer que les colonnes Oracle sont compatibles avec synthétiques
common_cols = [c for c in df_synth.columns if c in df_oracle.columns]
df_merged = pd.concat([df_synth, df_oracle[common_cols]], ignore_index=True)

# Sauvegarder sous le nom attendu par detector.py
df_oracle.to_csv(OUT_CSV, index=False)
print(f"      Sauvegardé : {OUT_CSV}")
print(f"      Total fusion : {len(df_merged)} lignes ({len(df_synth)} synthétiques + {len(df_oracle)} Oracle)")

# ─── 4. Lancer l'entraînement ─────────────────────────────────────────────────
print(f"\n[4/4] Entraînement du modèle en cours...")
t0 = datetime.now()

n_samples, version = detector.train()

elapsed = (datetime.now() - t0).seconds
print(f"\n✔ Modèle entraîné en {elapsed}s")
print(f"  Échantillons : {n_samples:,}")
print(f"  Version      : {version}")

# ─── Métriques cross-validation ──────────────────────────────────────────────
print("\n" + "=" * 60)
print("  VALIDATION CROISÉE — CROSS-VALIDATION 5 FOLDS")
print("=" * 60)

from sklearn.model_selection import cross_val_score, KFold
from sklearn.metrics import mean_absolute_error, r2_score, accuracy_score, classification_report
from app.services.preprocessor import extract_features_batch

# Utiliser directement le scaler et les modèles du détecteur (déjà en mémoire)
scaler = detector.scaler
reg    = detector.xgb_regressor
clf    = detector.xgb_classifier

# Construire features depuis le CSV Oracle via la méthode interne du détecteur
print(f"\nConstruction des features Oracle ({len(df_oracle)} lignes)...")

# Nettoyer les NaN avant conversion
df_clean = df_oracle.copy()
for col in df_clean.columns:
    if df_clean[col].dtype == object:
        df_clean[col] = df_clean[col].fillna('')
    else:
        df_clean[col] = df_clean[col].fillna(0)

# Utiliser _csv_to_messages du détecteur (déjà prévu pour ce cas)
msgs = detector._csv_to_messages(df_clean)
print(f"  Messages convertis : {len(msgs)}")

df_feat = extract_features_batch(msgs)
X = np.nan_to_num(df_feat.values.astype(float), nan=0.0)
X_scaled = scaler.transform(X)

y_score = pd.to_numeric(df_oracle['score'],       errors='coerce').fillna(0).values.astype(float)
y_class = pd.to_numeric(df_oracle['is_anomalie'],  errors='coerce').fillna(0).values.astype(int)
from sklearn.model_selection import cross_val_predict
kf = KFold(n_splits=5, shuffle=True, random_state=42)

print("\n--- XGBoost Régresseur (prédiction du score) ---")
if len(X_scaled) >= 5:
    y_pred_score = cross_val_predict(reg, X_scaled, y_score, cv=kf)
    mae  = mean_absolute_error(y_score, y_pred_score)
    r2   = r2_score(y_score, y_pred_score)
    print(f"  MAE (erreur moyenne)  : {mae:.2f} points sur 100")
    print(f"  R² (coeff détermin.)  : {r2:.4f}  (1.0 = parfait)")
    correct_level = 0
    for true_s, pred_s in zip(y_score, y_pred_score):
        def level(s): return 'HIGH' if s>=60 else ('MEDIUM' if s>=20 else 'LOW')
        if level(true_s) == level(pred_s): correct_level += 1
    acc_level = correct_level / len(y_score) * 100
    print(f"  Accuracy niveau (HIGH/MEDIUM/LOW) : {acc_level:.1f}%")
else:
    # Trop peu pour CV → simple train/test
    y_pred_score = reg.predict(X_scaled)
    mae  = mean_absolute_error(y_score, y_pred_score)
    r2   = r2_score(y_score, y_pred_score)
    print(f"  MAE  : {mae:.2f}  (train set — trop peu pour cross-val)")
    print(f"  R²   : {r2:.4f}")

print("\n--- XGBoost Classifieur (détection anomalie oui/non) ---")
if len(X_scaled) >= 5:
    y_pred_class = cross_val_predict(clf, X_scaled, y_class, cv=kf)
else:
    y_pred_class = clf.predict(X_scaled)

acc = accuracy_score(y_class, y_pred_class) * 100
print(f"  Accuracy globale      : {acc:.1f}%")
print(f"\n  Rapport détaillé :")
print(classification_report(y_class, y_pred_class,
      target_names=['NON-ANOMALIE', 'ANOMALIE'], zero_division=0))

# ─── Test rapide sur 5 messages représentatifs ──────────────────────────────
print("=" * 60)
print("  TEST POST-ENTRAÎNEMENT — 5 SCÉNARIOS RAPIDES")
print("=" * 60)

test_cases = [
    ("MT103 1.2M USD rejeté BIC_MANQUANT [HIGH]",
     {"amount":1200000,"currency":"USD","status":"rejected","sender_bic":None,
      "type_message":"MT103","direction":"OUT","created_at":"2026-05-04 02:00:00",
      "receiver_bic":"OFFSHKYY","sender_name":"BTL BANK","receiver_name":"OFFSHORE LTD"}),
    ("MT202 3.5M RUB rejeté sanctions [HIGH]",
     {"amount":3500000,"currency":"RUB","status":"rejected","sender_bic":"STBKTNTT",
      "type_message":"MT202","direction":"OUT","created_at":"2026-05-05 23:45:00",
      "receiver_bic":"SBERRUММ","sender_name":"STB BANK","receiver_name":"SBERBANK"}),
    ("MT103 0 EUR rejeté probe [HIGH]",
     {"amount":0,"currency":"EUR","status":"rejected","sender_bic":"BNPAFRPP",
      "type_message":"MT103","direction":"IN","created_at":"2026-05-05 04:00:00",
      "receiver_bic":"BTLMTNTT","sender_name":"BNP PARIBAS","receiver_name":"BTL BANK"}),
    ("MT103 280k USD traité légal [MEDIUM]",
     {"amount":280000,"currency":"USD","status":"processed","sender_bic":"DEUTDEDB",
      "type_message":"MT103","direction":"IN","created_at":"2026-05-02 10:30:00",
      "receiver_bic":"BTLMTNTT","sender_name":"DEUTSCHE BANK","receiver_name":"BTL BANK"}),
    ("MT103 3500 EUR salaire normal [LOW]",
     {"amount":3500,"currency":"EUR","status":"processed","sender_bic":"SOGEFRPP",
      "type_message":"MT103","direction":"IN","created_at":"2026-05-05 09:00:00",
      "receiver_bic":"BTLMTNTT","sender_name":"SOCIETE GENERALE","receiver_name":"BTL CLIENT"}),
]

from app.models.schemas import SwiftMessageRequest

expected = ["HIGH", "HIGH", "HIGH", "MEDIUM", "LOW"]
pass_count = 0

for (name, payload), exp in zip(test_cases, expected):
    try:
        msg = SwiftMessageRequest(**payload)
        r = detector.predict(msg)
    except Exception as e:
        print(f"  [ERR]  {name} → {e}")
        continue
    s = int(round(r.score * 100))
    n = "HIGH" if s >= 60 else ("MEDIUM" if s >= 20 else "LOW")
    rules = [x.rule for x in (r.reasons or [])]
    ok = (n == exp)
    if ok: pass_count += 1
    icon = "✔ PASS" if ok else "✘ FAIL"
    print(f"  [{icon}]  Score={s:3d}  Obtenu={n:6s}  Attendu={exp:6s}  | {name}")
    print(f"          Règles: {', '.join(rules) if rules else '(aucune)'}")

print(f"\n  RÉSULTAT FINAL : {pass_count}/{len(test_cases)} ({int(pass_count/len(test_cases)*100)}%)")
print("=" * 60)
print("Ré-entraînement terminé ✔")
print(f"Version modèle : {version}")
print("=" * 60)
