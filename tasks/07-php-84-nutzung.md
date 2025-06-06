# Implementierungsplan: PHP 8.4 Nutzung

## 1. Constructor Property Promotion

### Problem
Viele Klassen verwenden immer noch den traditionellen Stil mit separater Eigenschaftsdeklaration und -zuweisung im Konstruktor.

### Lösung

1. **Vorher: Traditioneller Stil**

```php
class ReportService
{
    private ReportRepository $reportRepository;
    private NotificationService $notificationService;
    private LoggerInterface $logger;

    public function __construct(
        ReportRepository $reportRepository,
        NotificationService $notificationService,
        LoggerInterface $logger
    ) {
        $this->reportRepository = $reportRepository;
        $this->notificationService = $notificationService;
        $this->logger = $logger;
    }
}
```

2. **Nachher: Constructor Property Promotion**

```php
class ReportService
{
    public function __construct(
        private readonly ReportRepository $reportRepository,
        private readonly NotificationService $notificationService,
        private readonly LoggerInterface $logger
    ) {}
}
```

## 2. Union und Intersection Types

### Problem
Viele Methoden verwenden PHPDoc-Kommentare für Typendeklarationen oder haben gar keine Typen.

### Lösung

1. **Vorher: PHPDoc oder fehlende Typen**

```php
/**
 * @param string|int $id
 * @param array $options
 * @return User|null
 */
public function findUserById($id, $options = [])
{
    // Implementation
}
```

2. **Nachher: Native Union Types**

```php
public function findUserById(string|int $id, array $options = []): ?User
{
    // Implementation
}
```

3. **Nachher: Mit Intersection Types**

```php
// Beispiel für Interface Intersection
public function processEntity(object&Identifiable&Serializable $entity): void
{
    // Verarbeitet nur Objekte, die beide Interfaces implementieren
}
```

## 3. Readonly Classes und Properties

### Problem
DTOs und Value Objects sind nicht durchgängig als unveränderlich (readonly) markiert.

### Lösung

1. **Vorher: Veränderbare DTO-Klasse**

```php
class UserDTO
{
    public string $id;
    public string $name;
    public string $email;

    public function __construct(string $id, string $name, string $email)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }
}
```

2. **Nachher: Readonly-Klasse mit Constructor Property Promotion**

```php
readonly class UserDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email
    ) {}
}
```

## 4. Enums für statische Typen

### Problem
String-basierte Enumerationen oder Konstantenklassen werden anstelle von nativen Enums verwendet.

### Lösung

1. **Vorher: Konstantenklasse**

```php
class PaymentStatusConstants
{
    public const PENDING = 'pending';
    public const COMPLETED = 'completed';
    public const FAILED = 'failed';
    public const REFUNDED = 'refunded';
}
```

2. **Nachher: Native Enum**

```php
enum PaymentStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function canBeRefunded(): bool
    {
        return $this === self::COMPLETED;
    }

    public static function fromStatus(string $status): self
    {
        return match($status) {
            'pending' => self::PENDING,
            'completed' => self::COMPLETED,
            'failed' => self::FAILED,
            'refunded' => self::REFUNDED,
            default => throw new \InvalidArgumentException("Ungültiger Status: {$status}")
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
```

## 5. Named Arguments

### Problem
Komplexe Methodenaufrufe mit vielen Parametern sind schwer lesbar.

### Lösung

1. **Vorher: Positionsbasierte Argumente**

```php
$reportService->generateReport(
    '550e8400-e29b-41d4-a716-446655440000',
    true,
    'pdf',
    'monthly',
    null,
    new \DateTimeImmutable('2023-01-01'),
    new \DateTimeImmutable('2023-01-31'),
    false
);
```

2. **Nachher: Named Arguments**

```php
$reportService->generateReport(
    id: '550e8400-e29b-41d4-a716-446655440000',
    sendNotification: true,
    format: 'pdf',
    type: 'monthly',
    filter: null,
    startDate: new \DateTimeImmutable('2023-01-01'),
    endDate: new \DateTimeImmutable('2023-01-31'),
    includeArchived: false
);
```

## 6. First-Class Callable Syntax

### Problem
Callback-Funktionen erfordern umständliche Syntax in Array-Funktionen.

### Lösung

1. **Vorher: Traditionelle Callback-Syntax**

```php
$userIds = array_map(function (User $user) {
    return $user->getId()->toString();
}, $users);

// Oder mit Callable-Array
$userIds = array_map([$userMapper, 'mapToId'], $users);
```

2. **Nachher: First-Class Callable Syntax**

```php
$userIds = array_map($user->getId(...)->toString(...), $users);

// Oder für Methoden einer Klasse
$userIds = array_map($userMapper->mapToId(...), $users);
```

## 7. Type Declarations überall

### Problem
Unvollständige oder fehlende Typ-Deklarationen machen den Code weniger sicher.

### Lösung

1. **Vorher: Fehlende oder unvollständige Typen**

```php
public function calculateTotals($items, $applyDiscount = false)
{
    // Implementation
}
```

2. **Nachher: Vollständige Typdeklarationen**

```php
/**
 * @param array<int, Item> $items Liste der zu berechnenden Artikel
 */
public function calculateTotals(array $items, bool $applyDiscount = false): float
{
    // Implementation
}
```

## 8. PHP 8.4-spezifische Features

### Problem
Neue PHP 8.4 Features werden nicht genutzt.

### Lösung

1. **Nutzung des neuen `class_alias` für Enumerationen**

```php
// Erstellung eines class_alias für Abwärtskompatibilität
class_alias(PaymentStatusEnum::class, 'App\Legacy\PaymentStatus');
```

2. **Attribute für Validierungslogik**

```php
class UpdatePaymentDTO
{
    public function __construct(
        #[Assert\PositiveOrZero]
        public ?int $amount = null,

        #[Assert\Choice(choices: ['EUR', 'USD', 'GBP'])]
        public ?string $currency = null,

        #[Assert\Choice(choices: ['cash', 'bank_transfer', 'credit_card', 'mobile_payment'])]
        public ?string $type = null,

        #[Assert\Length(max: 255)]
        public ?string $description = null,

        #[Assert\Length(max: 255)]
        public ?string $reference = null
    ) {}
}
```

## Zeitplan und Priorisierung

1. **Sofort (Woche 1):**
   - Aktualisierung der Enums zur Verwendung nativer Enums
   - Einführung von Constructor Property Promotion für neue Klassen

2. **Kurzfristig (Woche 2-3):**
   - Umstellung der DTOs auf readonly Klassen
   - Hinzufügen von Union Types zu kritischen Methoden

3. **Mittelfristig (Woche 4-6):**
   - Vollständige Type Declarations für alle Methoden
   - Refactoring komplexer Methodenaufrufe zu Named Arguments

4. **Langfristig (kontinuierlich):**
   - First-Class Callable Syntax für Array-Funktionen einführen
   - Überwachung und Anpassung an zukünftige PHP 8.4 Features
