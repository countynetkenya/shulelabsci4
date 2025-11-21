SHELL := /bin/bash
COMPOSE := docker compose -f .devcontainer/docker-compose.yml
STACK_COMPOSE := docker compose

.PHONY: up down logs bash reset-db docker-up docker-down docker-rebuild docker-logs docker-bash docker-composer-ci3 docker-composer-ci4

up:
$(COMPOSE) up -d

down:
$(COMPOSE) down

logs:
$(COMPOSE) logs -f app

bash:
$(COMPOSE) exec app bash

reset-db:
$(COMPOSE) exec db bash -c "mysql -uroot -pshulelabs -e \"DROP DATABASE IF EXISTS shulelabs; CREATE DATABASE shulelabs; GRANT ALL PRIVILEGES ON shulelabs.* TO 'shulelabs'@'%' IDENTIFIED BY 'shulelabs'; FLUSH PRIVILEGES;\""

docker-up:
$(STACK_COMPOSE) up -d --build

docker-down:
$(STACK_COMPOSE) down

docker-rebuild:
$(STACK_COMPOSE) build --no-cache

docker-logs:
$(STACK_COMPOSE) logs -f app

docker-bash:
$(STACK_COMPOSE) exec app bash

docker-composer-ci3:
$(STACK_COMPOSE) exec app bash -c "scripts/docker/run-composer.sh . install --no-dev --optimize-autoloader"

docker-composer-ci4:
$(STACK_COMPOSE) exec app bash -c "scripts/docker/run-composer.sh ci4 install --no-dev --optimize-autoloader"
