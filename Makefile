DOCKER_COMPOSE = docker-compose
CLI            = $(DOCKER_COMPOSE) run --rm richi-php-cli
SYMFONY        = $(CLI) symfony

getargs    = $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
escapeagrs = $(subst :,\:,$(1))

.PHONY: dummy migrations tests phpunit cli symfony

##
## Project maintenance ("make init", "make composer-install", "make db-init" or "make docker-up")
## -------------------------------------------------
all:
	@echo 'Please provide a command, for example, "make docker-up"'
init: docker-down-clear docker-pull docker-build docker-up
db-init: migrations fixtures
docker-up:
	$(DOCKER_COMPOSE) up -d
docker-down:
	$(DOCKER_COMPOSE) down --remove-orphans
docker-down-clear:
	$(DOCKER_COMPOSE) down -v --remove-orphans
docker-pull:
	$(DOCKER_COMPOSE) pull
docker-build:
	$(DOCKER_COMPOSE) build
composer-install:
	$(CLI) composer install
composer-update:
	$(CLI) composer update
migrations:
	$(SYMFONY) console doctrine:migrations:migrate --no-interaction
fixtures:
	$(SYMFONY) console doctrine:fixtures:load --no-interaction

##
## Code quality tests ("make tests")
## ---------------------------------
tests: phpunit

##
## Run unit tests ("make -- phpunit --filter testOne UnitTest.php" or "make -- phpunit --exclude-group database")
## ----------------------------------------------------------------
ifeq (phpunit,$(firstword $(MAKECMDGOALS)))
    PHPUNIT_ARGS         := $(call getargs)
    PHPUNIT_ARGS_ESCAPED := $(call escapeagrs, $(PHPUNIT_ARGS))
    $(eval $(PHPUNIT_ARGS_ESCAPED):dummy;@:)
endif
phpunit:
	$(CLI) ./bin/phpunit $(PHPUNIT_ARGS) $(-*-command-variables-*-)

##
## Run CLI command ("make -- cli ls -la /app")
## -----------------------------------------------
ifeq (cli,$(firstword $(MAKECMDGOALS)))
    CLI_ARGS         := $(call getargs)
    CLI_ARGS_ESCAPED := $(call escapeagrs, $(CLI_ARGS))
    $(eval $(CLI_ARGS_ESCAPED):dummy;@:)
endif
cli:
	$(CLI) $(CLI_ARGS) $(-*-command-variables-*-)

##
## Run Symfony CLI ("make sf security:check" or "make -- sf console doctrine:migrations:migrate --em=mysql_main2")
## -----------------------------------------------
ifeq (sf,$(firstword $(MAKECMDGOALS)))
    SYMFONY_ARGS         := $(call getargs)
    SYMFONY_ARGS_ESCAPED := $(call escapeagrs, $(SYMFONY_ARGS))
    $(eval $(SYMFONY_ARGS_ESCAPED):dummy;@:)
endif
sf:
	$(SYMFONY) $(SYMFONY_ARGS) $(-*-command-variables-*-)
