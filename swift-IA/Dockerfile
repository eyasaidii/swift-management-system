# ── BTL SWIFT AI — Microservice Python FastAPI ──────────────────────────────
# Image légère Python 3.11 slim
FROM python:3.11-slim

# Métadonnées
LABEL maintainer="BTL Bank - PFE SWIFT IA"
LABEL description="Microservice de détection d'anomalies SWIFT (XGBoost + IsolationForest)"

# Répertoire de travail
WORKDIR /app

# Copier les dépendances en premier (layer cache Docker)
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copier le reste du code source
COPY . .

# Créer les dossiers si absents
RUN mkdir -p model data

# Exposer le port FastAPI
EXPOSE 8001

# Variables d'environnement par défaut (surchargeable via docker-compose)
ENV APP_ENV=production
ENV DEBUG=false
ENV MODEL_DIR=/app/model
ENV DATA_DIR=/app/data
ENV ANOMALY_THRESHOLD=0.65
ENV IF_CONTAMINATION=0.05

# Lancer uvicorn en production (4 workers)
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8001", "--workers", "4"]
