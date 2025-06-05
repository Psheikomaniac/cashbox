<?php

declare(strict_types=1);

namespace App\Tests\Unit\ValueObjects;

use App\Enum\CurrencyEnum;
use App\ValueObject\Money;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function testCanBeCreatedWithAmountAndCurrency(): void
    {
        $money = new Money(150, CurrencyEnum::EUR);

        $this->assertSame(150, $money->getAmount());
        $this->assertSame(CurrencyEnum::EUR, $money->getCurrency());
    }

    public function testThrowsExceptionForNegativeAmount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Money(-100, CurrencyEnum::EUR);
    }

    public function testCanAddMoneyWithSameCurrency(): void
    {
        $money1 = new Money(100, CurrencyEnum::EUR);
        $money2 = new Money(50, CurrencyEnum::EUR);

        $result = $money1->add($money2);

        $this->assertSame(150, $result->getAmount());
        $this->assertSame(CurrencyEnum::EUR, $result->getCurrency());
    }

    public function testThrowsExceptionWhenAddingDifferentCurrencies(): void
    {
        $money1 = new Money(100, CurrencyEnum::EUR);
        $money2 = new Money(50, CurrencyEnum::USD);

        $this->expectException(DomainException::class);

        $money1->add($money2);
    }

    public function testCanSubtractMoneyWithSameCurrency(): void
    {
        $money1 = new Money(100, CurrencyEnum::EUR);
        $money2 = new Money(30, CurrencyEnum::EUR);

        $result = $money1->subtract($money2);

        $this->assertSame(70, $result->getAmount());
        $this->assertSame(CurrencyEnum::EUR, $result->getCurrency());
    }

    public function testThrowsExceptionWhenSubtractingDifferentCurrencies(): void
    {
        $money1 = new Money(100, CurrencyEnum::EUR);
        $money2 = new Money(30, CurrencyEnum::USD);

        $this->expectException(DomainException::class);

        $money1->subtract($money2);
    }

    public function testThrowsExceptionWhenSubtractionWouldResultInNegative(): void
    {
        $money1 = new Money(50, CurrencyEnum::EUR);
        $money2 = new Money(100, CurrencyEnum::EUR);

        $this->expectException(DomainException::class);

        $money1->subtract($money2);
    }

    public function testCanMultiplyByFactor(): void
    {
        $money = new Money(100, CurrencyEnum::EUR);

        $result = $money->multiply(1.5);

        $this->assertSame(150, $result->getAmount());
        $this->assertSame(CurrencyEnum::EUR, $result->getCurrency());
    }

    public function testFormatsAmountCorrectly(): void
    {
        $money = new Money(150, CurrencyEnum::EUR);

        $this->assertSame('1.50 €', $money->format());
    }

    public function testCanCompareEquality(): void
    {
        $money1 = new Money(100, CurrencyEnum::EUR);
        $money2 = new Money(100, CurrencyEnum::EUR);
        $money3 = new Money(100, CurrencyEnum::USD);
        $money4 = new Money(200, CurrencyEnum::EUR);

        $this->assertTrue($money1->equals($money2));
        $this->assertFalse($money1->equals($money3));
        $this->assertFalse($money1->equals($money4));
    }
}