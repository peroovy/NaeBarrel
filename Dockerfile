FROM bitnami/laravel:9-debian-10

WORKDIR /src

EXPOSE 1337

COPY composer.json .

RUN apt update && \
    apt install make && \
    composer install --no-scripts

COPY . .
