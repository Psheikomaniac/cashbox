<?php

declare(strict_types=1);

use App\Enum\CurrencyEnum;
use App\ValueObject\Money;

describe('Money', function () {
    it('can be created with amount and currency', function () {
        $money = new Money(150, CurrencyEnum::EUR);

        expect($money->getAmount())->toBe(150)
            ->and($money->getCurrency())->toBe(CurrencyEnum::EUR);
    });

    it('throws exception for negative amount', function () {
        new Money(-100, CurrencyEnum::EUR);
    })->throws(InvalidArgumentException::class);

    it('can add money with same currency', function () {
        $money1 = new Money(100, CurrencyEnum::EUR);
        $money2 = new Money(50, CurrencyEnum::EUR);

        $result = $money1->add($money2);

        expect($result->getAmount())->toBe(150)
            ->and($result->getCurrency())->toBe(CurrencyEnum::EUR);
    });

    it('throws exception when adding different currencies', function () {
        $money1 = new Money(100, CurrencyEnum::EUR);
        $money2 = new Money(50, CurrencyEnum::USD);

        $money1->add($money2);
    })->throws(DomainException::class);

    it('can subtract money with same currency', function () {
        $money1 = new Money(100, CurrencyEnum::EUR);
        $money2 = new Money(30, CurrencyEnum::EUR);

        $result = $money1->subtract($money2);

        expect($result->getAmount())->toBe(70)
            ->and($result->getCurrency())->toBe(CurrencyEnum::EUR);
    });

    it('throws exception when subtracting different currencies', function () {
        $money1 = new Money(100, CurrencyEnum::EUR);
        $money2 = new Money(30, CurrencyEnum::USD);

        $money1->subtract($money2);
    })->throws(DomainException::class);

    it('throws exception when subtraction would result in negative', function () {
        $money1 = new Money(50, CurrencyEnum::EUR);
        $money2 = new Money(100, CurrencyEnum::EUR);

        $money1->subtract($money2);
    })->throws(DomainException::class);

    it('can multiply by factor', function () {
        $money = new Money(100, CurrencyEnum::EUR);

        $result = $money->multiply(1.5);

        expect($result->getAmount())->toBe(150)
            ->and($result->getCurrency())->toBe(CurrencyEnum::EUR);
    });

    it('formats amount correctly', function () {
        $money = new Money(150, CurrencyEnum::EUR);

        expect($money->format())->toBe('1.50 €');
    });

    it('can compare equality', function () {
        $money1 = new Money(100, CurrencyEnum::EUR);
        $money2 = new Money(100, CurrencyEnum::EUR);
        $money3 = new Money(100, CurrencyEnum::USD);
        $money4 = new Money(200, CurrencyEnum::EUR);

        expect($money1->equals($money2))->toBeTrue()
            ->and($money1->equals($money3))->toBeFalse()
            ->and($money1->equals($money4))->toBeFalse();
    });
});