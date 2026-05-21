# Tour Catalog AI Embedding Service

Лёгкий FastAPI-сервис для расчёта multilingual embeddings для semantic search.

## Модель

- `sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2`

## Установка

```bash
cd /Users/alexey_muzgin/Projects/tour-catalog-ai/apps/embedding-service
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

Для локального проекта обычно достаточно Docker:

```bash
cd /Users/alexey_muzgin/Projects/tour-catalog-ai
docker compose up -d --build embedding-service
```

## Запуск

```bash
uvicorn main:app --reload --port 8001
```

Сервис в Docker работает CPU-only на `linux/amd64` и использует CPU wheel для `torch`, без GPU-зависимостей.

## Endpoints

- `GET /health`
- `POST /embed`

`POST /embed` возвращает:

- `embedding`: массив чисел
- `model`: имя Hugging Face модели
- `dimensions`: размерность embedding

Пример:

```bash
curl -X POST http://127.0.0.1:8001/embed \
  -H "Content-Type: application/json" \
  -d '{"text":"отдых у моря"}'
```
