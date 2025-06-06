#!/bin/sh
set -e

# Installieren von Abhängigkeiten nur, wenn sie noch nicht vorhanden sind
if [ ! -d "/var/www/vendor" ]; then
  echo "Installiere Abhängigkeiten..."
  apk update
  apk add --no-cache git unzip libpq-dev postgresql-dev libpng-dev libjpeg-turbo-dev freetype-dev zlib-dev libzip-dev
  docker-php-ext-configure gd --with-freetype --with-jpeg
  docker-php-ext-install -j$(nproc) pdo pdo_pgsql zip gd
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
  composer install --no-interaction
  echo "Abhängigkeiten installiert!"
fi

# Anwenden von Migrations statt direktem Schema-Update
if [ "$APP_ENV" = "dev" ]; then
  php bin/console doctrine:migrations:migrate --no-interaction
fi

# Ausführen des ursprünglichen Befehls
exec "$@"
