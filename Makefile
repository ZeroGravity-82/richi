DOCKER_COMPOSE = docker compose
PHP_CLI        = $(DOCKER_COMPOSE) run --rm richi-php-cli
SYMFONY        = $(PHP_CLI) symfony
NODEJS_CLI     = $(DOCKER_COMPOSE) run --rm richi-nodejs-cli

getargs    = $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
escapeagrs = $(subst :,\:,$(1))

.PHONY: dummy migrations tests

##
## Project maintenance ("make init", "make composer-install", "make db-init" or "make docker-up")
## ----------------------------------------------------------------------------------------------
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
	$(PHP_CLI) composer install
composer-update:
	$(PHP_CLI) composer update
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
## --------------------------------------------------------------------------------------------------------------
ifeq (phpunit,$(firstword $(MAKECMDGOALS)))
    PHPUNIT_ARGS         := $(call getargs)
    PHPUNIT_ARGS_ESCAPED := $(call escapeagrs, $(PHPUNIT_ARGS))
    $(eval $(PHPUNIT_ARGS_ESCAPED):dummy;@:)
endif
phpunit:
	$(PHP_CLI) ./bin/phpunit $(PHPUNIT_ARGS) $(-*-command-variables-*-)

##
## Run PHP CLI command ("make -- php-cli ls -la /app")
## ---------------------------------------------------
ifeq (php-cli,$(firstword $(MAKECMDGOALS)))
    PHP_CLI_ARGS         := $(call getargs)
    PHP_CLI_ARGS_ESCAPED := $(call escapeagrs, $(PHP_CLI_ARGS))
    $(eval $(PHP_CLI_ARGS_ESCAPED):dummy;@:)
endif
php-cli:
	$(PHP_CLI) $(PHP_CLI_ARGS) $(-*-command-variables-*-)

##
## Run Symfony CLI ("make sf security:check" or "make -- sf console cache:clear --env=dev")
## ----------------------------------------------------------------------------------------
ifeq (sf,$(firstword $(MAKECMDGOALS)))
    SYMFONY_ARGS         := $(call getargs)
    SYMFONY_ARGS_ESCAPED := $(call escapeagrs, $(SYMFONY_ARGS))
    $(eval $(SYMFONY_ARGS_ESCAPED):dummy;@:)
endif
sf:
	$(SYMFONY) $(SYMFONY_ARGS) $(-*-command-variables-*-)

##
## Run Node CLI command ("make nodejs-cli npm install" or "make -- nodejs-cli node --version")
## -------------------------------------------------------------------------------------------
ifeq (nodejs-cli,$(firstword $(MAKECMDGOALS)))
    NODEJS_CLI_ARGS         := $(call getargs)
    NODEJS_CLI_ARGS_ESCAPED := $(call escapeagrs, $(NODEJS_CLI_ARGS))
    $(eval $(NODEJS_CLI_ARGS_ESCAPED):dummy;@:)
endif
nodejs-cli:
	$(NODEJS_CLI) $(NODEJS_CLI_ARGS) $(-*-command-variables-*-)
