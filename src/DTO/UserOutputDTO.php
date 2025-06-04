<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\User;

/**
 * Modern PHP 8.4 readonly output DTO with asymmetric visibility
 */
final readonly class UserOutputDTO
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
        public ?string $email,
        public ?string $phoneNumber,
        public bool $active,
        public string $fullName,
        public string $createdAt,
        public string $updatedAt,
        public array $metadata = []
    ) {}

    /**
     * Create from User entity following documentation standards
     */
    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId()->toString(),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            email: $user->getEmail()?->getValue(),
            phoneNumber: $user->getPhoneNumber()?->getValue(),
            active: $user->isActive(),
            fullName: $user->getFullName(),
            createdAt: $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $user->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            metadata: [
                'hasEmail' => $user->getEmail() !== null,
                'hasPhoneNumber' => $user->getPhoneNumber() !== null,
                'memberSince' => $user->getCreatedAt()->format('Y-m-d'),
                'lastActivity' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * Legacy compatibility method - will be removed in future versions
     * @deprecated Use fromEntity() instead
     */
    public static function createFromEntity(User $user): self
    {
        return self::fromEntity($user);
    }

    /**
     * Convert to array for API responses
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'fullName' => $this->fullName,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'active' => $this->active,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Get user initials for UI
     */
    public function getInitials(): string
    {
        return strtoupper(substr($this->firstName, 0, 1) . substr($this->lastName, 0, 1));
    }

    /**
     * Check if user is recently created (within last 30 days)
     */
    public function isNewUser(): bool
    {
        $createdDate = new \DateTimeImmutable($this->createdAt);
        $thirtyDaysAgo = new \DateTimeImmutable('-30 days');
        
        return $createdDate > $thirtyDaysAgo;
    }
}
