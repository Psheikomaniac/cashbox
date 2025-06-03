# Version 1.1.0: User Experience and Reporting

## Overview

Version 1.1.0 focuses on enhancing the user experience and introducing reporting capabilities to Cashbox. Building on the foundation established in version 1.0.0, this release will provide more advanced filtering, sorting, and visualization features for better financial management.

## Release Timeline

- **Development Start**: August 1, 2025
- **Alpha Release**: August 15, 2025
- **Beta Release**: August 30, 2025
- **Production Release**: September 15, 2025

## Scope

### Core Components

1. **Enhanced API Functionality**
   - Advanced filtering capabilities
   - Sorting options for all resources
   - Pagination improvements
   - Search functionality

2. **Reporting System**
   - Basic financial reports
   - User penalty summaries
   - Team financial overviews
   - Date range filtering
   - Export to PDF and Excel

3. **User Dashboard**
   - Personal penalty overview
   - Payment history
   - Outstanding balances
   - Payment methods management

4. **Admin Dashboard**
   - Team financial overview
   - User balance tracking
   - Payment status monitoring
   - Financial metrics and statistics

5. **Notification System**
   - Email notifications for new penalties
   - Payment reminders
   - Balance updates
   - Notification preferences

6. **Export Functionality**
   - Export reports to various formats
   - Scheduled report generation
   - Customizable export templates
   - Batch export options

## Technical Requirements

### New Entities

1. **Report Entity**
   ```php
   use App\Enum\ReportTypeEnum;
   use App\Event\ReportCreatedEvent;
   use App\Event\ReportGeneratedEvent;
   use Doctrine\ORM\Mapping as ORM;
   use Ramsey\Uuid\Uuid;
   use Ramsey\Uuid\UuidInterface;
   use Symfony\Component\Validator\Constraints as Assert;

   #[ORM\Entity(repositoryClass: ReportRepository::class)]
   #[ORM\Table(name: 'reports')]
   class Report
   {
       #[ORM\Id]
       #[ORM\Column(type: 'uuid', unique: true)]
       private UuidInterface $id;
       
       #[ORM\Column(type: 'string', length: 255)]
       #[Assert\NotBlank]
       #[Assert\Length(max: 255)]
       private string $name;
       
       #[ORM\Column(type: 'string', length: 255, enumType: ReportTypeEnum::class)]
       private ReportTypeEnum $type;
       
       #[ORM\Column(type: 'json')]
       private array $parameters = [];
       
       #[ORM\Column(type: 'json', nullable: true)]
       private ?array $result = null;
       
       #[ORM\ManyToOne(targetEntity: User::class)]
       #[ORM\JoinColumn(nullable: false)]
       private User $createdBy;
       
       #[ORM\Column(type: 'boolean')]
       private bool $scheduled = false;
       
       #[ORM\Column(type: 'string', length: 255, nullable: true)]
       private ?string $cronExpression = null;
       
       #[ORM\Column(type: 'datetime_immutable')]
       private \DateTimeImmutable $createdAt;
       
       #[ORM\Column(type: 'datetime_immutable')]
       private \DateTimeImmutable $updatedAt;
       
       private array $domainEvents = [];

       public function __construct(
           string $name,
           ReportTypeEnum $type,
           array $parameters,
           User $createdBy,
           bool $scheduled = false,
           ?string $cronExpression = null
       ) {
           $this->id = Uuid::uuid7();
           $this->name = $name;
           $this->type = $type;
           $this->parameters = $parameters;
           $this->createdBy = $createdBy;
           $this->scheduled = $scheduled;
           $this->cronExpression = $cronExpression;
           $this->createdAt = new \DateTimeImmutable();
           $this->updatedAt = new \DateTimeImmutable();
           
           $this->recordEvent(new ReportCreatedEvent($this));
       }

       public function generate(array $result): void
       {
           $this->result = $result;
           $this->updatedAt = new \DateTimeImmutable();
           
           $this->recordEvent(new ReportGeneratedEvent($this));
       }

       public function update(string $name, array $parameters): void
       {
           $this->name = $name;
           $this->parameters = $parameters;
           $this->updatedAt = new \DateTimeImmutable();
       }

       // Property hooks for clean access (PHP 8.4)
       public function getId(): UuidInterface { return $this->id; }
       public function getName(): string { return $this->name; }
       public function getType(): ReportTypeEnum { return $this->type; }
       public function getParameters(): array { return $this->parameters; }
       public function getResult(): ?array { return $this->result; }
       public function getCreatedBy(): User { return $this->createdBy; }
       public function isScheduled(): bool { return $this->scheduled; }
       public function getCronExpression(): ?string { return $this->cronExpression; }
       public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
       public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

       private function recordEvent(object $event): void
       {
           $this->domainEvents[] = $event;
       }

       public function getEvents(): array
       {
           return $this->domainEvents;
       }

       public function clearEvents(): void
       {
           $this->domainEvents = [];
       }
   }
   ```

2. **Notification Entity**
   ```php
   use App\Enum\NotificationTypeEnum;
   use App\Event\NotificationCreatedEvent;
   use App\Event\NotificationReadEvent;
   use Doctrine\ORM\Mapping as ORM;
   use Ramsey\Uuid\Uuid;
   use Ramsey\Uuid\UuidInterface;
   use Symfony\Component\Validator\Constraints as Assert;

   #[ORM\Entity(repositoryClass: NotificationRepository::class)]
   #[ORM\Table(name: 'notifications')]
   #[ORM\Index(columns: ['user_id', 'read'], name: 'idx_user_read')]
   #[ORM\Index(columns: ['created_at'], name: 'idx_created_at')]
   class Notification
   {
       #[ORM\Id]
       #[ORM\Column(type: 'uuid', unique: true)]
       private UuidInterface $id;
       
       #[ORM\ManyToOne(targetEntity: User::class)]
       #[ORM\JoinColumn(nullable: false)]
       private User $user;
       
       #[ORM\Column(type: 'string', length: 255, enumType: NotificationTypeEnum::class)]
       private NotificationTypeEnum $type;
       
       #[ORM\Column(type: 'string', length: 255)]
       #[Assert\NotBlank]
       #[Assert\Length(max: 255)]
       private string $title;
       
       #[ORM\Column(type: 'text')]
       #[Assert\NotBlank]
       private string $message;
       
       #[ORM\Column(type: 'json', nullable: true)]
       private ?array $data = null;
       
       #[ORM\Column(type: 'boolean')]
       private bool $read = false;
       
       #[ORM\Column(type: 'datetime_immutable', nullable: true)]
       private ?\DateTimeImmutable $readAt = null;
       
       #[ORM\Column(type: 'datetime_immutable')]
       private \DateTimeImmutable $createdAt;
       
       private array $domainEvents = [];

       public function __construct(
           User $user,
           NotificationTypeEnum $type,
           string $title,
           string $message,
           ?array $data = null
       ) {
           $this->id = Uuid::uuid7();
           $this->user = $user;
           $this->type = $type;
           $this->title = $title;
           $this->message = $message;
           $this->data = $data;
           $this->createdAt = new \DateTimeImmutable();
           
           $this->recordEvent(new NotificationCreatedEvent($this));
       }

       public function markAsRead(): void
       {
           if ($this->read) {
               return;
           }
           
           $this->read = true;
           $this->readAt = new \DateTimeImmutable();
           
           $this->recordEvent(new NotificationReadEvent($this));
       }

       public function isUnread(): bool
       {
           return !$this->read;
       }

       // Property accessors
       public function getId(): UuidInterface { return $this->id; }
       public function getUser(): User { return $this->user; }
       public function getType(): NotificationTypeEnum { return $this->type; }
       public function getTitle(): string { return $this->title; }
       public function getMessage(): string { return $this->message; }
       public function getData(): ?array { return $this->data; }
       public function isRead(): bool { return $this->read; }
       public function getReadAt(): ?\DateTimeImmutable { return $this->readAt; }
       public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

       private function recordEvent(object $event): void
       {
           $this->domainEvents[] = $event;
       }

       public function getEvents(): array
       {
           return $this->domainEvents;
       }

       public function clearEvents(): void
       {
           $this->domainEvents = [];
       }
   }
   ```

3. **NotificationPreference Entity**
   ```php
   use App\Enum\NotificationTypeEnum;
   use App\Event\NotificationPreferenceUpdatedEvent;
   use Doctrine\ORM\Mapping as ORM;
   use Ramsey\Uuid\Uuid;
   use Ramsey\Uuid\UuidInterface;

   #[ORM\Entity(repositoryClass: NotificationPreferenceRepository::class)]
   #[ORM\Table(name: 'notification_preferences')]
   #[ORM\UniqueConstraint(columns: ['user_id', 'notification_type'])]
   class NotificationPreference
   {
       #[ORM\Id]
       #[ORM\Column(type: 'uuid', unique: true)]
       private UuidInterface $id;
       
       #[ORM\ManyToOne(targetEntity: User::class)]
       #[ORM\JoinColumn(nullable: false)]
       private User $user;
       
       #[ORM\Column(type: 'string', length: 255, enumType: NotificationTypeEnum::class)]
       private NotificationTypeEnum $notificationType;
       
       #[ORM\Column(type: 'boolean')]
       private bool $emailEnabled = true;
       
       #[ORM\Column(type: 'boolean')]
       private bool $inAppEnabled = true;
       
       #[ORM\Column(type: 'datetime_immutable')]
       private \DateTimeImmutable $createdAt;
       
       #[ORM\Column(type: 'datetime_immutable')]
       private \DateTimeImmutable $updatedAt;
       
       private array $domainEvents = [];

       public function __construct(
           User $user,
           NotificationTypeEnum $notificationType,
           bool $emailEnabled = true,
           bool $inAppEnabled = true
       ) {
           $this->id = Uuid::uuid7();
           $this->user = $user;
           $this->notificationType = $notificationType;
           $this->emailEnabled = $emailEnabled;
           $this->inAppEnabled = $inAppEnabled;
           $this->createdAt = new \DateTimeImmutable();
           $this->updatedAt = new \DateTimeImmutable();
       }

       public function updatePreferences(bool $emailEnabled, bool $inAppEnabled): void
       {
           $changed = $this->emailEnabled !== $emailEnabled || $this->inAppEnabled !== $inAppEnabled;
           
           if (!$changed) {
               return;
           }
           
           $this->emailEnabled = $emailEnabled;
           $this->inAppEnabled = $inAppEnabled;
           $this->updatedAt = new \DateTimeImmutable();
           
           $this->recordEvent(new NotificationPreferenceUpdatedEvent($this));
       }

       public function isNotificationAllowed(string $channel): bool
       {
           return match ($channel) {
               'email' => $this->emailEnabled,
               'in_app' => $this->inAppEnabled,
               default => false,
           };
       }

       // Property accessors
       public function getId(): UuidInterface { return $this->id; }
       public function getUser(): User { return $this->user; }
       public function getNotificationType(): NotificationTypeEnum { return $this->notificationType; }
       public function isEmailEnabled(): bool { return $this->emailEnabled; }
       public function isInAppEnabled(): bool { return $this->inAppEnabled; }
       public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
       public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

       private function recordEvent(object $event): void
       {
           $this->domainEvents[] = $event;
       }

       public function getEvents(): array
       {
           return $this->domainEvents;
       }

       public function clearEvents(): void
       {
           $this->domainEvents = [];
       }
   }
   ```

### New Enums

1. **ReportTypeEnum** - Enhanced enum with business logic
   ```php
   enum ReportTypeEnum: string
   {
       case FINANCIAL = 'financial';
       case PENALTY_SUMMARY = 'penalty_summary';
       case USER_ACTIVITY = 'user_activity';
       case TEAM_OVERVIEW = 'team_overview';
       case PAYMENT_HISTORY = 'payment_history';
       case AUDIT_LOG = 'audit_log';
       
       public function getLabel(): string
       {
           return match($this) {
               self::FINANCIAL => 'Financial Report',
               self::PENALTY_SUMMARY => 'Penalty Summary',
               self::USER_ACTIVITY => 'User Activity Report',
               self::TEAM_OVERVIEW => 'Team Overview',
               self::PAYMENT_HISTORY => 'Payment History',
               self::AUDIT_LOG => 'Audit Log',
           };
       }
       
       public function getRequiredParameters(): array
       {
           return match($this) {
               self::FINANCIAL, self::PENALTY_SUMMARY => ['dateFrom', 'dateTo', 'teamId'],
               self::USER_ACTIVITY => ['userId', 'dateFrom', 'dateTo'],
               self::TEAM_OVERVIEW => ['teamId'],
               self::PAYMENT_HISTORY => ['dateFrom', 'dateTo', 'userId'],
               self::AUDIT_LOG => ['dateFrom', 'dateTo'],
           };
       }
       
       public function getEstimatedExecutionTime(): int
       {
           return match($this) {
               self::FINANCIAL => 30, // seconds
               self::PENALTY_SUMMARY => 15,
               self::USER_ACTIVITY => 10,
               self::TEAM_OVERVIEW => 5,
               self::PAYMENT_HISTORY => 20,
               self::AUDIT_LOG => 60,
           };
       }
       
       public function requiresAsync(): bool
       {
           return $this->getEstimatedExecutionTime() > 30;
       }
   }
   ```

2. **NotificationTypeEnum** - Type-safe notification categories
   ```php
   enum NotificationTypeEnum: string
   {
       case PENALTY_CREATED = 'penalty_created';
       case PAYMENT_RECEIVED = 'payment_received';
       case PAYMENT_REMINDER = 'payment_reminder';
       case BALANCE_UPDATE = 'balance_update';
       case REPORT_GENERATED = 'report_generated';
       case SYSTEM_UPDATE = 'system_update';
       
       public function getLabel(): string
       {
           return match($this) {
               self::PENALTY_CREATED => 'New Penalty',
               self::PAYMENT_RECEIVED => 'Payment Received',
               self::PAYMENT_REMINDER => 'Payment Reminder',
               self::BALANCE_UPDATE => 'Balance Update',
               self::REPORT_GENERATED => 'Report Ready',
               self::SYSTEM_UPDATE => 'System Update',
           };
       }
       
       public function getIcon(): string
       {
           return match($this) {
               self::PENALTY_CREATED => 'exclamation-triangle',
               self::PAYMENT_RECEIVED => 'check-circle',
               self::PAYMENT_REMINDER => 'clock',
               self::BALANCE_UPDATE => 'calculator',
               self::REPORT_GENERATED => 'document-text',
               self::SYSTEM_UPDATE => 'cog',
           };
       }
       
       public function getPriority(): int
       {
           return match($this) {
               self::PENALTY_CREATED => 3,
               self::PAYMENT_REMINDER => 3,
               self::PAYMENT_RECEIVED => 2,
               self::BALANCE_UPDATE => 2,
               self::REPORT_GENERATED => 1,
               self::SYSTEM_UPDATE => 1,
           };
       }
   }
   ```

### New DTOs

1. **Report DTOs**
   
   **ReportInputDTO** - For POST/PUT requests
   ```php
   use App\Enum\ReportTypeEnum;
   use Symfony\Component\Validator\Constraints as Assert;

   readonly class ReportInputDTO
   {
       public function __construct(
           #[Assert\NotBlank]
           #[Assert\Length(max: 255)]
           public string $name,
           
           #[Assert\NotNull]
           public ReportTypeEnum $type,
           
           #[Assert\Type('array')]
           public array $parameters = [],
           
           public bool $scheduled = false,
           
           #[Assert\When(
               expression: 'this.scheduled === true',
               constraints: [new Assert\NotBlank()]
           )]
           public ?string $cronExpression = null,
       ) {}
   }
   ```
   
   **ReportOutputDTO** - For GET responses
   ```php
   use App\Enum\ReportTypeEnum;

   readonly class ReportOutputDTO
   {
       public function __construct(
           public string $id,
           public string $name,
           public ReportTypeEnum $type,
           public array $parameters,
           public ?array $result,
           public string $createdBy,
           public bool $scheduled,
           public ?string $cronExpression,
           public string $createdAt,
           public string $updatedAt,
       ) {}
       
       public static function fromEntity(Report $report): self
       {
           return new self(
               id: $report->getId()->toString(),
               name: $report->getName(),
               type: $report->getType(),
               parameters: $report->getParameters(),
               result: $report->getResult(),
               createdBy: $report->getCreatedBy()->getId()->toString(),
               scheduled: $report->isScheduled(),
               cronExpression: $report->getCronExpression(),
               createdAt: $report->getCreatedAt()->format('Y-m-d H:i:s'),
               updatedAt: $report->getUpdatedAt()->format('Y-m-d H:i:s'),
           );
       }
   }
   ```

2. **Notification DTOs**
   
   **NotificationInputDTO** - For POST requests
   ```php
   use App\Enum\NotificationTypeEnum;
   use Symfony\Component\Validator\Constraints as Assert;

   readonly class NotificationInputDTO
   {
       public function __construct(
           #[Assert\NotBlank]
           #[Assert\Uuid]
           public string $userId,
           
           #[Assert\NotNull]
           public NotificationTypeEnum $type,
           
           #[Assert\NotBlank]
           #[Assert\Length(max: 255)]
           public string $title,
           
           #[Assert\NotBlank]
           public string $message,
           
           #[Assert\Type('array')]
           public ?array $data = null,
       ) {}
   }
   ```
   
   **NotificationOutputDTO** - For GET responses
   ```php
   use App\Enum\NotificationTypeEnum;

   readonly class NotificationOutputDTO
   {
       public function __construct(
           public string $id,
           public NotificationTypeEnum $type,
           public string $title,
           public string $message,
           public ?array $data,
           public bool $read,
           public ?string $readAt,
           public string $createdAt,
       ) {}
       
       public static function fromEntity(Notification $notification): self
       {
           return new self(
               id: $notification->getId()->toString(),
               type: $notification->getType(),
               title: $notification->getTitle(),
               message: $notification->getMessage(),
               data: $notification->getData(),
               read: $notification->isRead(),
               readAt: $notification->getReadAt()?->format('Y-m-d H:i:s'),
               createdAt: $notification->getCreatedAt()->format('Y-m-d H:i:s'),
           );
       }
   }
   ```
   
   **NotificationPreferenceInputDTO** - For preference updates
   ```php
   use App\Enum\NotificationTypeEnum;
   use Symfony\Component\Validator\Constraints as Assert;

   readonly class NotificationPreferenceInputDTO
   {
       public function __construct(
           #[Assert\NotNull]
           public NotificationTypeEnum $notificationType,
           
           public bool $emailEnabled = true,
           public bool $inAppEnabled = true,
       ) {}
   }
   ```
   
   **NotificationPreferenceOutputDTO** - For preference responses
   ```php
   use App\Enum\NotificationTypeEnum;

   readonly class NotificationPreferenceOutputDTO
   {
       public function __construct(
           public string $id,
           public NotificationTypeEnum $notificationType,
           public bool $emailEnabled,
           public bool $inAppEnabled,
           public string $createdAt,
           public string $updatedAt,
       ) {}
       
       public static function fromEntity(NotificationPreference $preference): self
       {
           return new self(
               id: $preference->getId()->toString(),
               notificationType: $preference->getNotificationType(),
               emailEnabled: $preference->isEmailEnabled(),
               inAppEnabled: $preference->isInAppEnabled(),
               createdAt: $preference->getCreatedAt()->format('Y-m-d H:i:s'),
               updatedAt: $preference->getUpdatedAt()->format('Y-m-d H:i:s'),
           );
       }
   }
   ```

### New API Endpoints

1. **Reports**
   - `GET /api/reports` - Get all reports
   - `GET /api/reports/{id}` - Get report by ID
   - `POST /api/reports` - Create new report
   - `PUT /api/reports/{id}` - Update report
   - `DELETE /api/reports/{id}` - Delete report
   - `GET /api/reports/types` - Get available report types
   - `POST /api/reports/{id}/generate` - Generate report
   - `GET /api/reports/{id}/download` - Download report
   - `POST /api/reports/{id}/schedule` - Schedule report

2. **Dashboards**
   - `GET /api/dashboards/user` - Get user dashboard data
   - `GET /api/dashboards/admin` - Get admin dashboard data
   - `GET /api/dashboards/team/{teamId}` - Get team dashboard data
   - `GET /api/dashboards/financial-overview` - Get financial overview

3. **Notifications**
   - `GET /api/notifications` - Get user notifications
   - `GET /api/notifications/{id}` - Get notification by ID
   - `POST /api/notifications/{id}/read` - Mark notification as read
   - `POST /api/notifications/read-all` - Mark all notifications as read
   - `GET /api/notification-preferences` - Get notification preferences
   - `PUT /api/notification-preferences` - Update notification preferences

4. **Enhanced Penalty Endpoints**
   - `GET /api/penalties/search` - Search penalties with advanced filters
   - `GET /api/penalties/statistics` - Get penalty statistics
   - `GET /api/penalties/by-date-range` - Get penalties by date range
   - `GET /api/penalties/by-type` - Get penalties by type

5. **Export**
   - `POST /api/export/penalties` - Export penalties
   - `POST /api/export/reports/{id}` - Export specific report
   - `GET /api/export/formats` - Get available export formats

### API Platform Configuration

Modern API Platform 3.3+ configuration with enhanced features:

```yaml
# config/packages/api_platform.yaml
api_platform:
    title: 'Cashbox Management API'
    version: '1.1.0'
    description: 'Enhanced reporting and notification API'
    
    formats:
        jsonld: ['application/ld+json']
        json: ['application/json']
        html: ['text/html']
        csv: ['text/csv']
        
    docs_formats:
        jsonld: ['application/ld+json']
        json: ['application/json']
        html: ['text/html']
        
    # OpenAPI 3.1 support
    openapi:
        contact:
            name: 'Cashbox API Support'
            url: 'https://cashbox.example.com/support'
        license:
            name: 'MIT'
            
    # Enhanced pagination
    collection:
        pagination:
            enabled: true
            items_per_page: 30
            maximum_items_per_page: 100
            page_parameter_name: 'page'
            items_per_page_parameter_name: 'itemsPerPage'
            
    # Advanced filtering
    doctrine:
        enabled: true
    
    # Swagger UI customization
    swagger:
        versions: [3]
        api_keys:
            JWT:
                name: 'Authorization'
                type: 'header'
```

### Enhanced API Resource Configuration

```php
// Example: Report entity with API Platform 3.3+ features
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(
            paginationEnabled: true,
            paginationItemsPerPage: 30,
        ),
        new Post(
            denormalizationContext: ['groups' => ['report:create']],
            validationContext: ['groups' => ['Default', 'report:create']],
        ),
        new Put(
            denormalizationContext: ['groups' => ['report:update']],
        ),
        new Delete(),
        new Post(
            uriTemplate: '/reports/{id}/generate',
            controller: GenerateReportController::class,
            openapiContext: [
                'summary' => 'Generate report',
                'description' => 'Triggers asynchronous report generation',
            ]
        ),
    ],
    normalizationContext: ['groups' => ['report:read']],
    security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_MANAGER')",
    filters: [
        'report.search_filter',
        'report.date_filter',
        'report.order_filter',
    ]
)]
class Report
{
    // Entity implementation...
}
```

## Implementation Plan

### Phase 1: API Enhancement (Week 1)

1. Implement advanced filtering
2. Add sorting capabilities
3. Improve pagination
4. Add search functionality
5. Update API documentation

### Phase 2: Reporting System (Week 2)

1. Design report infrastructure
2. Implement report entity and service
3. Create report generators
4. Implement report export functionality
5. Develop scheduled reports

### Phase 3: Dashboard Development (Week 3)

1. Design user dashboard
2. Design admin dashboard
3. Implement dashboard data providers
4. Create visualization components
5. Implement dashboard filters

### Phase 4: Notification System (Week 4)

1. Design notification infrastructure
2. Implement notification entities
3. Create notification service
4. Develop email notification system
5. Implement notification preferences

### Phase 5: Export Functionality (Week 5)

1. Design export system
2. Implement export formats (PDF, Excel, CSV)
3. Create export templates
4. Develop batch export functionality
5. Add scheduled exports

### Phase 6: Testing and Finalization (Week 6)

1. **Comprehensive Pest PHP Test Suite**
   - Unit tests for all new services and value objects
   - Integration tests for domain events and repository interactions
   - Feature tests for complete user workflows
   - Performance tests for report generation

2. **Quality Assurance**
   - PHPStan level 8+ static analysis
   - Code coverage analysis (90%+ target)
   - Security vulnerability scanning
   - API documentation validation

3. **User Acceptance Testing**
   - Admin dashboard functionality
   - Report generation and export workflows
   - Notification system validation
   - Mobile responsiveness testing

4. **Performance and Load Testing**
   - Report generation with large datasets
   - Concurrent notification processing
   - Database query optimization
   - Memory usage profiling

5. **Documentation and Deployment**
   - API documentation updates with OpenAPI 3.1
   - User guides for new features
   - Admin configuration documentation
   - Production deployment with monitoring

## Dependencies

### Core Framework
- **Symfony 7.2+** - Latest LTS with PHP 8.4 support
- **API Platform 3.3+** - Modern REST/GraphQL API framework
- **Doctrine ORM 3.x** - Database abstraction with PHP 8+ attributes
- **PHP 8.4** - Latest PHP version with property hooks and enhanced enums

### Enhanced Libraries (Version 1.0.0 Foundation)
- **ramsey/uuid 4.x** - UUID v7 support for better database performance
- **beberlei/assert** - Runtime assertions for value objects
- **symfony/validator** - Enhanced validation with attributes
- **symfony/serializer** - Modern serialization with normalization

### Reporting and Export
- **spatie/browsershot** - Modern PDF generation using Chromium (replaces FPDF)
- **phpoffice/phpspreadsheet** - Excel/CSV generation and parsing
- **league/csv** - Fast CSV processing
- **twig/twig** - Template engine for report layouts

### Visualization and UI
- **chart.js 4.x** - Modern charting library
- **stimulus/stimulus** - Lightweight JS framework
- **tailwindcss** - Utility-first CSS framework for admin dashboards

### Communication and Processing
- **symfony/mailer** - Email notifications with modern transport
- **symfony/notifier** - Multi-channel notifications (email, SMS, Slack)
- **symfony/messenger** - Asynchronous message processing
- **symfony/scheduler** - Cron-like job scheduling

### Development and Quality
- **pestphp/pest** - Modern testing framework (from version 1.0.0)
- **phpstan/phpstan** - Static analysis at level 8+
- **rector/rector** - Automated code modernization
- **laravel/pint** - Modern code styling (alternative to PHP CS Fixer)

## Testing Strategy

### Framework and Approach
- **Pest PHP** - Modern testing framework with expressive syntax (established in version 1.0.0)
- **Test-Driven Development** - Write tests before implementation
- **Domain-Driven Testing** - Test business logic and domain events
- **90%+ Code Coverage** - Maintain high test coverage standards

### Test Types

1. **Unit Testing with Pest**
   ```php
   // Example: Report generator tests
   it('generates financial report with correct data')
       ->expect(fn() => $reportGenerator->generate(ReportTypeEnum::FINANCIAL, $params))
       ->toBeInstanceOf(Report::class)
       ->and(fn($report) => $report->getResult())
       ->toHaveKey('totalPenalties')
       ->toHaveKey('totalPayments');
   
   // Test notification services
   it('sends notification when penalty is created')
       ->expect(fn() => $notificationService->notify($user, NotificationTypeEnum::PENALTY_CREATED, $data))
       ->not->toThrow(Exception::class);
   ```

2. **Integration Testing**
   ```php
   // Test domain events integration
   it('triggers report generated event when report is completed')
       ->expect(fn() => $report->generate($results))
       ->and(fn() => $report->getEvents())
       ->toContain(ReportGeneratedEvent::class);
   
   // Test dashboard data providers
   test('dashboard aggregates user penalties correctly')
       ->expect($dashboardService->getUserDashboard($user))
       ->toHaveKey('totalOutstanding')
       ->toHaveKey('recentPenalties');
   ```

3. **API Testing with Pest**
   ```php
   // Test enhanced API endpoints
   it('filters penalties by date range correctly')
       ->get('/api/penalties?dateFrom=2025-01-01&dateTo=2025-12-31')
       ->assertOk()
       ->assertJsonStructure(['data' => [['id', 'amount', 'createdAt']]]);
   
   // Test notification endpoints
   it('marks notification as read')
       ->post("/api/notifications/{$notification->getId()}/read")
       ->assertOk()
       ->and(fn() => $notification->fresh())
       ->toBeTrue(fn($n) => $n->isRead());
   ```

4. **Feature Testing**
   ```php
   // Test complete report generation workflow
   it('generates and exports financial report end-to-end')
       ->actingAs($admin)
       ->post('/api/reports', $reportData)
       ->assertCreated()
       ->and(fn($response) => $response->json('id'))
       ->and(fn($id) => $this->post("/api/reports/{$id}/generate"))
       ->assertOk();
   ```

5. **Performance Testing**
   ```php
   // Test report generation performance
   it('generates large reports within acceptable time')
       ->expect(fn() => $reportGenerator->generate(ReportTypeEnum::ANNUAL, $params))
       ->toExecuteWithinMilliseconds(5000);
   ```

### Test Organization
```
tests/
├── Feature/           # End-to-end feature tests
│   ├── ReportingTest.php
│   ├── NotificationTest.php
│   └── DashboardTest.php
├── Unit/              # Isolated unit tests
│   ├── Services/
│   ├── ValueObjects/
│   └── Enums/
├── Integration/       # Component integration tests
│   ├── Repositories/
│   └── EventHandlers/
└── Datasets/          # Shared test data
    ├── Users.php
    ├── Reports.php
    └── Notifications.php
```

## Acceptance Criteria

- Advanced filtering and sorting work correctly
- Reports generate accurate data
- Export functionality produces correct formats
- Dashboards display accurate information
- Notifications are delivered properly
- All new features are properly documented
- All tests pass successfully

## Risks and Mitigation

1. **Risk**: Performance issues with large data sets in reports
   **Mitigation**: 
   - Implement asynchronous report generation using Symfony Messenger
   - Use database query optimization with proper indexing
   - Implement result caching with Redis/Memcached
   - Add pagination for large result sets

2. **Risk**: Email notification delivery issues
   **Mitigation**: 
   - Implement retry mechanism with exponential backoff
   - Use Symfony Notifier for multi-channel delivery
   - Add comprehensive notification logging and monitoring
   - Implement notification queue with dead letter handling

3. **Risk**: Report generation timeouts
   **Mitigation**: 
   - Use Symfony Messenger for asynchronous processing
   - Implement progress tracking for long-running reports
   - Add timeout configuration per report type
   - Use domain events for status updates

4. **Risk**: Memory usage with complex reports
   **Mitigation**:
   - Stream large datasets instead of loading into memory
   - Use generators for data processing
   - Implement memory monitoring and limits
   - Add garbage collection optimization

5. **Risk**: User adoption of new features
   **Mitigation**: 
   - Provide interactive API documentation with examples
   - Create comprehensive user guides and tutorials
   - Implement progressive feature rollout
   - Collect user feedback through in-app notifications

6. **Risk**: Domain event handling failures
   **Mitigation**:
   - Implement event store for replay capability
   - Add event handling monitoring and alerting
   - Use event versioning for backward compatibility
   - Implement circuit breaker pattern for external services

## Post-Release Activities

1. Monitor system performance with new features
2. Collect user feedback on reporting and dashboard functionality
3. Address any critical bugs
4. Plan for version 1.2.0
5. Conduct user training sessions

## Documentation

- Updated API documentation
- User guide for reporting and dashboards
- Administrator guide for system monitoring
- Export format specifications
- Notification configuration guide