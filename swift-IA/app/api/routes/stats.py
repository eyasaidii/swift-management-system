from fastapi import APIRouter
from app.models.schemas import StatsResponse
from app.services.detector import detector

router = APIRouter()


@router.get("/stats", response_model=StatsResponse, summary="Statistiques des anomalies")
async def stats():
    """Retourne les statistiques des anomalies détectées depuis le démarrage du service."""
    data = detector.get_stats()
    return StatsResponse(**data)
