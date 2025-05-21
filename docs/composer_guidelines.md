# Composer Dependency Guidelines

This document outlines the guidelines for managing dependencies in the Cashbox project using Composer.

## Overview

Composer is the dependency manager for PHP that allows us to declare, manage, and install libraries that our project depends on. In the Cashbox project, all dependencies must be managed through Composer to ensure consistency, maintainability, and security.

## Core Principles

1. **Use Established Packages**: Do not create custom implementations when well-maintained packages exist
2. **Document Dependencies**: All dependencies must be properly documented in `composer.json`
3. **Version Constraints**: Use appropriate version constraints to balance stability and updates
4. **Regular Updates**: Keep dependencies up to date to receive security fixes and improvements
5. **Development Dependencies**: Use `require-dev` for dependencies only needed in development

## Adding Dependencies

### Production Dependencies

For libraries required in production:

```bash
composer require vendor/package-name
```

Examples of production dependencies:

```bash
# API Platform components
composer require api-platform/core

# JWT Authentication
composer require lexik/jwt-authentication-bundle

# Doctrine extensions
composer require gedmo/doctrine-extensions

# UUID generation
composer require ramsey/uuid
```

### Development Dependencies

For libraries only needed during development:

```bash
composer require --dev vendor/package-name
```

Examples of development dependencies:

```bash
# Testing
composer require --dev symfony/test-pack phpunit/phpunit

# Static Analysis
composer require --dev phpstan/phpstan phpstan/phpstan-symfony

# Code Style
composer require --dev friendsofphp/php-cs-fixer
```

## Version Constraints

Use appropriate version constraints to balance stability with updates:

- `^1.0` - Allows updates to any 1.x version
- `~1.0` - Allows updates to patch versions (1.0.x)
- `1.0.*` - Allows updates to patch versions only
- `>=1.0 <2.0` - Explicit version range
- `1.0.0` - Exact version (avoid in most cases)

Prefer caret (`^`) constraints for most packages:

```json
{
    "require": {
        "symfony/framework-bundle": "^7.2",
        "api-platform/core": "^3.1"
    }
}
```

## Updating Dependencies

Regularly update dependencies to receive security fixes and improvements:

```bash
# Update all dependencies
composer update

# Update specific package
composer update vendor/package-name

# Update dependencies and lock file
composer update --lock

# Check for outdated packages
composer outdated
```

## Security Considerations

Regularly check for security vulnerabilities in dependencies:

```bash
# Check for known vulnerabilities
composer audit
```

Consider using a service like GitHub's Dependabot to automatically receive security updates.

## Recommended Packages

Below are recommended packages for common functionalities. Always use these established packages rather than creating custom implementations:

### API and HTTP
- `api-platform/core`: API Platform for RESTful APIs
- `lexik/jwt-authentication-bundle`: JWT authentication
- `guzzlehttp/guzzle`: HTTP client
- `symfony/http-client`: Symfony's HTTP client
- `nelmio/cors-bundle`: CORS support

### Database and ORM
- `doctrine/orm`: Doctrine ORM
- `doctrine/migrations`: Database migrations
- `doctrine/doctrine-fixtures-bundle`: Fixtures for testing
- `gedmo/doctrine-extensions`: Common Doctrine extensions

### Validation and Forms
- `symfony/validator`: Input validation
- `symfony/form`: Form handling
- `symfony/serializer`: Serialization/deserialization

### Utility Libraries
- `ramsey/uuid`: UUID generation
- `symfony/uid`: Modern identifiers
- `symfony/string`: String manipulation
- `twig/twig`: Template rendering
- `symfony/mailer`: Email handling
- `league/flysystem`: Filesystem abstraction

### Testing
- `symfony/test-pack`: Symfony testing utilities
- `phpunit/phpunit`: Unit testing
- `dama/doctrine-test-bundle`: Database testing
- `symfony/browser-kit`: Browser simulation for testing
- `symfony/css-selector`: CSS selectors for testing

### Development Tools
- `phpstan/phpstan`: Static analysis
- `vimeo/psalm`: Type checking
- `friendsofphp/php-cs-fixer`: Code style fixing
- `phpmd/phpmd`: PHP Mess Detector
- `sebastian/phpcpd`: Copy/Paste Detector
- `phpmetrics/phpmetrics`: Code metrics and analytics

## Private Repositories

If you need to use private packages, configure the repositories section in `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:your-organization/private-repo.git"
        }
    ]
}
```

## Commit Composer Files

Always commit both `composer.json` and `composer.lock` to the repository to ensure consistent dependency versions across all environments.

## Composer Scripts

Define common tasks as Composer scripts in `composer.json`:

```json
{
    "scripts": {
        "test": "phpunit",
        "cs-fix": "php-cs-fixer fix",
        "phpstan": "phpstan analyse src tests",
        "psalm": "psalm",
        "quality": [
            "@cs-fix",
            "@phpstan",
            "@psalm"
        ],
        "post-install-cmd": [
            "@auto-scripts"
        ],
        "post-update-cmd": [
            "@auto-scripts"
        ],
        "auto-scripts": {
            "cache:clear": "symfony-cmd",
            "assets:install %PUBLIC_DIR%": "symfony-cmd"
        }
    }
}
```

Run these scripts with `composer run-script script-name` or simply `composer script-name` for built-in scripts.

## Conclusion

Managing dependencies properly is crucial for maintaining a healthy, secure, and maintainable codebase. Always prefer established packages over custom implementations, keep dependencies up to date, and document why specific packages are chosen.