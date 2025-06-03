<?php

namespace App\ValueObject;

use App\Enum\CurrencyEnum;
use DomainException;
use InvalidArgumentException;

final class Money
{
    private int $amount;
    private CurrencyEnum $currency;

    public function __construct(int $amount, CurrencyEnum $currency)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): CurrencyEnum
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new DomainException('Cannot add money with different currencies');
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new DomainException('Cannot subtract money with different currencies');
        }

        if ($this->amount < $other->amount) {
            throw new DomainException('Result would be negative');
        }

        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self((int) round($this->amount * $factor), $this->currency);
    }

    public function format(): string
    {
        return $this->currency->formatAmount($this->amount);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }
}