<?php

namespace App\ValueObject;

use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

final class PhoneNumber implements \Stringable
{
    #[Assert\NotBlank(message: "Phone number cannot be blank.")]
    #[Assert\Regex(
        pattern: '/^[0-9+\s\-()]{7,20}$/',
        message: 'The phone number must be between 7 and 20 digits and can contain +, spaces, hyphens, and parentheses.'
    )]
    private string $value;

    public function __construct(string $phoneNumber)
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);
        if ($cleaned === null) {
            throw new InvalidArgumentException('Invalid phone number format');
        }

        if (strlen($cleaned) < 7 || strlen($cleaned) > 20) {
            throw new InvalidArgumentException('Invalid phone number length');
        }

        $this->value = $cleaned;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getFormatted(): string
    {
        // Simple formatting, can be enhanced with libphonenumber
        if (str_starts_with($this->value, '+49')) {
            $formatted = preg_replace('/(\+49)(\d{3})(\d+)/', '$1 $2 $3', $this->value);
            return $formatted ?? $this->value;
        }

        return $this->value;
    }

    public function __toString(): string
    {
        return $this->getFormatted();
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
