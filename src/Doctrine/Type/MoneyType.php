<?php

namespace App\Doctrine\Type;

use App\Enum\CurrencyEnum;
use App\ValueObject\Money;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class MoneyType extends Type
{
    public const NAME = 'money';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Money) {
            throw new \InvalidArgumentException('Expected Money instance');
        }

        return $value->getAmount();
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Money
    {
        if ($value === null) {
            return null;
        }

        // Note: This is a simplification. In a real application, you would need to store
        // and retrieve the currency as well. Here we're defaulting to EUR.
        return new Money((int) $value, CurrencyEnum::EUR);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
