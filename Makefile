up:
	@echo '************  Create Kontatudo network ************'
	@echo '*'
	@echo '*'
	docker network inspect kontatudo_network >/dev/null 2>&1 || docker network create kontatudo_network

	@echo '************  Waking UP Containers ************'
	@echo '*'
	@echo '*'
	docker-compose up -d

	@echo '*'
	@echo '*'
	@echo '************  Configuring env ************'
	@echo '*'
	@echo '*'
	cp .env.dist .env
	@echo '*'
	@echo '*'
	@echo '************  Starting API ************'
	@echo '*'
	@echo '*'
	@echo '************  Installing symfony ************'
	docker exec -it kontatudo-php composer install
	@echo '*'
	@echo '*'
	@echo '************  Create database ************'
	docker exec -it kontatudo-php php bin/console doctrine:database:create --env=dev
	docker exec -it kontatudo-php php bin/console doctrine:database:create --env=test
	@echo '************  Running migrations ************'
	docker exec -it kontatudo-php php bin/console doctrine:migration:migrate --env=dev
	@echo '************  Running migrations ************'
	docker exec -it kontatudo-php php bin/console doctrine:migration:migrate --env=test
	@echo '*'
	@echo '*'
	@echo '*'
	@echo '************  Running tests  ************'
	docker exec -it kontatudo-php ./vendor/bin/simple-phpunit
