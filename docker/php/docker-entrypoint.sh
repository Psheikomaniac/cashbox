#!/bin/sh
set -e

# Für Entwicklungsumgebung
if [ "$APP_ENV" = "dev" ]; then
  # Composer-Abhängigkeiten installieren, wenn sie fehlen
  if [ ! -d "/var/www/vendor" ] || [ ! -f "/var/www/vendor/autoload.php" ]; then
    echo "Installing dependencies..."
    composer install --no-interaction
  fi

  # Datenbank-Migrations ausführen
  echo "Running database migrations..."
  php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Für Produktionsumgebung
else
  # Nur Produktionsabhängigkeiten installieren, wenn sie fehlen
  if [ ! -d "/var/www/vendor" ] || [ ! -f "/var/www/vendor/autoload.php" ]; then
    echo "Installing production dependencies..."
    composer install --no-interaction --no-dev --optimize-autoloader
  fi

  # Cache leeren und aufwärmen
  echo "Clearing and warming up cache..."
  php bin/console cache:clear --no-debug --env=prod
  php bin/console cache:warmup --no-debug --env=prod

  # Datenbank-Migrations ausführen
  echo "Running database migrations..."
  php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
fi

# Ausführen des übergebenen Befehls
exec "$@"
