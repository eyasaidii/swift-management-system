"""
Route FastAPI /api/chat - Chatbot IA conversationnel pour l'analyse SWIFT.

Appele par Laravel (MessageSwiftController::chatIA) via POST http://python-api:8001/api/chat.
Utilise Groq API (llama-3.3-70b-versatile) pour repondre en francais aux questions
des responsables SWIFT sur un message donne.
"""

import logging
from typing import List, Optional

from fastapi import APIRouter, HTTPException
from groq import Groq, APITimeoutError, APIStatusError
from pydantic import BaseModel

from app.config import settings

router = APIRouter()
logger = logging.getLogger(__name__)


# Schemas

class ChatRequest(BaseModel):
    type_message: Optional[str] = None
    amount: Optional[float] = None
    currency: Optional[str] = None
    sender_name: Optional[str] = None
    reference: Optional[str] = None
    score_ia: Optional[int] = None
    niveau_risque: Optional[str] = None
    raisons: Optional[List[str]] = None
    status: Optional[str] = None
    question: str


class ChatResponse(BaseModel):
    response: str


# Endpoint

@router.post(
    "/chat",
    response_model=ChatResponse,
    summary="Chatbot IA - Analyse conversationnelle d'un message SWIFT",
)
async def chat(payload: ChatRequest):
    """
    Recoit le contexte complet d'un message SWIFT + une question en francais,
    et retourne une reponse professionnelle generee par Groq (llama-3.3-70b-versatile).

    Appele par Laravel :
        POST http://python-api:8001/api/chat
    """
    if not settings.GROQ_API_KEY:
        raise HTTPException(
            status_code=503,
            detail="Service chatbot non configure - cle GROQ_API_KEY manquante.",
        )

    raisons_str = (
        "\n".join(f"  - {r}" for r in payload.raisons)
        if payload.raisons
        else "Aucune anomalie specifique detectee"
    )

    montant_str = (
        f"{payload.amount:,.2f} {payload.currency or ''}".strip()
        if payload.amount is not None
        else "Non renseigne"
    )

    niveau_label = {
        "HIGH": "Risque Critique",
        "MEDIUM": "Risque Moyen",
        "LOW": "Risque Faible",
    }.get(payload.niveau_risque or "", payload.niveau_risque or "Non evalue")

    system_prompt = (
        "Tu es un expert senior en conformite bancaire, en analyse AML (Anti-Money Laundering) "
        "et en traitement des messages SWIFT pour BTL Bank (Tunisian Libyan Bank). "
        "Tu reponds toujours en francais, de facon professionnelle, structuree et directement "
        "exploitable par un responsable SWIFT. "
        "Tes recommandations sont basees uniquement sur les donnees fournies. "
        "Si une information manque pour conclure, indique-le clairement. "
        "Ne genere jamais de donnees fictives."
    )

    user_prompt = (
        f"Voici le contexte du message SWIFT en cours d'analyse :\n\n"
        f"  Type de message  : {payload.type_message or 'Non renseigne'}\n"
        f"  Reference        : {payload.reference or 'Non renseignee'}\n"
        f"  Emetteur         : {payload.sender_name or 'Non renseigne'}\n"
        f"  Montant          : {montant_str}\n"
        f"  Score de risque  : {payload.score_ia}/100 (0 = normal, 100 = critique)\n"
        f"  Niveau de risque : {niveau_label}\n"
        f"  Statut actuel    : {payload.status or 'Non renseigne'}\n"
        f"  Raisons detectees :\n{raisons_str}\n\n"
        f"Question du responsable SWIFT : {payload.question}"
    )

    try:
        client = Groq(api_key=settings.GROQ_API_KEY)
        completion = client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user",   "content": user_prompt},
            ],
            max_tokens=500,
            temperature=0.3,
        )
        answer = completion.choices[0].message.content
        logger.info(
            "ChatIA (Groq) - message=%s score=%s question_len=%d",
            payload.reference,
            payload.score_ia,
            len(payload.question),
        )
        return ChatResponse(response=answer)

    except APITimeoutError:
        logger.error("Timeout appel Groq API")
        raise HTTPException(
            status_code=504,
            detail="Delai d'attente depasse - l'API Groq n'a pas repondu a temps.",
        )
    except APIStatusError as exc:
        logger.error("Erreur HTTP Groq : %s - %s", exc.status_code, exc.message)
        raise HTTPException(
            status_code=502,
            detail=f"Erreur du service IA externe ({exc.status_code}).",
        )
    except Exception as exc:
        logger.exception("Erreur inattendue dans /api/chat")
        raise HTTPException(status_code=500, detail=f"Erreur interne : {exc}")
