<?php

declare(strict_types=1);

use App\ValueObject\PersonName;

describe('PersonName', function () {
    it('can be created with valid names', function () {
        $name = new PersonName('John', 'Doe');

        expect($name->getFirstName())->toBe('John')
            ->and($name->getLastName())->toBe('Doe')
            ->and($name->getFullName())->toBe('John Doe');
    });

    it('trims whitespace from names', function () {
        $name = new PersonName('  John  ', '  Doe  ');

        expect($name->getFirstName())->toBe('John')
            ->and($name->getLastName())->toBe('Doe');
    });

    it('generates correct initials', function () {
        $name = new PersonName('John', 'Doe');

        expect($name->getInitials())->toBe('JD');
    });

    it('generates correct initials for unicode characters', function () {
        $name = new PersonName('José', 'García');

        expect($name->getInitials())->toBe('JG');
    });

    it('can compare equality', function () {
        $name1 = new PersonName('John', 'Doe');
        $name2 = new PersonName('John', 'Doe');
        $name3 = new PersonName('Jane', 'Doe');

        expect($name1->equals($name2))->toBeTrue()
            ->and($name1->equals($name3))->toBeFalse();
    });

    it('throws exception for empty first name', function () {
        new PersonName('', 'Doe');
    })->throws(InvalidArgumentException::class);

    it('throws exception for empty last name', function () {
        new PersonName('John', '');
    })->throws(InvalidArgumentException::class);
});