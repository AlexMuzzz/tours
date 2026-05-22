# Tour Catalog AI Backend

Laravel 11 backend API for the Tour Catalog AI MVP.

## Stack

- PHP 8.3
- Laravel 11
- PostgreSQL
- REST API
- Laravel Sanctum
- PHPUnit

## Architecture

- `app/Http/Controllers` contains thin API controllers
- `app/Http/Requests` contains validation rules
- `app/Http/Resources` formats API responses
- `app/Models` contains Eloquent models and relationships
- `app/Services` contains business logic and future AI integration seams
- `app/Http/Middleware/EnsureUserIsAdmin.php` protects admin routes

Key domain entities:

- `User`
- `Tour`
- `TourImage`
- `TourDate`
- `TourRoutePoint`
- `TourEmbedding`

## Installation

```bash
cd /Users/alexey_muzgin/Projects/tour-catalog-ai/apps/backend
composer install
cp .env.example .env
cp .env.testing.example .env.testing
php artisan key:generate
php artisan migrate
php artisan db:seed
```

## Environment

Default `.env.example` values are prepared for Docker/PostgreSQL:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=tour_catalog
DB_USERNAME=tour_user
DB_PASSWORD=tour_password
```

Test defaults are prepared in `.env.testing.example`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=tour_catalog_test
DB_USERNAME=tour_user
DB_PASSWORD=tour_password
```

## Running locally

```bash
cd /Users/alexey_muzgin/Projects/tour-catalog-ai/apps/backend
php artisan serve
```

## API Documentation

Open the interactive API docs locally at:

- `http://localhost:8000/docs/api`

Open the generated OpenAPI JSON at:

- `http://localhost:8000/docs/api.json`

How to authorize admin requests in the docs UI:

1. Call `POST /api/admin/login` with any seeded admin account and password `password`.
2. Copy the returned token.
3. Open `http://localhost:8000/docs/api`.
4. Use the authorization control in the docs UI and paste `Bearer <token>`.

Admin endpoints are documented with a Bearer token security scheme. Public endpoints do not require authorization.

## Migrations and seeders

```bash
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed
```

## Tests

```bash
php artisan test
```

From the monorepo root:

```bash
cd /Users/alexey_muzgin/Projects/tour-catalog-ai
make backend-test
```

The test suite is configured to use PostgreSQL, not SQLite in-memory, because the project stores embeddings in `jsonb`.

Covered scenarios include:

- admin login, me, logout
- admin-only protection for `/api/admin/*`
- tour CRUD from admin API
- create/update/delete for tour images, dates, and route points
- public tours list/detail/filter/search flows
- hybrid catalog search ranking and semantic search validation/service wiring

## Admin credentials

- Email: `admin@example.com`
- Email: `editor@example.com`
- Email: `operator@example.com`
- Password: `password`

## Authentication

- Admin API uses Sanctum bearer tokens
- Login via `POST /api/admin/login`
- Pass the returned token as `Authorization: Bearer <token>`

Example:

```bash
curl -H "Authorization: Bearer <token>" http://localhost:8000/api/admin/me
```

## Main endpoints

Public:

- `GET /api/tours`
- `GET /api/tours/{slug}`
- `GET /api/tours/search/semantic?query=...`

Admin auth:

- `POST /api/admin/login`
- `POST /api/admin/logout`
- `GET /api/admin/me`

Admin tours:

- `GET /api/admin/tours`
- `POST /api/admin/tours`
- `GET /api/admin/tours/{id}`
- `PUT /api/admin/tours/{id}`
- `DELETE /api/admin/tours/{id}`

Admin related resources:

- `POST /api/admin/tours/{id}/images`
- `DELETE /api/admin/tour-images/{id}`
- `POST /api/admin/tours/{id}/dates`
- `PUT /api/admin/tour-dates/{id}`
- `DELETE /api/admin/tour-dates/{id}`
- `POST /api/admin/tours/{id}/route-points`
- `PUT /api/admin/tour-route-points/{id}`
- `DELETE /api/admin/tour-route-points/{id}`

## Semantic search

- `EmbeddingService` calls `POST /embed` on `EMBEDDING_SERVICE_URL` and expects a numeric embedding array in response.
- Local Docker is configured to use `http://embedding-service:8001`.
- Local default threshold is `0.4`.
- `GET /api/tours?search=...` uses hybrid ranking: direct catalog text matches and semantic candidates are merged into one response, while catalog filters and pagination stay unchanged.
- If semantic embeddings are temporarily unavailable, catalog search falls back to lexical matching instead of returning a 503 for the whole list endpoint.
- `SemanticSearchService` compares cosine similarity over stored embeddings, keeps only `active` tours, sorts matches by `score DESC`, and applies lightweight intent guardrails for strong vacation-at-sea queries such as `отдых у моря`.
- Testing keeps `EMBEDDING_SERVICE_URL` empty and enables fallback embeddings so PHPUnit does not depend on the Python service.

Rebuild embeddings after editing seed data or tour content:

```bash
docker compose exec -T backend php artisan embeddings:rebuild --chunk=25
```

## MVP stubs

- `EmbeddingService` falls back to a deterministic local embedding strategy only in local/testing environments where fallback is explicitly enabled
- Images are stored as plain URL strings
- Real file uploads are intentionally out of scope for this MVP

## TODO

- Add pgvector or vector similarity search
- Add visual admin panel
- For production SPA flows, switch to cookie-based Sanctum authentication
