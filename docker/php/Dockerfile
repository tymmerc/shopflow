FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev \
    && docker-php-ext-install pdo_mysql intl zip opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Dépendances d'abord : cette couche reste en cache tant que composer.json/lock ne bougent pas
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

# Puis le code de l'appli Symfony
COPY . .
RUN composer dump-autoload --optimize \
    && composer run-script auto-scripts \
    && mkdir -p var && chown -R www-data:www-data var
