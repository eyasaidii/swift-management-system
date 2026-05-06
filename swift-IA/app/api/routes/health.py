import time
from fastapi import APIRouter
from app.models.schemas import HealthResponse
from app.services.detector import detector

router = APIRouter()

_start_time = time.time()


@router.get("/health", response_model=HealthResponse, summary="Vérifier l'état du service")
async def health():
    """Health check — utilisé par Docker et Laravel pour vérifier que le service est up."""
    return HealthResponse(
        status="ok",
        model_loaded=detector.is_model_loaded,
        model_version=detector.model_version,
        uptime_seconds=round(time.time() - _start_time, 2),
    )
