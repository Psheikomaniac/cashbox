<?php

declare(strict_types=1);

use App\ValueObject\Email;

describe('Email', function () {
    it('can be created with valid email', function () {
        $email = new Email('test@example.com');

        expect($email->getValue())->toBe('test@example.com')
            ->and((string) $email)->toBe('test@example.com');
    });

    it('normalizes email to lowercase', function () {
        $email = new Email('Test@EXAMPLE.COM');

        expect($email->getValue())->toBe('test@example.com');
    });

    it('trims whitespace', function () {
        $email = new Email('  test@example.com  ');

        expect($email->getValue())->toBe('test@example.com');
    });

    it('extracts domain correctly', function () {
        $email = new Email('user@example.com');

        expect($email->getDomain())->toBe('example.com');
    });

    it('throws exception for invalid email', function () {
        new Email('invalid-email');
    })->throws(InvalidArgumentException::class);

    it('throws exception for empty email', function () {
        new Email('');
    })->throws(InvalidArgumentException::class);

    it('can compare equality', function () {
        $email1 = new Email('test@example.com');
        $email2 = new Email('TEST@EXAMPLE.COM');
        $email3 = new Email('other@example.com');

        expect($email1->equals($email2))->toBeTrue()
            ->and($email1->equals($email3))->toBeFalse();
    });
});