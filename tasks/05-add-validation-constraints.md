# Add Validation & Constraints

## Current Issues

1. **Missing Validation Constraints on Entities**
   - Entities lack proper validation annotations
   - No validation for required fields, string lengths, numeric ranges, etc.
   - Example: User entity has no validation for email format or required fields

2. **No Unique Constraints**
   - Missing unique constraints for fields that should be unique
   - Example: User email should be unique but has no constraint

3. **Inconsistent Validation**
   - Some controllers perform validation, others don't
   - No standardized approach to validation
   - Example from UserController:
   ```php
   $errors = $this->validator->validate($user);
   if (count($errors) > 0) {
       return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
   }
   ```

4. **No Value Objects**
   - Using primitive types for complex values
   - No encapsulation of validation logic in value objects
   - Example: Money values are represented as simple floats without validation

## Recommended Actions

1. **Add Symfony Validators to Entities**
   - Use Symfony's validation annotations/attributes on all entities
   - Example for User entity:
   ```php
   use Symfony\Component\Validator\Constraints as Assert;

   class User
   {
       #[Assert\NotBlank]
       #[Assert\Length(min: 2, max: 50)]
       private string $firstName;

       #[Assert\NotBlank]
       #[Assert\Length(min: 2, max: 50)]
       private string $lastName;

       #[Assert\Email]
       #[Assert\NotBlank]
       private ?string $email = null;

       #[Assert\Regex(pattern: '/^\+?[0-9]{10,15}$/')]
       private ?string $phoneNumber = null;
   }
   ```

2. **Add Database Constraints**
   - Implement unique constraints at the database level
   - Example for User entity:
   ```php
   #[ORM\Entity(repositoryClass: UserRepository::class)]
   #[ORM\Table(name: '`user`')]
   #[ORM\UniqueConstraint(name: "UNIQ_USER_EMAIL", columns: ["email"])]
   class User
   {
       // ...
   }
   ```

3. **Create Value Objects**
   - Develop value objects for complex values like Money, Email, PhoneNumber
   - Encapsulate validation logic within these objects
   - Example Money value object:
   ```php
   namespace App\ValueObject;

   use Symfony\Component\Validator\Constraints as Assert;

   class Money
   {
       #[Assert\NotNull]
       #[Assert\GreaterThanOrEqual(0)]
       private float $amount;

       #[Assert\Currency]
       private string $currency;

       public function __construct(float $amount, string $currency = 'EUR')
       {
           $this->amount = $amount;
           $this->currency = $currency;
       }

       public function getAmount(): float
       {
           return $this->amount;
       }

       public function getCurrency(): string
       {
           return $this->currency;
       }

       public function __toString(): string
       {
           return sprintf('%.2f %s', $this->amount, $this->currency);
       }
   }
   ```

4. **Standardize Validation Approach**
   - Create a centralized validation service
   - Use validation groups to control validation in different contexts
   - Implement consistent validation error handling

5. **Add Input Validation for API Requests**
   - Validate request data before processing
   - Use DTO classes with validation constraints
   - Return standardized validation error responses

## Implementation Priority

This task should be addressed with medium priority after fixing the security issues, standardizing the API approach, and implementing error handling. Proper validation is essential for data integrity and preventing invalid data from entering the system.
