ups:
	docker ps -a

img:
	docker images -a

dev:
	docker-compose up --build

up:
	docker-compose up -d --build

down:
	docker-compose down

build:
	docker-compose build --no-cache

command:
	docker exec -it app ${c}

help:
	php artisan

run:
	php artisan serve $(if $h, --host $h,)

migrate:
	php artisan migrate

reset:
	php artisan migrate:reset ${o}

refresh:
	php artisan migrate:refresh ${o}

rollback:
	php artisan migrate:rollback ${o}

migration:
	php artisan make:migration ${n}

controller:
	php artisan make:controller ${o} ${n}

resource:
	php artisan make:resource ${o} ${n}

model:
	php artisan make:model ${o} ${n}

routes:
	php artisan route:list ${o}

switch_on:
	php artisan up ${o}

switch_off:
	php artisan down ${o}
