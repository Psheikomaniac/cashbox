## Test Dependencies

All testing dependencies should be installed via Composer:

```bash
# Install primary testing packages
composer require --dev symfony/test-pack phpunit/phpunit dama/doctrine-test-bundle

# Install additional testing utilities
composer require --dev symfony/browser-kit symfony/css-selector symfony/http-client
```

Do not create custom testing frameworks or mocking libraries. Use the established tools that integrate well with Symfony.# Testing Guidelines

This document outlines the testing strategy, best practices, and guidelines for the Cashbox project.

## Overview

Testing is a critical component of our development process. We follow a comprehensive testing approach to ensure the quality, reliability, and security of our application. This document provides guidelines for implementing effective tests at various levels of the application.

## Testing Principles

1. **Test Early, Test Often**: Testing should be integrated throughout the development process.
2. **Test Automation**: Automate tests whenever possible to enable frequent execution.
3. **Test Coverage**: Aim for high test coverage (minimum 90%) for critical code paths.
4. **Test Independence**: Tests should be independent and not rely on the state of other tests.
5. **Test Readability**: Tests should be clear and readable, serving as documentation.

## Test Types

### Unit Tests

Unit tests verify the behavior of individual components in isolation.

#### Guidelines

- Test each class in isolation
- Mock external dependencies
- Focus on testing business logic
- Cover edge cases and error conditions
- Keep tests small and focused
- Use meaningful test names

#### Example

```php
namespace App\Tests\Unit\Service;

use App\Entity\Penalty;
use App\Service\PenaltyCalculator;
use PHPUnit\Framework\TestCase;

class PenaltyCalculatorTest extends TestCase
{
    private PenaltyCalculator $calculator;
    
    protected function setUp(): void
    {
        $this->calculator = new PenaltyCalculator();
    }
    
    public function testCalculateTotalForEmptyPenalties(): void
    {
        $this->assertEquals(0, $this->calculator->calculateTotal([]));
    }
    
    public function testCalculateTotalForSinglePenalty(): void
    {
        $penalty = new Penalty();
        $penalty->setAmount(100);
        
        $this->assertEquals(100, $this->calculator->calculateTotal([$penalty]));
    }
    
    public function testCalculateTotalForMultiplePenalties(): void
    {
        $penalty1 = new Penalty();
        $penalty1->setAmount(100);
        
        $penalty2 = new Penalty();
        $penalty2->setAmount(200);
        
        $this->assertEquals(300, $this->calculator->calculateTotal([$penalty1, $penalty2]));
    }
}
```

### Integration Tests

Integration tests verify the interaction between components.

#### Guidelines

- Test interactions between components
- Use real dependencies where practical
- Focus on boundaries between components
- Test database interactions
- Use DAMADoctrineTestBundle for database testing

#### Example

```php
namespace App\Tests\Integration\Repository;

use App\Entity\Penalty;
use App\Entity\PenaltyType;
use App\Entity\TeamUser;
use App\Repository\PenaltyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PenaltyRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private PenaltyRepository $repository;
    
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->repository = $this->entityManager->getRepository(Penalty::class);
    }
    
    public function testFindUnpaidPenalties(): void
    {
        // Create test data
        $teamUser = $this->createTeamUser();
        $penaltyType = $this->createPenaltyType();
        
        $penalty1 = new Penalty();
        $penalty1->setTeamUser($teamUser);
        $penalty1->setType($penaltyType);
        $penalty1->setReason('Test penalty 1');
        $penalty1->setAmount(100);
        $penalty1->setCurrency('EUR');
        $penalty1->setArchived(false);
        $this->entityManager->persist($penalty1);
        
        $penalty2 = new Penalty();
        $penalty2->setTeamUser($teamUser);
        $penalty2->setType($penaltyType);
        $penalty2->setReason('Test penalty 2');
        $penalty2->setAmount(200);
        $penalty2->setCurrency('EUR');
        $penalty2->setArchived(false);
        $penalty2->setPaidAt(new \DateTimeImmutable());
        $this->entityManager->persist($penalty2);
        
        $this->entityManager->flush();
        
        // Test repository method
        $unpaidPenalties = $this->repository->findUnpaidPenalties();
        
        $this->assertCount(1, $unpaidPenalties);
        $this->assertEquals('Test penalty 1', $unpaidPenalties[0]->getReason());
    }
    
    // Helper methods to create test entities
    private function createTeamUser(): TeamUser
    {
        // Implementation
    }
    
    private function createPenaltyType(): PenaltyType
    {
        // Implementation
    }
}
```

### Functional Tests

Functional tests verify the application's behavior from a user's perspective.

#### Guidelines

- Test complete features and user workflows
- Use Symfony's WebTestCase
- Test HTTP responses
- Verify form submissions
- Check redirects and flash messages
- Test authorization and access control

#### Example

```php
namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PenaltyControllerTest extends WebTestCase
{
    public function testListPenalties(): void
    {
        $client = static::createClient();
        $client->request('GET', '/penalties');
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Penalties');
    }
    
    public function testCreatePenalty(): void
    {
        $client = static::createClient();
        $client->request('GET', '/penalties/new');
        
        $this->assertResponseIsSuccessful();
        
        $client->submitForm('Create', [
            'penalty[reason]' => 'Test penalty',
            'penalty[amount]' => 100,
            'penalty[teamUser]' => 1,
            'penalty[type]' => 1,
        ]);
        
        $this->assertResponseRedirects('/penalties');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Penalty created');
    }
}
```

### API Tests

API tests verify the behavior of API endpoints.

#### Guidelines

- Test API endpoints
- Verify request/response formats
- Test authentication and authorization
- Check error handling
- Test with various inputs
- Verify response status codes

#### Example

```php
namespace App\Tests\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PenaltyApiTest extends WebTestCase
{
    public function testGetPenalties(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/penalties', [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAuthToken(),
        ]);
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
    }
    
    public function testCreatePenalty(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/penalties', [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAuthToken(),
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'reason' => 'Test penalty',
            'amount' => 100,
            'teamUser' => '/api/team_users/1',
            'type' => '/api/penalty_types/1',
        ]));
        
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
    }
    
    private function getAuthToken(): string
    {
        // Implementation to get auth token
    }
}
```

### Security Tests

Security tests verify the application's security controls.

#### Guidelines

- Test authentication mechanisms
- Verify authorization rules
- Test input validation
- Check for security headers
- Test for common vulnerabilities

#### Example

```php
namespace App\Tests\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityTest extends WebTestCase
{
    public function testSecureEndpointRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/penalties');
        
        $this->assertResponseStatusCodeSame(401);
    }
    
    public function testAdminEndpointRequiresAdminRole(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/users', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserToken(), // Non-admin token
        ]);
        
        $this->assertResponseStatusCodeSame(403);
    }
    
    public function testSecurityHeaders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        
        $response = $client->getResponse();
        $this->assertNotNull($response->headers->get('X-Content-Type-Options'));
        $this->assertNotNull($response->headers->get('X-Frame-Options'));
        $this->assertNotNull($response->headers->get('Content-Security-Policy'));
    }
    
    private function getUserToken(): string
    {
        // Implementation to get user token
    }
    
    private function getAdminToken(): string
    {
        // Implementation to get admin token
    }
}
```

### Smoke Tests

Smoke tests verify the basic functionality of the application.

#### Guidelines

- Test critical application paths
- Verify main pages load properly
- Check basic functionality works
- Use data providers for efficiency

#### Example

```php
namespace App\Tests\Smoke;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);
        
        $this->assertResponseIsSuccessful();
    }
    
    public function urlProvider(): \Generator
    {
        yield ['/'];
        yield ['/login'];
        yield ['/register'];
        yield ['/about'];
        yield ['/contact'];
    }
}
```

## Test Organization

### Directory Structure

```
tests/
├── Unit/
│   ├── Entity/
│   ├── Service/
│   └── Validator/
├── Integration/
│   ├── Repository/
│   └── Service/
├── Functional/
│   └── Controller/
├── API/
├── Security/
├── Smoke/
└── bootstrap.php
```

### Naming Conventions

- Test classes should be named with the class they test followed by "Test"
- Test methods should be named to describe the scenario being tested
- Test methods should be prefixed with "test"

## Test Configuration

### PHPUnit Configuration

Create a `phpunit.xml.dist` file in the project root:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         cacheDirectory=".phpunit.cache">
    <php>
        <ini name="display_errors" value="1" />
        <ini name="error_reporting" value="-1" />
        <server name="APP_ENV" value="test" force="true" />
        <server name="SHELL_VERBOSITY" value="-1" />
        <server name="SYMFONY_PHPUNIT_REMOVE" value="" />
        <server name="SYMFONY_PHPUNIT_VERSION" value="11.0" />
    </php>

    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Functional">
            <directory>tests/Functional</directory>
        </testsuite>
        <testsuite name="API">
            <directory>tests/API</directory>
        </testsuite>
        <testsuite name="Security">
            <directory>tests/Security</directory>
        </testsuite>
        <testsuite name="Smoke">
            <directory>tests/Smoke</directory>
        </testsuite>
    </testsuites>

    <source restrictDeprecations="true" restrictNotices="true" restrictWarnings="true">
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </source>

    <extensions>
        <extension class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension" />
    </extensions>
</phpunit>
```

### Database Testing Configuration

Configure DAMADoctrineTestBundle in `config/packages/test/dama_doctrine_test_bundle.yaml`:

```yaml
dama_doctrine_test:
    enable_static_connection: true
    enable_static_meta_data_cache: true
    enable_static_query_cache: true
```

## Test Data

### Fixtures

Use Doctrine fixtures for test data:

```php
namespace App\DataFixtures;

use App\Entity\PenaltyType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PenaltyTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $drinkType = new PenaltyType();
        $drinkType->setName('Drink');
        $drinkType->setDescription('Beverage penalty');
        $drinkType->setIsDrink(true);
        $manager->persist($drinkType);
        $this->addReference('penalty-type-drink', $drinkType);
        
        $lateType = new PenaltyType();
        $lateType->setName('Late for training');
        $lateType->setDescription('Penalty for being late to training');
        $lateType->setIsDrink(false);
        $manager->persist($lateType);
        $this->addReference('penalty-type-late', $lateType);
        
        $manager->flush();
    }
}
```

### Factory Pattern

Consider using the factory pattern for test data:

```php
namespace App\Tests\Factory;

use App\Entity\Penalty;
use App\Entity\PenaltyType;
use App\Entity\TeamUser;

class PenaltyFactory
{
    public static function create(array $attributes = []): Penalty
    {
        $penalty = new Penalty();
        $penalty->setTeamUser($attributes['teamUser'] ?? self::createTeamUser());
        $penalty->setType($attributes['type'] ?? self::createPenaltyType());
        $penalty->setReason($attributes['reason'] ?? 'Test penalty');
        $penalty->setAmount($attributes['amount'] ?? 100);
        $penalty->setCurrency($attributes['currency'] ?? 'EUR');
        $penalty->setArchived($attributes['archived'] ?? false);
        
        if (isset($attributes['paidAt'])) {
            $penalty->setPaidAt($attributes['paidAt']);
        }
        
        return $penalty;
    }
    
    private static function createTeamUser(): TeamUser
    {
        // Implementation
    }
    
    private static function createPenaltyType(): PenaltyType
    {
        // Implementation
    }
}
```

## Test Execution

### Running Tests

```bash
# Run all tests
php bin/phpunit

# Run specific test suite
php bin/phpunit --testsuite=Unit

# Run specific test class
php bin/phpunit tests/Unit/Service/PenaltyCalculatorTest.php

# Run specific test method
php bin/phpunit --filter=testCalculateTotalForEmptyPenalties

# Generate coverage report
php bin/phpunit --coverage-html var/coverage
```

### Continuous Integration

Set up tests to run in your CI/CD pipeline:

```yaml
# .github/workflows/tests.yml
name: Tests

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  tests:
    runs-on: ubuntu-latest
    
    services:
      database:
        image: postgres:15
        env:
          POSTGRES_USER: postgres
          POSTGRES_PASSWORD: postgres
          POSTGRES_DB: app_test
        ports:
          - 5432:5432
        options: --health-cmd pg_isready --health-interval 10s --health-timeout 5s --health-retries 5
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, intl, pdo_pgsql
          coverage: xdebug
      
      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress
      
      - name: Execute Unit & Integration Tests
        run: php bin/phpunit --testsuite=Unit,Integration
      
      - name: Execute Functional Tests
        run: php bin/phpunit --testsuite=Functional
      
      - name: Execute API Tests
        run: php bin/phpunit --testsuite=API
      
      - name: Execute Security Tests
        run: php bin/phpunit --testsuite=Security
      
      - name: Execute Smoke Tests
        run: php bin/phpunit --testsuite=Smoke
      
      - name: Generate Coverage Report
        run: php bin/phpunit --coverage-clover coverage.xml
      
      - name: Upload Coverage to Codecov
        uses: codecov/codecov-action@v2
        with:
          file: ./coverage.xml
```

## Best Practices

1. **Arrange-Act-Assert Pattern**: Structure tests with setup, action, and verification phases.
2. **One Assertion Per Test**: Aim for focused tests with a single assertion.
3. **Test Isolation**: Tests should run independently and not depend on other tests.
4. **Use Data Providers**: Parameterize tests to cover multiple scenarios.
5. **Mock External Dependencies**: Use mocks to isolate the unit under test.
6. **Test Edge Cases**: Include tests for boundary conditions and error cases.
7. **Test Public API**: Focus on testing the public interface of classes.
8. **Descriptive Test Names**: Name tests to describe the scenario and expected outcome.
9. **Clean Test Data**: Create and clean up test data properly.

## Test Documentation

Document tests to help other developers understand their purpose:

```php
/**
 * Tests the penalty calculator service.
 */
class PenaltyCalculatorTest extends TestCase
{
    /**
     * Tests that the calculator returns zero for an empty array of penalties.
     */
    public function testCalculateTotalForEmptyPenalties(): void
    {
        // Test implementation
    }
    
    /**
     * Tests that the calculator correctly calculates the total for a single penalty.
     */
    public function testCalculateTotalForSinglePenalty(): void
    {
        // Test implementation
    }
}
```

## PHP 8.4 + PHPUnit 11 Modern Testing Patterns

### Property Hooks in Test Classes

Leverage PHP 8.4 property hooks for clean test setup:

```php
class PaymentServiceTest extends TestCase
{
    private PaymentService $service {
        get => $this->service ??= new PaymentService(
            $this->createMock(PaymentRepository::class),
            $this->createMock(EventDispatcherInterface::class)
        );
    }
    
    public function testProcessPayment(): void
    {
        $payment = new Payment(/* ... */);
        $result = $this->service->process($payment);
        
        $this->assertTrue($result->isSuccessful());
    }
}
```

### Readonly Test Data Objects

Use readonly classes for immutable test data:

```php
final readonly class TestPaymentData
{
    public function __construct(
        public PaymentTypeEnum $type = PaymentTypeEnum::CASH,
        public CurrencyEnum $currency = CurrencyEnum::EUR,
        public int $amount = 10000, // 100.00 EUR
        public \DateTimeImmutable $createdAt = new \DateTimeImmutable
    ) {}
    
    public static function bankTransfer(): self
    {
        return new self(type: PaymentTypeEnum::BANK_TRANSFER);
    }
    
    public static function creditCard(int $amount): self
    {
        return new self(type: PaymentTypeEnum::CREDIT_CARD, amount: $amount);
    }
}
```

### Enhanced Data Providers with Enums

Use modern enum patterns in PHPUnit data providers:

```php
class PaymentValidationTest extends TestCase
{
    /**
     * @dataProvider invalidPaymentTypeProvider
     */
    public function testInvalidPaymentTypesThrowException(PaymentTypeEnum $type): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Payment($type, -100); // Invalid amount
    }
    
    public static function invalidPaymentTypeProvider(): \Generator
    {
        foreach (PaymentTypeEnum::cases() as $type) {
            yield [$type];
        }
    }
    
    /**
     * @dataProvider validCurrencyProvider
     */
    public function testValidCurrencyFormatting(CurrencyEnum $currency, int $amount, string $expected): void
    {
        $money = new Money($amount, $currency);
        $this->assertEquals($expected, $money->format());
    }
    
    public static function validCurrencyProvider(): array
    {
        return [
            'EUR currency' => [CurrencyEnum::EUR, 12345, '123.45 €'],
            'USD currency' => [CurrencyEnum::USD, 12345, '$123.45'],
            'GBP currency' => [CurrencyEnum::GBP, 12345, '£123.45'],
        ];
    }
}
```

### Parallel Testing with PHPUnit 11

Leverage PHPUnit 11's parallel execution for faster tests:

```xml
<!-- phpunit.xml.dist -->
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         executionOrder="depends,defects"
         processIsolation="false"
         stopOnError="false"
         stopOnFailure="false"
         processTimeout="60"
         cacheDirectory=".phpunit.cache">
         
    <extensions>
        <extension class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension" />
    </extensions>
    
    <testsuites>
        <testsuite name="Fast">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Slow">
            <directory>tests/Integration</directory>
            <directory>tests/Functional</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

Run tests in parallel:
```bash
# Run fast tests in parallel
php bin/phpunit --testsuite=Fast --parallel

# Run all tests with optimized execution order
php bin/phpunit --order-by=depends,defects
```

### Mock Objects with Enhanced Type Safety

Create type-safe mocks with PHP 8.4 features:

```php
class ContributionServiceTest extends TestCase
{
    public function testCreateContribution(): void
    {
        $mockRepository = $this->createMock(ContributionRepository::class);
        $mockEventDispatcher = $this->createMock(EventDispatcherInterface::class);
        
        // Type-safe mock configuration
        $mockRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Contribution::class));
            
        $mockEventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ContributionCreatedEvent::class));
        
        $service = new ContributionService($mockRepository, $mockEventDispatcher);
        
        $contribution = $service->createContribution(
            teamUser: $this->createTestTeamUser(),
            type: $this->createTestContributionType(),
            description: 'Test contribution',
            amount: new Money(5000, CurrencyEnum::EUR),
            dueDate: new \DateTimeImmutable('+1 month')
        );
        
        $this->assertInstanceOf(Contribution::class, $contribution);
    }
}
```

### Performance Testing with PHP 8.4 JIT

Test performance improvements with JIT compilation:

```php
class PerformanceTest extends TestCase
{
    /**
     * @group performance
     */
    public function testBulkContributionCreationPerformance(): void
    {
        $start = microtime(true);
        
        $service = $this->getContainer()->get(ContributionService::class);
        
        // Create 1000 contributions
        for ($i = 0; $i < 1000; $i++) {
            $service->createContribution(/* ... */);
        }
        
        $executionTime = microtime(true) - $start;
        
        // With PHP 8.4 JIT, this should be significantly faster
        $this->assertLessThan(1.0, $executionTime, 'Bulk creation should complete within 1 second');
    }
}
```

### Test Configuration for PHP 8.4

Enhanced test configuration utilizing PHP 8.4 features:

```php
// tests/bootstrap.php
declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Enable PHP 8.4 JIT for tests if available
if (function_exists('opcache_get_status') && opcache_get_status()['jit']['enabled']) {
    echo "Running tests with PHP 8.4 JIT enabled\n";
}
```

## Conclusion

A comprehensive testing strategy is essential for maintaining code quality and preventing regressions. By following these guidelines and leveraging PHP 8.4's enhanced features like property hooks, asymmetric visibility, readonly classes, and JIT performance improvements alongside PHPUnit 11's parallel execution and enhanced assertions, we can create more reliable, maintainable, and performant test suites.