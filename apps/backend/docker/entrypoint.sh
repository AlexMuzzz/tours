#!/bin/sh

set -eu

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if ! grep -Eq '^APP_KEY=base64:' .env 2>/dev/null; then
    echo "APP_KEY is missing. Generating a new key..."
    php artisan key:generate --force
fi

attempt=1
max_attempts=30

until php artisan migrate --force; do
    if [ "$attempt" -ge "$max_attempts" ]; then
        echo "Database migrations failed after ${max_attempts} attempts."
        exit 1
    fi

    echo "Database is not ready yet. Retrying in 2 seconds... (${attempt}/${max_attempts})"
    attempt=$((attempt + 1))
    sleep 2
done

exec php artisan serve --host=0.0.0.0 --port=8000
