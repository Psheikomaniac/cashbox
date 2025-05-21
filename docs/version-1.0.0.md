# Version 1.0.0: Foundation and Penalties Management

## Overview

Version 1.0.0 focuses on establishing the foundation of Cashbox, with particular emphasis on the core functionality for tracking and managing penalties and drinks. This version will implement the essential components required for a functional system.

## Release Timeline

- **Development Start**: June 1, 2025
- **Alpha Release**: June 15, 2025
- **Beta Release**: June 30, 2025
- **Production Release**: July 15, 2025

## Scope

### Core Components

1. **System Architecture**
   - Establish project structure using Symfony 7.2
   - Configure API Platform
   - Set up development environment
   - Configure logging and monitoring
   - Set up testing framework

2. **Database Structure**
   - Implement the initial database schema
   - Set up Doctrine ORM entities
   - Configure migrations system
   - Implement UUID as primary keys
   - Set up Gedmo for timestamps

3. **Authentication and Authorization**
   - Implement JWT authentication
   - Set up user roles and permissions
   - Configure security settings
   - Create login and token refresh endpoints

4. **Team and User Management**
   - Create Team entity and API endpoints
   - Create User entity and API endpoints
   - Implement TeamUser relationship
   - Set up user roles within teams

5. **Penalties and Drinks Management**
   - Create PenaltyType entity for categorizing penalties and drinks
   - Create Penalty entity for tracking individual items
   - Implement API endpoints for CRUD operations
   - Set up business logic for calculating totals

6. **Import Functionality**
   - Develop CSV import capability
   - Implement validation for imported data
   - Create data transformation services
   - Set up error handling for imports

## Technical Requirements

### Enums

1. **UserRoleEnum**
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
               self::USD => '

### Entities

1. **Team Entity**
   ```php
   /**
    * @ORM\Entity
    */
   class Team
   {
       /**
        * @ORM\Id
        * @ORM\Column(type="uuid", unique=true)
        */
       private UuidInterface $id;
       
       /**
        * @ORM\Column(type="string", length=255)
        */
       private string $name;
       
       /**
        * @ORM\Column(type="string", length=255)
        */
       private string $externalId;
       
       /**
        * @ORM\Column(type="boolean")
        */
       private bool $active = true;
       
       /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $createdAt;
       
       /**
        * @Gedmo\Timestampable(on="update")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $updatedAt;
       
       // Relationships, getters, setters, etc.
   }
   ```

2. **User Entity**
   ```php
   /**
    * @ORM\Entity
    */
   class User
   {
       /**
        * @ORM\Id
        * @ORM\Column(type="uuid", unique=true)
        */
       private UuidInterface $id;
       
       /**
        * @ORM\Column(type="string", length=255)
        */
       private string $firstName;
       
       /**
        * @ORM\Column(type="string", length=255)
        */
       private string $lastName;
       
       /**
        * @ORM\Column(type="string", length=255, nullable=true)
        */
       private ?string $email = null;
       
       /**
        * @ORM\Column(type="string", length=255, nullable=true)
        */
       private ?string $phoneNumber = null;
       
       /**
        * @ORM\Column(type="boolean")
        */
       private bool $active = true;
       
       /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $createdAt;
       
       /**
        * @Gedmo\Timestampable(on="update")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $updatedAt;
       
       // Relationships, getters, setters, etc.
   }
   ```

3. **TeamUser Entity**
   ```php
   /**
    * @ORM\Entity
    */
   class TeamUser
   {
       /**
        * @ORM\Id
        * @ORM\Column(type="uuid", unique=true)
        */
       private UuidInterface $id;
       
       /**
        * @ORM\ManyToOne(targetEntity=Team::class)
        * @ORM\JoinColumn(nullable=false)
        */
       private Team $team;
       
       /**
        * @ORM\ManyToOne(targetEntity=User::class)
        * @ORM\JoinColumn(nullable=false)
        */
       private User $user;
       
       /**
        * @ORM\Column(type="json")
        */
       private array $roles = [];
       
       /**
        * @ORM\Column(type="boolean")
        */
       private bool $active = true;
       
       /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $createdAt;
       
       /**
        * @Gedmo\Timestampable(on="update")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $updatedAt;
       
       /**
        * @return array<UserRoleEnum>
        */
       public function getRoles(): array
       {
           return array_map(
               fn (string $role) => UserRoleEnum::from($role),
               $this->roles
           );
       }
       
       /**
        * @param array<UserRoleEnum> $roles
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
       
       // Other getters, setters, etc.
   }
   ```

4. **PenaltyType Entity**
   ```php
   /**
    * @ORM\Entity
    */
   class PenaltyType
   {
       /**
        * @ORM\Id
        * @ORM\Column(type="uuid", unique=true)
        */
       private UuidInterface $id;
       
       /**
        * @ORM\Column(type="string", length=255)
        */
       private string $name;
       
       /**
        * @ORM\Column(type="text", nullable=true)
        */
       private ?string $description = null;
       
       /**
        * @ORM\Column(type="string", length=30)
        */
       private string $type;
       
       /**
        * @ORM\Column(type="boolean")
        */
       private bool $active = true;
       
       /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $createdAt;
       
       /**
        * @Gedmo\Timestampable(on="update")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $updatedAt;
       
       public function getType(): PenaltyTypeEnum
       {
           return PenaltyTypeEnum::from($this->type);
       }
       
       public function setType(PenaltyTypeEnum $type): self
       {
           $this->type = $type->value;
           
           return $this;
       }
       
       public function isDrink(): bool
       {
           return $this->getType()->isDrink();
       }
       
       // Other getters, setters, etc.
   }
   ```

5. **Penalty Entity**
   ```php
   /**
    * @ORM\Entity
    */
   class Penalty
   {
       /**
        * @ORM\Id
        * @ORM\Column(type="uuid", unique=true)
        */
       private UuidInterface $id;
       
       /**
        * @ORM\ManyToOne(targetEntity=TeamUser::class)
        * @ORM\JoinColumn(nullable=false)
        */
       private TeamUser $teamUser;
       
       /**
        * @ORM\ManyToOne(targetEntity=PenaltyType::class)
        * @ORM\JoinColumn(nullable=false)
        */
       private PenaltyType $type;
       
       /**
        * @ORM\Column(type="string", length=255)
        */
       private string $reason;
       
       /**
        * @ORM\Column(type="integer")
        */
       private int $amount;
       
       /**
        * @ORM\Column(type="string", length=3)
        */
       private string $currency = CurrencyEnum::EUR->value;
       
       /**
        * @ORM\Column(type="boolean")
        */
       private bool $archived = false;
       
       /**
        * @ORM\Column(type="datetime_immutable", nullable=true)
        */
       private ?\DateTimeImmutable $paidAt = null;
       
       /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $createdAt;
       
       /**
        * @Gedmo\Timestampable(on="update")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $updatedAt;
       
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
       
       // Other getters, setters, etc.
   }
   ```

6. **Payment Entity**
   ```php
   /**
    * @ORM\Entity
    */
   class Payment
   {
       /**
        * @ORM\Id
        * @ORM\Column(type="uuid", unique=true)
        */
       private UuidInterface $id;
       
       /**
        * @ORM\ManyToOne(targetEntity=TeamUser::class)
        * @ORM\JoinColumn(nullable=false)
        */
       private TeamUser $teamUser;
       
       /**
        * @ORM\Column(type="integer")
        */
       private int $amount;
       
       /**
        * @ORM\Column(type="string", length=3)
        */
       private string $currency = CurrencyEnum::EUR->value;
       
       /**
        * @ORM\Column(type="string", length=30)
        */
       private string $type = PaymentTypeEnum::CASH->value;
       
       /**
        * @ORM\Column(type="string", length=255, nullable=true)
        */
       private ?string $description = null;
       
       /**
        * @ORM\Column(type="string", length=255, nullable=true)
        */
       private ?string $reference = null;
       
       /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $createdAt;
       
       /**
        * @Gedmo\Timestampable(on="update")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $updatedAt;
       
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
       
       public function requiresReference(): bool
       {
           return $this->getType()->requiresReference();
       }
       
       // Other getters, setters, etc.
   }
   ```

### DTOs

1. **TeamDTO**
   ```php
   class TeamDTO
   {
       public string $name;
       public string $externalId;
       public bool $active;
       
       public static function createFromEntity(Team $team): self
       {
           $dto = new self();
           $dto->name = $team->getName();
           $dto->externalId = $team->getExternalId();
           $dto->active = $team->isActive();
           
           return $dto;
       }
   }
   ```

2. **UserDTO**
   ```php
   class UserDTO
   {
       public string $id;
       public string $firstName;
       public string $lastName;
       public ?string $email;
       public ?string $phoneNumber;
       public bool $active;
       
       public static function createFromEntity(User $user): self
       {
           $dto = new self();
           $dto->id = $user->getId()->toString();
           $dto->firstName = $user->getFirstName();
           $dto->lastName = $user->getLastName();
           $dto->email = $user->getEmail();
           $dto->phoneNumber = $user->getPhoneNumber();
           $dto->active = $user->isActive();
           
           return $dto;
       }
   }
   ```

3. **TeamUserDTO**
   ```php
   class TeamUserDTO
   {
       public string $id;
       public string $teamId;
       public string $userId;
       public array $roles;
       public bool $active;
       
       public static function createFromEntity(TeamUser $teamUser): self
       {
           $dto = new self();
           $dto->id = $teamUser->getId()->toString();
           $dto->teamId = $teamUser->getTeam()->getId()->toString();
           $dto->userId = $teamUser->getUser()->getId()->toString();
           $dto->roles = array_map(
               fn (UserRoleEnum $role) => [
                   'value' => $role->value,
                   'label' => $role->getLabel(),
                   'permissions' => $role->getPermissions(),
               ],
               $teamUser->getRoles()
           );
           $dto->active = $teamUser->isActive();
           
           return $dto;
       }
   }
   ```

4. **PenaltyTypeDTO**
   ```php
   class PenaltyTypeDTO
   {
       public string $id;
       public string $name;
       public ?string $description;
       public array $type;
       public bool $active;
       
       public static function createFromEntity(PenaltyType $penaltyType): self
       {
           $dto = new self();
           $dto->id = $penaltyType->getId()->toString();
           $dto->name = $penaltyType->getName();
           $dto->description = $penaltyType->getDescription();
           $dto->type = [
               'value' => $penaltyType->getType()->value,
               'label' => $penaltyType->getType()->getLabel(),
               'isDrink' => $penaltyType->getType()->isDrink(),
           ];
           $dto->active = $penaltyType->isActive();
           
           return $dto;
       }
   }
   ```

5. **PenaltyDTO**
   ```php
   class PenaltyDTO
   {
       public string $id;
       public string $userId;
       public string $teamId;
       public string $typeId;
       public string $reason;
       public int $amount;
       public array $currency;
       public string $formattedAmount;
       public bool $archived;
       public ?string $paidAt;
       
       public static function createFromEntity(Penalty $penalty): self
       {
           $dto = new self();
           $dto->id = $penalty->getId()->toString();
           $dto->userId = $penalty->getTeamUser()->getUser()->getId()->toString();
           $dto->teamId = $penalty->getTeamUser()->getTeam()->getId()->toString();
           $dto->typeId = $penalty->getType()->getId()->toString();
           $dto->reason = $penalty->getReason();
           $dto->amount = $penalty->getAmount();
           $dto->currency = [
               'value' => $penalty->getCurrency()->value,
               'symbol' => $penalty->getCurrency()->getSymbol(),
           ];
           $dto->formattedAmount = $penalty->getFormattedAmount();
           $dto->archived = $penalty->isArchived();
           $dto->paidAt = $penalty->getPaidAt() ? $penalty->getPaidAt()->format('Y-m-d H:i:s') : null;
           
           return $dto;
       }
   }
   ```

6. **PaymentDTO**
   ```php
   class PaymentDTO
   {
       public string $id;
       public string $userId;
       public string $teamId;
       public int $amount;
       public array $currency;
       public string $formattedAmount;
       public array $type;
       public ?string $description;
       public ?string $reference;
       
       public static function createFromEntity(Payment $payment): self
       {
           $dto = new self();
           $dto->id = $payment->getId()->toString();
           $dto->userId = $payment->getTeamUser()->getUser()->getId()->toString();
           $dto->teamId = $payment->getTeamUser()->getTeam()->getId()->toString();
           $dto->amount = $payment->getAmount();
           $dto->currency = [
               'value' => $payment->getCurrency()->value,
               'symbol' => $payment->getCurrency()->getSymbol(),
           ];
           $dto->formattedAmount = $payment->getFormattedAmount();
           $dto->type = [
               'value' => $payment->getType()->value,
               'label' => $payment->getType()->getLabel(),
               'requiresReference' => $payment->getType()->requiresReference(),
           ];
           $dto->description = $payment->getDescription();
           $dto->reference = $payment->getReference();
           
           return $dto;
       }
   }
   ```

### API Endpoints

1. **Teams**
   - `GET /api/teams` - Get all teams
   - `GET /api/teams/{id}` - Get team by ID
   - `POST /api/teams` - Create new team
   - `PUT /api/teams/{id}` - Update team
   - `DELETE /api/teams/{id}` - Delete team

2. **Users**
   - `GET /api/users` - Get all users
   - `GET /api/users/{id}` - Get user by ID
   - `POST /api/users` - Create new user
   - `PUT /api/users/{id}` - Update user
   - `DELETE /api/users/{id}` - Delete user

3. **Penalties**
   - `GET /api/penalties` - Get all penalties
   - `GET /api/penalties/{id}` - Get penalty by ID
   - `POST /api/penalties` - Create new penalty
   - `PUT /api/penalties/{id}` - Update penalty
   - `DELETE /api/penalties/{id}` - Delete penalty
   - `GET /api/penalties/user/{userId}` - Get penalties by user
   - `GET /api/penalties/team/{teamId}` - Get penalties by team
   - `GET /api/penalties/unpaid` - Get all unpaid penalties
   - `POST /api/penalties/{id}/pay` - Mark penalty as paid
   - `POST /api/penalties/{id}/archive` - Archive penalty

4. **PenaltyTypes**
   - `GET /api/penalty-types` - Get all penalty types
   - `GET /api/penalty-types/{id}` - Get penalty type by ID
   - `POST /api/penalty-types` - Create new penalty type
   - `PUT /api/penalty-types/{id}` - Update penalty type
   - `DELETE /api/penalty-types/{id}` - Delete penalty type
   - `GET /api/penalty-types/drinks` - Get all drink types

5. **Import**
   - `POST /api/import/penalties` - Import penalties from CSV

## Implementation Plan

### Phase 1: Project Setup (Week 1)

The basic project structure with Symfony 7.2 is already set up. Initial focus will be on:

1. Configuring API Platform for the project's needs
2. Setting up proper security configuration
3. Finalizing Docker configuration
4. Establishing CI/CD pipeline with GitHub Actions
5. Setting up development environments
6. Configuring logging and monitoring

Since Symfony and the repository are already initialized, we'll build upon this foundation rather than starting from scratch.

### Phase 2: Core Entities (Week 2)

1. Implement Team entity
2. Implement User entity
3. Implement TeamUser entity
4. Implement PenaltyType entity
5. Implement Penalty entity
6. Create database migrations

### Phase 3: API Development (Week 3)

1. Configure API resources
2. Implement DTOs for input/output
3. Create controllers for custom operations
4. Implement business logic in services
5. Configure serialization groups
6. Set up validation

### Phase 4: Import/Export (Week 4)

1. Develop CSV import service
2. Implement data validation
3. Create data transformation services
4. Set up error handling
5. Implement export functionality

### Phase 5: Testing and Documentation (Week 5)

1. Develop comprehensive testing strategy
2. Write unit tests for all business logic
3. Write integration tests for repositories
4. Write functional tests for API endpoints
5. Implement security tests for authentication
6. Write smoke tests for critical functionality
7. Configure PHPStan and other code quality tools
8. Create API documentation
9. Prepare user documentation
10. Conduct security audit
11. Perform performance testing

### Phase 6: Finalization (Week 6)

1. Bug fixing
2. Code optimization
3. Final testing
4. Documentation review
5. Prepare for release
6. Production deployment

## Dependencies

- Symfony 7.2
- API Platform 3.x
- Doctrine ORM
- Gedmo Extensions
- LexikJWTAuthenticationBundle
- Ramsey UUID
- PHPUnit 11.x
- PHPStan for static analysis
- PHP CS Fixer for code style
- Psalm for additional static analysis
- PHPMD for code quality
- PHP Copy/Paste Detector
- DAMADoctrineTestBundle for database tests
- Symfony Test Pack for testing utilities

## Testing Strategy

1. **Unit Testing**
   - Test individual components in isolation
   - Focus on business logic and validation
   - Aim for 90%+ code coverage
   - Use mock objects for dependencies

2. **Integration Testing**
   - Test component interactions
   - Test database operations
   - Use DAMADoctrineTestBundle for transaction management
   - Test repository implementations

3. **API Testing**
   - Test API endpoints
   - Verify correct status codes and responses
   - Test authentication and authorization
   - Test input validation
   - Test edge cases and error handling

4. **Security Testing**
   - Test authentication mechanisms
   - Test authorization rules
   - Verify protection against common vulnerabilities
   - Test input validation and sanitization
   - Verify proper error handling

5. **Smoke Testing**
   - Test critical application paths
   - Verify basic functionality works
   - Use data providers for test efficiency

6. **Performance Testing**
   - Profile API response times
   - Test database query performance
   - Identify bottlenecks
   - Establish performance baselines

## Acceptance Criteria

- All API endpoints return proper responses with correct status codes
- Data validation works correctly
- Authentication and authorization function properly
- CSV import successfully processes data
- All unit and integration tests pass
- Documentation is complete and accurate
- The system can handle the expected load

## Risks and Mitigation

1. **Risk**: Data migration issues from existing system
   **Mitigation**: Thorough testing of import functionality and data validation

2. **Risk**: Performance issues with large datasets
   **Mitigation**: Implement pagination and optimize queries

3. **Risk**: Security vulnerabilities
   **Mitigation**: Conduct security audit and follow Symfony security best practices

4. **Risk**: Compatibility issues with PHP 8.4
   **Mitigation**: Regular testing with PHP 8.4 and addressing any compatibility issues

## Post-Release Activities

1. Monitor system performance
2. Collect user feedback
3. Address any critical bugs
4. Plan for version 1.1.0
5. Conduct retrospective to identify areas for improvement

## Documentation

- API documentation using Swagger/OpenAPI
- Installation and setup guide
- User manual
- Developer documentation
- Database schema documentation,
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

### Entities

1. **Team Entity**
   ```php
   /**
    * @ORM\Entity
    */
   class Team
   {
       /**
        * @ORM\Id
        * @ORM\Column(type="uuid", unique=true)
        */
       private UuidInterface $id;
       
       /**
        * @ORM\Column(type="string", length=255)
        */
       private string $name;
       
       /**
        * @ORM\Column(type="string", length=255)
        */
       private string $externalId;
       
       /**
        * @ORM\Column(type="boolean")
        */
       private bool $active = true;
       
       /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $createdAt;
       
       /**
        * @Gedmo\Timestampable(on="update")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $updatedAt;
       
       // Relationships, getters, setters, etc.
   }
   ```

2. **User Entity**
   ```php
   /**
    * @ORM\Entity
    */
   class User
   {
       /**
        * @ORM\Id
        * @ORM\Column(type="uuid", unique=true)
        */
       private UuidInterface $id;
       
       /**
        * @ORM\Column(type="string", length=255)
        */
       private string $firstName;
       
       /**
        * @ORM\Column(type="string", length=255)
        */
       private string $lastName;
       
       /**
        * @ORM\Column(type="string", length=255, nullable=true)
        */
       private ?string $email = null;
       
       /**
        * @ORM\Column(type="string", length=255, nullable=true)
        */
       private ?string $phoneNumber = null;
       
       /**
        * @ORM\Column(type="boolean")
        */
       private bool $active = true;
       
       /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $createdAt;
       
       /**
        * @Gedmo\Timestampable(on="update")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $updatedAt;
       
       // Relationships, getters, setters, etc.
   }
   ```

3. **PenaltyType Entity**
   ```php
   /**
    * @ORM\Entity
    */
   class PenaltyType
   {
       /**
        * @ORM\Id
        * @ORM\Column(type="uuid", unique=true)
        */
       private UuidInterface $id;
       
       /**
        * @ORM\Column(type="string", length=255)
        */
       private string $name;
       
       /**
        * @ORM\Column(type="text", nullable=true)
        */
       private ?string $description = null;
       
       /**
        * @ORM\Column(type="boolean")
        */
       private bool $isDrink = false;
       
       /**
        * @ORM\Column(type="boolean")
        */
       private bool $active = true;
       
       /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $createdAt;
       
       /**
        * @Gedmo\Timestampable(on="update")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $updatedAt;
       
       // Getters, setters, etc.
   }
   ```

4. **Penalty Entity**
   ```php
   /**
    * @ORM\Entity
    */
   class Penalty
   {
       /**
        * @ORM\Id
        * @ORM\Column(type="uuid", unique=true)
        */
       private UuidInterface $id;
       
       /**
        * @ORM\ManyToOne(targetEntity=TeamUser::class)
        * @ORM\JoinColumn(nullable=false)
        */
       private TeamUser $teamUser;
       
       /**
        * @ORM\ManyToOne(targetEntity=PenaltyType::class)
        * @ORM\JoinColumn(nullable=false)
        */
       private PenaltyType $type;
       
       /**
        * @ORM\Column(type="string", length=255)
        */
       private string $reason;
       
       /**
        * @ORM\Column(type="integer")
        */
       private int $amount;
       
       /**
        * @ORM\Column(type="string", length=3)
        */
       private string $currency = 'EUR';
       
       /**
        * @ORM\Column(type="boolean")
        */
       private bool $archived = false;
       
       /**
        * @ORM\Column(type="datetime_immutable", nullable=true)
        */
       private ?\DateTimeImmutable $paidAt = null;
       
       /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $createdAt;
       
       /**
        * @Gedmo\Timestampable(on="update")
        * @ORM\Column(type="datetime_immutable")
        */
       private \DateTimeImmutable $updatedAt;
       
       // Getters, setters, etc.
   }
   ```

### DTOs

1. **TeamDTO**
   ```php
   class TeamDTO
   {
       public string $name;
       public string $externalId;
       public bool $active;
       
       public static function createFromEntity(Team $team): self
       {
           $dto = new self();
           $dto->name = $team->getName();
           $dto->externalId = $team->getExternalId();
           $dto->active = $team->isActive();
           
           return $dto;
       }
   }
   ```

2. **PenaltyDTO**
   ```php
   class PenaltyDTO
   {
       public string $userId;
       public string $teamId;
       public string $typeId;
       public string $reason;
       public int $amount;
       public string $currency;
       public bool $archived;
       public ?string $paidAt;
       
       public static function createFromEntity(Penalty $penalty): self
       {
           $dto = new self();
           $dto->userId = $penalty->getTeamUser()->getUser()->getId()->toString();
           $dto->teamId = $penalty->getTeamUser()->getTeam()->getId()->toString();
           $dto->typeId = $penalty->getType()->getId()->toString();
           $dto->reason = $penalty->getReason();
           $dto->amount = $penalty->getAmount();
           $dto->currency = $penalty->getCurrency();
           $dto->archived = $penalty->isArchived();
           $dto->paidAt = $penalty->getPaidAt() ? $penalty->getPaidAt()->format('Y-m-d H:i:s') : null;
           
           return $dto;
       }
   }
   ```

### API Endpoints

1. **Teams**
   - `GET /api/teams` - Get all teams
   - `GET /api/teams/{id}` - Get team by ID
   - `POST /api/teams` - Create new team
   - `PUT /api/teams/{id}` - Update team
   - `DELETE /api/teams/{id}` - Delete team

2. **Users**
   - `GET /api/users` - Get all users
   - `GET /api/users/{id}` - Get user by ID
   - `POST /api/users` - Create new user
   - `PUT /api/users/{id}` - Update user
   - `DELETE /api/users/{id}` - Delete user

3. **Penalties**
   - `GET /api/penalties` - Get all penalties
   - `GET /api/penalties/{id}` - Get penalty by ID
   - `POST /api/penalties` - Create new penalty
   - `PUT /api/penalties/{id}` - Update penalty
   - `DELETE /api/penalties/{id}` - Delete penalty
   - `GET /api/penalties/user/{userId}` - Get penalties by user
   - `GET /api/penalties/team/{teamId}` - Get penalties by team
   - `GET /api/penalties/unpaid` - Get all unpaid penalties
   - `POST /api/penalties/{id}/pay` - Mark penalty as paid
   - `POST /api/penalties/{id}/archive` - Archive penalty

4. **PenaltyTypes**
   - `GET /api/penalty-types` - Get all penalty types
   - `GET /api/penalty-types/{id}` - Get penalty type by ID
   - `POST /api/penalty-types` - Create new penalty type
   - `PUT /api/penalty-types/{id}` - Update penalty type
   - `DELETE /api/penalty-types/{id}` - Delete penalty type
   - `GET /api/penalty-types/drinks` - Get all drink types

5. **Import**
   - `POST /api/import/penalties` - Import penalties from CSV

## Implementation Plan

### Phase 1: Project Setup (Week 1)

1. Initialize Symfony 7.2 project
2. Configure API Platform
3. Set up database connection
4. Configure JWT authentication
5. Set up development environment
6. Configure CI/CD pipeline

### Phase 2: Core Entities (Week 2)

1. Implement Team entity
2. Implement User entity
3. Implement TeamUser entity
4. Implement PenaltyType entity
5. Implement Penalty entity
6. Create database migrations

### Phase 3: API Development (Week 3)

1. Configure API resources
2. Implement DTOs for input/output
3. Create controllers for custom operations
4. Implement business logic in services
5. Configure serialization groups
6. Set up validation

### Phase 4: Import/Export (Week 4)

1. Develop CSV import service
2. Implement data validation
3. Create data transformation services
4. Set up error handling
5. Implement export functionality

### Phase 5: Testing and Documentation (Week 5)

1. Develop comprehensive testing strategy
2. Write unit tests for all business logic
3. Write integration tests for repositories
4. Write functional tests for API endpoints
5. Implement security tests for authentication
6. Write smoke tests for critical functionality
7. Configure PHPStan and other code quality tools
8. Create API documentation
9. Prepare user documentation
10. Conduct security audit
11. Perform performance testing

### Phase 6: Finalization (Week 6)

1. Bug fixing
2. Code optimization
3. Final testing
4. Documentation review
5. Prepare for release
6. Production deployment

## Dependencies

- Symfony 7.2
- API Platform 3.x
- Doctrine ORM
- Gedmo Extensions
- LexikJWTAuthenticationBundle
- Ramsey UUID
- PHPUnit 11.x
- PHPStan for static analysis
- PHP CS Fixer for code style
- Psalm for additional static analysis
- PHPMD for code quality
- PHP Copy/Paste Detector
- DAMADoctrineTestBundle for database tests
- Symfony Test Pack for testing utilities

## Testing Strategy

1. **Unit Testing**
   - Test individual components in isolation
   - Focus on business logic and validation
   - Aim for 90%+ code coverage
   - Use mock objects for dependencies

2. **Integration Testing**
   - Test component interactions
   - Test database operations
   - Use DAMADoctrineTestBundle for transaction management
   - Test repository implementations

3. **API Testing**
   - Test API endpoints
   - Verify correct status codes and responses
   - Test authentication and authorization
   - Test input validation
   - Test edge cases and error handling

4. **Security Testing**
   - Test authentication mechanisms
   - Test authorization rules
   - Verify protection against common vulnerabilities
   - Test input validation and sanitization
   - Verify proper error handling

5. **Smoke Testing**
   - Test critical application paths
   - Verify basic functionality works
   - Use data providers for test efficiency

6. **Performance Testing**
   - Profile API response times
   - Test database query performance
   - Identify bottlenecks
   - Establish performance baselines

## Acceptance Criteria

- All API endpoints return proper responses with correct status codes
- Data validation works correctly
- Authentication and authorization function properly
- CSV import successfully processes data
- All unit and integration tests pass
- Documentation is complete and accurate
- The system can handle the expected load

## Risks and Mitigation

1. **Risk**: Data migration issues from existing system
   **Mitigation**: Thorough testing of import functionality and data validation

2. **Risk**: Performance issues with large datasets
   **Mitigation**: Implement pagination and optimize queries

3. **Risk**: Security vulnerabilities
   **Mitigation**: Conduct security audit and follow Symfony security best practices

4. **Risk**: Compatibility issues with PHP 8.4
   **Mitigation**: Regular testing with PHP 8.4 and addressing any compatibility issues

## Post-Release Activities

1. Monitor system performance
2. Collect user feedback
3. Address any critical bugs
4. Plan for version 1.1.0
5. Conduct retrospective to identify areas for improvement

## Documentation

- API documentation using Swagger/OpenAPI
- Installation and setup guide
- User manual
- Developer documentation
- Database schema documentation