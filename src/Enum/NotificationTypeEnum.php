<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Type-safe notification categories with business logic.
 * 
 * This enum defines notification types and provides methods for
 * categorization, prioritization, and display configuration.
 */
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
            self::PENALTY_CREATED => 3, // High priority
            self::PAYMENT_REMINDER => 3,
            self::PAYMENT_RECEIVED => 2, // Medium priority
            self::BALANCE_UPDATE => 2,
            self::REPORT_GENERATED => 1, // Low priority
            self::SYSTEM_UPDATE => 1,
        };
    }
    
    public function getColor(): string
    {
        return match($this) {
            self::PENALTY_CREATED => 'red',
            self::PAYMENT_REMINDER => 'orange',
            self::PAYMENT_RECEIVED => 'green',
            self::BALANCE_UPDATE => 'blue',
            self::REPORT_GENERATED => 'purple',
            self::SYSTEM_UPDATE => 'gray',
        };
    }
    
    public function isActionRequired(): bool
    {
        return match($this) {
            self::PENALTY_CREATED, self::PAYMENT_REMINDER => true,
            self::PAYMENT_RECEIVED, self::BALANCE_UPDATE, 
            self::REPORT_GENERATED, self::SYSTEM_UPDATE => false,
        };
    }
    
    public function getDefaultTitle(array $data = []): string
    {
        return match($this) {
            self::PENALTY_CREATED => 'New penalty assigned',
            self::PAYMENT_RECEIVED => 'Payment confirmed',
            self::PAYMENT_REMINDER => 'Payment due reminder',
            self::BALANCE_UPDATE => 'Balance updated',
            self::REPORT_GENERATED => 'Report ready for download',
            self::SYSTEM_UPDATE => 'System notification',
        };
    }
    
    public function shouldSendEmail(): bool
    {
        return match($this) {
            self::PENALTY_CREATED, self::PAYMENT_REMINDER, 
            self::PAYMENT_RECEIVED => true,
            self::BALANCE_UPDATE, self::REPORT_GENERATED, 
            self::SYSTEM_UPDATE => false,
        };
    }
    
    public function getRetentionDays(): int
    {
        return match($this) {
            self::PENALTY_CREATED, self::PAYMENT_RECEIVED => 365, // Keep for 1 year
            self::PAYMENT_REMINDER => 90, // Keep for 3 months
            self::BALANCE_UPDATE => 30, // Keep for 1 month
            self::REPORT_GENERATED => 7, // Keep for 1 week
            self::SYSTEM_UPDATE => 30, // Keep for 1 month
        };
    }
    
    /**
     * Get notification types by priority level.
     */
    public static function getByPriority(int $priority): array
    {
        return array_filter(
            self::cases(),
            fn (self $case) => $case->getPriority() === $priority
        );
    }
    
    /**
     * Get all notification types formatted for frontend consumption.
     */
    public static function getAllForFrontend(): array
    {
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => $case->getLabel(),
                'icon' => $case->getIcon(),
                'priority' => $case->getPriority(),
                'color' => $case->getColor(),
                'actionRequired' => $case->isActionRequired(),
                'defaultEmailEnabled' => $case->shouldSendEmail(),
                'retentionDays' => $case->getRetentionDays(),
            ],
            self::cases()
        );
    }
}