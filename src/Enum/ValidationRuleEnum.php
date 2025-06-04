<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Modern PHP 8.4 enum for input validation with enhanced performance
 */
enum ValidationRuleEnum: string
{
    case EMAIL = 'email';
    case UUID = 'uuid';
    case PHONE = 'phone';
    case AMOUNT = 'amount';
    case NAME = 'name';
    case CURRENCY = 'currency';
    
    /**
     * Validate input according to the rule type
     * Optimized for PHP 8.4 JIT compilation
     */
    public function validate(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }
        
        return match($this) {
            self::EMAIL => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            self::UUID => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-7][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1,
            self::PHONE => preg_match('/^\+?[1-9]\d{1,14}$/', preg_replace('/[\s\-\(\)]/', '', $value)) === 1,
            self::AMOUNT => is_numeric($value) && $value > 0,
            self::NAME => preg_match('/^[a-zA-Z\s\-\'\.]{1,100}$/u', trim($value)) === 1,
            self::CURRENCY => preg_match('/^[A-Z]{3}$/', $value) === 1,
        };
    }
    
    /**
     * Get validation error message
     */
    public function getErrorMessage(): string
    {
        return match($this) {
            self::EMAIL => 'Please provide a valid email address',
            self::UUID => 'Invalid UUID format',
            self::PHONE => 'Please provide a valid phone number',
            self::AMOUNT => 'Amount must be a positive number',
            self::NAME => 'Name can only contain letters, spaces, hyphens, apostrophes, and periods',
            self::CURRENCY => 'Currency must be a 3-letter ISO code',
        };
    }
    
    /**
     * Sanitize input according to the rule type
     */
    public function sanitize(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }
        
        return match($this) {
            self::EMAIL => strtolower(trim($value)),
            self::UUID => strtolower(trim($value)),
            self::PHONE => preg_replace('/[\s\-\(\)]/', '', trim($value)),
            self::AMOUNT => is_numeric($value) ? (float) $value : 0,
            self::NAME => trim($value),
            self::CURRENCY => strtoupper(trim($value)),
        };
    }
    
    /**
     * Get example valid value for documentation/testing
     */
    public function getExample(): string
    {
        return match($this) {
            self::EMAIL => 'user@example.com',
            self::UUID => '550e8400-e29b-41d4-a716-446655440000',
            self::PHONE => '+1234567890',
            self::AMOUNT => '99.99',
            self::NAME => 'John Doe',
            self::CURRENCY => 'EUR',
        };
    }
}