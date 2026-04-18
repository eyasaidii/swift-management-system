# Makefile - helper targets for docker workflow

.PHONY: up up-oracle build-app logs migrate stop clean-oracle prod prod-build prod-stop prod-logs

# ── Développement ─────────────────────────────────────────
up:
	docker-compose up -d

up-oracle:
	docker-compose -f docker-compose.yml -f docker-compose.oracle.yml up -d --build

build-app:
	docker-compose build --no-cache app

logs:
	docker-compose logs -f app

migrate:
	docker exec btl_swift_app php artisan migrate --force

stop:
	docker-compose down

clean-oracle:
	-docker rm -f btl_oracle
	-docker volume rm btl_swift_platform_main_oracle-data || true

# ── Production (Nginx + PHP-FPM) ─────────────────────────
prod:
	docker-compose -f docker-compose.prod.yml up -d --build

prod-build:
	docker-compose -f docker-compose.prod.yml build --no-cache

prod-stop:
	docker-compose -f docker-compose.prod.yml down

prod-logs:
	docker-compose -f docker-compose.prod.yml logs -f

prod-migrate:
	docker exec btl_swift_app php artisan migrate --force

# ── Tests ─────────────────────────────────────────────────
test:
	docker exec btl_swift_app ./vendor/bin/pest

lint:
	docker exec btl_swift_app php artisan route:list
