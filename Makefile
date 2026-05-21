.PHONY: backend-install backend-key backend-migrate backend-seed backend-fresh backend-test backend-serve frontend-test frontend-build test

backend-install:
	docker compose run --rm backend composer install

backend-key:
	docker compose run --rm backend php artisan key:generate --force

backend-migrate:
	docker compose run --rm backend php artisan migrate --force

backend-seed:
	docker compose run --rm backend php artisan db:seed --force

backend-fresh:
	docker compose run --rm backend php artisan migrate:fresh --seed --force

backend-test:
	docker compose run --rm \
		-e APP_ENV=testing \
		-e DB_CONNECTION=pgsql \
		-e DB_HOST=postgres \
		-e DB_PORT=5432 \
		-e DB_DATABASE=tour_catalog_test \
		-e DB_USERNAME=tour_user \
		-e DB_PASSWORD=tour_password \
		-e EMBEDDING_FALLBACK_ENABLED=true \
		backend php artisan test

backend-serve:
	docker compose up -d --build postgres embedding-service backend

frontend-test:
	cd apps/frontend && npm run test

frontend-build:
	cd apps/frontend && npm run build

test: backend-test frontend-test frontend-build
