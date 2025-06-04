# PHP Enums Best Practices

This document outlines the best practices for using PHP enums in the Cashbox project, leveraging modern PHP 8.4 features and state-of-the-art patterns.

## Introduction

PHP 8.1 introduced Enumerations (enums), and PHP 8.4 has enhanced them with improved performance, better integration with property hooks, and enhanced type system support. In Cashbox, we extensively use enums to improve code quality, maintain type safety, and make the code more readable and maintainable while taking advantage of modern PHP 8.4 optimizations.

## Types of Enums

### Pure Enums

Pure enums are simple enumerations without associated values.

```php
enum Status
{
    case PENDING;
    case APPROVED;
    case REJECTED;
}
```

### Backed Enums

Backed enums have string or integer values associated with each case, making them ideal for database storage.

```php
enum Status: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
```

## Enum Usage in the Project

The Cashbox Management System uses several key enums:

### UserRoleEnum

Used to define user roles within a team.

```php
enum UserRoleEnum: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case TREASURER = 'treasurer';
    case MEMBER = 'member';
    
    public function getLabel(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Manager',
            self::TREASURER => 'Treasurer',
            self::MEMBER => 'Member',
        };
    }
    
    public function getPermissions(): array
    {
        return match($this) {
            self::ADMIN => ['team:edit', 'user:edit', 'penalty:edit', 'penalty:delete', 'contribution:edit', 'payment:edit', 'report:view'],
            self::MANAGER => ['team:view', 'user:view', 'penalty:edit', 'contribution:view', 'payment:view', 'report:view'],
            self::TREASURER => ['team:view', 'user:view', 'penalty:view', 'contribution:edit', 'payment:edit', 'report:view'],
            self::MEMBER => ['team:view', 'user:view', 'penalty:view', 'contribution:view'],
        };
    }
}
```

### PenaltyTypeEnum

Used to categorize different types of penalties.

```php
enum PenaltyTypeEnum: string
{
    case DRINK = 'drink';
    case LATE_ARRIVAL = 'late_arrival';
    case MISSED_TRAINING = 'missed_training';
    case CUSTOM = 'custom';
    
    public function getLabel(): string
    {
        return match($this) {
            self::DRINK => 'Drink',
            self::LATE_ARRIVAL => 'Late Arrival',
            self::MISSED_TRAINING => 'Missed Training',
            self::CUSTOM => 'Custom',
        };
    }
    
    public function isDrink(): bool
    {
        return $this === self::DRINK;
    }
}
```

### CurrencyEnum

Used to standardize currency handling.

```php
enum CurrencyEnum: string
{
    case EUR = 'EUR';
    case USD = 'USD';
    case GBP = 'GBP';
    
    public function getSymbol(): string
    {
        return match($this) {
            self::EUR => '€',
            self::USD => '$',
            self::GBP => '£',
        };
    }
    
    public function formatAmount(int $amount): string
    {
        $formattedAmount = number_format($amount / 100, 2);
        
        return match($this) {
            self::EUR => $formattedAmount . ' ' . $this->getSymbol(),
            self::USD, self::GBP => $this->getSymbol() . $formattedAmount,
        };
    }
}
```

### PaymentTypeEnum

Used to categorize different types of payments.

```php
enum PaymentTypeEnum: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CREDIT_CARD = 'credit_card';
    case MOBILE_PAYMENT = 'mobile_payment';
    
    public function getLabel(): string
    {
        return match($this) {
            self::CASH => 'Cash',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CREDIT_CARD => 'Credit Card',
            self::MOBILE_PAYMENT => 'Mobile Payment',
        };
    }
    
    public function requiresReference(): bool
    {
        return match($this) {
            self::CASH => false,
            self::BANK_TRANSFER, self::CREDIT_CARD, self::MOBILE_PAYMENT => true,
        };
    }
}
```

## Best Practices

### 1. Use String-Backed Enums for Database Storage

When storing enum values in the database, use string-backed enums for better readability and maintainability.

```php
/**
 * @ORM\Column(type="string", length=30)
 */
private string $type;

public function getType(): PenaltyTypeEnum
{
    return PenaltyTypeEnum::from($this->type);
}

public function setType(PenaltyTypeEnum $type): self
{
    $this->type = $type->value;
    
    return $this;
}
```

### 2. Add Helper Methods to Enums

Extend enums with methods that provide additional functionality:

```php
enum CurrencyEnum: string
{
    // Cases...
    
    public function getSymbol(): string { /* ... */ }
    public function formatAmount(int $amount): string { /* ... */ }
    public function getDefaultFormat(): string { /* ... */ }
    public function getExchangeRateUrl(): string { /* ... */ }
}
```

### 3. Use Enums in Type Hints

Leverage PHP's type system by using enums in method signatures:

```php
public function createPayment(TeamUser $teamUser, int $amount, CurrencyEnum $currency, PaymentTypeEnum $type): Payment
{
    $payment = new Payment();
    $payment->setTeamUser($teamUser);
    $payment->setAmount($amount);
    $payment->setCurrency($currency);
    $payment->setType($type);
    
    return $payment;
}
```

### 4. Use Match Expressions with Enums

Take advantage of PHP's match expression for concise, type-safe handling of enum cases:

```php
public function getPermissionLabel(Permission $permission): string
{
    return match($permission->getType()) {
        PermissionTypeEnum::READ => 'Can read',
        PermissionTypeEnum::WRITE => 'Can write',
        PermissionTypeEnum::DELETE => 'Can delete',
        PermissionTypeEnum::ADMIN => 'Full access',
    };
}
```

### 5. Provide Conversion Methods for Frontend Use

When sending enum data to the frontend, provide methods to convert enums to a frontend-friendly format:

```php
public function toArray(): array
{
    return [
        'value' => $this->value,
        'label' => $this->getLabel(),
        // Add any other properties needed by the frontend
    ];
}

// Or static methods to get all cases
public static function getAllForFrontend(): array
{
    return array_map(
        fn (self $case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
        ],
        self::cases()
    );
}
```

### 6. Use Enums in Form Types

Leverage enums in Symfony form types:

```php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;

class PaymentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EnumType::class, [
                'class' => PaymentTypeEnum::class,
                'choice_label' => fn (PaymentTypeEnum $choice) => $choice->getLabel(),
            ])
            ->add('currency', EnumType::class, [
                'class' => CurrencyEnum::class,
                'choice_label' => fn (CurrencyEnum $choice) => $choice->value . ' (' . $choice->getSymbol() . ')',
            ]);
    }
}
```

### 7. Document Enums Thoroughly

Provide comprehensive documentation for enums to help other developers understand their purpose and usage:

```php
/**
 * Represents the type of a penalty.
 *
 * This enum is used to categorize penalties in the system.
 * The DRINK type is handled specially for beverages tracking.
 */
enum PenaltyTypeEnum: string
{
    /**
     * Represents a drink penalty.
     */
    case DRINK = 'drink';
    
    /**
     * Represents a penalty for being late to training.
     */
    case LATE_ARRIVAL = 'late_arrival';
    
    // Other cases...
}
```

### 8. Create Repositories for Enum Access

For complex applications with many enums, consider creating a repository for centralized access:

```php
class EnumRepository
{
    /**
     * @return array<string, string>
     */
    public function getPenaltyTypes(): array
    {
        return array_combine(
            array_column(PenaltyTypeEnum::cases(), 'value'),
            array_map(fn (PenaltyTypeEnum $case) => $case->getLabel(), PenaltyTypeEnum::cases())
        );
    }
    
    /**
     * @return array<string, string>
     */
    public function getCurrencies(): array
    {
        return array_combine(
            array_column(CurrencyEnum::cases(), 'value'),
            array_map(fn (CurrencyEnum $case) => $case->value . ' (' . $case->getSymbol() . ')', CurrencyEnum::cases())
        );
    }
}
```

### 9. Use Enums in Validation Constraints

Leverage enums in Symfony validation constraints:

```php
use Symfony\Component\Validator\Constraints as Assert;

class PaymentRequest
{
    #[Assert\NotNull]
    #[Assert\Type(PaymentTypeEnum::class)]
    public PaymentTypeEnum $type;
    
    #[Assert\NotNull]
    #[Assert\Type(CurrencyEnum::class)]
    public CurrencyEnum $currency;
    
    // Other properties...
}
```

### 10. Handle Database Migration to Enums

When migrating from string columns to enums, ensure proper validation:

```php
// In a migration class
public function up(Schema $schema): void
{
    // First, validate that all existing values are valid enum values
    $this->connection->executeQuery('
        SELECT DISTINCT type FROM penalty_type
        WHERE type NOT IN (:validTypes)
    ', [
        'validTypes' => array_map(fn ($case) => $case->value, PenaltyTypeEnum::cases())
    ]);
    
    // Then, update any schema constraints if needed
    $this->addSql('ALTER TABLE penalty_type ADD CONSTRAINT check_penalty_type_type CHECK (type IN (:validTypes))', [
        'validTypes' => array_map(fn ($case) => $case->value, PenaltyTypeEnum::cases())
    ]);
}
```

## Serialization with API Platform

When using API Platform, make sure to configure proper serialization/deserialization for enum types:

```php
// config/packages/api_platform.yaml
api_platform:
    # ...
    formats:
        jsonld:
            mime_types: ['application/ld+json']
        json:
            mime_types: ['application/json']
        html:
            mime_types: ['text/html']
    # ...
    doctrine:
        # ...
    mapping:
        paths: ['%kernel.project_dir%/src/Entity']
    patch_formats:
        json: ['application/merge-patch+json']
    swagger:
        versions: [3]
    serializer:
        mapping:
            paths: ['%kernel.project_dir%/config/serializer']

# In a serialization config file
AppBundle\Entity\Payment:
    attributes:
        currency:
            serialization_type: string
            serialization_value: "object.getCurrency().value"
            deserialization_type: object
            deserialization_value: "CurrencyEnum::from(value)"
```

## Testing Enums

When testing code that uses enums, make sure to test all possible enum values:

```php
public function testPaymentTypeRequiresReference(): void
{
    $this->assertFalse(PaymentTypeEnum::CASH->requiresReference());
    $this->assertTrue(PaymentTypeEnum::BANK_TRANSFER->requiresReference());
    $this->assertTrue(PaymentTypeEnum::CREDIT_CARD->requiresReference());
    $this->assertTrue(PaymentTypeEnum::MOBILE_PAYMENT->requiresReference());
}

/**
 * @dataProvider currencyFormatProvider
 */
public function testCurrencyFormat(CurrencyEnum $currency, int $amount, string $expected): void
{
    $this->assertSame($expected, $currency->formatAmount($amount));
}

public function currencyFormatProvider(): array
{
    return [
        [CurrencyEnum::EUR, 10050, '100.50 €'],
        [CurrencyEnum::USD, 10050, '$100.50'],
        [CurrencyEnum::GBP, 10050, '£100.50'],
    ];
}
```

## PHP 8.4 Modern Enum Patterns

### Property Hooks Integration

Leverage PHP 8.4 property hooks for clean enum-based property management:

```php
class PaymentRequest
{
    public function __construct(
        public PaymentTypeEnum $type {
            set => match($value) {
                PaymentTypeEnum::CASH => $value,
                PaymentTypeEnum::BANK_TRANSFER => $this->validateBankTransfer($value),
                default => $value
            };
        },
        public CurrencyEnum $currency,
        public readonly int $amount
    ) {}
    
    private function validateBankTransfer(PaymentTypeEnum $type): PaymentTypeEnum
    {
        // Custom validation logic
        return $type;
    }
}
```

### Asymmetric Visibility with Enums

Use public(set) for enum properties that should be publicly readable but privately settable:

```php
class OrderStatus
{
    public function __construct(
        public(set) OrderStatusEnum $status = OrderStatusEnum::PENDING,
        public readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable
    ) {}
    
    public function markAsProcessing(): void
    {
        $this->status = OrderStatusEnum::PROCESSING;
    }
}
```

### Enhanced Enum Performance in PHP 8.4

PHP 8.4 provides significant performance improvements for enum operations:

```php
enum CacheKeyEnum: string
{
    case USER_PROFILE = 'user_profile_';
    case TEAM_STATS = 'team_stats_';
    case FINANCIAL_REPORT = 'financial_report_';
    
    // Optimized for PHP 8.4 JIT compilation
    public function withId(string $id): string
    {
        return $this->value . $id;
    }
    
    // Enhanced pattern matching performance
    public function getTtl(): int
    {
        return match($this) {
            self::USER_PROFILE => 3600,      // 1 hour
            self::TEAM_STATS => 1800,        // 30 minutes  
            self::FINANCIAL_REPORT => 21600, // 6 hours
        };
    }
}
```

### Modern Enum Serialization

Enhanced serialization patterns for APIs with PHP 8.4:

```php
enum PaymentStatusEnum: string implements \JsonSerializable
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->getLabel(),
            'metadata' => $this->getMetadata(),
        ];
    }
    
    public function getMetadata(): array
    {
        return match($this) {
            self::PENDING => ['color' => 'yellow', 'icon' => 'clock'],
            self::COMPLETED => ['color' => 'green', 'icon' => 'check'],
            self::FAILED => ['color' => 'red', 'icon' => 'x'],
            self::REFUNDED => ['color' => 'blue', 'icon' => 'arrow-left'],
        };
    }
}
```

### Type-Safe Enum Collections

Modern collection patterns with enhanced type safety:

```php
/**
 * @template T of PaymentTypeEnum
 */
final readonly class PaymentTypeCollection
{
    /**
     * @param array<T> $types
     */
    public function __construct(
        private array $types = []
    ) {}
    
    public function contains(PaymentTypeEnum $type): bool
    {
        return in_array($type, $this->types, true);
    }
    
    public function filter(callable $predicate): self
    {
        return new self(array_filter($this->types, $predicate));
    }
    
    /**
     * @return array<string>
     */
    public function toValues(): array
    {
        return array_map(fn(PaymentTypeEnum $type) => $type->value, $this->types);
    }
}
```

## Conclusion

Enums are a powerful feature in PHP 8.1+ that have been significantly enhanced in PHP 8.4. By following these best practices and leveraging modern PHP 8.4 features like property hooks, asymmetric visibility, and enhanced performance optimizations, we can create more robust, maintainable, and performant code in Cashbox.