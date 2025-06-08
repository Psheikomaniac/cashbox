<?php

declare(strict_types=1);

namespace App\Tests\Unit\ValueObjects;

use App\ValueObject\PhoneNumber;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function testCanBeCreatedWithValidPhoneNumber(): void
    {
        $phoneNumber = new PhoneNumber('+491234567890');

        $this->assertSame('+491234567890', $phoneNumber->getValue());
    }

    public function testCleansNonNumericCharacters(): void
    {
        $phoneNumber = new PhoneNumber('+49 (123) 456-7890');

        $this->assertSame('+491234567890', $phoneNumber->getValue());
    }

    public function testThrowsExceptionForInvalidPhoneNumberLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid phone number length');

        new PhoneNumber('123456'); // Too short
    }

    public function testThrowsExceptionForTooLongPhoneNumber(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid phone number length');

        new PhoneNumber('123456789012345678901'); // Too long (21 digits)
    }

    public function testFormatsGermanPhoneNumberCorrectly(): void
    {
        $phoneNumber = new PhoneNumber('+49123456789');

        $this->assertSame('+49 123 456789', $phoneNumber->getFormatted());
    }

    public function testDoesNotFormatNonGermanPhoneNumbers(): void
    {
        $phoneNumber = new PhoneNumber('+11234567890');

        $this->assertSame('+11234567890', $phoneNumber->getFormatted());
    }

    public function testToStringReturnsFormattedNumber(): void
    {
        $phoneNumber = new PhoneNumber('+49123456789');

        $this->assertSame('+49 123 456789', (string) $phoneNumber);
    }

    public function testCanCompareEquality(): void
    {
        $phoneNumber1 = new PhoneNumber('+491234567890');
        $phoneNumber2 = new PhoneNumber('+49 (123) 456-7890'); // Same after cleaning
        $phoneNumber3 = new PhoneNumber('+491234567891'); // Different

        $this->assertTrue($phoneNumber1->equals($phoneNumber2));
        $this->assertFalse($phoneNumber1->equals($phoneNumber3));
    }
}
