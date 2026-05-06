"""
Route POST /api/train-from-oracle

Déclenche un réentraînement du modèle à partir des vraies données Oracle
en appelant l'API Laravel qui exporte MESSAGES_SWIFT + ANOMALIES_SWIFT.
"""

from fastapi import APIRouter, HTTPException, BackgroundTasks
import logging
import httpx
import pandas as pd
import numpy as np
import os

from app.services.detector import detector
from app.models.schemas import SwiftMessageRequest
from app.config import settings

router = APIRouter()
logger = logging.getLogger(__name__)


@router.post("/train-from-oracle", summary="Réentraîner depuis Oracle (données réelles)")
async def train_from_oracle(background_tasks: BackgroundTasks):
    """
    Appelle Laravel pour exporter les données Oracle, puis réentraîne les modèles.

    Flux :
      1. GET {LARAVEL_BASE_URL}/api/swift-export  → JSON avec messages + scores
      2. Convertit en SwiftMessageRequest
      3. Appelle detector.train() avec labels réels
      4. Retourne la version du nouveau modèle
    """
    background_tasks.add_task(_retrain_from_oracle)
    return {
        "status": "started",
        "message": "Réentraînement lancé en arrière-plan depuis Oracle. Vérifier /api/health pour la progression.",
    }


@router.post("/train-from-oracle/sync", summary="Réentraîner depuis Oracle (synchrone)")
async def train_from_oracle_sync():
    """Version synchrone — attend la fin du réentraînement."""
    result = await _retrain_from_oracle()
    return result


async def _retrain_from_oracle():
    """Télécharge les données depuis Laravel et réentraîne."""
    try:
        export_url = f"{settings.LARAVEL_BASE_URL}/api/swift-export"
        headers = {}
        if settings.LARAVEL_API_KEY:
            headers["X-API-Key"] = settings.LARAVEL_API_KEY

        logger.info("Export Oracle depuis %s ...", export_url)

        async with httpx.AsyncClient(timeout=120) as client:
            response = await client.get(export_url, headers=headers)

        if response.status_code != 200:
            raise HTTPException(
                status_code=502,
                detail=f"Laravel a répondu {response.status_code} : {response.text[:300]}"
            )

        data = response.json()
        messages_raw = data.get("messages", [])

        if not messages_raw:
            raise HTTPException(status_code=404, detail="Aucune donnée reçue depuis Oracle.")

        logger.info("Reçu %d messages depuis Oracle", len(messages_raw))

        # Convertir en SwiftMessageRequest avec labels
        messages = []
        labels_score = []
        labels_class = []

        for row in messages_raw:
            try:
                msg = SwiftMessageRequest(
                    id             = row.get("id"),
                    type_message   = row.get("type_message") or "OTHER",
                    direction      = row.get("direction") or "OUT",
                    sender_bic     = row.get("sender_bic"),
                    receiver_bic   = row.get("receiver_bic"),
                    sender_name    = row.get("sender_name"),
                    receiver_name  = row.get("receiver_name"),
                    amount         = float(row.get("amount") or 0),
                    currency       = row.get("currency") or "EUR",
                    value_date     = row.get("value_date"),
                    created_at     = row.get("created_at"),
                    reference      = row.get("reference"),
                    status         = row.get("status"),
                    translation_errors = row.get("translation_errors"),
                    category       = row.get("category"),
                    sender_country = row.get("sender_country"),
                    receiver_country = row.get("receiver_country"),
                )
                messages.append(msg)

                # Labels depuis ANOMALIES_SWIFT (si disponibles)
                score = row.get("anomaly_score")
                is_anomaly = row.get("is_anomalie")
                labels_score.append(float(score) if score is not None else None)
                labels_class.append(int(is_anomaly) if is_anomaly is not None else None)

            except Exception as e:
                logger.warning("Ligne ignorée : %s", e)
                continue

        if len(messages) < 10:
            raise HTTPException(status_code=400, detail=f"Seulement {len(messages)} messages valides, minimum 10.")

        # Si les labels sont disponibles → sauvegarder en CSV enrichi pour retrain supervisé
        has_labels = all(s is not None for s in labels_score) and any(s > 0 for s in labels_score)

        if has_labels:
            logger.info("Labels Oracle disponibles → entraînement supervisé")
            _save_oracle_csv(messages, labels_score, labels_class)
            # Injecter les labels dans les messages pour le train()
            # (passer via CSV sauvegardé, train() le chargera automatiquement)
            n_samples, version = detector.train(messages=None)
        else:
            n_samples, version = detector.train(messages=messages)

        logger.info("Réentraînement terminé — %d échantillons, version %s", n_samples, version)
        return {
            "status": "success",
            "message": f"Modèle réentraîné avec {n_samples} messages Oracle réels",
            "samples_used": n_samples,
            "model_version": version,
            "has_labels": has_labels,
        }

    except HTTPException:
        raise
    except Exception as e:
        logger.exception("Erreur réentraînement Oracle")
        raise HTTPException(status_code=500, detail=str(e))


def _save_oracle_csv(messages, labels_score, labels_class):
    """Sauvegarde les données Oracle en CSV pour réentraînements futurs."""
    try:
        rows = []
        for msg, score, is_anom in zip(messages, labels_score, labels_class):
            row = msg.model_dump()
            row["score"] = score if score is not None else 0
            row["is_anomalie"] = is_anom if is_anom is not None else 0
            rows.append(row)

        df = pd.DataFrame(rows)
        csv_path = os.path.join(settings.DATA_DIR, "oracle_messages.csv")
        os.makedirs(settings.DATA_DIR, exist_ok=True)
        df.to_csv(csv_path, index=False)
        logger.info("CSV Oracle sauvegardé : %s (%d lignes)", csv_path, len(df))
    except Exception as e:
        logger.warning("Impossible de sauvegarder le CSV Oracle : %s", e)
