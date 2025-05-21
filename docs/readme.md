# Cashbox

A comprehensive API-based solution for managing team finances, including penalties, drinks, and contributions.

## Overview

Cashbox provides a robust API for tracking and managing financial transactions within sports teams. The system allows team administrators to:

- Track financial penalties (for late arrivals, missed training, etc.)
- Manage drink purchases
- Record and track membership contributions
- Generate reports and statistics
- Monitor payment status

This system is built on modern technologies and follows best practices for scalable and maintainable API development.

## Repository

Cashbox is hosted on GitHub:

- **Repository URL**: `git@github.com:Psheikomaniac/cashbox.git`
- **Web interface**: [https://github.com/Psheikomaniac/cashbox](https://github.com/Psheikomaniac/cashbox)
- **Issue tracker**: [https://github.com/Psheikomaniac/cashbox/issues](https://github.com/Psheikomaniac/cashbox/issues)
- **Wiki**: [https://github.com/Psheikomaniac/cashbox/wiki](https://github.com/Psheikomaniac/cashbox/wiki)

## Technology Stack

- **PHP 8.4**: Leveraging the latest language features including property hooks and improvements
- **Symfony 7.2**: The latest version of the Symfony framework
- **API Platform**: For building RESTful APIs with minimal effort
- **Doctrine ORM**: For database interactions and entity management
- **PostgreSQL**: As the primary database
- **JWT Authentication**: For secure API access
- **Gedmo Extensions**: For automated timestamping and logging

## Getting Started

### Prerequisites

- PHP 8.4 or higher
- Composer
- Docker and Docker Compose
- PostgreSQL 15 or higher

### Getting Started

The project already has Symfony 7.2 installed and the basic structure in place. To get started with development:

1. Clone the repository:
   ```bash
   git clone git@github.com:Psheikomaniac/cashbox.git
   cd cashbox
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy the environment variables:
   ```bash
   cp .env .env.local
   # Edit .env.local to match your environment
   ```

4. Start the Docker environment (if not already running):
   ```bash
   docker-compose up -d
   ```

5. Run migrations:
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

6. Load fixtures (optional for development):
   ```bash
   php bin/console doctrine:fixtures:load
   ```

### Dependencies

All dependencies are managed via Composer. Do not create custom libraries; use existing packages when possible. Add new dependencies using Composer:

```bash
composer require [package-name]
composer require --dev [package-name]
```

## Documentation

- API documentation is available at `/api/docs`
- For more detailed information, refer to the `docs/` directory

## Project Structure

```
cashbox-management-system/
├── assets/              # Frontend assets (if applicable)
├── bin/                 # Symfony console and other executables
├── config/              # Configuration files
├── docs/                # Project documentation
├── migrations/          # Database migrations
├── public/              # Web server root
├── src/                 # Application source code
│   ├── Controller/      # API controllers
│   ├── DataTransformer/ # DTO transformers
│   ├── Dto/             # Data Transfer Objects
│   ├── Entity/          # Doctrine entities
│   ├── EventListener/   # Event listeners
│   ├── Exception/       # Custom exceptions
│   ├── Repository/      # Doctrine repositories
│   ├── Service/         # Business logic services
│   └── Validator/       # Custom validators
├── templates/           # Twig templates (if applicable)
├── tests/               # Test suite
└── var/                 # Cache, logs, and other generated files
```

## Testing

The project has a comprehensive test suite covering all aspects of the application:

```bash
# Run the complete test suite
php bin/phpunit

# Run specific test directory
php bin/phpunit tests/Unit/

# Run tests with coverage report
php bin/phpunit --coverage-html var/coverage
```

We follow a test-driven development approach and aim for at least 90% code coverage.

## Code Quality

We use several tools to ensure high code quality:

### PHPStan (Static Analysis)

```bash
# Run PHPStan at maximum level
php vendor/bin/phpstan analyse src tests --level=8

# Generate baseline file for legacy code
php vendor/bin/phpstan analyse src tests --level=8 --generate-baseline
```

### PHP CS Fixer (Code Style)

```bash
# Check code style
php vendor/bin/php-cs-fixer fix --dry-run --diff

# Fix code style issues
php vendor/bin/php-cs-fixer fix
```

### Psalm (Type Checking)

```bash
# Run Psalm
php vendor/bin/psalm

# Run Psalm with security analysis
php vendor/bin/psalm --taint-analysis
```

### PHP Mess Detector

```bash
# Run PHPMD
php vendor/bin/phpmd src text phpmd.xml

# Run PHPMD on tests
php vendor/bin/phpmd tests text phpmd.xml
```

All these tools are integrated into our CI/CD pipeline to ensure consistent code quality.

## Deployment

Deployment instructions are available in the `docs/deployment.md` file.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For available versions, see the [tags on this repository](https://github.com/Psheikomaniac/cashbox/tags).

## Contributing

Please read our [Contributing Guide](docs/CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## License

This project is proprietary and confidential.

## Documentation

Comprehensive documentation is available in the `docs/` directory:

- [Git Guidelines](docs/git_guideline.md)
- [Codebase Guidelines](docs/codebase_guideline.md)
- [Static Analysis and Code Quality](docs/static_analysis.md)
- [Security Guidelines](docs/security.md)
- [Testing Guidelines](docs/testing.md)
- [PHP Enums Best Practices](docs/enum_best_practices.md)
- [Project Roadmap](docs/roadmap.md)
- [Composer Dependency Guidelines](docs/composer_guidelines.md)