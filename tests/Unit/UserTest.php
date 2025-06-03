<?php

declare(strict_types=1);

use App\Entity\User;
use App\ValueObject\Email;
use App\ValueObject\PersonName;
use App\ValueObject\PhoneNumber;

describe('User', function () {
    it('can be created with person name', function () {
        $name = new PersonName('John', 'Doe');
        $user = new User($name);

        expect($user)
            ->toBeInstanceOf(User::class)
            ->and($user->getName()->getFullName())->toBe('John Doe')
            ->and($user->isActive())->toBeTrue();
    });

    it('can be created with email and phone', function () {
        $name = new PersonName('Jane', 'Smith');
        $email = new Email('jane@example.com');
        $phone = new PhoneNumber('+1234567890');
        
        $user = new User($name, $email, $phone);

        expect($user->getEmail()->getValue())->toBe('jane@example.com')
            ->and($user->getPhoneNumber()->getValue())->toBe('+1234567890');
    });

    it('can update profile information', function () {
        $user = new User(
            new PersonName('John', 'Doe'),
            new Email('john@example.com')
        );

        $newName = new PersonName('Jane', 'Smith');
        $newEmail = new Email('jane@example.com');

        $user->updateProfile($newName, $newEmail);

        expect($user->getName()->getFullName())->toBe('Jane Smith')
            ->and($user->getEmail()->getValue())->toBe('jane@example.com');
    });

    it('can manage preferences', function () {
        $user = new User(new PersonName('John', 'Doe'));
        
        $user->setPreference('language', 'en');
        $user->setPreference('notifications', true);

        expect($user->getPreference('language'))->toBe('en')
            ->and($user->getPreference('notifications'))->toBeTrue()
            ->and($user->getPreference('nonexistent', 'default'))->toBe('default');
    });

    it('provides legacy compatibility methods', function () {
        $user = new User(new PersonName('John', 'Doe'));

        expect($user->getFirstName())->toBe('John')
            ->and($user->getLastName())->toBe('Doe')
            ->and($user->getFullName())->toBe('John Doe');

        $user->setFirstName('Jane');
        $user->setLastName('Smith');

        expect($user->getFirstName())->toBe('Jane')
            ->and($user->getLastName())->toBe('Smith');
    });
});