<?php

namespace App\Doctrine\Type;

use App\Enum\PaymentTypeEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class PaymentTypeEnumType extends Type
{
    public const NAME = 'payment_type_enum';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL([
            'length' => 30,
        ]);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof PaymentTypeEnum) {
            return $value->value;
        }

        throw new \InvalidArgumentException('Expected PaymentTypeEnum instance');
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?PaymentTypeEnum
    {
        if ($value === null) {
            return null;
        }

        return PaymentTypeEnum::from($value);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
