from fastapi import APIRouter, HTTPException
import logging

from app.models.schemas import SwiftMessageRequest, PredictResponse
from app.services.detector import detector

router = APIRouter()
logger = logging.getLogger(__name__)


@router.post("/predict", response_model=PredictResponse, summary="Analyser un message SWIFT")
async def predict(message: SwiftMessageRequest):
    """
    Analyse un message SWIFT et retourne un score d'anomalie + raisons.

    Appelé par Laravel :
    ```
    POST http://python-api:8001/api/predict
    ```
    """
    try:
        result = detector.predict(message)
        return result
    except Exception as e:
        logger.exception("Erreur lors de la prédiction")
        raise HTTPException(status_code=500, detail=f"Erreur interne : {str(e)}")
