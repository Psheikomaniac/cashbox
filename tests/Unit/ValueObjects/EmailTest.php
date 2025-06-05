<?php

declare(strict_types=1);

namespace App\Tests\Unit\ValueObjects;

use App\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testCanBeCreatedWithValidEmail(): void
    {
        $email = new Email('test@example.com');

        $this->assertSame('test@example.com', $email->getValue());
        $this->assertSame('test@example.com', (string) $email);
    }

    public function testNormalizesEmailToLowercase(): void
    {
        $email = new Email('Test@EXAMPLE.COM');

        $this->assertSame('test@example.com', $email->getValue());
    }

    public function testTrimsWhitespace(): void
    {
        $email = new Email('  test@example.com  ');

        $this->assertSame('test@example.com', $email->getValue());
    }

    public function testExtractsDomainCorrectly(): void
    {
        $email = new Email('user@example.com');

        $this->assertSame('example.com', $email->getDomain());
    }

    public function testThrowsExceptionForInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('invalid-email');
    }

    public function testThrowsExceptionForEmptyEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('');
    }

    public function testCanCompareEquality(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('TEST@EXAMPLE.COM');
        $email3 = new Email('other@example.com');

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }
}