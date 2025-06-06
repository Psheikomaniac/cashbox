#!/bin/bash
# docker/php/run-tests.sh
set -e

# Warte auf die Datenbank
echo "Warte auf Datenbank..."
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  sleep 1
done

# Erstelle die Testdatenbank, falls sie nicht existiert
echo "Prüfe Testdatenbank..."
php bin/console doctrine:database:create --if-not-exists --env=test

# Führe Migrationen für die Testumgebung aus
echo "Führe Migrationen aus..."
php bin/console doctrine:migrations:migrate --no-interaction --env=test

# Führe Tests aus
echo "Starte Tests..."
php bin/phpunit "$@"

# Zeige Ergebnis an
if [ $? -eq 0 ]; then
  echo "Tests erfolgreich abgeschlossen!"
  exit 0
else
  echo "Tests fehlgeschlagen!"
  exit 1
fi
