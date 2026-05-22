# Tour Catalog AI

Полнофункциональный каталог туров с AI-функциями в формате монорепозитория. Проект включает публичный каталог, админ-панель, семантический поиск, отдельный embedding-service и набор автоматических проверок для backend и frontend.

## Возможности

- Публичный каталог туров с фильтрами, сортировкой и пагинацией
- Детальные страницы туров с описанием, датами, ценами, галереей и маршрутными точками
- Админ-панель для управления турами, изображениями, датами и маршрутами
- Семантический поиск на основе Hugging Face embeddings
- Отдельный FastAPI embedding-service
- Аутентификация админки через Laravel Sanctum
- Swagger / OpenAPI документация
- Docker-окружение для локального запуска
- Автоматические тесты для backend и frontend

## Что внутри

- Laravel 11 API в `apps/backend`
- Vue 3 + Vike frontend в `apps/frontend`
- FastAPI embedding-service в `apps/embedding-service`
- PostgreSQL-ориентированная модель данных
- Демо-данные: 17 туров с полными русскоязычными описаниями и 3 admin-аккаунта
- Feature-тесты для backend и smoke / component tests для frontend

## Структура

```text
tours/
  apps/
    backend/
    frontend/
    embedding-service/
  docs/
  docker/
  docker-compose.yml
  Makefile
  README.md
```

## Быстрый старт

```bash
git clone git@github.com:AlexMuzzz/tours.git
cd tours
cp apps/backend/.env.example apps/backend/.env
cp apps/backend/.env.testing.example apps/backend/.env.testing
make backend-install
make backend-key
make backend-fresh
make backend-serve
```

## Проверка качества

```bash
make backend-test
make frontend-test
make frontend-build
make test
```

`make test` запускает:

1. backend PHPUnit feature tests
2. frontend Vitest smoke tests
3. frontend production build

## Семантический поиск

Семантический поиск позволяет искать туры не только по точным словам, но и по смыслу. Например, запрос `отдых у моря` может найти туры про пляжный или прибрежный отдых, даже если формулировки в описании отличаются.

- Гибридный поиск работает в `GET /api/tours?search=...`: текстовые совпадения и семантическая релевантность объединяются в единую выдачу
- Отдельный endpoint для проверки семантического поиска: `GET /api/tours/search/semantic?query=...`
- Backend отправляет запрос в `embedding-service`, получает embedding размерности `384` и сравнивает его с `tour_embeddings.embedding` по cosine similarity
- Локальный threshold по умолчанию: `0.4`
- Для запросов вроде `отдых у моря` backend добавляет лёгкие intent guardrails, чтобы зимние и приключенческие туры не обгоняли пляжные направления
- Если embedding-service временно недоступен, каталог автоматически откатывается к обычному lexical search вместо полного падения поиска

Пересборка embeddings после изменения seed-данных или контента:

```bash
docker compose exec -T backend php artisan embeddings:rebuild --chunk=25
```

## AI Workflow

Проект собирался в агентском workflow, с разбиением на независимые и проверяемые этапы:

1. Backend API и модель данных
2. Аутентификация админки и CRUD-операции
3. Публичный frontend и интерфейсы админки
4. Семантический поиск и embedding-service
5. Тесты, CI, документация и smoke-проверки

AI-агенты использовались как ассистенты разработки для реализации, рефакторинга, генерации тестов и технического аудита. Каждый крупный этап проверялся через автоматические тесты, сборки, ручные API-проверки и локальные smoke-сценарии.

## CI

GitHub Actions workflow находится в `.github/workflows/ci.yml`.

Он запускается на каждый `push` и `pull_request` и состоит из двух лёгких job:

1. `backend`
   - устанавливает Composer-зависимости в `apps/backend`
   - копирует `.env.example` и `.env.testing.example`
   - генерирует Laravel app keys
   - поднимает PostgreSQL как GitHub Actions service
   - запускает `php artisan test`
2. `frontend`
   - устанавливает npm-зависимости в `apps/frontend`
   - запускает `npm run build`

Локальные эквиваленты:

```bash
make backend-test
make frontend-build
```

## Docker и инфраструктура

- `docker-compose.yml` включает `postgres`, `embedding-service` и `backend`
- PostgreSQL init scripts создают `tour_catalog` и `tour_catalog_test`
- Локальный Docker использует модель `sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2` через `apps/embedding-service`
- Embedding-service работает на CPU и не требует GPU-зависимостей
- PHPUnit-тесты оставляют `EMBEDDING_SERVICE_URL` пустым и используют локальный deterministic fallback, чтобы тесты не зависели от Python-сервиса

## Документация

- Настройка backend и детали API: [apps/backend/README.md](./apps/backend/README.md)
- Настройка frontend и детали тестирования: [apps/frontend/README.md](./apps/frontend/README.md)

## API-документация

При локально запущенном backend:

- UI: [http://localhost:8000/docs/api](http://localhost:8000/docs/api)
- OpenAPI JSON: [http://localhost:8000/docs/api.json](http://localhost:8000/docs/api.json)

Чтобы протестировать админские методы в документации:

1. Выполните `POST /api/admin/login`
2. Скопируйте возвращённый Sanctum token
3. Передавайте его как `Authorization: Bearer <token>`

## Покрытие тестами

Backend покрывает:

- auth flow админки
- CRUD для туров
- вложенные admin resources для изображений, дат и маршрутных точек
- сценарии списка, детальной страницы, фильтров и поиска в публичном каталоге
- ранжирование hybrid search и проверку semantic search

Frontend покрывает:

- smoke-рендер главной страницы
- отображение карточек туров
- состояния загрузки, пустого результата и ошибок в каталоге
- admin login flow с mocked auth service
- сохранение admin session в auth store
- redirect-логику admin guard
- placeholder-логику карт и пустых маршрутов

## Следующие шаги

- Добавить `pgvector` или другую database-level стратегию векторного поиска
- Заменить deterministic description generation на реального LLM-провайдера
- Расширить UX публичного каталога и админки
- Добавить Playwright E2E smoke tests
