.PHONY: build install test test-filter ci bash

build:
	docker compose build

install:
	docker compose run --rm php composer install

test:
	docker compose run --rm php ./vendor/bin/pest

test-filter:
	docker compose run --rm php ./vendor/bin/pest --filter="$(FILTER)"

ci: install test

bash:
	docker compose run --rm php bash
