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

run:
	php artisan serve $(if $h, --host $h,)

migration:
	php artisan make:migration ${n}

migrate:
	php artisan migrate
