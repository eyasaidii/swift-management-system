"""
Script d'évaluation de la précision des modèles IA — BTL Swift Platform
Usage : python scripts/evaluate_models.py [data/swift_messages.csv]

Métriques calculées :
  - XGBClassifier  : Accuracy, Precision, Recall, F1, ROC-AUC, Matrice de confusion
  - XGBRegressor   : MAE, RMSE, R²
  - IsolationForest: taux de détection vs labels réels

Utilise une séparation 80/20 train/test pour éviter l'overfitting.
"""

import sys
import os

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

import numpy as np
import pandas as pd
import joblib

from sklearn.metrics import (
    accuracy_score,
    precision_score,
    recall_score,
    f1_score,
    roc_auc_score,
    confusion_matrix,
    mean_absolute_error,
    mean_squared_error,
    r2_score,
)
from sklearn.model_selection import train_test_split

from app.services.preprocessor import extract_features_batch
from app.models.schemas import SwiftMessageRequest
from app.config import settings


def print_section(title: str):
    print(f"\n{'='*60}")
    print(f"  {title}")
    print('='*60)


def csv_row_to_request(row) -> SwiftMessageRequest:
    def sv(col, default=""):
        val = row.get(col)
        if val is None or (isinstance(val, float) and np.isnan(val)):
            return default
        return str(val).strip()

    def fv(col, default=0.0):
        try:
            f = float(row.get(col, default))
            return default if (np.isnan(f) or np.isinf(f)) else f
        except (TypeError, ValueError):
            return default

    return SwiftMessageRequest(
        id=None,
        type_message       = sv("type_message") or "OTHER",
        direction          = sv("direction") or "OUT",
        sender_bic         = sv("sender_bic") or None,
        receiver_bic       = sv("receiver_bic") or None,
        sender_name        = sv("sender_name") or None,
        receiver_name      = sv("receiver_name") or None,
        amount             = fv("amount"),
        currency           = sv("currency") or "EUR",
        value_date         = sv("value_date") or None,
        created_at         = sv("created_at") or None,
        reference          = sv("reference") or None,
        status             = sv("status") or None,
        translation_errors = sv("translation_errors") or None,
        category           = sv("category") or None,
        sender_country     = sv("sender_country") or None,
        receiver_country   = sv("receiver_country") or None,
    )


def main():
    csv_path = sys.argv[1] if len(sys.argv) > 1 else "data/swift_messages.csv"

    if not os.path.exists(csv_path):
        print(f"ERREUR : fichier '{csv_path}' introuvable.")
        sys.exit(1)

    # ─────────────────────────────────────────────
    # Chargement des données
    # ─────────────────────────────────────────────
    print_section("CHARGEMENT DES DONNÉES")
    raw = pd.read_csv(csv_path)
    print(f"  Lignes       : {len(raw):,}")
    print(f"  Colonnes     : {len(raw.columns)}")

    if "is_anomalie" not in raw.columns or "score" not in raw.columns:
        print("ERREUR : le CSV doit contenir les colonnes 'is_anomalie' et 'score'.")
        sys.exit(1)

    y_class = pd.to_numeric(raw["is_anomalie"], errors="coerce").fillna(0).astype(int).values
    y_score = pd.to_numeric(raw["score"], errors="coerce").fillna(0).clip(0, 100).values.astype(float)
    print(f"  Anomalies    : {y_class.sum():,} ({y_class.mean()*100:.1f}%)")
    print(f"  Score moyen  : {y_score.mean():.1f} ± {y_score.std():.1f}")

    # Extraction des features
    print("\n  Extraction des features...")
    LABEL_COLS = {"score", "niveau_risque", "is_anomalie", "raisons",
                  "id", "message_id", "verifie_par", "verifie_at", "updated_at"}
    feat_cols = [c for c in raw.columns if c.lower() not in LABEL_COLS]
    msgs = [csv_row_to_request(row) for _, row in raw[feat_cols].iterrows()]
    df_feat = extract_features_batch(msgs)

    X = df_feat.values.astype(float)
    X = np.nan_to_num(X, nan=0.0, posinf=0.0, neginf=0.0)
    print(f"  Features     : {X.shape[1]}")

    # Split 80/20 stratifié
    X_train, X_test, yc_train, yc_test, ys_train, ys_test = train_test_split(
        X, y_class, y_score, test_size=0.20, random_state=42, stratify=y_class
    )
    print(f"  Train        : {len(X_train):,}")
    print(f"  Test         : {len(X_test):,}")

    # ─────────────────────────────────────────────
    # Chargement des modèles
    # ─────────────────────────────────────────────
    print_section("CHARGEMENT DES MODÈLES")
    model_dir = settings.MODEL_DIR

    scaler_path  = os.path.join(model_dir, "scaler.pkl")
    xgbr_path    = os.path.join(model_dir, "xgb_regressor.pkl")
    xgbc_path    = os.path.join(model_dir, "xgb_classifier.pkl")
    if_path      = os.path.join(model_dir, "isolation_forest.pkl")
    ver_path     = os.path.join(model_dir, "version.txt")

    for name, path in [("scaler.pkl", scaler_path), ("xgb_regressor.pkl", xgbr_path),
                       ("xgb_classifier.pkl", xgbc_path), ("isolation_forest.pkl", if_path)]:
        status = "✓" if os.path.exists(path) else "✗ MANQUANT"
        print(f"  {name:30s} {status}")

    if not all(os.path.exists(p) for p in [scaler_path, xgbr_path, xgbc_path]):
        print("\nERREUR : modèles manquants. Lance d'abord : python train.py")
        sys.exit(1)

    scaler           = joblib.load(scaler_path)
    xgb_regressor    = joblib.load(xgbr_path)
    xgb_classifier   = joblib.load(xgbc_path)
    isolation_forest = joblib.load(if_path) if os.path.exists(if_path) else None

    X_test_sc = scaler.transform(X_test)

    # ─────────────────────────────────────────────
    # XGBClassifier
    # ─────────────────────────────────────────────
    print_section("XGBClassifier — Détection d'anomalies (0/1)")

    yc_pred       = xgb_classifier.predict(X_test_sc)
    yc_pred_proba = xgb_classifier.predict_proba(X_test_sc)[:, 1]

    acc     = accuracy_score(yc_test, yc_pred)
    prec    = precision_score(yc_test, yc_pred, zero_division=0)
    rec     = recall_score(yc_test, yc_pred, zero_division=0)
    f1      = f1_score(yc_test, yc_pred, zero_division=0)
    roc_auc = roc_auc_score(yc_test, yc_pred_proba)
    cm      = confusion_matrix(yc_test, yc_pred)

    print(f"  Accuracy     : {acc*100:.2f}%")
    print(f"  Precision    : {prec*100:.2f}%")
    print(f"  Recall       : {rec*100:.2f}%")
    print(f"  F1-Score     : {f1*100:.2f}%")
    print(f"  ROC-AUC      : {roc_auc:.4f}")
    print(f"\n  Matrice de confusion :")
    print(f"                  Prédit Normal  Prédit Anomalie")
    print(f"  Réel Normal         {cm[0][0]:>6}         {cm[0][1]:>6}")
    print(f"  Réel Anomalie       {cm[1][0]:>6}         {cm[1][1]:>6}")

    fn_rate = cm[1][0] / (cm[1][0] + cm[1][1]) if (cm[1][0] + cm[1][1]) > 0 else 0
    print(f"\n  Taux de faux négatifs (anomalies manquées) : {fn_rate*100:.2f}%")

    # ─────────────────────────────────────────────
    # XGBRegressor
    # ─────────────────────────────────────────────
    print_section("XGBRegressor — Prédiction du score (0-100)")

    ys_pred = xgb_regressor.predict(X_test_sc)
    mae     = mean_absolute_error(ys_test, ys_pred)
    rmse    = float(np.sqrt(mean_squared_error(ys_test, ys_pred)))
    r2      = r2_score(ys_test, ys_pred)

    print(f"  MAE  (erreur moyenne absolue) : {mae:.2f} pts")
    print(f"  RMSE (erreur quadratique)     : {rmse:.2f} pts")
    print(f"  R²   (variance expliquée)     : {r2:.4f}")

    low    = int(np.sum(ys_pred < 20))
    medium = int(np.sum((ys_pred >= 20) & (ys_pred < 60)))
    high   = int(np.sum(ys_pred >= 60))
    total  = len(ys_pred)
    print(f"\n  Distribution prédite sur test :")
    print(f"    LOW    (< 20)  : {low:>5} ({low/total*100:.1f}%)")
    print(f"    MEDIUM (20-60) : {medium:>5} ({medium/total*100:.1f}%)")
    print(f"    HIGH   (>= 60) : {high:>5} ({high/total*100:.1f}%)")

    # ─────────────────────────────────────────────
    # IsolationForest
    # ─────────────────────────────────────────────
    if isolation_forest:
        print_section("IsolationForest — Détection non supervisée (fallback)")

        if_pred   = isolation_forest.predict(X_test_sc)
        if_labels = np.where(if_pred == -1, 1, 0)

        if_acc  = accuracy_score(yc_test, if_labels)
        if_prec = precision_score(yc_test, if_labels, zero_division=0)
        if_rec  = recall_score(yc_test, if_labels, zero_division=0)
        if_f1   = f1_score(yc_test, if_labels, zero_division=0)

        print(f"  Accuracy     : {if_acc*100:.2f}%")
        print(f"  Precision    : {if_prec*100:.2f}%")
        print(f"  Recall       : {if_rec*100:.2f}%")
        print(f"  F1-Score     : {if_f1*100:.2f}%")
        print(f"  (non supervisé — entraîné sans labels)")

    # ─────────────────────────────────────────────
    # Résumé final
    # ─────────────────────────────────────────────
    version = open(ver_path).read().strip() if os.path.exists(ver_path) else "inconnu"

    print_section("RÉSUMÉ FINAL")
    print(f"  Dataset       : {len(raw):,} échantillons  |  Test set : {len(X_test):,}")
    print(f"  Version       : {version}")
    print()
    print(f"  ┌──────────────────────────────────────────────┐")
    print(f"  │ XGBClassifier Accuracy  : {acc*100:>6.2f}%             │")
    print(f"  │ XGBClassifier F1        : {f1*100:>6.2f}%             │")
    print(f"  │ XGBClassifier ROC-AUC   : {roc_auc:>7.4f}             │")
    print(f"  │ XGBRegressor  RMSE      : {rmse:>6.2f} pts           │")
    print(f"  │ XGBRegressor  R²        : {r2:>7.4f}             │")
    print(f"  └──────────────────────────────────────────────┘")
    print()
    if acc >= 0.90 and f1 >= 0.85:
        print("  ✅ MODÈLE EXCELLENT — prêt pour la production")
    elif acc >= 0.80 and f1 >= 0.70:
        print("  ✅ MODÈLE BON — acceptable pour la production")
    elif acc >= 0.70:
        print("  ⚠️  MODÈLE MOYEN — envisager un réentraînement")
    else:
        print("  ❌ MODÈLE FAIBLE — réentraînement nécessaire")
    print()


if __name__ == "__main__":
    main()
