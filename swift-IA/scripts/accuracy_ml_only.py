"""
Mesure l'accuracy ML PURE sur les données Oracle réelles.

Sans aucune règle métier PHP (STATUT_REJETE, MONTANT_ELEVE, etc.)
Uniquement : XGBoost Regresseur + XGBoost Classifieur + Isolation Forest

Usage :
  docker exec btl_python_api python3 /app/scripts/accuracy_ml_only.py
"""

import sys, os, numpy as np, pandas as pd
sys.path.insert(0, '/app')

from sklearn.metrics import (
    accuracy_score, classification_report, confusion_matrix,
    mean_absolute_error, r2_score
)
from app.services.preprocessor import extract_features_batch
from app.services.detector import detector
from app.models.schemas import SwiftMessageRequest

print("=" * 62)
print("  ACCURACY ML PURE — SANS RÈGLES MÉTIER — DONNÉES ORACLE")
print("=" * 62)

# ── 1. Charger les données Oracle
CSV = '/app/data/oracle_training_data.csv'
df  = pd.read_csv(CSV,
      usecols=lambda c: c not in ['anomaly_raisons'],
      on_bad_lines='skip')
print(f"\nMessages Oracle chargés : {len(df)}")

# Nettoyer NaN
for col in df.columns:
    if df[col].dtype == object:
        df[col] = df[col].fillna('')
    else:
        df[col] = df[col].fillna(0)

# Labels réels
y_score_real = pd.to_numeric(df['anomaly_score'], errors='coerce').fillna(0).values.astype(float)
y_class_real = pd.to_numeric(df['is_anomaly'],    errors='coerce').fillna(0).values.astype(int)

n_high   = (y_score_real >= 60).sum()
n_medium = ((y_score_real >= 20) & (y_score_real < 60)).sum()
n_low    = (y_score_real < 20).sum()
print(f"Labels Oracle  →  HIGH:{n_high}  MEDIUM:{n_medium}  LOW:{n_low}")

# ── 2. Convertir messages → features
msgs = detector._csv_to_messages(df)
df_feat = extract_features_batch(msgs)
X = np.nan_to_num(df_feat.values.astype(float), nan=0.0)
X_names = detector.training_stats.get('feature_names', list(df_feat.columns))
X_aligned = np.array([[
    df_feat[n].iloc[i] if n in df_feat.columns else 0.0
    for n in X_names
] for i in range(len(df_feat))], dtype=float)
X_aligned = np.nan_to_num(X_aligned, nan=0.0)
X_scaled = detector.scaler.transform(X_aligned)

# ── 3. Prédiction ML PURE (sans règles)
reg = detector.xgb_regressor
clf = detector.xgb_classifier
iso = detector.isolation_forest

y_pred_score = np.clip(reg.predict(X_scaled), 0, 100)
y_pred_proba = clf.predict_proba(X_scaled)[:, 1]
y_pred_class = (y_pred_proba >= 0.5).astype(int)
y_pred_if    = iso.predict(X_scaled)   # -1=anomalie, 1=normal
y_pred_if_bin = (y_pred_if == -1).astype(int)

# Niveaux prédits ML
def to_level(score): return 'HIGH' if score >= 60 else ('MEDIUM' if score >= 20 else 'LOW')
def to_level_int(score): return 2 if score >= 60 else (1 if score >= 20 else 0)

y_level_real = np.array([to_level_int(s) for s in y_score_real])
y_level_pred = np.array([to_level_int(s) for s in y_pred_score])

# ── 4. Résultats
print("\n" + "─" * 62)
print("  [A] XGBoost Régresseur — Prédiction du score (0-100)")
print("─" * 62)
mae  = mean_absolute_error(y_score_real, y_pred_score)
r2   = r2_score(y_score_real, y_pred_score)
corr = np.corrcoef(y_score_real, y_pred_score)[0, 1]
acc_level = accuracy_score(y_level_real, y_level_pred) * 100
print(f"  MAE  (erreur moyenne score)    : {mae:.1f} points")
print(f"  R²   (coeff détermination)     : {r2:.4f}")
print(f"  Corr (Pearson)                 : {corr:.4f}")
print(f"  Accuracy niveau HIGH/MED/LOW   : {acc_level:.1f}%")

print("\n  Quelques prédictions (réel → prédit ML) :")
print(f"  {'Réf':20s}  {'Réel':6s}  {'ML':6s}  {'Écart':>6s}")
for i in range(min(15, len(df))):
    ref  = str(df['reference'].iloc[i])[:20]
    real = y_score_real[i]
    pred = y_pred_score[i]
    rl   = to_level(real)[0]  # H/M/L
    pl   = to_level(pred)[0]
    ok   = "✔" if rl == pl else "✘"
    print(f"  {ref:20s}  {real:5.0f}{rl}  {pred:5.0f}{pl}  {ok}  {pred-real:+.0f}")

print("\n" + "─" * 62)
print("  [B] XGBoost Classifieur — Détection anomalie (oui/non)")
print("─" * 62)
acc_clf = accuracy_score(y_class_real, y_pred_class) * 100
print(f"  Accuracy globale : {acc_clf:.1f}%")
print()
print(classification_report(y_class_real, y_pred_class,
      target_names=['NON-ANOMALIE', 'ANOMALIE'], zero_division=0))

cm = confusion_matrix(y_class_real, y_pred_class)
print(f"  Matrice de confusion :")
print(f"  {'':14s}  Prédit NON  Prédit OUI")
print(f"  Réel NON       {cm[0,0]:6d}      {cm[0,1]:6d}   ← Faux positifs: {cm[0,1]}")
print(f"  Réel OUI       {cm[1,0]:6d}      {cm[1,1]:6d}   ← Faux négatifs: {cm[1,0]}")

print("\n" + "─" * 62)
print("  [C] Isolation Forest — Détection non supervisée")
print("─" * 62)
acc_if = accuracy_score(y_class_real, y_pred_if_bin) * 100
print(f"  Accuracy : {acc_if:.1f}%")
print()
print(classification_report(y_class_real, y_pred_if_bin,
      target_names=['NON-ANOMALIE', 'ANOMALIE'], zero_division=0))

print("\n" + "=" * 62)
print("  BILAN — ACCURACY ML PURE (sans règles métier)")
print("=" * 62)
print(f"  XGBoost Régresseur  (score 0-100) : MAE={mae:.1f}  R²={r2:.3f}")
print(f"  XGBoost Classifieur (anomalie ?)  : {acc_clf:.1f}% accuracy")
print(f"  Isolation Forest    (non-supervisé): {acc_if:.1f}% accuracy")
print(f"  Accuracy niveau HIGH/MEDIUM/LOW   : {acc_level:.1f}%")

# ── 5. Analyse des erreurs
errors = [(i, y_score_real[i], y_pred_score[i])
          for i in range(len(y_score_real))
          if to_level(y_score_real[i]) != to_level(y_pred_score[i])]
if errors:
    print(f"\n  Erreurs de niveau ({len(errors)} messages mal classés) :")
    for i, real, pred in errors[:10]:
        ref = str(df['reference'].iloc[i])
        print(f"    {ref:25s}  réel={real:.0f}({to_level(real)[:3]})  prédit={pred:.0f}({to_level(pred)[:3]})")
else:
    print("\n  Aucune erreur de niveau !")
print("=" * 62)
