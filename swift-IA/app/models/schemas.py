from pydantic import BaseModel, Field, model_validator
from typing import Optional, List
from datetime import datetime
from enum import Enum


# ──────────────────────────────────────────────
# Enums
# ──────────────────────────────────────────────

class Direction(str, Enum):
    IN  = "IN"
    OUT = "OUT"


# ──────────────────────────────────────────────
# Request schemas
# ──────────────────────────────────────────────

class SwiftMessageRequest(BaseModel):
    """
    Schéma d'un message SWIFT envoyé par Laravel pour analyse.

    Correspond aux colonnes de la table MESSAGES_SWIFT (Oracle BTL).
    Tous les champs optionnels reflètent les valeurs NULL réelles en BD.
    """
    # ── Identifiant
    id: Optional[int] = None

    # ── Type de message (MX ISO 20022 ou MT legacy)
    # Accepte type_message (colonne Oracle) ou message_type (alias legacy)
    type_message: Optional[str] = Field(None, description="PACS.008, PACS.009, MT103, MT202, CAMT.053…")
    message_type: Optional[str] = Field(None, description="Alias legacy de type_message")

    # ── Direction
    direction: str = Field("OUT", description="IN (reçu) ou OUT (émis)")

    # ── BIC codes (colonnes SENDER_BIC / RECEIVER_BIC)
    sender_bic:   Optional[str] = Field(None, description="BIC banque émettrice (ex: BTLKTNTT)")
    receiver_bic: Optional[str] = Field(None, description="BIC banque bénéficiaire (ex: BNPAFRPP)")
    # Aliases legacy
    sender_bank:   Optional[str] = Field(None, description="Alias legacy de sender_bic")
    receiver_bank: Optional[str] = Field(None, description="Alias legacy de receiver_bic")

    # ── Noms
    sender_name:   Optional[str] = Field(None, description="Nom établissement émetteur")
    receiver_name: Optional[str] = Field(None, description="Nom bénéficiaire (peut contenir PASS NO)")

    # ── Montant & devise
    amount:   float = Field(0.0, ge=0, description="Montant de la transaction")
    currency: str   = Field("EUR", min_length=3, max_length=3, description="Code devise ISO 4217")

    # ── Dates
    value_date: Optional[str] = Field(None, description="Date valeur YYYY-MM-DD")
    created_at: Optional[str] = Field(None, description="Date création ISO 8601")

    # ── Référence & statut
    reference: Optional[str] = Field(None, description="Référence unique du message")
    status:    Optional[str] = Field(None, description="pending/processed/authorized/rejected/suspicious")

    # ── Erreurs de traduction XML→MT
    translation_errors: Optional[str] = Field(
        None, description="JSON array des erreurs de parsing XML (CLOB Oracle)"
    )

    # ── Catégorie (PACS / CAMT / PAIN / 1 / 2 / 9)
    category: Optional[str] = Field(None, description="Catégorie du message")

    # ── Pays (dérivés des BIC si non fournis)
    sender_country:   Optional[str] = Field(None, max_length=2)
    receiver_country: Optional[str] = Field(None, max_length=2)

    # ── Détails libres
    details: Optional[str] = Field(None, description="Détails / motif (DESCRIPTION en Oracle)")

    @model_validator(mode="after")
    def _resolve_aliases(self) -> "SwiftMessageRequest":
        """Résout les aliases legacy → champs canoniques."""
        # type_message ← message_type si absent
        if not self.type_message and self.message_type:
            self.type_message = self.message_type
        if not self.message_type and self.type_message:
            self.message_type = self.type_message

        # sender_bic ← sender_bank si absent
        if not self.sender_bic and self.sender_bank:
            self.sender_bic = self.sender_bank
        if not self.sender_bank and self.sender_bic:
            self.sender_bank = self.sender_bic

        # receiver_bic ← receiver_bank si absent
        if not self.receiver_bic and self.receiver_bank:
            self.receiver_bic = self.receiver_bank
        if not self.receiver_bank and self.receiver_bic:
            self.receiver_bank = self.receiver_bic

        return self

    def get_type(self) -> str:
        return (self.type_message or self.message_type or "OTHER").upper()

    def get_sender_bic(self) -> str:
        return (self.sender_bic or self.sender_bank or "").strip()

    def get_receiver_bic(self) -> str:
        return (self.receiver_bic or self.receiver_bank or "").strip()

    class Config:
        json_schema_extra = {
            "example": {
                "id": 1234,
                "type_message": "PACS.008",
                "direction": "OUT",
                "sender_bic": "BTLKTNTT",
                "receiver_bic": "BNPAFRPP",
                "sender_name": "BTL BANK TUNIS",
                "receiver_name": "ENTERPRISE SARL",
                "amount": 150000.00,
                "currency": "EUR",
                "value_date": "2026-04-18",
                "created_at": "2026-04-18T10:30:00",
                "reference": "REF2026041800123",
                "status": "pending",
                "translation_errors": None,
                "category": "PACS",
            }
        }


class TrainRequest(BaseModel):
    """Schéma pour déclencher le réentraînement."""
    messages: Optional[List[SwiftMessageRequest]] = Field(
        None, description="Données d'entraînement. Si vide, utilise les données locales."
    )
    contamination: Optional[float] = Field(
        None, ge=0.001, le=0.5, description="Taux de contamination Isolation Forest"
    )


# ──────────────────────────────────────────────
# Response schemas
# ──────────────────────────────────────────────

class AnomalyReason(BaseModel):
    rule: str = Field(..., description="Identifiant de la règle déclenchée")
    description: str = Field(..., description="Explication humaine")
    severity: str = Field("medium", description="low / medium / high / critical")


class PredictResponse(BaseModel):
    message_id: Optional[int] = None
    score: float = Field(..., description="Score d'anomalie 0.0 (normal) → 1.0 (très suspect)")
    # Score renvoyé par le modèle ML (0-100). Utilisé par Laravel pour la formule hybride.
    score_ml: float = Field(0.0, description="Score ML 0-100 (pour intégration hybride)")
    is_anomaly: bool
    reasons: List[AnomalyReason] = []
    model_version: str = ""

    class Config:
        json_schema_extra = {
            "example": {
                "message_id": 1234,
                "score": 0.82,
                "is_anomaly": True,
                "reasons": [
                    {
                        "rule": "unusual_amount",
                        "description": "Montant 150 000 EUR dépasse 3σ pour MT103 OUT (moyenne: 12 000 EUR)",
                        "severity": "high",
                    },
                    {
                        "rule": "unusual_destination",
                        "description": "Destination FR non habituelle pour cet émetteur",
                        "severity": "medium",
                    },
                ],
                "model_version": "v1.0.0-20260418",
            }
        }


class TrainResponse(BaseModel):
    status: str
    message: str
    samples_used: int = 0
    model_version: str = ""


class HealthResponse(BaseModel):
    status: str
    model_loaded: bool
    model_version: str = ""
    uptime_seconds: float = 0


class StatsResponse(BaseModel):
    total_predictions: int = 0
    total_anomalies: int = 0
    anomaly_rate: float = 0.0
    anomalies_by_type: dict = {}
    anomalies_by_rule: dict = {}
    recent_anomalies: List[dict] = []
