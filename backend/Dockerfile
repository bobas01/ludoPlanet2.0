FROM php:8.4-fpm-alpine

ENV APP_ENV=prod
ENV APP_DEBUG=0

RUN apk add --no-cache curl ca-certificates mariadb-dev vim && \
    docker-php-ext-install pdo_mysql && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# Install des deps avec lock pour build reproductible (sans dev en prod)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs

COPY . .

# Symfony lit /app/.env au démarrage. Dokploy injecte les variables via env_file
# sans copier ce fichier (il est ignoré par git). Un fichier vide suffit :
# les variables déjà présentes dans l'environnement ne sont pas écrasées.
RUN if [ ! -f .env ]; then touch .env; fi && chmod a+r .env

RUN composer dump-autoload --optimize --no-dev

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/router.php"]