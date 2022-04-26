
bash:
	docker-compose exec app bash

build:
	docker-compose up --build --remove-orphans

build-d:
	docker-compose up --build --remove-orphans -d

up:
	docker-compose up --remove-orphans -d

up-nd:
	docker-compose up --remove-orphans
