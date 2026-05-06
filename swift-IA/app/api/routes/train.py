from fastapi import APIRouter, HTTPException
import logging

from app.models.schemas import TrainRequest, TrainResponse
from app.services.detector import detector

router = APIRouter()
logger = logging.getLogger(__name__)


@router.post("/train", response_model=TrainResponse, summary="Réentraîner le modèle")
async def train(request: TrainRequest = None):
    """
    Réentraîne les modèles de détection d'anomalies.

    - Si `messages` est fourni : entraîne sur ces données.
    - Sinon : utilise le fichier `data/swift_messages.csv`.
    """
    try:
        messages = request.messages if request else None
        contamination = request.contamination if request else None

        n_samples, version = detector.train(
            messages=messages,
            contamination=contamination,
        )

        return TrainResponse(
            status="success",
            message=f"Modèle entraîné avec succès sur {n_samples} échantillons",
            samples_used=n_samples,
            model_version=version,
        )

    except FileNotFoundError as e:
        raise HTTPException(status_code=404, detail=str(e))
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))
    except Exception as e:
        logger.exception("Erreur lors de l'entraînement")
        raise HTTPException(status_code=500, detail=f"Erreur interne : {str(e)}")
