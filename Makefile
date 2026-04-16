# Makefile - helper targets for docker workflow

.PHONY: up up-oracle build-app logs migrate stop clean-oracle

up:
	docker-compose up -d

up-oracle:
	# Start app + optional oracle container (oracle not exposed to host by default)
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
