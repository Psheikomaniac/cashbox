# CLI Usage Guide

This document explains how to execute CLI commands in the Cashbox project.

## Using the CLI Container

The project now includes a dedicated CLI container that allows you to execute command-line operations and see their output. This is particularly useful for running tests, database migrations, and other maintenance tasks.

### Starting the CLI Container

To start all containers including the CLI container:

```bash
docker-compose up -d
```

### Accessing the CLI Container

Once the containers are running, you can access the CLI container with:

```bash
docker exec -it cashbox_cli sh
```

This will give you a shell inside the container where you can execute commands.

### Running PHP Scripts

To run PHP scripts from the project root:

```bash
# Inside the CLI container
php test_authenticated_api.php
```

### Running Symfony Console Commands

To run Symfony console commands:

```bash
# Inside the CLI container
php bin/console cache:clear
php bin/console doctrine:migrations:migrate
```

### Running Tests

To run PHPUnit tests:

```bash
# Inside the CLI container
php bin/phpunit
```

## Executing One-off Commands

If you want to run a single command without entering the container shell, you can use:

```bash
docker exec -it cashbox_cli php test_authenticated_api.php
```

This will execute the script and show its output directly in your terminal.

## Troubleshooting

If you encounter any issues with CLI execution:

1. Make sure the CLI container is running:
   ```bash
   docker ps | grep cashbox_cli
   ```

2. Check the container logs for any errors:
   ```bash
   docker logs cashbox_cli
   ```

3. Ensure the necessary PHP extensions are installed:
   ```bash
   docker exec -it cashbox_cli php -m
   ```
