help:           ## Show this help.
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

bash:
	docker-compose exec app bash

build:
	docker-compose up --build --remove-orphans -d

build-nd:
	docker-compose up --build --remove-orphans -d

up:
	docker-compose up --remove-orphans -d

up-nd:
	docker-compose up --remove-orphans
