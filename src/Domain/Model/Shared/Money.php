<?php

namespace App\Domain\Model\Shared;

use InvalidArgumentException;

readonly class Money
{
    private int $amount;
    private CurrencyEnum $currency;

    public function __construct(int $amount, CurrencyEnum $currency = CurrencyEnum::EUR)
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

    public function getFormattedAmount(): string
    {
        return $this->currency->formatAmount($this->amount);
    }

    public function add(Money $money): self
    {
        if (!$this->isSameCurrency($money)) {
            throw new InvalidArgumentException('Cannot add money with different currencies');
        }

        return new self($this->amount + $money->getAmount(), $this->currency);
    }

    public function subtract(Money $money): self
    {
        if (!$this->isSameCurrency($money)) {
            throw new InvalidArgumentException('Cannot subtract money with different currencies');
        }

        $newAmount = $this->amount - $money->getAmount();

        if ($newAmount < 0) {
            throw new InvalidArgumentException('Result would be negative');
        }

        return new self($newAmount, $this->currency);
    }

    public function isSameCurrency(Money $money): bool
    {
        return $this->currency === $money->getCurrency();
    }

    public function equals(Money $money): bool
    {
        return $this->amount === $money->getAmount() && $this->isSameCurrency($money);
    }
}
