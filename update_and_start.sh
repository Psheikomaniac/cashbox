#!/bin/bash

# Script to update Homebrew, Composer dependencies, and Docker containers
# Then start the application using docker-compose

echo "Starting update process..."

# Update Homebrew and its packages
echo "Updating Homebrew packages..."
brew update
brew upgrade
echo "Homebrew update completed."

# Update Composer dependencies
echo "Updating Composer dependencies..."
composer update
echo "Composer update completed."

# Pull latest Docker images
echo "Updating Docker containers..."
docker-compose pull

# Stop and remove existing containers
echo "Stopping existing containers..."
docker-compose down

# Start containers with docker-compose
echo "Starting containers with docker-compose..."
docker-compose up -d

echo "Update and startup process completed."
echo "The application is now running."
echo "You can access the CLI container with: docker exec -it cashbox_cli sh"
