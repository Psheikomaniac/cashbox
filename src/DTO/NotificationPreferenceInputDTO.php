<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\NotificationTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Modern readonly DTO for notification preference updates.
 */
readonly class NotificationPreferenceInputDTO
{
    public function __construct(
        #[Assert\NotNull]
        public NotificationTypeEnum $notificationType,
        
        public bool $emailEnabled = true,
        public bool $inAppEnabled = true,
    ) {}
}
