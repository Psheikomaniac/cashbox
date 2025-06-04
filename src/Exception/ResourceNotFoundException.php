<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Modern PHP 8.4 Exception for resource not found scenarios
 */
final class ResourceNotFoundException extends NotFoundHttpException
{
    public function __construct(string $resource, string $id, ?\Throwable $previous = null)
    {
        $message = sprintf('%s with ID "%s" not found', $resource, $id);
        parent::__construct($message, $previous);
    }

    public static function forUser(string $id): self
    {
        return new self('User', $id);
    }

    public static function forTeam(string $id): self
    {
        return new self('Team', $id);
    }

    public static function forContribution(string $id): self
    {
        return new self('Contribution', $id);
    }

    public static function forPayment(string $id): self
    {
        return new self('Payment', $id);
    }

    public static function forPenalty(string $id): self
    {
        return new self('Penalty', $id);
    }

    public static function forReport(string $id): self
    {
        return new self('Report', $id);
    }
}