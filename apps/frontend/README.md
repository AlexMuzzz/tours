# Tour Catalog AI Frontend

Frontend MVP для проекта **Tour Catalog AI**.

## Стек

- Vue 3
- Vike
- Vite
- TypeScript
- Tailwind CSS 4
- PrimeVue 4
- tailwindcss-primeui
- Pinia
- Node.js >= 20

## Требования

- Node.js `>= 20`
- Доступный backend API на `http://localhost:8000/api`

## Установка

```bash
cd apps/frontend
npm install
```

## Настройка окружения

Создайте `.env` на основе примера:

```bash
cp .env.example .env
```

Доступные переменные:

```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_YANDEX_MAPS_API_KEY=your_yandex_maps_api_key
```

## Запуск

Dev server:

```bash
npm run dev
```

Production build:

```bash
npm run build
```

Preview production build:

```bash
npm run preview
```

Frontend tests:

```bash
npm run test
```

## Реализованные страницы

- `/`
  - hero section
  - описание сервиса
  - подборка туров
- `/tours`
  - карточки туров
  - обычный поиск
  - semantic search
  - фильтры
  - сортировка
  - пагинация
- `/tours/@slug`
  - галерея
  - описание
  - даты и цены
  - route points
  - блок карты Yandex Maps с fallback
- `/admin/login`
  - логин администратора
- `/admin/tours`
  - таблица туров
  - переход к созданию/редактированию
- `/admin/tours/create`
  - форма создания тура
- `/admin/tours/@id/edit`
  - редактирование основной информации
  - images
  - dates
  - route points

## Используемые backend endpoints

### Public API

- `GET /api/tours`
- `GET /api/tours/{slug}`
- `GET /api/tours/search/semantic?query=`

### Admin auth

- `POST /api/admin/login`
- `POST /api/admin/logout`
- `GET /api/admin/me`

### Admin tours

- `GET /api/admin/tours`
- `POST /api/admin/tours`
- `GET /api/admin/tours/{id}`
- `PUT /api/admin/tours/{id}`
- `DELETE /api/admin/tours/{id}`

### Admin nested resources

- `POST /api/admin/tours/{id}/images`
- `DELETE /api/admin/tour-images/{id}`
- `POST /api/admin/tours/{id}/dates`
- `PUT /api/admin/tour-dates/{id}`
- `DELETE /api/admin/tour-dates/{id}`
- `POST /api/admin/tours/{id}/route-points`
- `PUT /api/admin/tour-route-points/{id}`
- `DELETE /api/admin/tour-route-points/{id}`

## Как работает admin login

1. Страница `/admin/login` отправляет `POST /api/admin/login`
2. Полученный `Bearer token` сохраняется в `localStorage`
3. Текущий пользователь сохраняется в Pinia store
4. Все admin API requests автоматически отправляют заголовок:

```http
Authorization: Bearer <token>
```

## Yandex Maps

Карта на странице тура использует:

- `VITE_YANDEX_MAPS_API_KEY`
- route points из backend API

Если ключ не задан, приложение не падает и показывает fallback:

- `Yandex Maps API key is not configured`

Если route points пустые, приложение показывает:

- `Маршрут пока не добавлен`

## SSR / client-only

- Публичные страницы подготовлены под Vike SSR
- Browser-only логика выполняется только на клиенте:
  - `localStorage`
  - `window`
  - `document`
  - Yandex Maps
- Admin pages переведены в client-only режим через Vike config

## Тесты

Минимальный smoke suite построен на:

- Vitest
- Vue Test Utils

Покрытые сценарии:

- home page рендерится без падения
- tour card показывает title/category/duration/price
- catalog page показывает error, empty и loading states
- admin login вызывает auth flow через mocked service
- Pinia auth store сохраняет token и user
- admin guard редиректит без токена и уводит с login при наличии токена
- TourMap показывает placeholder без API key
- TourMap не падает при пустом route_points

Запуск:

```bash
cd /Users/alexey_muzgin/Projects/tour-catalog-ai/apps/frontend
npm run test
npm run build
```

## TODO

- Добавить richer loading skeletons и micro-interactions
- Подключить реальные embeddings/semantic search после обновления backend
- Поддержать upload flow, если backend позже начнёт принимать файлы
- Добавить Playwright E2E smoke tests, когда для проекта будет зафиксирована browser automation инфраструктура
