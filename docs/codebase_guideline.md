## External Dependencies

### Package Management

- Use Composer for all dependencies
- Do not create custom libraries when established packages exist
- Document all dependencies in `composer.json`
- Keep dependencies up to date with `composer update`
- Use version constraints appropriately
- Include development dependencies in `require-dev`

### Recommended Packages

The following packages are recommended and should be used instead of creating custom implementations:

- **Doctrine Extensions**: Use `gedmo/doctrine-extensions` for common entity behaviors
- **API Platform**: Already integrated for RESTful API creation
- **Symfony Serializer**: For data transformation and serialization
- **Lexik JWT Authentication**: For JWT-based API authentication
- **Ramsey UUID**: For UUID generation
- **Symfony Validator**: For input validation
- **Symfony Messenger**: For asynchronous processing
- **Flysystem**: For file storage abstraction
- **Symfony Mailer**: For email handling
- **Twig**: For template rendering (if needed)
- **Monolog**: For logging (already included with Symfony)# Codebase Guidelines

## Introduction

This document outlines the coding standards and best practices for the Cashbox project. Adherence to these guidelines ensures code consistency, maintainability, quality, and security. The project emphasizes comprehensive testing and static code analysis to maintain the highest standards of code quality.

## General Principles

### SOLID Principles

All code should follow the SOLID principles:
- **S**ingle Responsibility Principle
- **O**pen/Closed Principle
- **L**iskov Substitution Principle
- **I**nterface Segregation Principle
- **D**ependency Inversion Principle

### Clean Code

- Write self-documenting code with clear, intention-revealing names
- Keep methods and classes small and focused
- Avoid deep nesting and complex conditionals
- Follow the "Boy Scout Rule": Leave the code cleaner than you found it

## PHP Standards

### Version and Features

- Use PHP 8.4 features appropriately
- Leverage property hooks for clean accessor implementations
- Use typed properties and return types for all methods
- Employ readonly properties when applicable
- Utilize constructor property promotion where appropriate
- Take advantage of enums for type-safe enumerated values (see PHP Enums section below)
- Always use DateTimeImmutable when working with dates and times unless there's a specific need for mutable dates

### PHP Enums

- Use PHP enums extensively for any value with a fixed set of possible options
- Prefer backed enums (with string or int values) for database storage
- Extend enum functionality by adding methods for labels, business logic, etc.
- Use enums over string constants or magic values
- Document enums with meaningful PHPDoc
- Use enums for type safety in method parameters and return types
- Implement helper methods in enums to support common operations
- Store enum values in the database and convert to enum objects in entity getters/setters

### Formatting

- Follow PSR-12 coding standards
- Use 4 spaces for indentation
- Keep line length around 120 characters
- Use single quotes for strings unless double quotes provide a benefit

### Naming Conventions

- **Classes**: PascalCase, descriptive nouns (e.g., `PaymentProcessor`)
- **Interfaces**: PascalCase, descriptive adjectives (e.g., `Cacheable`, `Processable`)
- **Methods**: camelCase, verb or verb phrases (e.g., `calculateTotal`, `findByUser`)
- **Properties**: camelCase, descriptive nouns (e.g., `paymentAmount`, `userStatus`)
- **Constants**: UPPER_CASE with underscores (e.g., `MAX_RETRY_ATTEMPTS`)
- **Variables**: camelCase, clear and descriptive (e.g., `$totalAmount`, `$isActive`)

### Documentation

- All classes, public methods, and interfaces should have PHPDoc blocks
- Document parameters, return types, and exceptions
- Include descriptive comments for complex logic
- Keep comments current when code changes

## Symfony Best Practices

### Configuration

- Use environment variables for configuration where appropriate
- Avoid hardcoded values; use parameters instead
- Follow Symfony's recommended directory structure

### Controllers

- Keep controllers thin; move business logic to services
- Use controller actions for a single purpose
- Return appropriate HTTP status codes
- Use route annotations/attributes for defining routes

### Services

- Define services in the DI container with explicit configuration
- Implement dependency injection through constructor injection
- Tag services appropriately
- Use interface bindings where beneficial

### Entities

- Use Doctrine annotations/attributes for mapping
- Implement entity validation using Symfony's validator
- Use UUID for primary keys
- Implement Gedmo extensions for timestampable behavior

## API Platform Guidelines

### API Design

- Design your API with resource-oriented architecture
- Use appropriate HTTP methods (GET, POST, PUT, DELETE)
- Implement proper HTTP status codes
- Create consistent API responses

### Resources

- Use API Platform attributes to define resources
- Configure operations properly (GET, POST, PUT, DELETE)
- Implement proper serialization groups for input/output control
- Use DTOs for complex operations

### Security

- Implement proper API authentication
- Define fine-grained access control
- Validate all input data
- Protect against common security vulnerabilities

## Data Transfer Objects (DTOs)

- Use DTOs for API input and output
- Keep DTOs immutable when possible
- Implement validation at the DTO level
- Create mapper services for entity-to-DTO conversion

## Testing

### Philosophy

- Aim for high test coverage (minimum 90%)
- Tests are mandatory for all new features and bug fixes
- Write tests before fixing bugs (Test-Driven Bug Fixing)
- Consider Test-Driven Development for complex features
- Treat tests as first-class citizens in the codebase

### Types of Tests

- **Unit Tests**: Test individual components in isolation
- **Integration Tests**: Test component interactions
- **Functional Tests**: Test complete features
- **API Tests**: Test API endpoints and responses
- **Security Tests**: Test protection against common vulnerabilities
- **Performance Tests**: Measure and benchmark critical code paths

### Testing Practices

- Use PHPUnit 11 for unit and integration tests
- Leverage Symfony's testing tools and PHPUnit Bridge
- Mock dependencies to isolate components
- Use data providers for parameterized tests
- Implement test fixtures for consistent test data
- Write smoke tests for critical application paths
- Use WebTestCase for functional testing
- Use API test cases for API testing
- Leverage DAMADoctrineTestBundle for database tests

### Test Organization

- Mirror production code structure in test directory
- Name test classes with `Test` suffix
- Group related tests with appropriate annotations
- Use test namespaces matching production namespaces
- Organize test fixtures in a dedicated directory

## Code Quality Tools

The project uses several code quality tools to maintain high standards of code quality and detect issues early.

### PHPStan

- All code must pass PHPStan at level 8
- Use strict rules configuration
- Include Symfony and Doctrine extensions
- Configure PHPStan to analyze tests directory
- Integrate PHPStan in the CI/CD pipeline
- Address all PHPStan warnings and errors

### PHP CS Fixer

- Ensure consistent code style across the project
- Use Symfony style rules
- Enforce PSR-12 compliance
- Run PHP CS Fixer before committing code
- Configure IDE to use these rules automatically

### Psalm

- Complement PHPStan with Psalm for additional checks
- Configure to target maximum strictness level
- Use in tandem with PHPStan for comprehensive checks

### PHP Mess Detector

- Identify potential bugs
- Find suboptimal code
- Detect overcomplicated expressions
- Discover unused parameters, methods, properties

### PHP Copy/Paste Detector

- Identify duplicated code
- Enforce DRY principles

### Integration

- All tools must be integrated into the CI/CD pipeline
- Local pre-commit hooks should run appropriate tools
- Pull requests must pass all code quality checks
- Repository should include configuration files for all tools
- Documentation should explain how to run tools locally

## Security

### General Principles

- Security is a top priority for the application
- Follow OWASP Top 10 guidance
- Regularly update dependencies
- Implement proper input validation
- Apply least privilege principle
- Use parameterized queries for database interactions

### Authentication and Authorization

- Use secure authentication mechanisms
- Implement proper session management
- Enforce strong password policies
- Use multi-factor authentication where appropriate
- Define detailed authorization rules
- Never hard-code credentials

### Data Protection

- Encrypt sensitive data at rest
- Use HTTPS for all communications
- Implement proper access controls
- Follow data minimization principles
- Properly handle file uploads

### API Security

- Use JWT for API authentication
- Implement rate limiting
- Validate all API inputs
- Set appropriate CORS headers
- Use HTTPS for all API endpoints

### Security Testing

- Perform regular security audits
- Use automated vulnerability scanning
- Conduct penetration testing
- Implement security-focused unit tests
- Test for common vulnerabilities (XSS, CSRF, SQLi)

## Code Review Process

- Review all code before merging to main branches
- Use the project's code review checklist
- Address all comments before considering a review complete
- Automate checks where possible (static analysis, style, etc.)

## External Resources

- [PHP-FIG Standards](https://www.php-fig.org/psr/)
- [Symfony Best Practices](https://symfony.com/doc/current/best_practices.html)
- [API Platform Documentation](https://api-platform.com/docs/)
- [PHP 8.4 Documentation](https://www.php.net/releases/8.4/en.php)