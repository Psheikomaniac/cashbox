<?php

namespace App\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Webmozart\Assert\Assert as WebmozartAssert;

#[ORM\Embeddable]
final class PersonName
{
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private string $firstName;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private string $lastName;

    public function __construct(string $firstName, string $lastName)
    {
        WebmozartAssert::notEmpty($firstName, 'First name cannot be empty');
        WebmozartAssert::notEmpty($lastName, 'Last name cannot be empty');

        $this->firstName = trim($firstName);
        $this->lastName = trim($lastName);
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return sprintf('%s %s', $this->firstName, $this->lastName);
    }

    public function getInitials(): string
    {
        return strtoupper(
            mb_substr($this->firstName, 0, 1) .
            mb_substr($this->lastName, 0, 1)
        );
    }

    public function equals(self $other): bool
    {
        return $this->firstName === $other->firstName
            && $this->lastName === $other->lastName;
    }
}