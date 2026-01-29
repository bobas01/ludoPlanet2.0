FROM php:8.4-fpm-alpine

RUN apk add --no-cache curl ca-certificates mariadb-dev && \
    docker-php-ext-install pdo_mysql && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY composer.json ./

RUN composer install

COPY . .

EXPOSE 9000