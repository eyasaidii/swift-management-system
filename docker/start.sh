#!/bin/sh
# Démarre php artisan serve + le queue worker en parallèle

PHP_OPTS="-d opcache.enable=1 \
          -d opcache.enable_cli=1 \
          -d opcache.memory_consumption=256 \
          -d opcache.max_accelerated_files=20000 \
          -d opcache.validate_timestamps=0 \
          -d opcache.interned_strings_buffer=16"

# ── Cache Laravel ───────────────────────────────────────────────
php /var/www/artisan config:cache
php /var/www/artisan route:cache
php /var/www/artisan view:cache

# ── Queue worker en arrière-plan ────────────────────────────────
php $PHP_OPTS /var/www/artisan queue:work \
    --sleep=3 --tries=3 --timeout=60 --queue=default &

# ── PHP artisan serve en avant-plan (PHP_CLI_SERVER_WORKERS=4) ──
exec php $PHP_OPTS /var/www/artisan serve --host=0.0.0.0 --port=8000 --no-reload
