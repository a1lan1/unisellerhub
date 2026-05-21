install:
	@make build
	@make up

reinstall:
	make storage-clear
	make prune
	make destroy
	make down-v
	rm -rf vendor node_modules public/build public/storage storage/logs/.initialized
	make install

build:
	docker compose build

up:
	docker compose --profile '*' up -d

storage-clear:
	@docker compose exec app php artisan storage:clear || true
	@docker compose exec app php artisan reports:cleanup-old
	rm -rf coverage.xml coverage

clear:
	docker compose exec app php artisan queue:flush || true
	docker compose exec app php artisan horizon:purge || true
	docker compose exec app php artisan storage:clear || true
	docker compose exec app php artisan reports:cleanup-old
	make cache-clear

stop:
	docker compose stop

down:
	docker compose down

down-v:
	docker compose down --remove-orphans --volumes

destroy:
	docker compose down --rmi all --volumes --remove-orphans

prune:
	docker builder prune --all --force

ps:
	docker compose ps

shell:
	docker compose exec app sh

tinker:
	docker compose exec app php artisan tinker

dump:
	docker compose exec app php artisan dump-server

permissions: wait-for-app
	@docker compose exec app chown -R www-data:www-data storage bootstrap/cache
	@docker compose exec app chmod -R 775 storage bootstrap/cache

wait-for-app:
	@echo "Waiting for the app container to be ready..."
	@until docker compose exec app true 2>/dev/null; do sleep 1; done

migrate:
	docker compose exec app php artisan migrate

dbs: wait-for-app
	docker compose exec app php artisan migrate:fresh --seed

scout-reindex: wait-for-app
	docker compose exec app php artisan scout:reimport-all

optimize:
	composer dump-autoload -o
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan optimize

wayfinder:
	docker compose exec app php artisan wayfinder:generate --with-form

optimize-clear:
	docker compose exec app php artisan optimize:clear

cache:
	docker compose exec app composer dump-autoload --optimize
	make optimize
	docker compose exec app php artisan config:cache
	docker compose exec app php artisan event:cache
	docker compose exec app php artisan view:cache

cache-clear:
	docker compose exec app composer clear-cache
	make optimize-clear
	docker compose exec app php artisan event:clear
	docker compose exec app php artisan view:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan route:clear
	docker compose exec app composer dump-autoload --optimize

redis:
	docker compose exec redis redis-cli

backup:
	docker compose exec app php artisan backup:run

horizon-clear:
	docker compose exec app php artisan cache:clear || true
	docker compose exec app php artisan horizon:forget --all || true

ide:
	@docker compose exec app php artisan clear-compiled
	@docker compose exec app php artisan ide-helper:generate
	@docker compose exec app php artisan ide-helper:meta
	@docker compose exec app php artisan ide-helper:models -RW

test:
	@docker compose exec app php artisan config:clear --env=testing
	@docker compose run --rm -e APP_ENV=testing app php artisan test --parallel

lint:
	@make ide
	@make lint-back
	@make lint-front

lint-back:
	@echo "Linting backend..."
	docker compose exec app ./vendor/bin/phpstan analyse --memory-limit=1G
	docker compose exec app ./vendor/bin/rector process --ansi
	docker compose exec app ./vendor/bin/pint --parallel

lint-front:
	@echo "Linting frontend..."
	docker compose exec app yarn lint
	docker compose exec app yarn types:check
