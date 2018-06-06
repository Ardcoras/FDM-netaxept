SHELL := /bin/sh
CURRDIR := $(shell basename $(shell pwd) | sed 's/-/_/g')
.PHONY: help
help:
	@echo "$$(grep -hE '^\S+:.*##' $(MAKEFILE_LIST) | sed -e 's/:.*##\s*/:/' | column -c2 -t -s :)"

docker-config: ## output config with variable subtitution
docker-config:
	@CURRDIR=$(CURRDIR) docker-compose config

docker-start: ## start containers
docker-start:
	@CURRDIR=$(CURRDIR) docker-compose up --remove-orphans -d

docker-stop: ## stop containers
docker-stop:
	@CURRDIR=$(CURRDIR) docker-compose stop

docker-remove: ## remove containers
docker-remove:
	@CURRDIR=$(CURRDIR) docker-compose down

docker-restart: ## stop, remove and start containers
docker-restart: docker-remove docker-start

docker-rebuild: ## rebuild containers without cache
docker-rebuild:
	@CURRDIR=$(CURRDIR) docker-compose build --no-cache

docker-clean: ## Remove all containers, rebuild them, and start them.
docker-clean: docker-remove docker-rebuild docker-start

docker-shell: ## Open a shell session on the PHP container
docker-shell:
	@docker exec -it $(CURRDIR)_php /bin/sh

docker-logs: ## follow all containers' logs
docker-logs:
	@CURRDIR=$(CURRDIR) docker-compose logs --tail="10" -f

codecheck: ## Check that all the codeses are nice.
codecheck: ecsfix phpstan

ecs: ## Invoke EasyCodingStandard on the src/ directory
ecs:
	@vendor/bin/ecs check --no-progress-bar -- src/ tests/

ecsfix: ## Invoke EasyCodingStandard on the src/ directory
ecsfix:
	@vendor/bin/ecs check --no-progress-bar --fix -- src/ tests/

phpstan: ## Invoke PHP static analysis on the src/ directory
phpstan:
	@vendor/bin/phpstan analyse -l max --no-interaction --no-progress -- src/ tests/

test: ## Run tests
test:
	@vendor/bin/phpunit