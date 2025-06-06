<?php

namespace App\DTO\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class LoginRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email darf nicht leer sein')]
        #[Assert\Email(message: 'Ungültige Email-Adresse')]
        public string $email,

        #[Assert\NotBlank(message: 'Passwort darf nicht leer sein')]
        #[Assert\Length(
            min: 8,
            minMessage: 'Passwort muss mindestens {{ limit }} Zeichen haben'
        )]
        public string $password
    ) {}
}
