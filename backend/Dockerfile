FROM php:8.4-fpm-alpine

ENV APP_ENV=prod
ENV APP_DEBUG=0

RUN apk add --no-cache curl ca-certificates mariadb-dev vim openssl && \
    docker-php-ext-install pdo_mysql && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# Install des deps avec lock pour build reproductible (sans dev en prod)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs

COPY . .

RUN composer dump-autoload --optimize --no-dev

# Entrypoint : génère les clés JWT dans un volume si absentes (persistance Dokploy).
COPY bin/entrypoint.sh /app/bin/entrypoint.sh
RUN chmod +x /app/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/app/bin/entrypoint.sh"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/router.php"]