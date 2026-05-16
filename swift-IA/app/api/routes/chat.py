"""
Route FastAPI /api/chat - Chatbot IA conversationnel pour l'analyse SWIFT.

Appele par Laravel (SwiftController::chatIA) via POST http://python-api:8001/api/chat.
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


# ============================================================
# SCHEMAS — /api/chat  (chatbot détail message)
# ============================================================

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


# ============================================================
# ENDPOINT — /api/chat  (chatbot détail message SWIFT)
# ============================================================

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

    # ✅ INDENTATION CORRECTE — à l'intérieur de la fonction (4 espaces)
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
        "HIGH":   "Risque Critique",
        "MEDIUM": "Risque Moyen",
        "LOW":    "Risque Faible",
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
        return ChatResponse(response=answer)  # ✅ DANS le try, 8 espaces

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


# ============================================================
# SCHEMAS — /api/chat-global  (chatbot global tous dashboards)
# ============================================================

class ChatGlobalRequest(BaseModel):
    role: Optional[str] = "user"
    page: Optional[str] = "dashboard"
    stats: Optional[dict] = {}
    question: str = ""
    history: Optional[List[dict]] = []


class ChatGlobalResponse(BaseModel):
    answer: str


# ============================================================
# ENDPOINT — /api/chat-global  (chatbot FAB tous dashboards)
# ============================================================

@router.post(
    "/chat-global",
    response_model=ChatGlobalResponse,
    summary="Chatbot IA global - Conversation multi-page et role-aware",
)
async def chat_global(payload: ChatGlobalRequest):
    """
    Endpoint global pour le chatbot accessible depuis tous les dashboards.
    Recoit: role, page, stats, question, history
    Retourne: {"answer": "..."}
    """

    if not settings.GROQ_API_KEY:
        raise HTTPException(
            status_code=503,
            detail="Service chatbot non configure - cle GROQ_API_KEY manquante.",
        )

    role     = payload.role or "user"
    page     = payload.page or "dashboard"
    stats    = payload.stats or {}
    question = payload.question or ""
    history  = payload.history or []

    system_prompt = (
        f"Tu es l'assistant IA officiel de BTL Bank (Tunisian Libyan Bank).\n"
        f"Tu aides les agents bancaires a analyser les messages SWIFT.\n"
        f"Role de l'agent connecte : {role}\n"
        f"Page actuelle : {page}\n"
        f"Statistiques temps reel de la plateforme :\n"
        f"  - Total messages      : {stats.get('total', 0)}\n"
        f"  - En attente          : {stats.get('pending', 0)}\n"
        f"  - Anomalies HIGH      : {stats.get('anomalies', 0)}\n"
        f"  - Volume traite       : {stats.get('volume', '0')}\n"
        f"  - Messages recus      : {stats.get('received', 0)}\n"
        f"  - Messages emis       : {stats.get('emitted', 0)}\n"
        f"Reponds en francais, de facon professionnelle et concise.\n"
        f"Si tu ne sais pas, dis-le honnetement."
    )

    # Historique limité aux 10 derniers messages
    groq_history = []
    for item in (history or [])[-10:]:
        try:
            r = item.get("role", "user")
            c = item.get("content", "")
            if r and c:
                groq_history.append({"role": r, "content": c})
        except Exception:
            continue

    messages = [{"role": "system", "content": system_prompt}]
    messages.extend(groq_history)
    messages.append({"role": "user", "content": question})

    try:
        client = Groq(api_key=settings.GROQ_API_KEY)
        completion = client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=messages,
            max_tokens=300,
            temperature=0.3,
        )
        answer = completion.choices[0].message.content
        logger.info(
            "ChatGlobal (Groq) - role=%s page=%s question_len=%d",
            role,
            page,
            len(question),
        )
        return ChatGlobalResponse(answer=answer)  # ✅ DANS le try, 8 espaces

    except APITimeoutError:
        logger.error("Timeout appel Groq API - chat-global")
        raise HTTPException(
            status_code=504,
            detail="Service temporairement indisponible - timeout.",
        )
    except APIStatusError as exc:
        logger.error("Erreur HTTP Groq chat-global: %s - %s", exc.status_code, exc.message)
        raise HTTPException(
            status_code=502,
            detail=f"Erreur du service IA externe ({exc.status_code}).",
        )
    except Exception as exc:
        logger.exception("Erreur inattendue dans /api/chat-global")
        raise HTTPException(status_code=500, detail=f"Erreur interne : {exc}")