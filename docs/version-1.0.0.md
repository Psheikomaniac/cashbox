# Version 1.0.0: Foundation and Penalties Management (Modernized)

## Overview

Version 1.0.0 focuses on establishing the foundation of Cashbox, with particular emphasis on the core functionality for tracking and managing penalties and drinks. This version implements essential components using modern PHP 8.4 features and current best practices.

## Release Timeline

- **Development Start**: June 1, 2025
- **Alpha Release**: June 15, 2025
- **Beta Release**: June 30, 2025
- **Production Release**: July 15, 2025

## Scope

### Core Components

1. **System Architecture**
    - Establish project structure using Symfony 7.2
    - Configure API Platform 3.3+
    - Set up development environment with Docker
    - Configure OpenTelemetry for observability
    - Set up testing framework with PHPUnit 11 and Pest PHP
    - Implement event-driven architecture with Symfony Messenger

2. **Database Structure**
    - Implement the initial database schema
    - Set up Doctrine ORM 3.x with attributes
    - Configure migrations system
    - Implement UUID v7 as primary keys (more efficient than v4)
    - Set up Gedmo for timestamps
    - Add soft deletes for data recovery

3. **Authentication and Authorization**
    - Implement JWT authentication with refresh tokens
    - Add OAuth2/OpenID Connect support
    - Set up user roles and permissions with attributes
    - Configure security with rate limiting
    - Implement API key authentication for service-to-service

4. **Team and User Management**
    - Create Team entity with domain events
    - Create User entity with value objects
    - Implement TeamUser relationship
    - Set up user roles within teams
    - Add audit logging for changes

5. **Penalties and Drinks Management**
    - Create PenaltyType entity with state machine
    - Create Penalty entity with aggregates
    - Implement API endpoints with OpenAPI 3.1
    - Set up business logic with domain services
    - Add event sourcing for penalty history

6. **Import Functionality**
    - Develop CSV/Excel import with streaming
    - Implement async processing with Messenger
    - Create data transformation with DTOs
    - Set up error handling with detailed reports
    - Add import history tracking

## Technical Requirements

### Modern PHP 8.4 Features

1. **Property Hooks**
   ```php
   class Penalty
   {
       private int $amountInCents;

       public int $amount {
           get => $this->amountInCents / 100;
           set => $this->amountInCents = $value * 100;
       }
   }
   ```

2. **Asymmetric Visibility**
   ```php
   class User
   {
       public private(set) UuidInterface $id;
       public private(set) string $email;
   }
   ```

### Enhanced Enums

1. **UserRoleEnum with Interface**
   ```php
   interface RoleInterface
   {
       public function getLabel(): string;
       public function getPermissions(): array;
       public function hasPermission(string $permission): bool;
       public function getPriority(): int;
   }

   enum UserRoleEnum: string implements RoleInterface
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
               self::ADMIN => Permission::all(),
               self::MANAGER => [
                   Permission::TEAM_VIEW,
                   Permission::USER_VIEW,
                   Permission::PENALTY_EDIT,
                   Permission::REPORT_VIEW,
               ],
               self::TREASURER => [
                   Permission::TEAM_VIEW,
                   Permission::USER_VIEW,
                   Permission::PENALTY_VIEW,
                   Permission::CONTRIBUTION_EDIT,
                   Permission::PAYMENT_EDIT,
                   Permission::REPORT_VIEW,
               ],
               self::MEMBER => [
                   Permission::TEAM_VIEW,
                   Permission::USER_VIEW,
                   Permission::PENALTY_VIEW,
                   Permission::CONTRIBUTION_VIEW,
               ],
           };
       }

       public function hasPermission(string $permission): bool
       {
           return in_array($permission, $this->getPermissions(), true);
       }

       public function getPriority(): int
       {
           return match($this) {
               self::ADMIN => 100,
               self::MANAGER => 75,
               self::TREASURER => 50,
               self::MEMBER => 10,
           };
       }

       public static function fromPriority(int $priority): self
       {
           return match(true) {
               $priority >= 100 => self::ADMIN,
               $priority >= 75 => self::MANAGER,
               $priority >= 50 => self::TREASURER,
               default => self::MEMBER,
           };
       }
   }
   ```

2. **PenaltyTypeEnum**
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

       public function getDefaultAmount(): int
       {
           return match($this) {
               self::DRINK => 150, // 1.50 EUR in cents
               self::LATE_ARRIVAL => 500,
               self::MISSED_TRAINING => 1500,
               self::CUSTOM => 0,
           };
       }
   }
   ```

3. **CurrencyEnum**
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

4. **PaymentTypeEnum**
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

### Modern Entity Design

1. **Team Entity with Domain Events**
   ```php
   #[ORM\Entity(repositoryClass: TeamRepository::class)]
   #[ORM\Table(name: 'teams')]
   #[ORM\HasLifecycleCallbacks]
   #[Gedmo\SoftDeleteable(fieldName: 'deletedAt')]
   class Team implements AggregateRootInterface
   {
       use EventRecorderTrait;

       #[ORM\Id]
       #[ORM\Column(type: 'uuid', unique: true)]
       public private(set) UuidInterface $id;

       #[ORM\Column(length: 255)]
       #[Assert\NotBlank]
       #[Assert\Length(min: 3, max: 255)]
       public private(set) string $name;

       #[ORM\Column(length: 255, unique: true)]
       public private(set) string $externalId;

       #[ORM\Column(type: 'boolean')]
       public private(set) bool $active = true;

       #[ORM\Column(type: 'json')]
       private array $metadata = [];

       #[ORM\Column(type: 'datetime_immutable', nullable: true)]
       private ?\DateTimeImmutable $deletedAt = null;

       #[ORM\Column(type: 'datetime_immutable')]
       #[Gedmo\Timestampable(on: 'create')]
       public private(set) \DateTimeImmutable $createdAt;

       #[ORM\Column(type: 'datetime_immutable')]
       #[Gedmo\Timestampable(on: 'update')]
       public private(set) \DateTimeImmutable $updatedAt;

       #[ORM\OneToMany(mappedBy: 'team', targetEntity: TeamUser::class)]
       private Collection $teamUsers;

       private function __construct(
           UuidInterface $id,
           string $name,
           string $externalId
       ) {
           $this->id = $id;
           $this->name = $name;
           $this->externalId = $externalId;
           $this->teamUsers = new ArrayCollection();

           $this->record(new TeamCreatedEvent($id, $name, $externalId));
       }

       public static function create(
           string $name,
           string $externalId
       ): self {
           Assert::notEmpty($name);
           Assert::notEmpty($externalId);

           return new self(
               Uuid::v7(),
               $name,
               $externalId
           );
       }

       public function rename(string $newName): void
       {
           Assert::notEmpty($newName);
           Assert::notEq($this->name, $newName);

           $oldName = $this->name;
           $this->name = $newName;

           $this->record(new TeamRenamedEvent($this->id, $oldName, $newName));
       }

       public function deactivate(): void
       {
           if (!$this->active) {
               throw new DomainException('Team is already inactive');
           }

           $this->active = false;
           $this->record(new TeamDeactivatedEvent($this->id));
       }

       public function activate(): void
       {
           if ($this->active) {
               throw new DomainException('Team is already active');
           }

           $this->active = true;
           $this->record(new TeamActivatedEvent($this->id));
       }

       public function addMetadata(string $key, mixed $value): void
       {
           $this->metadata[$key] = $value;
       }

       public function getMetadata(string $key): mixed
       {
           return $this->metadata[$key] ?? null;
       }
   }
   ```

2. **User Entity with Value Objects**
   ```php
   #[ORM\Entity(repositoryClass: UserRepository::class)]
   #[ORM\Table(name: 'users')]
   #[ORM\HasLifecycleCallbacks]
   #[UniqueEntity('email')]
   class User
   {
       #[ORM\Id]
       #[ORM\Column(type: 'uuid', unique: true)]
       public private(set) UuidInterface $id;

       #[ORM\Embedded(class: PersonName::class)]
       public private(set) PersonName $name;

       #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
       #[Assert\Email]
       public private(set) ?Email $email = null;

       #[ORM\Column(type: 'string', length: 50, nullable: true)]
       public private(set) ?PhoneNumber $phoneNumber = null;

       #[ORM\Column(type: 'boolean')]
       public private(set) bool $active = true;

       #[ORM\Column(type: 'json')]
       private array $preferences = [];

       #[ORM\Column(type: 'datetime_immutable')]
       #[Gedmo\Timestampable(on: 'create')]
       public private(set) \DateTimeImmutable $createdAt;

       #[ORM\Column(type: 'datetime_immutable')]
       #[Gedmo\Timestampable(on: 'update')]
       public private(set) \DateTimeImmutable $updatedAt;

       #[ORM\OneToMany(mappedBy: 'user', targetEntity: TeamUser::class)]
       private Collection $teamUsers;

       public function __construct(
           PersonName $name,
           ?Email $email = null,
           ?PhoneNumber $phoneNumber = null
       ) {
           $this->id = Uuid::v7();
           $this->name = $name;
           $this->email = $email;
           $this->phoneNumber = $phoneNumber;
           $this->teamUsers = new ArrayCollection();
       }

       public function updateProfile(
           PersonName $name,
           ?Email $email = null,
           ?PhoneNumber $phoneNumber = null
       ): void {
           $this->name = $name;
           $this->email = $email;
           $this->phoneNumber = $phoneNumber;
       }

       public function setPreference(string $key, mixed $value): void
       {
           $this->preferences[$key] = $value;
       }

       public function getPreference(string $key, mixed $default = null): mixed
       {
           return $this->preferences[$key] ?? $default;
       }
   }
   ```

3. **TeamUser Entity**
   ```php
   #[ORM\Entity(repositoryClass: TeamUserRepository::class)]
   #[ORM\Table(name: 'team_users')]
   #[ORM\UniqueConstraint(columns: ['team_id', 'user_id'])]
   class TeamUser
   {
       #[ORM\Id]
       #[ORM\Column(type: 'uuid', unique: true)]
       public private(set) UuidInterface $id;

       #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'teamUsers')]
       #[ORM\JoinColumn(nullable: false)]
       private Team $team;

       #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'teamUsers')]
       #[ORM\JoinColumn(nullable: false)]
       private User $user;

       #[ORM\Column(type: 'json')]
       private array $roles = [];

       #[ORM\Column(type: 'boolean')]
       private bool $active = true;

       #[ORM\Column(type: 'datetime_immutable')]
       #[Gedmo\Timestampable(on: 'create')]
       private \DateTimeImmutable $createdAt;

       #[ORM\Column(type: 'datetime_immutable')]
       #[Gedmo\Timestampable(on: 'update')]
       private \DateTimeImmutable $updatedAt;

       public function __construct(Team $team, User $user, array $roles = [])
       {
           $this->id = Uuid::v7();
           $this->team = $team;
           $this->user = $user;
           $this->setRoles($roles);
       }

       /**
        * @return UserRoleEnum[]
        */
       public function getRoles(): array
       {
           return array_map(
               fn (string $role) => UserRoleEnum::from($role),
               $this->roles
           );
       }

       /**
        * @param UserRoleEnum[] $roles
        */
       public function setRoles(array $roles): self
       {
           $this->roles = array_map(
               fn (UserRoleEnum $role) => $role->value,
               $roles
           );

           return $this;
       }

       public function addRole(UserRoleEnum $role): self
       {
           if (!in_array($role->value, $this->roles, true)) {
               $this->roles[] = $role->value;
           }

           return $this;
       }

       public function removeRole(UserRoleEnum $role): self
       {
           $key = array_search($role->value, $this->roles, true);
           if ($key !== false) {
               unset($this->roles[$key]);
               $this->roles = array_values($this->roles);
           }

           return $this;
       }

       public function hasRole(UserRoleEnum $role): bool
       {
           return in_array($role->value, $this->roles, true);
       }

       public function hasPermission(string $permission): bool
       {
           foreach ($this->getRoles() as $role) {
               if ($role->hasPermission($permission)) {
                   return true;
               }
           }

           return false;
       }

       // Getters and setters
   }
   ```

4. **PenaltyType Entity**
   ```php
   #[ORM\Entity(repositoryClass: PenaltyTypeRepository::class)]
   #[ORM\Table(name: 'penalty_types')]
   class PenaltyType
   {
       #[ORM\Id]
       #[ORM\Column(type: 'uuid', unique: true)]
       public private(set) UuidInterface $id;

       #[ORM\Column(length: 255)]
       private string $name;

       #[ORM\Column(type: 'text', nullable: true)]
       private ?string $description = null;

       #[ORM\Column(type: 'string', length: 30)]
       private string $type;

       #[ORM\Column(type: 'integer')]
       private int $defaultAmount;

       #[ORM\Column(type: 'boolean')]
       private bool $active = true;

       #[ORM\Column(type: 'datetime_immutable')]
       #[Gedmo\Timestampable(on: 'create')]
       private \DateTimeImmutable $createdAt;

       #[ORM\Column(type: 'datetime_immutable')]
       #[Gedmo\Timestampable(on: 'update')]
       private \DateTimeImmutable $updatedAt;

       public function __construct(
           string $name,
           PenaltyTypeEnum $type,
           ?string $description = null
       ) {
           $this->id = Uuid::v7();
           $this->name = $name;
           $this->type = $type->value;
           $this->defaultAmount = $type->getDefaultAmount();
           $this->description = $description;
       }

       public function getType(): PenaltyTypeEnum
       {
           return PenaltyTypeEnum::from($this->type);
       }

       public function setType(PenaltyTypeEnum $type): self
       {
           $this->type = $type->value;
           $this->defaultAmount = $type->getDefaultAmount();

           return $this;
       }

       public function isDrink(): bool
       {
           return $this->getType()->isDrink();
       }

       // Other getters and setters
   }
   ```

5. **Penalty Entity**
   ```php
   #[ORM\Entity(repositoryClass: PenaltyRepository::class)]
   #[ORM\Table(name: 'penalties')]
   #[ORM\HasLifecycleCallbacks]
   class Penalty implements AggregateRootInterface
   {
       use EventRecorderTrait;

       #[ORM\Id]
       #[ORM\Column(type: 'uuid', unique: true)]
       public private(set) UuidInterface $id;

       #[ORM\ManyToOne(targetEntity: TeamUser::class)]
       #[ORM\JoinColumn(nullable: false)]
       private TeamUser $teamUser;

       #[ORM\ManyToOne(targetEntity: PenaltyType::class)]
       #[ORM\JoinColumn(nullable: false)]
       private PenaltyType $type;

       #[ORM\Column(length: 255)]
       private string $reason;

       #[ORM\Column(type: 'integer')]
       private int $amount;

       #[ORM\Column(type: 'string', length: 3)]
       private string $currency = CurrencyEnum::EUR->value;

       #[ORM\Column(type: 'boolean')]
       private bool $archived = false;

       #[ORM\Column(type: 'datetime_immutable', nullable: true)]
       private ?\DateTimeImmutable $paidAt = null;

       #[ORM\Column(type: 'datetime_immutable')]
       #[Gedmo\Timestampable(on: 'create')]
       private \DateTimeImmutable $createdAt;

       #[ORM\Column(type: 'datetime_immutable')]
       #[Gedmo\Timestampable(on: 'update')]
       private \DateTimeImmutable $updatedAt;

       public function __construct(
           TeamUser $teamUser,
           PenaltyType $type,
           string $reason,
           int $amount,
           CurrencyEnum $currency = CurrencyEnum::EUR
       ) {
           $this->id = Uuid::v7();
           $this->teamUser = $teamUser;
           $this->type = $type;
           $this->reason = $reason;
           $this->amount = $amount;
           $this->currency = $currency->value;

           $this->record(new PenaltyCreatedEvent(
               $this->id,
               $teamUser->getUser()->id,
               $teamUser->getTeam()->id,
               $reason,
               new Money($amount, $currency)
           ));
       }

       public function pay(\DateTimeImmutable $paidAt = null): void
       {
           if ($this->paidAt !== null) {
               throw new DomainException('Penalty is already paid');
           }

           $this->paidAt = $paidAt ?? new \DateTimeImmutable();

           $this->record(new PenaltyPaidEvent(
               $this->id,
               $this->paidAt
           ));
       }

       public function archive(): void
       {
           if ($this->archived) {
               throw new DomainException('Penalty is already archived');
           }

           $this->archived = true;

           $this->record(new PenaltyArchivedEvent($this->id));
       }

       public function getCurrency(): CurrencyEnum
       {
           return CurrencyEnum::from($this->currency);
       }

       public function setCurrency(CurrencyEnum $currency): self
       {
           $this->currency = $currency->value;

           return $this;
       }

       public function getFormattedAmount(): string
       {
           return $this->getCurrency()->formatAmount($this->amount);
       }

       public function isPaid(): bool
       {
           return $this->paidAt !== null;
       }

       // Other getters and setters
   }
   ```

6. **Payment Entity**
   ```php
   #[ORM\Entity(repositoryClass: PaymentRepository::class)]
   #[ORM\Table(name: 'payments')]
   class Payment
   {
       #[ORM\Id]
       #[ORM\Column(type: 'uuid', unique: true)]
       public private(set) UuidInterface $id;

       #[ORM\ManyToOne(targetEntity: TeamUser::class)]
       #[ORM\JoinColumn(nullable: false)]
       private TeamUser $teamUser;

       #[ORM\Column(type: 'integer')]
       private int $amount;

       #[ORM\Column(type: 'string', length: 3)]
       private string $currency = CurrencyEnum::EUR->value;

       #[ORM\Column(type: 'string', length: 30)]
       private string $type = PaymentTypeEnum::CASH->value;

       #[ORM\Column(type: 'string', length: 255, nullable: true)]
       private ?string $description = null;

       #[ORM\Column(type: 'string', length: 255, nullable: true)]
       private ?string $reference = null;

       #[ORM\Column(type: 'datetime_immutable')]
       #[Gedmo\Timestampable(on: 'create')]
       private \DateTimeImmutable $createdAt;

       #[ORM\Column(type: 'datetime_immutable')]
       #[Gedmo\Timestampable(on: 'update')]
       private \DateTimeImmutable $updatedAt;

       public function __construct(
           TeamUser $teamUser,
           int $amount,
           CurrencyEnum $currency,
           PaymentTypeEnum $type,
           ?string $description = null
       ) {
           $this->id = Uuid::v7();
           $this->teamUser = $teamUser;
           $this->amount = $amount;
           $this->currency = $currency->value;
           $this->type = $type->value;
           $this->description = $description;

           if ($type->requiresReference() && $this->reference === null) {
               throw new DomainException('Payment type requires a reference');
           }
       }

       public function getCurrency(): CurrencyEnum
       {
           return CurrencyEnum::from($this->currency);
       }

       public function setCurrency(CurrencyEnum $currency): self
       {
           $this->currency = $currency->value;

           return $this;
       }

       public function getType(): PaymentTypeEnum
       {
           return PaymentTypeEnum::from($this->type);
       }

       public function setType(PaymentTypeEnum $type): self
       {
           $this->type = $type->value;

           return $this;
       }

       public function getFormattedAmount(): string
       {
           return $this->getCurrency()->formatAmount($this->amount);
       }

       public function setReference(?string $reference): self
       {
           if ($this->getType()->requiresReference() && $reference === null) {
               throw new DomainException('Payment type requires a reference');
           }

           $this->reference = $reference;

           return $this;
       }

       // Other getters and setters
   }
   ```

### Value Objects

1. **PersonName Value Object**
   ```php
   #[ORM\Embeddable]
   final class PersonName
   {
       #[ORM\Column(length: 255)]
       #[Assert\NotBlank]
       #[Assert\Length(min: 2, max: 255)]
       private string $firstName;

       #[ORM\Column(length: 255)]
       #[Assert\NotBlank]
       #[Assert\Length(min: 2, max: 255)]
       private string $lastName;

       public function __construct(string $firstName, string $lastName)
       {
           Assert::notEmpty($firstName);
           Assert::notEmpty($lastName);

           $this->firstName = trim($firstName);
           $this->lastName = trim($lastName);
       }

       public function getFullName(): string
       {
           return sprintf('%s %s', $this->firstName, $this->lastName);
       }

       public function getInitials(): string
       {
           return strtoupper(
               mb_substr($this->firstName, 0, 1) .
               mb_substr($this->lastName, 0, 1)
           );
       }

       public function equals(self $other): bool
       {
           return $this->firstName === $other->firstName
               && $this->lastName === $other->lastName;
       }
   }
   ```

2. **Email Value Object**
   ```php
   final class Email implements \Stringable
   {
       private string $value;

       public function __construct(string $email)
       {
           $email = trim(strtolower($email));

           if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
               throw new InvalidArgumentException('Invalid email address');
           }

           $this->value = $email;
       }

       public function getValue(): string
       {
           return $this->value;
       }

       public function getDomain(): string
       {
           return substr($this->value, strrpos($this->value, '@') + 1);
       }

       public function __toString(): string
       {
           return $this->value;
       }
   }
   ```

3. **PhoneNumber Value Object**
   ```php
   final class PhoneNumber implements \Stringable
   {
       private string $value;

       public function __construct(string $phoneNumber)
       {
           $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);

           if (strlen($phoneNumber) < 7 || strlen($phoneNumber) > 20) {
               throw new InvalidArgumentException('Invalid phone number');
           }

           $this->value = $phoneNumber;
       }

       public function getValue(): string
       {
           return $this->value;
       }

       public function getFormatted(): string
       {
           // Simple formatting, can be enhanced with libphonenumber
           if (str_starts_with($this->value, '+49')) {
               return preg_replace('/(\+49)(\d{3})(\d+)/', '$1 $2 $3', $this->value);
           }

           return $this->value;
       }

       public function __toString(): string
       {
           return $this->getFormatted();
       }
   }
   ```

4. **Money Value Object**
   ```php
   final class Money
   {
       private int $amount;
       private CurrencyEnum $currency;

       public function __construct(int $amount, CurrencyEnum $currency)
       {
           if ($amount < 0) {
               throw new InvalidArgumentException('Amount cannot be negative');
           }

           $this->amount = $amount;
           $this->currency = $currency;
       }

       public function getAmount(): int
       {
           return $this->amount;
       }

       public function getCurrency(): CurrencyEnum
       {
           return $this->currency;
       }

       public function add(Money $other): self
       {
           if (!$this->currency->equals($other->currency)) {
               throw new DomainException('Cannot add money with different currencies');
           }

           return new self($this->amount + $other->amount, $this->currency);
       }

       public function subtract(Money $other): self
       {
           if (!$this->currency->equals($other->currency)) {
               throw new DomainException('Cannot subtract money with different currencies');
           }

           if ($this->amount < $other->amount) {
               throw new DomainException('Result would be negative');
           }

           return new self($this->amount - $other->amount, $this->currency);
       }

       public function multiply(float $factor): self
       {
           return new self((int) round($this->amount * $factor), $this->currency);
       }

       public function format(): string
       {
           return $this->currency->formatAmount($this->amount);
       }

       public function equals(Money $other): bool
       {
           return $this->amount === $other->amount
               && $this->currency === $other->currency;
       }
   }
   ```

### Modern DTOs with Validation

1. **Team DTOs**
   ```php
   final readonly class CreateTeamDTO
   {
       public function __construct(
           #[Assert\NotBlank]
           #[Assert\Length(min: 3, max: 255)]
           public string $name,

           #[Assert\NotBlank]
           #[Assert\Length(min: 3, max: 255)]
           public string $externalId,

           public bool $active = true,

           #[Assert\Valid]
           public ?array $metadata = null
       ) {}
   }

   final readonly class UpdateTeamDTO
   {
       public function __construct(
           #[Assert\Length(min: 3, max: 255)]
           public ?string $name = null,

           public ?bool $active = null,

           #[Assert\Valid]
           public ?array $metadata = null
       ) {}
   }

   final readonly class TeamResponseDTO
   {
       public function __construct(
           public string $id,
           public string $name,
           public string $externalId,
           public bool $active,
           public array $metadata,
           public string $createdAt,
           public string $updatedAt
       ) {}

       public static function fromEntity(Team $team): self
       {
           return new self(
               id: $team->id->toString(),
               name: $team->name,
               externalId: $team->externalId,
               active: $team->active,
               metadata: $team->getAllMetadata(),
               createdAt: $team->createdAt->format(\DateTimeInterface::ATOM),
               updatedAt: $team->updatedAt->format(\DateTimeInterface::ATOM)
           );
       }
   }
   ```

2. **User DTOs**
   ```php
   final readonly class CreateUserDTO
   {
       public function __construct(
           #[Assert\NotBlank]
           #[Assert\Length(min: 2, max: 255)]
           public string $firstName,

           #[Assert\NotBlank]
           #[Assert\Length(min: 2, max: 255)]
           public string $lastName,

           #[Assert\Email]
           public ?string $email = null,

           #[Assert\Length(min: 7, max: 20)]
           public ?string $phoneNumber = null,

           public bool $active = true
       ) {}
   }

   final readonly class UserResponseDTO
   {
       public function __construct(
           public string $id,
           public string $firstName,
           public string $lastName,
           public string $fullName,
           public ?string $email,
           public ?string $phoneNumber,
           public bool $active,
           public string $createdAt,
           public string $updatedAt
       ) {}

       public static function fromEntity(User $user): self
       {
           return new self(
               id: $user->id->toString(),
               firstName: $user->name->getFirstName(),
               lastName: $user->name->getLastName(),
               fullName: $user->name->getFullName(),
               email: $user->email?->getValue(),
               phoneNumber: $user->phoneNumber?->getValue(),
               active: $user->active,
               createdAt: $user->createdAt->format(\DateTimeInterface::ATOM),
               updatedAt: $user->updatedAt->format(\DateTimeInterface::ATOM)
           );
       }
   }
   ```

3. **Penalty DTOs**
   ```php
   final readonly class CreatePenaltyDTO
   {
       public function __construct(
           #[Assert\NotBlank]
           #[Assert\Uuid]
           public string $teamUserId,

           #[Assert\NotBlank]
           #[Assert\Uuid]
           public string $typeId,

           #[Assert\NotBlank]
           #[Assert\Length(min: 3, max: 255)]
           public string $reason,

           #[Assert\Positive]
           public int $amount,

           #[Assert\Choice(callback: [CurrencyEnum::class, 'values'])]
           public string $currency = CurrencyEnum::EUR->value
       ) {}
   }

   final readonly class PenaltyResponseDTO
   {
       public function __construct(
           public string $id,
           public string $userId,
           public string $userName,
           public string $teamId,
           public string $teamName,
           public string $typeId,
           public string $typeName,
           public string $reason,
           public int $amount,
           public array $currency,
           public string $formattedAmount,
           public bool $archived,
           public bool $paid,
           public ?string $paidAt,
           public string $createdAt,
           public string $updatedAt
       ) {}

       public static function fromEntity(Penalty $penalty): self
       {
           return new self(
               id: $penalty->id->toString(),
               userId: $penalty->getTeamUser()->getUser()->id->toString(),
               userName: $penalty->getTeamUser()->getUser()->name->getFullName(),
               teamId: $penalty->getTeamUser()->getTeam()->id->toString(),
               teamName: $penalty->getTeamUser()->getTeam()->name,
               typeId: $penalty->getType()->id->toString(),
               typeName: $penalty->getType()->getName(),
               reason: $penalty->getReason(),
               amount: $penalty->getAmount(),
               currency: [
                   'value' => $penalty->getCurrency()->value,
                   'symbol' => $penalty->getCurrency()->getSymbol(),
               ],
               formattedAmount: $penalty->getFormattedAmount(),
               archived: $penalty->isArchived(),
               paid: $penalty->isPaid(),
               paidAt: $penalty->getPaidAt()?->format(\DateTimeInterface::ATOM),
               createdAt: $penalty->getCreatedAt()->format(\DateTimeInterface::ATOM),
               updatedAt: $penalty->getUpdatedAt()->format(\DateTimeInterface::ATOM)
           );
       }
   }
   ```

### API Platform 3.3 Configuration

1. **Modern API Resource**
   ```php
   #[ApiResource(
       shortName: 'Team',
       operations: [
           new GetCollection(
               uriTemplate: '/teams',
               security: "is_granted('ROLE_USER')",
               name: 'get_teams'
           ),
           new Get(
               uriTemplate: '/teams/{id}',
               requirements: ['id' => Requirement::UUID_V7],
               security: "is_granted('TEAM_VIEW', object)"
           ),
           new Post(
               uriTemplate: '/teams',
               security: "is_granted('ROLE_ADMIN')",
               input: CreateTeamDTO::class,
               processor: CreateTeamProcessor::class
           ),
           new Put(
               uriTemplate: '/teams/{id}',
               requirements: ['id' => Requirement::UUID_V7],
               security: "is_granted('TEAM_EDIT', object)",
               input: UpdateTeamDTO::class
           ),
           new Delete(
               uriTemplate: '/teams/{id}',
               requirements: ['id' => Requirement::UUID_V7],
               security: "is_granted('TEAM_DELETE', object)"
           )
       ],
       paginationItemsPerPage: 20,
       formats: ['jsonld', 'json', 'csv'],
       filters: [
           'teams.search_filter',
           'teams.order_filter',
           'teams.date_filter'
       ]
   )]
   #[ApiFilter(SearchFilter::class, properties: [
       'name' => 'partial',
       'externalId' => 'exact',
       'active' => 'exact'
   ])]
   #[ApiFilter(OrderFilter::class, properties: [
       'name',
       'createdAt',
       'updatedAt'
   ])]
   #[ApiFilter(DateFilter::class, properties: [
       'createdAt',
       'updatedAt'
   ])]
   class Team
   {
       // Entity implementation
   }
   ```

### Event-Driven Architecture

1. **Domain Events**
   ```php
   final readonly class TeamCreatedEvent
   {
       public function __construct(
           public UuidInterface $teamId,
           public string $name,
           public string $externalId,
           public \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
       ) {}
   }

   final readonly class TeamRenamedEvent
   {
       public function __construct(
           public UuidInterface $teamId,
           public string $oldName,
           public string $newName,
           public \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
       ) {}
   }

   final readonly class PenaltyCreatedEvent
   {
       public function __construct(
           public UuidInterface $penaltyId,
           public UuidInterface $userId,
           public UuidInterface $teamId,
           public string $reason,
           public Money $amount,
           public \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
       ) {}
   }

   final readonly class PenaltyPaidEvent
   {
       public function __construct(
           public UuidInterface $penaltyId,
           public \DateTimeImmutable $paidAt,
           public \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
       ) {}
   }
   ```

2. **Event Handlers**
   ```php
   #[AsMessageHandler]
   final class TeamEventHandler
   {
       public function __construct(
           private readonly NotificationService $notificationService,
           private readonly AuditLogger $auditLogger,
           private readonly MetricsCollector $metrics
       ) {}

       public function __invoke(TeamCreatedEvent $event): void
       {
           $this->auditLogger->log('team.created', [
               'teamId' => $event->teamId->toString(),
               'name' => $event->name,
               'externalId' => $event->externalId
           ]);

           $this->metrics->increment('team.created');

           $this->notificationService->notifyAdmins(
               'New team created',
               sprintf('Team "%s" has been created', $event->name)
           );
       }
   }

   #[AsMessageHandler]
   final class PenaltyEventHandler
   {
       public function __construct(
           private readonly NotificationService $notificationService,
           private readonly PenaltyStatisticsService $statisticsService,
           private readonly EventStore $eventStore
       ) {}

       public function __invoke(PenaltyCreatedEvent $event): void
       {
           // Store event for event sourcing
           $this->eventStore->append($event);

           // Update statistics
           $this->statisticsService->recordPenalty(
               $event->teamId,
               $event->userId,
               $event->amount
           );

           // Send notification
           $this->notificationService->notifyUser(
               $event->userId,
               'New penalty assigned',
               sprintf('You have a new penalty: %s (%s)',
                   $event->reason,
                   $event->amount->format()
               )
           );
       }
   }
   ```

### Modern Repository Pattern

```php
#[AsEntityRepository(Team::class)]
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly TracerInterface $tracer
    ) {
        parent::__construct($registry, Team::class);
    }

    /**
     * @return Team[]
     */
    public function findActiveTeams(): array
    {
        $span = $this->tracer->spanBuilder('team.repository.findActiveTeams')
            ->startSpan();

        try {
            $teams = $this->createQueryBuilder('t')
                ->where('t.active = :active')
                ->setParameter('active', true)
                ->orderBy('t.name', 'ASC')
                ->getQuery()
                ->getResult();

            $span->setAttribute('team.count', count($teams));

            return $teams;
        } finally {
            $span->end();
        }
    }

    public function findByExternalId(string $externalId): ?Team
    {
        return $this->createQueryBuilder('t')
            ->where('t.externalId = :externalId')
            ->setParameter('externalId', $externalId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Team[]
     */
    public function findWithUsers(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.teamUsers', 'tu')
            ->leftJoin('tu.user', 'u')
            ->addSelect('tu', 'u')
            ->where('t.active = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    public function save(Team $team): void
    {
        $this->getEntityManager()->persist($team);
        $this->getEntityManager()->flush();
    }
}
```

### API Endpoints

1. **Teams**
    - `GET /api/teams` - Get all teams with filtering, sorting, and pagination
    - `GET /api/teams/{id}` - Get team by ID with related data
    - `POST /api/teams` - Create new team
    - `PUT /api/teams/{id}` - Update team
    - `DELETE /api/teams/{id}` - Soft delete team
    - `POST /api/teams/{id}/activate` - Activate team
    - `POST /api/teams/{id}/deactivate` - Deactivate team

2. **Users**
    - `GET /api/users` - Get all users with filtering
    - `GET /api/users/{id}` - Get user by ID
    - `POST /api/users` - Create new user
    - `PUT /api/users/{id}` - Update user
    - `DELETE /api/users/{id}` - Delete user
    - `GET /api/users/{id}/teams` - Get user's teams
    - `GET /api/users/{id}/penalties` - Get user's penalties

3. **Team Users**
    - `POST /api/teams/{teamId}/users` - Add user to team
    - `PUT /api/teams/{teamId}/users/{userId}` - Update user roles in team
    - `DELETE /api/teams/{teamId}/users/{userId}` - Remove user from team
    - `GET /api/teams/{teamId}/users` - Get team members

4. **Penalties**
    - `GET /api/penalties` - Get all penalties with advanced filtering
    - `GET /api/penalties/{id}` - Get penalty by ID
    - `POST /api/penalties` - Create new penalty
    - `PUT /api/penalties/{id}` - Update penalty
    - `DELETE /api/penalties/{id}` - Delete penalty
    - `GET /api/penalties/user/{userId}` - Get penalties by user
    - `GET /api/penalties/team/{teamId}` - Get penalties by team
    - `GET /api/penalties/unpaid` - Get all unpaid penalties
    - `POST /api/penalties/{id}/pay` - Mark penalty as paid
    - `POST /api/penalties/{id}/archive` - Archive penalty
    - `GET /api/penalties/statistics` - Get penalty statistics

5. **Penalty Types**
    - `GET /api/penalty-types` - Get all penalty types
    - `GET /api/penalty-types/{id}` - Get penalty type by ID
    - `POST /api/penalty-types` - Create new penalty type
    - `PUT /api/penalty-types/{id}` - Update penalty type
    - `DELETE /api/penalty-types/{id}` - Delete penalty type
    - `GET /api/penalty-types/drinks` - Get all drink types

6. **Payments**
    - `GET /api/payments` - Get all payments
    - `GET /api/payments/{id}` - Get payment by ID
    - `POST /api/payments` - Create new payment
    - `PUT /api/payments/{id}` - Update payment
    - `DELETE /api/payments/{id}` - Delete payment
    - `GET /api/payments/user/{userId}` - Get payments by user
    - `GET /api/payments/team/{teamId}` - Get payments by team

7. **Import/Export**
    - `POST /api/import/penalties` - Import penalties from CSV/Excel
    - `GET /api/import/{id}/status` - Check import status
    - `GET /api/import/{id}/errors` - Get import errors
    - `POST /api/export/penalties` - Export penalties
    - `GET /api/export/{id}/download` - Download export file

### Modern Testing Approach

1. **Pest PHP Tests**
   ```php
   use App\Entity\Team;
   use App\Entity\User;
   use App\ValueObject\PersonName;

   describe('Team', function () {
       it('can be created with valid data', function () {
           $team = Team::create('Test Team', 'TEST001');

           expect($team)
               ->toBeInstanceOf(Team::class)
               ->and($team->name)->toBe('Test Team')
               ->and($team->externalId)->toBe('TEST001')
               ->and($team->active)->toBeTrue();
       });

       it('records domain events when created', function () {
           $team = Team::create('Test Team', 'TEST001');
           $events = $team->releaseEvents();

           expect($events)
               ->toHaveCount(1)
               ->and($events[0])->toBeInstanceOf(TeamCreatedEvent::class);
       });

       it('throws exception when creating with empty name', function () {
           Team::create('', 'TEST001');
       })->throws(InvalidArgumentException::class);

       it('can be renamed', function () {
           $team = Team::create('Old Name', 'TEST001');
           $team->rename('New Name');

           expect($team->name)->toBe('New Name');

           $events = $team->releaseEvents();
           expect($events)->toHaveCount(2)
               ->and($events[1])->toBeInstanceOf(TeamRenamedEvent::class);
       });
   });

   describe('User', function () {
       it('can update profile information', function () {
           $user = new User(
               new PersonName('John', 'Doe'),
               new Email('john@example.com')
           );

           $newName = new PersonName('Jane', 'Smith');
           $newEmail = new Email('jane@example.com');

           $user->updateProfile($newName, $newEmail);

           expect($user->name->getFullName())->toBe('Jane Smith')
               ->and($user->email->getValue())->toBe('jane@example.com');
       });

       it('validates email format', function () {
           new Email('invalid-email');
       })->throws(InvalidArgumentException::class);
   });

   describe('Penalty', function () {
       beforeEach(function () {
           $this->team = Team::create('Test Team', 'TEST001');
           $this->user = new User(new PersonName('John', 'Doe'));
           $this->teamUser = new TeamUser($this->team, $this->user);
           $this->penaltyType = new PenaltyType('Drink', PenaltyTypeEnum::DRINK);
       });

       it('can be created and paid', function () {
           $penalty = new Penalty(
               $this->teamUser,
               $this->penaltyType,
               'Coffee',
               150,
               CurrencyEnum::EUR
           );

           expect($penalty->isPaid())->toBeFalse();

           $penalty->pay();

           expect($penalty->isPaid())->toBeTrue();
       });

       it('cannot be paid twice', function () {
           $penalty = new Penalty(
               $this->teamUser,
               $this->penaltyType,
               'Coffee',
               150,
               CurrencyEnum::EUR
           );

           $penalty->pay();
           $penalty->pay();
       })->throws(DomainException::class);
   });
   ```

2. **PHPUnit Integration Tests**
   ```php
   class PenaltyRepositoryTest extends KernelTestCase
   {
       private EntityManagerInterface $entityManager;
       private PenaltyRepository $repository;

       protected function setUp(): void
       {
           $kernel = self::bootKernel();
           $this->entityManager = $kernel->getContainer()
               ->get('doctrine')
               ->getManager();
           $this->repository = $this->entityManager
               ->getRepository(Penalty::class);
       }

       public function testFindUnpaidPenalties(): void
       {
           // Create test data
           $team = Team::create('Test Team', 'TEST001');
           $user = new User(new PersonName('John', 'Doe'));
           $teamUser = new TeamUser($team, $user);
           $penaltyType = new PenaltyType('Drink', PenaltyTypeEnum::DRINK);

           $this->entityManager->persist($team);
           $this->entityManager->persist($user);
           $this->entityManager->persist($teamUser);
           $this->entityManager->persist($penaltyType);

           $penalty1 = new Penalty($teamUser, $penaltyType, 'Coffee', 150);
           $penalty2 = new Penalty($teamUser, $penaltyType, 'Tea', 150);
           $penalty2->pay();

           $this->entityManager->persist($penalty1);
           $this->entityManager->persist($penalty2);
           $this->entityManager->flush();

           // Test repository method
           $unpaidPenalties = $this->repository->findUnpaidPenalties();

           $this->assertCount(1, $unpaidPenalties);
           $this->assertEquals('Coffee', $unpaidPenalties[0]->getReason());
       }
   }
   ```

### Observability and Monitoring

1. **OpenTelemetry Integration**
   ```php
   #[AsDecorator(TeamRepository::class)]
   final class TracedTeamRepository
   {
       public function __construct(
           private readonly TeamRepository $repository,
           private readonly TracerInterface $tracer
       ) {}

       public function findActiveTeams(): array
       {
           $span = $this->tracer->spanBuilder('team.repository.findActiveTeams')
               ->startSpan();

           try {
               $teams = $this->repository->findActiveTeams();
               $span->setAttribute('team.count', count($teams));

               return $teams;
           } finally {
               $span->end();
           }
       }
   }
   ```

2. **Metrics Collection**
   ```php
   #[AsService]
   final class MetricsCollector
   {
       public function __construct(
           private readonly MeterProviderInterface $meterProvider
       ) {}

       public function recordPenaltyCreated(string $teamId, int $amount): void
       {
           $meter = $this->meterProvider->getMeter('cashbox');

           $counter = $meter->createCounter('penalties.created');
           $counter->add(1, ['team_id' => $teamId]);

           $histogram = $meter->createHistogram('penalties.amount');
           $histogram->record($amount, ['team_id' => $teamId]);
       }
   }
   ```

### Modern Security Implementation

1. **API Security with Rate Limiting**
   ```php
   #[Route('/api/login', methods: ['POST'])]
   #[RateLimiter('login', strategy: 'sliding_window')]
   class LoginController extends AbstractController
   {
       public function __invoke(
           #[MapRequestPayload] LoginRequest $request,
           AuthenticationService $authService
       ): JsonResponse {
           $tokens = $authService->authenticate(
               $request->email,
               $request->password
           );

           return $this->json([
               'access_token' => $tokens->accessToken,
               'refresh_token' => $tokens->refreshToken,
               'expires_in' => $tokens->expiresIn,
               'token_type' => 'Bearer'
           ]);
       }
   }
   ```

2. **OAuth2/OpenID Connect Support**
   ```php
   #[Route('/api/oauth/authorize', methods: ['GET'])]
   class OAuthController extends AbstractController
   {
       public function authorize(
           #[MapQueryString] AuthorizeRequest $request,
           OAuth2Service $oauth2Service
       ): Response {
           return $oauth2Service->handleAuthorizeRequest($request);
       }

       #[Route('/api/oauth/token', methods: ['POST'])]
       public function token(
           #[MapRequestPayload] TokenRequest $request,
           OAuth2Service $oauth2Service
       ): JsonResponse {
           return $oauth2Service->handleTokenRequest($request);
       }
   }
   ```

## Implementation Plan

### Phase 1: Modern Architecture Setup (Week 1)

1. Configure Symfony 7.2 with latest features
2. Set up API Platform 3.3
3. Configure OpenTelemetry
4. Set up event-driven architecture
5. Configure modern testing with Pest PHP
6. Implement CI/CD with quality gates

### Phase 2: Domain Model Implementation (Week 2)

1. Implement value objects
2. Create aggregate roots with domain events
3. Set up domain services
4. Implement repositories with specifications
5. Configure event store
6. Create domain event handlers

### Phase 3: Modern API Development (Week 3)

1. Configure API Platform resources
2. Implement state processors
3. Create custom operations
4. Set up GraphQL support
5. Configure API versioning
6. Implement HATEOAS

### Phase 4: Advanced Features (Week 4)

1. Implement CQRS pattern
2. Set up async command/query buses
3. Configure event sourcing
4. Implement saga pattern
5. Set up distributed tracing

### Phase 5: Security & Performance (Week 5)

1. Implement OAuth2/OIDC
2. Configure rate limiting
3. Set up API keys
4. Implement caching strategies
5. Configure CDN integration
6. Performance profiling

### Phase 6: Testing & Documentation (Week 6)

1. Write Pest PHP tests
2. Implement contract testing
3. Set up mutation testing
4. Create API documentation
5. Performance benchmarking
6. Security audit

## Modern Dependencies

```json
{
    "require": {
        "php": ">=8.4",
        "symfony/framework-bundle": "^7.2",
        "api-platform/core": "^3.3",
        "doctrine/orm": "^3.0",
        "ramsey/uuid": "^4.7",
        "beberlei/assert": "^3.3",
        "league/fractal": "^0.20",
        "symfony/messenger": "^7.2",
        "symfony/rate-limiter": "^7.2",
        "open-telemetry/sdk": "^1.0",
        "nelmio/api-doc-bundle": "^4.0",
        "lexik/jwt-authentication-bundle": "^3.0",
        "gedmo/doctrine-extensions": "^3.14"
    },
    "require-dev": {
        "pestphp/pest": "^2.0",
        "pestphp/pest-plugin-symfony": "^2.0",
        "phpunit/phpunit": "^11.0",
        "phpstan/phpstan": "^1.10",
        "phpstan/phpstan-symfony": "^1.3",
        "phpstan/phpstan-doctrine": "^1.3",
        "rector/rector": "^0.18",
        "infection/infection": "^0.27",
        "friendsofphp/php-cs-fixer": "^3.40",
        "phpbench/phpbench": "^1.2",
        "symfony/test-pack": "^1.1",
        "dama/doctrine-test-bundle": "^8.0"
    }
}
```

## Testing Strategy

1. **Unit Testing**
    - Test individual components in isolation
    - Focus on business logic and validation
    - Aim for 95%+ code coverage
    - Use mock objects for dependencies
    - Use Pest PHP for modern syntax

2. **Integration Testing**
    - Test component interactions
    - Test database operations
    - Use DAMADoctrineTestBundle for transaction management
    - Test repository implementations
    - Test event handling

3. **API Testing**
    - Test API endpoints
    - Verify correct status codes and responses
    - Test authentication and authorization
    - Test input validation
    - Test edge cases and error handling
    - Use API Platform test utilities

4. **Security Testing**
    - Test authentication mechanisms
    - Test authorization rules
    - Verify protection against common vulnerabilities
    - Test input validation and sanitization
    - Verify proper error handling
    - Test rate limiting

5. **Performance Testing**
    - Profile API response times
    - Test database query performance
    - Identify bottlenecks
    - Establish performance baselines
    - Use PHPBench for benchmarking

6. **Mutation Testing**
    - Use Infection for mutation testing
    - Aim for 80%+ mutation score
    - Identify untested edge cases
    - Improve test quality

## Acceptance Criteria

- All code uses PHP 8.4 features appropriately
- 100% type coverage with PHPStan level 9
- Mutation testing score > 80%
- API response time < 100ms for 95th percentile
- Zero security vulnerabilities
- OpenAPI documentation complete
- Event sourcing implemented for audit trail
- Distributed tracing operational
- All API endpoints return proper responses with correct status codes
- Data validation works correctly
- Authentication and authorization function properly
- CSV/Excel import successfully processes data
- All unit and integration tests pass
- Documentation is complete and accurate
- The system can handle the expected load

## Risks and Mitigation

1. **Risk**: Complexity from DDD and event-driven architecture
   **Mitigation**: Gradual implementation, team training, clear documentation

2. **Risk**: Performance overhead from events and tracing
   **Mitigation**: Async processing, caching, performance monitoring

3. **Risk**: Learning curve for PHP 8.4 features
   **Mitigation**: Code examples, pair programming, gradual adoption

4. **Risk**: Integration complexity with multiple systems
   **Mitigation**: Clear interfaces, comprehensive testing, monitoring

5. **Risk**: Security vulnerabilities in new features
   **Mitigation**: Security-first development, regular audits, automated scanning

## Post-Release Activities

1. Monitor system performance and observability metrics
2. Collect user feedback through analytics
3. Address any critical bugs with hotfixes
4. Plan for version 1.1.0 based on usage patterns
5. Conduct retrospective to identify improvements
6. Update documentation based on real-world usage
7. Optimize based on performance metrics

## Documentation

- OpenAPI 3.1 documentation with examples
- Architecture decision records (ADRs)
- Domain model documentation
- Event catalog
- API usage guide
- Developer onboarding guide
- Security guidelines
- Performance tuning guide
- Monitoring and observability guide
