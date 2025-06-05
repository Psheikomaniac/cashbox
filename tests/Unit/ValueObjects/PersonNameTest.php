<?php

declare(strict_types=1);

namespace App\Tests\Unit\ValueObjects;

use App\ValueObject\PersonName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PersonNameTest extends TestCase
{
    public function testCanBeCreatedWithValidNames(): void
    {
        $name = new PersonName('John', 'Doe');

        $this->assertSame('John', $name->getFirstName());
        $this->assertSame('Doe', $name->getLastName());
        $this->assertSame('John Doe', $name->getFullName());
    }

    public function testTrimsWhitespaceFromNames(): void
    {
        $name = new PersonName('  John  ', '  Doe  ');

        $this->assertSame('John', $name->getFirstName());
        $this->assertSame('Doe', $name->getLastName());
    }

    public function testGeneratesCorrectInitials(): void
    {
        $name = new PersonName('John', 'Doe');

        $this->assertSame('JD', $name->getInitials());
    }

    public function testGeneratesCorrectInitialsForUnicodeCharacters(): void
    {
        $name = new PersonName('José', 'García');

        $this->assertSame('JG', $name->getInitials());
    }

    public function testCanCompareEquality(): void
    {
        $name1 = new PersonName('John', 'Doe');
        $name2 = new PersonName('John', 'Doe');
        $name3 = new PersonName('Jane', 'Doe');

        $this->assertTrue($name1->equals($name2));
        $this->assertFalse($name1->equals($name3));
    }

    public function testThrowsExceptionForEmptyFirstName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PersonName('', 'Doe');
    }

    public function testThrowsExceptionForEmptyLastName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PersonName('John', '');
    }
}