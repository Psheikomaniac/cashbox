#!/bin/bash
# docker/deploy.sh
set -e

echo "Starting deployment process..."

# Überprüfen, ob die erforderlichen Umgebungsvariablen gesetzt sind
if [ -z "$DOCKER_REGISTRY" ]; then
  echo "Error: DOCKER_REGISTRY environment variable is not set"
  exit 1
fi

# Neue Image-Version holen
echo "Pulling latest Docker image from $DOCKER_REGISTRY..."
docker compose pull

# Anwendung neustarten
echo "Stopping current application..."
docker compose down

echo "Starting new application version..."
docker compose up -d

# Warten, bis die Datenbank bereit ist
echo "Waiting for database to be ready..."
until docker compose exec -T database pg_isready -d cashbox_db -U postgres > /dev/null 2>&1; do
  echo "Database is not ready yet... waiting"
  sleep 2
done

# Datenbankmigrationen ausführen
echo "Running database migrations..."
docker compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction

# Cache leeren und aufwärmen
echo "Clearing and warming up cache..."
docker compose exec -T php php bin/console cache:clear --no-debug --env=prod
docker compose exec -T php php bin/console cache:warmup --no-debug --env=prod

# Überprüfen, ob die Anwendung erfolgreich gestartet wurde
echo "Checking application health..."
if curl -s -f http://localhost:8080/health > /dev/null; then
  echo "Application is healthy!"
else
  echo "Warning: Application health check failed!"
  echo "Please check the logs for more information:"
  docker compose logs php
fi

echo "Deployment completed successfully!"
