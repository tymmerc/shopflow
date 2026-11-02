#!/bin/sh
set -e
# Premier lancement : installer les dépendances PHP si vendor/ manque
if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi
mkdir -p var && chown -R www-data:www-data var
exec docker-php-entrypoint "$@"
