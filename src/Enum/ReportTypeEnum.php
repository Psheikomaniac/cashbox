<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Enhanced enum for report types with business logic.
 * 
 * This enum defines the available report types in the system and provides
 * business methods for report configuration and execution estimation.
 */
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
    
    public function getDescription(): string
    {
        return match($this) {
            self::FINANCIAL => 'Comprehensive financial overview including penalties, payments, and balances',
            self::PENALTY_SUMMARY => 'Summary of penalties by type, status, and team member',
            self::USER_ACTIVITY => 'Detailed user activity including penalties and payments',
            self::TEAM_OVERVIEW => 'Team statistics and member overview',
            self::PAYMENT_HISTORY => 'Complete payment history with transactions',
            self::AUDIT_LOG => 'System audit log with user actions and changes',
        };
    }
    
    public function getDefaultFormat(): string
    {
        return match($this) {
            self::FINANCIAL, self::PENALTY_SUMMARY => 'pdf',
            self::USER_ACTIVITY, self::TEAM_OVERVIEW => 'html',
            self::PAYMENT_HISTORY => 'excel',
            self::AUDIT_LOG => 'csv',
        };
    }
    
    /**
     * Get all report types formatted for frontend consumption.
     */
    public static function getAllForFrontend(): array
    {
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => $case->getLabel(),
                'description' => $case->getDescription(),
                'estimatedTime' => $case->getEstimatedExecutionTime(),
                'requiresAsync' => $case->requiresAsync(),
                'requiredParameters' => $case->getRequiredParameters(),
                'defaultFormat' => $case->getDefaultFormat(),
            ],
            self::cases()
        );
    }
}