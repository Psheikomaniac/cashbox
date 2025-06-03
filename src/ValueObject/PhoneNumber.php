<?php

namespace App\ValueObject;

use InvalidArgumentException;

final class PhoneNumber implements \Stringable
{
    private string $value;

    public function __construct(string $phoneNumber)
    {
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);

        if (strlen($phoneNumber) < 7 || strlen($phoneNumber) > 20) {
            throw new InvalidArgumentException('Invalid phone number');
        }

        $this->value = $phoneNumber;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getFormatted(): string
    {
        // Simple formatting, can be enhanced with libphonenumber
        if (str_starts_with($this->value, '+49')) {
            return preg_replace('/(\+49)(\d{3})(\d+)/', '$1 $2 $3', $this->value);
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