COMPONENT := queues
CODE_CONTAINER := phpfpm
CLI_CONTAINER := phpcli
APP_ROOT := /app/queues
IMAGES ?= false
CI ?= false
ENV ?= staging

all: dev logs

dev:
	@docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml up -d
	@sleep 2

enter:
	@./ops/scripts/enter.sh ${COMPONENT}

kill:
	@docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml kill

nodev:
	@docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml kill
	@docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml rm -f
ifeq ($(IMAGES),true)
	@docker rmi ${COMPONENT}_${CODE_CONTAINER}
	@docker rmi ${COMPONENT}_${CLI_CONTAINER}

endif

tests: test

test: all-test

fast-test: unit

slow-test: integration functional

all-test: fast-test slow-test

unit:
ifeq ($(shell uname -s),Linux)
		@./ops/scripts/unit.sh ${CI}
else
	 @docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml run \
	 --entrypoint "${APP_ROOT}/ops/scripts/operations.sh unit" --rm ${CODE_CONTAINER}

endif

integration:
ifeq ($(shell uname -s),Linux)
		@./ops/scripts/integration.sh ${CI}
else
	 @docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml run \
	 --entrypoint "${APP_ROOT}/ops/scripts/operations.sh integration" --rm ${CODE_CONTAINER}

endif

functional:
ifeq ($(shell uname -s),Linux)
		@./ops/scripts/functional.sh ${CI}
else
		@docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml run \
		--entrypoint "${APP_ROOT}/ops/scripts/operations.sh functional" --rm ${CODE_CONTAINER}
endif

fixtures:
ifeq ($(shell uname -s),Linux)
		#@php app/console fixtures
else
	 #@docker exec -t $(shell docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml ps -q ${CODE_CONTAINER}) \
	 ${APP_ROOT}/ops/scripts/operations.sh fixtures
endif

pack: bundle

package: bundle

bundle:
	@./ops/scripts/bundle.sh

push: publish

publish:
	@./ops/scripts/s3push.sh

deps: dependencies

dependencies:
ifeq ($(shell uname -s),Linux)
		@composer install --no-interaction
else
	 @docker exec -t $(shell docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml ps -q ${CODE_CONTAINER}) \
	 ${APP_ROOT}/ops/scripts/operations.sh dependencies
endif

deploy:
ifeq ($(shell uname -s),Linux)
		@./ops/scripts/deployment.sh ${ENV}
else
		@echo Environment to deploy: ${ENV}
		@docker exec -t $(shell docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml ps -q ${CODE_CONTAINER}) \
 	 ${APP_ROOT}/ops/scripts/operations.sh deploy $(ENV)
endif

ps: status

restart: nodev dev fixtures logs

logs:
	@docker-compose -p ${COMPONENT} -f ops/docker/docker-compose.yml logs

tag: # List last tag for this repo
	@git tag -l | sort -r |head -1

.PHONY: test docs