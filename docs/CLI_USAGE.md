# CLI Usage Guide

This document explains how to execute CLI commands in the Cashbox project using modern PHP 8.4 and state-of-the-art development practices.

## Environment Requirements

- **PHP 8.4+**: Latest PHP version with property hooks, asymmetric visibility, and JIT improvements
- **Symfony 7.2**: Latest LTS version with enhanced performance
- **Docker with BuildKit**: For optimized container builds
- **Composer 2.7+**: Latest dependency management

## Using the CLI Container

The project includes a dedicated CLI container optimized for PHP 8.4 development that allows you to execute command-line operations with enhanced performance. This is particularly useful for running tests, database migrations, static analysis, and other maintenance tasks.

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

To run Symfony console commands with PHP 8.4 optimizations:

```bash
# Inside the CLI container
php bin/console cache:clear --env=prod --no-debug  # JIT optimized
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:warmup --env=prod  # Enhanced performance with PHP 8.4
```

### Running Tests with PHPUnit 11

To run PHPUnit 11 tests with PHP 8.4 performance improvements:

```bash
# Inside the CLI container
php bin/phpunit --testsuite=Unit  # Fast unit tests
php bin/phpunit --testsuite=Integration  # Integration tests
php bin/phpunit --coverage-html var/coverage  # Coverage with JIT
php bin/phpunit --parallel  # Parallel execution (PHPUnit 11 feature)
```

### Static Analysis with PHP 8.4

Run modern static analysis tools:

```bash
# PHPStan level 8 with PHP 8.4 extensions
php vendor/bin/phpstan analyse --level=8 --memory-limit=256M

# Psalm with taint analysis for security
php vendor/bin/psalm --taint-analysis

# PHP CS Fixer with PHP 8.4 rules
php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php
```

## Executing One-off Commands

If you want to run a single command without entering the container shell, you can use:

```bash
docker exec -it cashbox_cli php test_authenticated_api.php
```

This will execute the script and show its output directly in your terminal.

## Updating and Starting the Application

The project includes a script to update Homebrew packages, Composer dependencies, and Docker containers, then start the application:

```bash
./update_and_start.sh
```

This script performs the following actions:
1. Updates Homebrew and its packages
2. Updates Composer dependencies
3. Pulls the latest Docker images
4. Stops and removes existing containers
5. Starts the application with docker-compose

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
