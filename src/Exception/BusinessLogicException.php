<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Modern PHP 8.4 Exception for business logic violations
 */
final class BusinessLogicException extends BadRequestHttpException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }

    public static function userAlreadyExists(string $email): self
    {
        return new self("User with email '$email' already exists");
    }

    public static function inactiveUser(): self
    {
        return new self('User account is inactive');
    }

    public static function contributionAlreadyPaid(): self
    {
        return new self('Contribution has already been paid');
    }

    public static function insufficientPermissions(): self
    {
        return new self('Insufficient permissions for this operation');
    }

    public static function teamMembershipRequired(): self
    {
        return new self('User must be a team member to perform this action');
    }

    public static function invalidPaymentAmount(): self
    {
        return new self('Payment amount must be positive');
    }

    public static function reportGenerationFailed(): self
    {
        return new self('Report generation failed');
    }
}