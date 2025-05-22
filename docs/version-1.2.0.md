# Version 1.2.0: Contribution Management

## Overview

Version 1.2.0 focuses on implementing the contribution management features of Cashbox. This release will enable teams to track and manage member contributions, including dues, membership fees, and other recurring payments. Building on the foundation established in previous versions, this release will integrate contribution tracking with the existing penalty and payment systems.

## Release Timeline

- **Development Start**: October 1, 2025
- **Alpha Release**: October 15, 2025
- **Beta Release**: October 30, 2025
- **Production Release**: November 15, 2025

## Scope

### Core Components

1. **Contribution Management**
   - Track various types of member contributions
   - Configure recurring contributions
   - Set due dates and payment terms
   - Generate contribution invoices
   - Track payment status

2. **Contribution Templates**
   - Create reusable contribution templates
   - Apply templates to multiple users
   - Schedule automated contribution creation
   - Configure variable amounts

3. **Payment Tracking for Contributions**
   - Record contribution payments
   - Generate payment receipts
   - Track payment history
   - Calculate outstanding balances
   - Manage partial payments

4. **Contribution Reporting**
   - Generate contribution reports
   - Track contribution status
   - Analyze contribution trends
   - Export contribution data

5. **Enhanced User Dashboard**
   - View contribution status
   - Track due dates
   - Manage payment methods
   - View payment history

6. **Import/Export for Contributions**
   - Import contribution data from CSV
   - Export contribution reports
   - Batch update contribution status
   - Generate contribution statements

## Technical Requirements

### New Entities

1. **Contribution Entity**
   ```php
   /**
    * @ORM\Entity
    */
   class Contribution
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
        * @ORM\ManyToOne(targetEntity=ContributionType::class)
        * @ORM\JoinColumn(nullable=false)
        */
       private ContributionType $type;

       /**
        * @ORM\Column(type="string", length=255)
        */
       private string $description;

       /**
        * @ORM\Column(type="integer")
        */
       private int $amount;

       /**
        * @ORM\Column(type="string", length=3)
        */
       private string $currency = 'EUR';

       /**
        * @ORM\Column(type="datetime")
        */
       private \DateTimeInterface $dueDate;

       /**
        * @ORM\Column(type="datetime", nullable=true)
        */
       private ?\DateTimeInterface $paidAt = null;

       /**
        * @ORM\Column(type="boolean")
        */
       private bool $active = true;

       /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime")
        */
       private \DateTimeInterface $createdAt;

       /**
        * @Gedmo\Timestampable(on="update")
        * @ORM\Column(type="datetime")
        */
       private \DateTimeInterface $updatedAt;

       // Getters, setters, etc.
   }
   ```

2. **ContributionType Entity**
   ```php
   /**
    * @ORM\Entity
    */
   class ContributionType
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
       private bool $recurring = false;

       /**
        * @ORM\Column(type="string", length=255, nullable=true)
        */
       private ?string $recurrencePattern = null;

       /**
        * @ORM\Column(type="boolean")
        */
       private bool $active = true;

       /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime")
        */
       private \DateTimeInterface $createdAt;

       /**
        * @Gedmo\Timestampable(on="update")
        * @ORM\Column(type="datetime")
        */
       private \DateTimeInterface $updatedAt;

       // Getters, setters, etc.
   }
   ```

3. **ContributionTemplate Entity**
   ```php
   /**
    * @ORM\Entity
    */
   class ContributionTemplate
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
        * @ORM\Column(type="string", length=255)
        */
       private string $name;

       /**
        * @ORM\Column(type="text", nullable=true)
        */
       private ?string $description = null;

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
       private bool $recurring = false;

       /**
        * @ORM\Column(type="string", length=255, nullable=true)
        */
       private ?string $recurrencePattern = null;

       /**
        * @ORM\Column(type="integer", nullable=true)
        */
       private ?int $dueDays = null;

       /**
        * @ORM\Column(type="boolean")
        */
       private bool $active = true;

       /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime")
        */
       private \DateTimeInterface $createdAt;

       /**
        * @Gedmo\Timestampable(on="update")
        * @ORM\Column(type="datetime")
        */
       private \DateTimeInterface $updatedAt;

       // Getters, setters, etc.
   }
   ```

4. **ContributionPayment Entity**
   ```php
   /**
    * @ORM\Entity
    */
   class ContributionPayment
   {
       /**
        * @ORM\Id
        * @ORM\Column(type="uuid", unique=true)
        */
       private UuidInterface $id;

       /**
        * @ORM\ManyToOne(targetEntity=Contribution::class)
        * @ORM\JoinColumn(nullable=false)
        */
       private Contribution $contribution;

       /**
        * @ORM\Column(type="integer")
        */
       private int $amount;

       /**
        * @ORM\Column(type="string", length=3)
        */
       private string $currency = 'EUR';

       /**
        * @ORM\Column(type="string", length=255, nullable=true)
        */
       private ?string $paymentMethod = null;

       /**
        * @ORM\Column(type="string", length=255, nullable=true)
        */
       private ?string $reference = null;

       /**
        * @ORM\Column(type="text", nullable=true)
        */
       private ?string $notes = null;

       /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime")
        */
       private \DateTimeInterface $createdAt;

       /**
        * @Gedmo\Timestampable(on="update")
        * @ORM\Column(type="datetime")
        */
       private \DateTimeInterface $updatedAt;

       // Getters, setters, etc.
   }
   ```

### New DTOs

1. **Contribution DTOs**

   **ContributionInputDTO** - For POST/PUT requests
   ```php
   class ContributionInputDTO
   {
       public string $teamUserId;
       public string $typeId;
       public string $description;
       public int $amount;
       public string $currency;
       public string $dueDate;
       public ?string $paidAt;
   }
   ```

   **ContributionOutputDTO** - For GET responses
   ```php
   class ContributionOutputDTO
   {
       public string $id;
       public string $teamUserId;
       public string $typeId;
       public string $description;
       public int $amount;
       public string $currency;
       public string $dueDate;
       public ?string $paidAt;
       public bool $active;
       public string $createdAt;
       public string $updatedAt;

       public static function createFromEntity(Contribution $contribution): self
       {
           $dto = new self();
           $dto->id = $contribution->getId()->toString();
           $dto->teamUserId = $contribution->getTeamUser()->getId()->toString();
           $dto->typeId = $contribution->getType()->getId()->toString();
           $dto->description = $contribution->getDescription();
           $dto->amount = $contribution->getAmount();
           $dto->currency = $contribution->getCurrency();
           $dto->dueDate = $contribution->getDueDate()->format('Y-m-d');
           $dto->paidAt = $contribution->getPaidAt() ? $contribution->getPaidAt()->format('Y-m-d') : null;
           $dto->active = $contribution->isActive();
           $dto->createdAt = $contribution->getCreatedAt()->format('Y-m-d H:i:s');
           $dto->updatedAt = $contribution->getUpdatedAt()->format('Y-m-d H:i:s');

           return $dto;
       }
   }
   ```

2. **ContributionType DTOs**

   **ContributionTypeInputDTO** - For POST/PUT requests
   ```php
   class ContributionTypeInputDTO
   {
       public string $name;
       public ?string $description;
       public bool $recurring;
       public ?string $recurrencePattern;
   }
   ```

   **ContributionTypeOutputDTO** - For GET responses
   ```php
   class ContributionTypeOutputDTO
   {
       public string $id;
       public string $name;
       public ?string $description;
       public bool $recurring;
       public ?string $recurrencePattern;
       public bool $active;
       public string $createdAt;
       public string $updatedAt;

       public static function createFromEntity(ContributionType $type): self
       {
           $dto = new self();
           $dto->id = $type->getId()->toString();
           $dto->name = $type->getName();
           $dto->description = $type->getDescription();
           $dto->recurring = $type->isRecurring();
           $dto->recurrencePattern = $type->getRecurrencePattern();
           $dto->active = $type->isActive();
           $dto->createdAt = $type->getCreatedAt()->format('Y-m-d H:i:s');
           $dto->updatedAt = $type->getUpdatedAt()->format('Y-m-d H:i:s');

           return $dto;
       }
   }
   ```

3. **ContributionTemplate DTOs**

   **ContributionTemplateInputDTO** - For POST/PUT requests
   ```php
   class ContributionTemplateInputDTO
   {
       public string $teamId;
       public string $name;
       public ?string $description;
       public int $amount;
       public string $currency;
       public bool $recurring;
       public ?string $recurrencePattern;
       public ?int $dueDays;
   }
   ```

   **ContributionTemplateOutputDTO** - For GET responses
   ```php
   class ContributionTemplateOutputDTO
   {
       public string $id;
       public string $teamId;
       public string $name;
       public ?string $description;
       public int $amount;
       public string $currency;
       public bool $recurring;
       public ?string $recurrencePattern;
       public ?int $dueDays;
       public bool $active;
       public string $createdAt;
       public string $updatedAt;

       public static function createFromEntity(ContributionTemplate $template): self
       {
           $dto = new self();
           $dto->id = $template->getId()->toString();
           $dto->teamId = $template->getTeam()->getId()->toString();
           $dto->name = $template->getName();
           $dto->description = $template->getDescription();
           $dto->amount = $template->getAmount();
           $dto->currency = $template->getCurrency();
           $dto->recurring = $template->isRecurring();
           $dto->recurrencePattern = $template->getRecurrencePattern();
           $dto->dueDays = $template->getDueDays();
           $dto->active = $template->isActive();
           $dto->createdAt = $template->getCreatedAt()->format('Y-m-d H:i:s');
           $dto->updatedAt = $template->getUpdatedAt()->format('Y-m-d H:i:s');

           return $dto;
       }
   }
   ```

### New API Endpoints

1. **Contributions**
   - `GET /api/contributions` - Get all contributions
   - `GET /api/contributions/{id}` - Get contribution by ID
   - `POST /api/contributions` - Create new contribution
   - `PUT /api/contributions/{id}` - Update contribution
   - `DELETE /api/contributions/{id}` - Delete contribution
   - `GET /api/contributions/user/{userId}` - Get contributions by user
   - `GET /api/contributions/team/{teamId}` - Get contributions by team
   - `GET /api/contributions/unpaid` - Get unpaid contributions
   - `POST /api/contributions/{id}/pay` - Mark contribution as paid
   - `GET /api/contributions/upcoming` - Get upcoming due dates

2. **Contribution Types**
   - `GET /api/contribution-types` - Get all contribution types
   - `GET /api/contribution-types/{id}` - Get contribution type by ID
   - `POST /api/contribution-types` - Create new contribution type
   - `PUT /api/contribution-types/{id}` - Update contribution type
   - `DELETE /api/contribution-types/{id}` - Delete contribution type

3. **Contribution Templates**
   - `GET /api/contribution-templates` - Get all templates
   - `GET /api/contribution-templates/{id}` - Get template by ID
   - `POST /api/contribution-templates` - Create new template
   - `PUT /api/contribution-templates/{id}` - Update template
   - `DELETE /api/contribution-templates/{id}` - Delete template
   - `POST /api/contribution-templates/{id}/apply` - Apply template to users
   - `GET /api/contribution-templates/team/{teamId}` - Get templates by team

4. **Contribution Payments**
   - `GET /api/contribution-payments` - Get all payments
   - `GET /api/contribution-payments/{id}` - Get payment by ID
   - `POST /api/contribution-payments` - Create new payment
   - `PUT /api/contribution-payments/{id}` - Update payment
   - `DELETE /api/contribution-payments/{id}` - Delete payment
   - `GET /api/contribution-payments/contribution/{contributionId}` - Get payments by contribution

5. **Contribution Reports**
   - `GET /api/reports/contributions` - Get contribution reports
   - `GET /api/reports/contributions/summary` - Get contribution summary
   - `GET /api/reports/contributions/due` - Get due contributions report
   - `GET /api/reports/contributions/paid` - Get paid contributions report

6. **Import/Export**
   - `POST /api/import/contributions` - Import contributions from CSV
   - `GET /api/export/contributions` - Export contributions to CSV
   - `GET /api/export/contributions/template` - Download import template

## Implementation Plan

### Phase 1: Core Entities and Structure (Week 1)

1. Implement Contribution entity
2. Implement ContributionType entity
3. Implement ContributionTemplate entity
4. Implement ContributionPayment entity
5. Create database migrations
6. Update database schema

### Phase 2: API Development (Week 2)

1. Configure API resources for new entities
2. Implement DTOs for contributions
3. Create controllers for custom operations
4. Implement business logic in services
5. Configure serialization groups
6. Set up validation

### Phase 3: Contribution Management Features (Week 3)

1. Implement recurring contribution logic
2. Develop contribution template system
3. Create payment tracking for contributions
4. Implement due date calculations
5. Develop balance calculation services

### Phase 4: Reporting and Dashboard (Week 4)

1. Enhance reporting system for contributions
2. Update user dashboard to show contributions
3. Implement contribution summary views
4. Create due date notifications
5. Develop payment history tracking

### Phase 5: Import/Export Features (Week 5)

1. Implement CSV import for contributions
2. Create export functionality for contribution data
3. Develop batch update capabilities
4. Implement contribution statement generation
5. Create template download functionality

### Phase 6: Testing and Finalization (Week 6)

1. Write unit tests for new components
2. Perform integration testing
3. Update API documentation
4. Update user documentation
5. Conduct security and performance testing
6. Bug fixes and final adjustments

## Dependencies

- Symfony 7.2
- API Platform 3.x
- Doctrine ORM
- PHPSpreadsheet (for Excel exports)
- TCPDF (for PDF generation)
- Symfony Mailer (for notifications)
- Symfony Scheduler Component (for recurring contributions)

## Testing Strategy

1. **Unit Testing**
   - Test contribution creation logic
   - Test recurring contribution scheduling
   - Test payment application

2. **Integration Testing**
   - Test interaction between contributions and payments
   - Test template application
   - Test import/export functionality

3. **API Testing**
   - Test all contribution endpoints
   - Verify correct status codes and responses
   - Test filtering and search functionality

4. **Functional Testing**
   - Test end-to-end contribution workflows
   - Test payment application
   - Test reporting functionality

## Acceptance Criteria

- Users can create and manage different types of contributions
- Recurring contributions function correctly
- Contribution templates can be applied to multiple users
- Payments can be recorded against contributions
- Reports show accurate contribution data
- Import/export functionality works correctly
- Notifications for due dates are sent appropriately
- All tests pass successfully

## Risks and Mitigation

1. **Risk**: Complexity in recurring contribution logic
   **Mitigation**: Thoroughly test different recurrence patterns and edge cases

2. **Risk**: Data integrity issues when recording payments
   **Mitigation**: Implement transaction management and validation checks

3. **Risk**: Performance issues with large contribution datasets
   **Mitigation**: Implement pagination and optimize queries

4. **Risk**: Security concerns with financial data
   **Mitigation**: Ensure proper access controls and audit trails

## Post-Release Activities

1. Monitor system performance
2. Collect user feedback on contribution management
3. Provide training on new features
4. Plan for version 1.3.0
5. Review and optimize database performance

## Documentation

- Updated API documentation
- User guide for contribution management
- Administrator guide for managing contribution types
- Import/export format specifications
- Reporting guide for contributions
