<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\ValidationRuleEnum;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Modern PHP 8.4 DTO with property hooks for validation
 * Note: Cannot be readonly due to property hooks limitation in PHP 8.4
 */
final class UserInputDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'First name is required')]
        #[Assert\Length(max: 100, maxMessage: 'First name cannot be longer than {{ limit }} characters')]
        public string $firstName {
            set => trim($value) ?: throw new \InvalidArgumentException('First name cannot be empty');
        },
        
        #[Assert\NotBlank(message: 'Last name is required')]
        #[Assert\Length(max: 100, maxMessage: 'Last name cannot be longer than {{ limit }} characters')]
        public string $lastName {
            set => trim($value) ?: throw new \InvalidArgumentException('Last name cannot be empty');
        },
        
        #[Assert\Email(message: 'Please provide a valid email address')]
        #[Assert\Length(max: 255, maxMessage: 'Email cannot be longer than {{ limit }} characters')]
        public ?string $email = null {
            set => $value !== null 
                ? (ValidationRuleEnum::EMAIL->validate($value) 
                    ? strtolower(trim($value)) 
                    : throw new \InvalidArgumentException('Invalid email format'))
                : null;
        },
        
        public ?string $phoneNumber = null {
            set => $value !== null 
                ? (ValidationRuleEnum::PHONE->validate($value) 
                    ? $value 
                    : throw new \InvalidArgumentException('Invalid phone number format'))
                : null;
        },
        
        public bool $active = true
    ) {}
    
    /**
     * Create from array data with validation
     */
    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['firstName'] ?? throw new \InvalidArgumentException('firstName is required'),
            lastName: $data['lastName'] ?? throw new \InvalidArgumentException('lastName is required'),
            email: $data['email'] ?? null,
            phoneNumber: $data['phoneNumber'] ?? null,
            active: $data['active'] ?? true
        );
    }
    
    /**
     * Convert to array for serialization
     */
    public function toArray(): array
    {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'active' => $this->active,
        ];
    }
}
