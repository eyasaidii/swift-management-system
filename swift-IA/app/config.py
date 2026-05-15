import os
from pydantic_settings import BaseSettings
from typing import List


class Settings(BaseSettings):
    APP_NAME: str = "btl-swift-ai"
    APP_ENV: str = "production"
    DEBUG: bool = False

    ANOMALY_THRESHOLD: float = 0.65
    IF_CONTAMINATION: float = 0.05

    MODEL_DIR: str = "./model"
    DATA_DIR: str = "./data"

    LARAVEL_BASE_URL: str = "http://btl-swift-platform:80"
    LARAVEL_API_KEY: str = ""

    # Réentraînement automatique : nb minimum de nouveaux messages avant retrain
    RETRAIN_MIN_SAMPLES: int = 100

    # Chatbot IA — Groq
    GROQ_API_KEY: str = ""

    CORS_ORIGINS: str = "http://localhost"

    @property
    def cors_origins_list(self) -> List[str]:
        return [o.strip() for o in self.CORS_ORIGINS.split(",") if o.strip()]

    class Config:
        env_file = ".env"
        env_file_encoding = "utf-8"


settings = Settings()
