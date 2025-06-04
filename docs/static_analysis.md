# Static Analysis and Code Quality Tools

This document outlines the static analysis and code quality tools used in the Cashbox project, along with configuration and best practices.

## Overview

Code quality is a top priority for this project. We use a comprehensive set of static analysis and code quality tools to ensure that our code is clean, maintainable, secure, and follows best practices. These tools are integrated into our development workflow and CI/CD pipeline.

## PHPStan

[PHPStan](https://phpstan.org/) is our primary static analysis tool, helping us catch bugs and errors before they make it to production.

### Installation

All code quality tools should be installed via Composer as dev dependencies. Do not attempt to create custom implementations or wrappers for these tools.

```bash
# Install all code quality tools at once
composer require --dev phpstan/phpstan phpstan/extension-installer phpstan/phpstan-symfony phpstan/phpstan-doctrine friendsofphp/php-cs-fixer vimeo/psalm phpmd/phpmd sebastian/phpcpd phpmetrics/phpmetrics phpro/grumphp
```

Alternatively, install them individually as needed:

### Configuration

We use a `phpstan.neon` file in the project root with the following configuration:

```neon
parameters:
    level: 8
    paths:
        - src
        - tests
    symfony:
        container_xml_path: var/cache/dev/App_KernelDevDebugContainer.xml
    doctrine:
        objectManagerLoader: tests/object-manager.php
    checkMissingIterableValueType: false
    checkGenericClassInNonGenericObjectType: false
    ignoreErrors:
        # Add specific ignored errors here
    excludePaths:
        - vendor/*
```

### Usage

```bash
# Run PHPStan analysis
php vendor/bin/phpstan analyse

# Run with specific configuration
php vendor/bin/phpstan analyse -c phpstan.neon
```

### PHPStan Level Progression

For existing projects, we recommend gradually increasing the PHPStan level:

1. Start at level 0 to get the most critical errors
2. Fix issues and incrementally increase levels
3. Aim to reach level 8 (maximum strictness)

New code should always adhere to level 8 standards.

## PHP CS Fixer

[PHP CS Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) ensures consistent code style across the project.

### Installation

```bash
composer require --dev friendsofphp/php-cs-fixer
```

### Configuration

We use a `.php-cs-fixer.dist.php` file in the project root:

```php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->exclude('var')
    ->exclude('vendor')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'no_unused_imports' => true,
        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
```

### Usage

```bash
# Check code style
php vendor/bin/php-cs-fixer fix --dry-run --diff

# Fix code style issues
php vendor/bin/php-cs-fixer fix
```

## Psalm

[Psalm](https://psalm.dev/) provides additional static analysis capabilities, complementing PHPStan.

### Installation

```bash
composer require --dev vimeo/psalm
```

### Configuration

We use a `psalm.xml` file in the project root:

```xml
<?xml version="1.0"?>
<psalm
    errorLevel="3"
    resolveFromConfigFile="true"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="https://getpsalm.org/schema/config"
    xsi:schemaLocation="https://getpsalm.org/schema/config vendor/vimeo/psalm/config.xsd"
>
    <projectFiles>
        <directory name="src" />
        <ignoreFiles>
            <directory name="vendor" />
        </ignoreFiles>
    </projectFiles>

    <plugins>
        <pluginClass class="Psalm\SymfonyPsalmPlugin\Plugin" />
    </plugins>
</psalm>
```

### Usage

```bash
# Run Psalm analysis
php vendor/bin/psalm

# Run with security analysis
php vendor/bin/psalm --taint-analysis
```

## PHP Mess Detector (PHPMD)

[PHPMD](https://phpmd.org/) identifies code smells and potential problems.

### Installation

```bash
composer require --dev phpmd/phpmd
```

### Configuration

We use a `phpmd.xml` file in the project root:

```xml
<?xml version="1.0"?>
<ruleset name="Cashbox PHPMD rule set"
         xmlns="http://pmd.sf.net/ruleset/1.0.0"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://pmd.sf.net/ruleset/1.0.0 http://pmd.sf.net/ruleset_xml_schema.xsd"
         xsi:noNamespaceSchemaLocation="http://pmd.sf.net/ruleset_xml_schema.xsd">
    <description>
        Cashbox PHPMD custom rule set
    </description>

    <!-- Import existing rule sets -->
    <rule ref="rulesets/cleancode.xml">
        <exclude name="StaticAccess" />
    </rule>
    <rule ref="rulesets/codesize.xml" />
    <rule ref="rulesets/controversial.xml" />
    <rule ref="rulesets/design.xml" />
    <rule ref="rulesets/naming.xml" />
    <rule ref="rulesets/unusedcode.xml" />
</ruleset>
```

### Usage

```bash
# Run PHPMD on source code
php vendor/bin/phpmd src text phpmd.xml

# Run PHPMD on tests
php vendor/bin/phpmd tests text phpmd.xml
```

## PHP Copy/Paste Detector (PHPCPD)

[PHPCPD](https://github.com/sebastianbergmann/phpcpd) helps identify duplicated code.

### Installation

```bash
composer require --dev sebastian/phpcpd
```

### Usage

```bash
# Run PHPCPD on source code
php vendor/bin/phpcpd src

# Run with specific exclusions
php vendor/bin/phpcpd --exclude tests --exclude vendor .
```

## PHP Metrics

[PHP Metrics](https://github.com/phpmetrics/PhpMetrics) provides visual and comprehensive code quality reports.

### Installation

```bash
composer require --dev phpmetrics/phpmetrics
```

### Usage

```bash
# Generate PHP Metrics report
php vendor/bin/phpmetrics --report-html=metrics src
```

## Integration with CI/CD

All these tools are integrated into our CI/CD pipeline to ensure consistent code quality. Here's an example GitHub Actions workflow:

```yaml
name: Code Quality

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  code-quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, intl, pdo_pgsql, opcache, apcu
          coverage: xdebug
          tools: composer:2.7
          ini-values: opcache.enable=1, opcache.jit=tracing, opcache.jit_buffer_size=100M
          
      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress
        
      - name: PHPStan
        run: vendor/bin/phpstan analyse
        
      - name: PHP CS Fixer
        run: vendor/bin/php-cs-fixer fix --dry-run --diff
        
      - name: Psalm
        run: vendor/bin/psalm --output-format=github
        
      - name: PHPMD
        run: vendor/bin/phpmd src github phpmd.xml
        
      - name: PHPCPD
        run: vendor/bin/phpcpd src
```

## Pre-commit Hooks

We recommend using [GrumPHP](https://github.com/phpro/grumphp) to set up pre-commit hooks that run these tools automatically.

### Installation

```bash
composer require --dev phpro/grumphp
```

### Configuration

Create a `grumphp.yml` file in the project root:

```yaml
grumphp:
  tasks:
    composer:
      no_check_all: true
    phpcsfixer:
      config: .php-cs-fixer.dist.php
    phpstan:
      configuration: phpstan.neon
    psalm:
      config: psalm.xml
      triggered_by: [php]
    phpmd:
      ruleset: [phpmd.xml]
    phpcpd:
      directory: [src]
```

## Best Practices

1. **Early Integration**: Set up static analysis tools at the beginning of the project
2. **Incremental Adoption**: For existing projects, adopt tools incrementally
3. **CI/CD Integration**: Always integrate tools into your CI/CD pipeline
4. **Pre-commit Hooks**: Use pre-commit hooks to catch issues early
5. **Developer Education**: Ensure all team members understand the tools and their benefits
6. **Regular Updates**: Keep tools and their configurations up to date
7. **Documentation**: Document tool-specific configurations and exceptions

## PHP 8.4 Enhanced Static Analysis

### Property Hooks Analysis

Configure PHPStan to understand PHP 8.4 property hooks:

```neon
# phpstan.neon
parameters:
    level: 8
    paths:
        - src
        - tests
    php8Features:
        propertyHooks: true
        asymmetricVisibility: true
    symfony:
        container_xml_path: var/cache/dev/App_KernelDevDebugContainer.xml
    doctrine:
        objectManagerLoader: tests/object-manager.php
    checkExplicitMixed: true
    checkFunctionNameCase: true
    checkInternalClassCaseSensitivity: true
    reportUnmatchedIgnoredErrors: true
```

### Enhanced PHP CS Fixer Rules for PHP 8.4

Update your `.php-cs-fixer.dist.php` configuration:

```php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->exclude(['var', 'vendor'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
        '@PHP84Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'no_unused_imports' => true,
        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'property_hooks' => true,
        'asymmetric_visibility' => true,
        'new_without_parentheses' => true,
        'readonly_class' => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
```

### Psalm Configuration for PHP 8.4

Enhanced `psalm.xml` configuration:

```xml
<?xml version="1.0"?>
<psalm
    errorLevel="1"
    resolveFromConfigFile="true"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="https://getpsalm.org/schema/config"
    xsi:schemaLocation="https://getpsalm.org/schema/config vendor/vimeo/psalm/config.xsd"
    findUnusedCode="true"
    findUnusedVariablesAndParams="true"
    checkForThrowsDocblock="true"
    checkForThrowsInGlobalScope="true">
    
    <projectFiles>
        <directory name="src" />
        <directory name="tests" />
        <ignoreFiles>
            <directory name="vendor" />
        </ignoreFiles>
    </projectFiles>

    <plugins>
        <pluginClass class="Psalm\SymfonyPsalmPlugin\Plugin" />
    </plugins>
    
    <issueHandlers>
        <PropertyNotSetInConstructor>
            <errorLevel type="suppress">
                <referencedProperty name="$service" />
            </errorLevel>
        </PropertyNotSetInConstructor>
    </issueHandlers>
</psalm>
```

### Modern Rector Rules for PHP 8.4

Create a `rector.php` configuration for automated refactoring:

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/src/Kernel.php',
    ])
    ->withPhpSets(php84: true)
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0)
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
        ExplicitNullableParamTypeRector::class,
    ]);
```

### Performance Monitoring in CI/CD

Enhanced GitHub Actions workflow with performance monitoring:

```yaml
name: Code Quality & Performance

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  code-quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP 8.4 with JIT
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, intl, pdo_pgsql, opcache, apcu
          coverage: xdebug
          tools: composer:2.7, phpbench
          ini-values: |
            opcache.enable=1
            opcache.jit=tracing
            opcache.jit_buffer_size=100M
            memory_limit=512M
            
      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress --optimize-autoloader
        
      - name: Warm up cache for JIT
        run: |
          php bin/console cache:warmup --env=prod
          
      - name: PHPStan Analysis
        run: vendor/bin/phpstan analyse --memory-limit=256M --error-format=github
        
      - name: PHP CS Fixer Check
        run: vendor/bin/php-cs-fixer fix --dry-run --diff --format=checkstyle
        
      - name: Psalm Analysis
        run: vendor/bin/psalm --output-format=github --taint-analysis
        
      - name: Rector Check
        run: vendor/bin/rector process --dry-run
        
      - name: Security Audit
        run: composer audit --format=json
        
      - name: Performance Benchmark
        run: vendor/bin/phpbench run --report=aggregate --output=console
```

### Code Quality Metrics Dashboard

Create a quality metrics dashboard with PHP 8.4 specific metrics:

```bash
# Generate comprehensive metrics report
vendor/bin/phpmetrics --report-html=var/metrics \
    --exclude=vendor,var \
    --extensions=php \
    --report-violations=var/metrics/violations.xml \
    src

# Performance profiling with Blackfire
blackfire curl http://localhost/api/contributions
```

## Conclusion

By using these tools consistently and leveraging PHP 8.4's enhanced features, we maintain high code quality standards, catch bugs early, and ensure a more maintainable and secure codebase. The enhanced static analysis tools provide better insight into modern PHP patterns like property hooks, asymmetric visibility, and performance characteristics. All developers on the project are expected to use these tools and address any issues they identify.