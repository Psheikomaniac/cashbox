<?php

namespace App\Doctrine\Type;

use App\Enum\CurrencyEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class CurrencyEnumType extends Type
{
    public const NAME = 'currency_enum';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL([
            'length' => 3,
        ]);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof CurrencyEnum) {
            return $value->value;
        }

        throw new \InvalidArgumentException('Expected CurrencyEnum instance');
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?CurrencyEnum
    {
        if ($value === null) {
            return null;
        }

        return CurrencyEnum::from($value);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
