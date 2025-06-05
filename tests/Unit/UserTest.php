<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\User;
use App\ValueObject\Email;
use App\ValueObject\PersonName;
use App\ValueObject\PhoneNumber;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCanBeCreatedWithPersonName(): void
    {
        $name = new PersonName('John', 'Doe');
        $user = new User($name);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('John Doe', $user->getName()->getFullName());
        $this->assertTrue($user->isActive());
    }

    public function testCanBeCreatedWithEmailAndPhone(): void
    {
        $name = new PersonName('Jane', 'Smith');
        $email = new Email('jane@example.com');
        $phone = new PhoneNumber('+1234567890');
        
        $user = new User($name, $email, $phone);

        $this->assertSame('jane@example.com', $user->getEmail()->getValue());
        $this->assertSame('+1234567890', $user->getPhoneNumber()->getValue());
    }

    public function testCanUpdateProfileInformation(): void
    {
        $user = new User(
            new PersonName('John', 'Doe'),
            new Email('john@example.com')
        );

        $newName = new PersonName('Jane', 'Smith');
        $newEmail = new Email('jane@example.com');

        $user->updateProfile($newName, $newEmail);

        $this->assertSame('Jane Smith', $user->getName()->getFullName());
        $this->assertSame('jane@example.com', $user->getEmail()->getValue());
    }

    public function testCanManagePreferences(): void
    {
        $user = new User(new PersonName('John', 'Doe'));
        
        $user->setPreference('language', 'en');
        $user->setPreference('notifications', true);

        $this->assertSame('en', $user->getPreference('language'));
        $this->assertTrue($user->getPreference('notifications'));
        $this->assertSame('default', $user->getPreference('nonexistent', 'default'));
    }

    public function testProvidesLegacyCompatibilityMethods(): void
    {
        $user = new User(new PersonName('John', 'Doe'));

        $this->assertSame('John', $user->getFirstName());
        $this->assertSame('Doe', $user->getLastName());
        $this->assertSame('John Doe', $user->getFullName());

        $user->setFirstName('Jane');
        $user->setLastName('Smith');

        $this->assertSame('Jane', $user->getFirstName());
        $this->assertSame('Smith', $user->getLastName());
    }
}