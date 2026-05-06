"""
BTL SWIFT AI — Détection d'anomalies dans les messages SWIFT bancaires.

Service FastAPI appelé par la plateforme Laravel (btl-swift-platform).
"""

import logging
from contextlib import asynccontextmanager

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from app.config import settings
from app.api.routes import predict, train, health, stats, dashboard, retrain

# ── Logging
logging.basicConfig(
    level=logging.DEBUG if settings.DEBUG else logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
)
logger = logging.getLogger(__name__)


# ── Lifespan
@asynccontextmanager
async def lifespan(app: FastAPI):
    logger.info("🚀 %s démarré (env=%s)", settings.APP_NAME, settings.APP_ENV)
    yield
    logger.info("🛑 %s arrêté", settings.APP_NAME)


# ── App
app = FastAPI(
    title="BTL SWIFT AI",
    description=(
        "API de détection d'anomalies dans les messages SWIFT "
        "pour BTL Bank. Utilise XGBoost (Regressor + Classifier) + Isolation Forest + règles métier."
    ),
    version="1.0.0",
    lifespan=lifespan,
    docs_url="/docs",
    redoc_url="/redoc",
)

# ── CORS (pour permettre les appels depuis Laravel / front)
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.cors_origins_list,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ── Routes
app.include_router(predict.router,   prefix="/api", tags=["Prediction"])
app.include_router(train.router,     prefix="/api", tags=["Training"])
app.include_router(retrain.router,   prefix="/api", tags=["Training"])
app.include_router(health.router,    prefix="/api", tags=["Health"])
app.include_router(stats.router,     prefix="/api", tags=["Statistics"])
app.include_router(dashboard.router, prefix="",     tags=["Dashboard"])


@app.get("/", include_in_schema=False)
async def root():
    return {
        "service": settings.APP_NAME,
        "version": "1.0.0",
        "docs": "/docs",
    }