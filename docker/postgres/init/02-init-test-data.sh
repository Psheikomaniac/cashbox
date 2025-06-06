#!/bin/bash
# docker/postgres/init/02-init-test-data.sh
set -e

# Warte auf die Datenbank
echo "Warte auf Datenbank..."
until pg_isready -d "$POSTGRES_DB" -U "$POSTGRES_USER" > /dev/null 2>&1; do
  sleep 1
done

# Initialisiere grundlegende Datenbankstruktur
echo "Initialisiere Datenbankstruktur..."
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
  -- Stelle sicher, dass die UUID-Erweiterung aktiviert ist
  CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
EOSQL

# Wenn wir in der Entwicklungsumgebung sind, fügen wir Testdaten hinzu
if [ "$APP_ENV" = "dev" ]; then
  echo "Generiere Testdaten für Entwicklungsumgebung..."

  # Warte auf den PHP-Container
  echo "Warte auf PHP-Container..."
  until nc -z php 8000 > /dev/null 2>&1; do
    sleep 2
    echo "Warte auf PHP-Container..."
  done

  # Führe den Testdaten-Generator aus
  echo "Führe Testdaten-Generator aus..."
  curl -s http://php:8000/api/generate-test-data > /dev/null || echo "Testdaten-Generator konnte nicht ausgeführt werden. Dies ist normal beim ersten Start."
fi

echo "Datenbankinitialisierung abgeschlossen!"
