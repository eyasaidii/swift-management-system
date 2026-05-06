"""
Moteur de détection d'anomalies SWIFT — BTL Bank.

Architecture deux tables Oracle :
  MESSAGES_SWIFT   → colonnes features  (input du modèle)
  ANOMALIES_SWIFT  → colonnes labels    (score 0-100, is_anomalie 0/1)

Modèles :
  ① XGBRegressor   — prédit le SCORE (0-100) depuis ANOMALIES_SWIFT
  ② XGBClassifier  — prédit IS_ANOMALIE (0/1) depuis ANOMALIES_SWIFT
  ③ IsolationForest — détection non supervisée (fallback / complément)

Formule hybride (compatible AnomalyService.php) :
  score_final = min( (score_regles × 0.60) + (score_ml × 0.40) , 100 )
  → score_ml  = XGBRegressor output (ou XGBClassifier * 100 en fallback)
"""

import os
import json
import logging
import threading
from datetime import datetime
from typing import Optional, List, Dict, Tuple

import numpy as np
import pandas as pd
import joblib

from sklearn.ensemble import IsolationForest
from sklearn.preprocessing import StandardScaler
from xgboost import XGBRegressor, XGBClassifier

from app.config import settings
from app.models.schemas import SwiftMessageRequest, PredictResponse, AnomalyReason
from app.services.preprocessor import (
    extract_features,
    extract_features_batch,
    get_feature_names,
    check_business_rules,
    # aliases legacy
    check_missing_fields,
    check_high_risk_country,
)

logger = logging.getLogger(__name__)


class AnomalyDetector:
    """
    Détecteur singleton chargé au démarrage du service FastAPI.

    Entraînement (train()) :
      - Charge le CSV data/swift_messages.csv  (JOIN MESSAGES_SWIFT + ANOMALIES_SWIFT)
      - Colonnes features  : tout sauf [score, niveau_risque, is_anomalie, raisons]
      - Colonnes labels    : score (0-100), is_anomalie (0/1)
      - Entraîne XGBRegressor  → prédit le score
      - Entraîne XGBClassifier → prédit is_anomalie
      - Entraîne IsolationForest → détection non supervisée (fallback)

    Prédiction (predict()) :
      - Reçoit seulement les champs MESSAGES_SWIFT (pas de labels)
      - Renvoie score_ml (0-100) + is_anomaly + reasons
    """

    # Colonnes du CSV qui sont des labels (ANOMALIES_SWIFT) — ne jamais utiliser comme features
    LABEL_COLS = {"score", "niveau_risque", "is_anomalie", "raisons",
                  "id", "message_id", "verifie_par", "verifie_at", "updated_at"}

    def __init__(self):
        self._lock = threading.Lock()

        # ── Modèles
        self.isolation_forest: Optional[IsolationForest] = None
        self.xgb_regressor:   Optional[XGBRegressor]    = None
        self.xgb_classifier:  Optional[XGBClassifier]   = None
        self.scaler:          Optional[StandardScaler]   = None

        # ── Métadonnées
        self.training_stats: Dict = {}
        self.model_version: str   = "not-trained"
        self.feature_names: List[str] = get_feature_names()

        # ── Log runtime
        self.prediction_log: List[Dict] = []

        self._load_models()

    # ──────────────────────────────────────────
    # Chemins
    # ──────────────────────────────────────────

    def _path(self, name: str) -> str:
        return os.path.join(settings.MODEL_DIR, name)

    # ──────────────────────────────────────────
    # Chargement depuis disque
    # ──────────────────────────────────────────

    def _load_models(self):
        try:
            if_path   = self._path("isolation_forest.pkl")
            sc_path   = self._path("scaler.pkl")
            xgbr_path = self._path("xgb_regressor.pkl")
            xgbc_path = self._path("xgb_classifier.pkl")
            stats_path = self._path("training_stats.json")
            ver_path   = self._path("version.txt")

            if os.path.exists(if_path) and os.path.exists(sc_path):
                self.isolation_forest = joblib.load(if_path)
                self.scaler           = joblib.load(sc_path)
                logger.info("IsolationForest + Scaler chargés")

            if os.path.exists(xgbr_path):
                self.xgb_regressor = joblib.load(xgbr_path)
                logger.info("XGBRegressor chargé")

            if os.path.exists(xgbc_path):
                self.xgb_classifier = joblib.load(xgbc_path)
                logger.info("XGBClassifier chargé")

            if os.path.exists(stats_path):
                with open(stats_path, "r") as f:
                    self.training_stats = json.load(f)

            if os.path.exists(ver_path):
                with open(ver_path, "r") as f:
                    self.model_version = f.read().strip()

        except Exception as e:
            logger.warning("Erreur chargement modèles : %s", e)

    # ──────────────────────────────────────────
    # Sauvegarde sur disque
    # ──────────────────────────────────────────

    def _save_models(self):
        os.makedirs(settings.MODEL_DIR, exist_ok=True)

        if self.isolation_forest:
            joblib.dump(self.isolation_forest, self._path("isolation_forest.pkl"))
        if self.scaler:
            joblib.dump(self.scaler, self._path("scaler.pkl"))
        if self.xgb_regressor:
            joblib.dump(self.xgb_regressor, self._path("xgb_regressor.pkl"))
        if self.xgb_classifier:
            joblib.dump(self.xgb_classifier, self._path("xgb_classifier.pkl"))
        if self.training_stats:
            with open(self._path("training_stats.json"), "w") as f:
                json.dump(self.training_stats, f, indent=2)

        self.model_version = f"v2.0-{datetime.utcnow().strftime('%Y%m%d%H%M%S')}"
        with open(self._path("version.txt"), "w") as f:
            f.write(self.model_version)

        logger.info("Modèles sauvegardés — version %s", self.model_version)

    # ──────────────────────────────────────────
    # Conversion CSV → SwiftMessageRequest
    # ──────────────────────────────────────────

    def _csv_to_messages(self, df: pd.DataFrame) -> List[SwiftMessageRequest]:
        """
        Convertit chaque ligne du CSV (MESSAGES_SWIFT + ANOMALIES_SWIFT joinés)
        en SwiftMessageRequest en ignorant les colonnes labels.
        Gère les valeurs NULL/NaN gracieusement.
        """
        messages = []
        for _, row in df.iterrows():
            def sv(col, default=""):
                val = row.get(col)
                if val is None or (isinstance(val, float) and np.isnan(val)):
                    return default
                return str(val).strip()

            def fv(col, default=0.0):
                val = row.get(col)
                try:
                    f = float(val)
                    return default if (np.isnan(f) or np.isinf(f)) else f
                except (TypeError, ValueError):
                    return default

            msg = SwiftMessageRequest(
                id            = int(row.get("id", 0)) if pd.notna(row.get("id")) else None,
                type_message  = sv("type_message") or sv("message_type") or "OTHER",
                direction     = sv("direction") or "OUT",
                sender_bic    = sv("sender_bic") or sv("sender_bank"),
                receiver_bic  = sv("receiver_bic") or sv("receiver_bank"),
                sender_name   = sv("sender_name") or None,
                receiver_name = sv("receiver_name") or None,
                amount        = fv("amount"),
                currency      = sv("currency") or "EUR",
                value_date    = sv("value_date") or None,
                created_at    = sv("created_at") or None,
                reference     = sv("reference") or None,
                status        = sv("status") or None,
                translation_errors = sv("translation_errors") or None,
                category      = sv("category") or None,
                sender_country   = sv("sender_country") or None,
                receiver_country = sv("receiver_country") or None,
            )
            messages.append(msg)
        return messages

    # ──────────────────────────────────────────
    # Entraînement
    # ──────────────────────────────────────────

    def train(
        self,
        messages: Optional[List[SwiftMessageRequest]] = None,
        contamination: Optional[float] = None,
    ) -> Tuple[int, str]:
        """
        Entraîne les trois modèles.

        Si messages fourni → entraîne sans labels (IF seulement).
        Si CSV disponible  → entraîne sur score + is_anomalie (supervisé).

        Retourne (nb_samples, version).
        """
        with self._lock:
            contamination = contamination or settings.IF_CONTAMINATION

            # ── Charger les données
            has_labels = False
            y_score = None
            y_class = None

            # ── Charger le CSV synthétique (base de départ toujours présente)
            csv_synth   = os.path.join(settings.DATA_DIR, "swift_messages.csv")
            csv_oracle  = os.path.join(settings.DATA_DIR, "oracle_messages.csv")

            all_feat_frames = []
            y_score_parts   = []
            y_class_parts   = []

            def _load_csv_source(path: str, label: str):
                """Charge un CSV et retourne (df_feat, y_score, y_class, has_lbl)."""
                raw = pd.read_csv(path)
                logger.info("%s chargé : %d lignes", label, len(raw))
                lbl = False
                ys, yc = None, None
                if "score" in raw.columns and "is_anomalie" in raw.columns:
                    lbl = True
                    ys = pd.to_numeric(raw["score"], errors="coerce").fillna(0).clip(0, 100).values.astype(float)
                    yc = pd.to_numeric(raw["is_anomalie"], errors="coerce").fillna(0).astype(int).values
                feat_cols = [c for c in raw.columns if c.lower() not in self.LABEL_COLS]
                msgs_csv  = self._csv_to_messages(raw[feat_cols].copy())
                df        = extract_features_batch(msgs_csv)
                return df, ys, yc, lbl

            if messages:
                # Données Oracle passées directement (depuis /api/train-from-oracle)
                df_oracle = extract_features_batch(messages)
                all_feat_frames.append(df_oracle)
                logger.info("Données Oracle en mémoire : %d messages", len(df_oracle))

                # Toujours combiner avec le CSV synthétique si disponible
                if os.path.exists(csv_synth):
                    df_s, ys_s, yc_s, lbl_s = _load_csv_source(csv_synth, "CSV synthétique")
                    all_feat_frames.append(df_s)
                    if lbl_s:
                        y_score_parts.append(ys_s)
                        y_class_parts.append(yc_s)
                        has_labels = True
            else:
                # Pas de messages passés → charger les CSV disponibles
                if not os.path.exists(csv_synth):
                    raise FileNotFoundError(
                        f"Fichier introuvable : {csv_synth}\n"
                        "Génère d'abord les données : python scripts/generate_synthetic_data.py"
                    )
                df_s, ys_s, yc_s, lbl_s = _load_csv_source(csv_synth, "CSV synthétique")
                all_feat_frames.append(df_s)
                if lbl_s:
                    y_score_parts.append(ys_s)
                    y_class_parts.append(yc_s)
                    has_labels = True

            # Ajouter le CSV Oracle sauvegardé si disponible (réentraînements précédents)
            if os.path.exists(csv_oracle) and not messages:
                df_o, ys_o, yc_o, lbl_o = _load_csv_source(csv_oracle, "CSV Oracle réel")
                all_feat_frames.append(df_o)
                if lbl_o:
                    y_score_parts.append(ys_o)
                    y_class_parts.append(yc_o)
                    has_labels = True

            # Fusionner toutes les sources
            df_feat = pd.concat(all_feat_frames, ignore_index=True)
            logger.info("Dataset final fusionné : %d échantillons (synthétique + Oracle)", len(df_feat))

            # Fusionner les labels si disponibles (aligner avec df_feat)
            if has_labels and y_score_parts:
                y_score = np.concatenate(y_score_parts)
                y_class = np.concatenate(y_class_parts)
                # Si Oracle en mémoire n'a pas de labels, compléter avec des zéros
                if messages and len(y_score) < len(df_feat):
                    pad = len(df_feat) - len(y_score)
                    y_score = np.concatenate([np.zeros(pad), y_score])
                    y_class = np.concatenate([np.zeros(pad, dtype=int), y_class])

            if df_feat.empty:
                raise ValueError("DataFrame de features vide après extraction")

            n_samples = len(df_feat)
            X = df_feat.values.astype(float)
            # Remplacer NaN/Inf résiduels
            X = np.nan_to_num(X, nan=0.0, posinf=0.0, neginf=0.0)

            # ── Scaling
            self.scaler = StandardScaler()
            X_scaled = self.scaler.fit_transform(X)

            # ── ① Isolation Forest (toujours, non supervisé)
            self.isolation_forest = IsolationForest(
                contamination=contamination,
                n_estimators=300,
                max_samples="auto",
                random_state=42,
                n_jobs=-1,
            )
            self.isolation_forest.fit(X_scaled)
            logger.info("IsolationForest entraîné sur %d échantillons", n_samples)

            if has_labels and y_score is not None and y_class is not None:
                # ── ② XGBRegressor → prédit le score (0-100)
                self.xgb_regressor = XGBRegressor(
                    n_estimators=300,
                    max_depth=6,
                    learning_rate=0.05,
                    subsample=0.8,
                    colsample_bytree=0.8,
                    reg_alpha=0.1,
                    reg_lambda=1.0,
                    random_state=42,
                    n_jobs=-1,
                    verbosity=0,
                )
                self.xgb_regressor.fit(X_scaled, y_score)
                preds_score = self.xgb_regressor.predict(X_scaled)
                rmse = float(np.sqrt(np.mean((preds_score - y_score) ** 2)))
                logger.info("XGBRegressor entraîné — RMSE train=%.2f", rmse)

                # ── ③ XGBClassifier → prédit is_anomalie (0/1)
                # Calcul du scale_pos_weight pour gérer le déséquilibre de classes
                n_neg = int(np.sum(y_class == 0))
                n_pos = int(np.sum(y_class == 1))
                spw   = (n_neg / n_pos) if n_pos > 0 else 1.0

                self.xgb_classifier = XGBClassifier(
                    n_estimators=300,
                    max_depth=6,
                    learning_rate=0.05,
                    subsample=0.8,
                    colsample_bytree=0.8,
                    scale_pos_weight=spw,
                    use_label_encoder=False,
                    eval_metric="logloss",
                    random_state=42,
                    n_jobs=-1,
                    verbosity=0,
                )
                self.xgb_classifier.fit(X_scaled, y_class)
                acc = float(np.mean(self.xgb_classifier.predict(X_scaled) == y_class))
                logger.info("XGBClassifier entraîné — Acc train=%.3f (scale_pos_weight=%.2f)", acc, spw)
            else:
                # Fallback : pseudo-labels IF pour XGBClassifier
                logger.info("Pas de labels → entraînement XGBoost sur pseudo-labels IF")
                if_labels = self.isolation_forest.predict(X_scaled)
                pseudo_y  = np.where(if_labels == -1, 1, 0)
                pseudo_score = np.where(if_labels == -1, 70.0, 10.0)

                self.xgb_regressor = XGBRegressor(
                    n_estimators=200, max_depth=5, random_state=42, n_jobs=-1, verbosity=0
                )
                self.xgb_regressor.fit(X_scaled, pseudo_score)

                self.xgb_classifier = XGBClassifier(
                    n_estimators=200, max_depth=5, random_state=42, n_jobs=-1,
                    use_label_encoder=False, eval_metric="logloss", verbosity=0
                )
                self.xgb_classifier.fit(X_scaled, pseudo_y)

            # ── Statistiques d'entraînement
            self.training_stats = {
                "n_samples":       n_samples,
                "has_labels":      has_labels,
                "amount_mean":     float(df_feat["amount"].mean()),
                "amount_std":      float(df_feat["amount"].std()),
                "trained_at":      datetime.utcnow().isoformat(),
                "feature_count":   len(df_feat.columns),
                "feature_names":   list(df_feat.columns),
            }
            if has_labels and y_score is not None:
                self.training_stats["score_mean"] = float(y_score.mean())
                self.training_stats["score_std"]  = float(y_score.std())
                self.training_stats["anomaly_rate"] = float(y_class.mean())

            self._save_models()
            return n_samples, self.model_version

    # ──────────────────────────────────────────
    # Prédiction
    # ──────────────────────────────────────────

    def predict(self, msg: SwiftMessageRequest) -> PredictResponse:
        """
        Analyse un message SWIFT (MESSAGES_SWIFT uniquement).

        Retourne :
          score      : score final normalisé 0.0-1.0
          score_ml   : score ML 0-100 (utilisé par Laravel pour la formule hybride)
          is_anomaly : bool
          reasons    : règles déclenchées + flag ML
        """
        reasons: List[AnomalyReason] = []

        # ── 1. Règles métier (9 règles AnomalyService.php)
        rule_reasons = check_business_rules(msg)
        rule_score_100 = sum({
            "MONTANT_ZERO": 40, "MONTANT_ELEVE": 25, "STATUT_REJETE": 30,
            "TYPE_ERROR": 35, "TRANSLATION_ERROR": 25, "PASSPORT_DETECTE": 30,
            "BIC_MANQUANT": 15, "DEVISE_INHABITUELLE": 20,
        }.get(r["rule"], 0) for r in rule_reasons)
        rule_score_100 = min(rule_score_100, 100)

        for r in rule_reasons:
            reasons.append(AnomalyReason(**r))

        # ── 2. Score ML
        score_ml_100 = 0.0

        if self.xgb_regressor and self.scaler:
            feat = extract_features(msg)
            X_names  = self.training_stats.get("feature_names", list(feat.keys()))
            X_row    = np.array([[feat.get(n, 0.0) for n in X_names]], dtype=float)
            X_row    = np.nan_to_num(X_row, nan=0.0, posinf=0.0, neginf=0.0)
            X_scaled = self.scaler.transform(X_row)

            # Régression → score ML
            try:
                score_ml_100 = float(np.clip(self.xgb_regressor.predict(X_scaled)[0], 0, 100))
            except Exception as e:
                logger.warning("XGBRegressor predict error : %s", e)
                score_ml_100 = 0.0

            # Classification → proba anomalie
            xgbc_proba = 0.0
            if self.xgb_classifier:
                try:
                    proba = self.xgb_classifier.predict_proba(X_scaled)[0]
                    xgbc_proba = float(proba[1]) if len(proba) > 1 else 0.0
                except Exception:
                    pass

            # IF fallback
            if_score_100 = 0.0
            if self.isolation_forest:
                try:
                    raw_if = self.isolation_forest.decision_function(X_scaled)[0]
                    if_norm = float(1.0 / (1.0 + np.exp(5 * raw_if)))
                    if_score_100 = if_norm * 100.0
                except Exception:
                    pass

            # Raisons ML si score élevé
            if score_ml_100 >= 50:
                reasons.append(AnomalyReason(
                    rule="ML_SCORE",
                    description=f"Score ML élevé : {score_ml_100:.1f}/100 (XGBoost Regressor)",
                    severity="high" if score_ml_100 >= 70 else "medium",
                ))
            if xgbc_proba >= 0.6:
                reasons.append(AnomalyReason(
                    rule="ML_CLASSIFIER",
                    description=f"Probabilité anomalie XGBoost : {xgbc_proba:.1%}",
                    severity="high" if xgbc_proba >= 0.8 else "medium",
                ))

        # ── 3. Formule hybride (identique à AnomalyService.php)
        # score_final = min( (score_regles × 0.60) + (score_ml × 0.40) , 100 )
        final_score_100 = min((rule_score_100 * 0.60) + (score_ml_100 * 0.40), 100.0)
        final_score     = round(final_score_100 / 100.0, 4)

        # Seuils BTL : LOW < 20, MEDIUM 20-59, HIGH >= 60
        is_anomaly = final_score_100 >= 20.0

        response = PredictResponse(
            message_id   = msg.id,
            score        = final_score,
            score_ml     = round(score_ml_100, 2),
            is_anomaly   = is_anomaly,
            reasons      = reasons,
            model_version= self.model_version,
        )

        # ── Log
        self.prediction_log.append({
            "message_id":   msg.id,
            "type_message": msg.get_type(),
            "score":        final_score_100,
            "score_ml":     score_ml_100,
            "is_anomaly":   is_anomaly,
            "reasons":      [r.rule for r in reasons],
            "timestamp":    datetime.utcnow().isoformat(),
        })
        if len(self.prediction_log) > 10_000:
            self.prediction_log = self.prediction_log[-10_000:]

        return response

    # ──────────────────────────────────────────
    # Stats
    # ──────────────────────────────────────────

    def get_stats(self) -> Dict:
        total     = len(self.prediction_log)
        anomalies = [p for p in self.prediction_log if p["is_anomaly"]]

        by_type: Dict[str, int] = {}
        by_rule: Dict[str, int] = {}
        for p in anomalies:
            mt = p.get("type_message", "UNKNOWN")
            by_type[mt] = by_type.get(mt, 0) + 1
            for r in p.get("reasons", []):
                by_rule[r] = by_rule.get(r, 0) + 1

        return {
            "total_predictions": total,
            "total_anomalies":   len(anomalies),
            "anomaly_rate":      round(len(anomalies) / total, 4) if total > 0 else 0.0,
            "anomalies_by_type": by_type,
            "anomalies_by_rule": by_rule,
            "recent_anomalies":  anomalies[-20:][::-1],
        }

    @property
    def is_model_loaded(self) -> bool:
        return self.xgb_regressor is not None and self.scaler is not None


# ── Instance globale
detector = AnomalyDetector()

