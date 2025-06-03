<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\NotificationTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Modern readonly DTO for notification creation.
 */
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