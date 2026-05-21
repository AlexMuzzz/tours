# Tour Catalog AI

Monorepo for the Tour Catalog AI MVP.

## Structure

```text
tour-catalog-ai/
  apps/
    backend/
  docs/
  docker/
  docker-compose.yml
  Makefile
  README.md
```

## Included in this MVP

- Laravel 11 backend API in `apps/backend`
- Vue 3 + Vike frontend in `apps/frontend`
- PostgreSQL-oriented data model
- Public tours catalog API
- Admin API with Sanctum bearer tokens
- Seed data for 17 demo tours with full Russian descriptions and three admin accounts
- Backend feature tests with PostgreSQL test database defaults
- Frontend smoke tests with Vitest + Vue Test Utils
- Semantic search with a dedicated FastAPI embedding service and a multilingual Hugging Face model

## Quick start

```bash
cd /Users/alexey_muzgin/Projects/tour-catalog-ai
cp apps/backend/.env.example apps/backend/.env
cp apps/backend/.env.testing.example apps/backend/.env.testing
make backend-install
make backend-key
make backend-fresh
make backend-serve
```

## Quality Checks

```bash
cd /Users/alexey_muzgin/Projects/tour-catalog-ai
make backend-test
make frontend-test
make frontend-build
make test
```

What `make test` runs:

1. backend PHPUnit feature tests
2. frontend Vitest smoke tests
3. frontend production build

## CI

GitHub Actions workflow lives in:

- `.github/workflows/ci.yml`

It runs automatically on every `push` and `pull_request` and contains two lightweight jobs:

1. `backend`
   - installs Composer dependencies in `apps/backend`
   - copies `.env.example` and `.env.testing.example`
   - generates Laravel app keys
   - starts PostgreSQL as a GitHub Actions service
   - runs `php artisan test`
2. `frontend`
   - installs npm dependencies in `apps/frontend`
   - runs `npm run build`

Local equivalents:

```bash
cd /Users/alexey_muzgin/Projects/tour-catalog-ai
make backend-test
make frontend-build
```

## Docker notes

- `docker-compose.yml` includes `postgres`, `embedding-service`, and `backend` services.
- PostgreSQL init scripts create `tour_catalog` and `tour_catalog_test`.
- Local Docker uses `sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2` via `apps/embedding-service`.
- The embedding service runs CPU-only on `linux/amd64`; no GPU dependencies are required.
- PHPUnit tests keep `EMBEDDING_SERVICE_URL` empty and use the local deterministic fallback so test runs do not depend on Python.

## Semantic Search

- Public catalog search is now hybrid on `GET /api/tours?search=...`: direct text matches and semantic relevance are combined in one ranked response.
- Public endpoint: `GET /api/tours/search/semantic?query=...`
- Backend sends the query to `embedding-service`, receives a `384`-dimensional embedding, and compares it with `tour_embeddings.embedding` through cosine similarity.
- Local default threshold is `0.4`.
- For strong “seaside relaxation” queries such as `отдых у моря`, backend adds lightweight intent guardrails on top of embedding similarity so winter/adventure/coastal-but-not-vacation tours do not outrank beach vacations.
- If the embedding service is temporarily unavailable, catalog search gracefully falls back to lexical matching instead of failing the whole `/api/tours` request.
- Rebuild all stored embeddings after seed or content changes:

```bash
cd /Users/alexey_muzgin/Projects/tour-catalog-ai
docker compose exec -T backend php artisan embeddings:rebuild --chunk=25
```

## Documentation

- Backend setup and API details: [apps/backend/README.md](./apps/backend/README.md)
- Frontend setup and testing details: [apps/frontend/README.md](./apps/frontend/README.md)

## API Documentation

With the backend running locally, open:

- UI: [http://localhost:8000/docs/api](http://localhost:8000/docs/api)
- OpenAPI JSON: [http://localhost:8000/docs/api.json](http://localhost:8000/docs/api.json)

To try admin endpoints in the docs:

1. Login via `POST /api/admin/login`
2. Copy the returned Sanctum token
3. Authorize requests with `Authorization: Bearer <token>`

## Next MVP steps

- Add pgvector or another database-level vector similarity strategy
- Replace deterministic description generation with a real LLM provider
- Add richer frontend/admin UX polish
- Expand visual admin panel capabilities

## Test Coverage

Backend coverage includes:

- admin auth flow
- admin CRUD for tours
- nested admin resources for images, dates, and route points
- public catalog list/detail/filter/search scenarios
- hybrid catalog search ranking and semantic search validation/service invocation

Frontend coverage includes:

- home page render smoke
- tour card display
- catalog loading, empty, and error states
- admin login flow with mocked auth service
- auth store session persistence behavior
- admin guard redirect behavior
- Yandex Maps placeholders and empty route handling

## TODO

- Add Playwright E2E smoke tests once browser automation infrastructure is standardized for the project
