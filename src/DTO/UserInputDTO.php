<?php

namespace App\DTO;

class UserInputDTO
{
    public string $firstName;
    public string $lastName;
    public ?string $email = null;
    public ?string $phoneNumber = null;
    public bool $active = true;
}
