import os
from functools import lru_cache
from typing import List

from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field
from sentence_transformers import SentenceTransformer


MODEL_NAME = os.getenv(
    "EMBEDDING_MODEL_NAME",
    "sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2",
)

app = FastAPI(
    title="Tour Catalog AI Embedding Service",
    version="0.1.0",
    description="Lightweight multilingual embedding service for Tour Catalog AI.",
)


class EmbedRequest(BaseModel):
    text: str = Field(..., min_length=1, max_length=20000)


class EmbedResponse(BaseModel):
    embedding: List[float]
    model: str
    dimensions: int


@lru_cache
def get_model() -> SentenceTransformer:
    return SentenceTransformer(MODEL_NAME)


@app.get("/health")
def health() -> dict[str, str]:
    return {
        "status": "ok",
        "model": MODEL_NAME,
    }


@app.post("/embed", response_model=EmbedResponse)
def embed(payload: EmbedRequest) -> EmbedResponse:
    text = payload.text.strip()

    if not text:
        raise HTTPException(status_code=422, detail="Text must not be empty.")

    vector = get_model().encode(text, normalize_embeddings=True).tolist()

    return EmbedResponse(
        embedding=[float(value) for value in vector],
        model=MODEL_NAME,
        dimensions=len(vector),
    )
