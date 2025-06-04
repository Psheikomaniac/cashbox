<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Enum\UserRoleEnum;
use App\ValueObject\Email;
use App\ValueObject\PersonName;
use App\ValueObject\PhoneNumber;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['emailValue'], message: 'This email is already registered')]
#[Assert\GroupSequence(['User', 'Strict'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['user:read'])]
    private UuidInterface $id;

    #[ORM\Embedded(class: PersonName::class)]
    #[Groups(['user:read', 'user:write'])]
    private PersonName $name;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\Email(message: 'Please provide a valid email address')]
    #[Assert\Length(max: 255, maxMessage: 'Email cannot be longer than 255 characters')]
    private ?string $emailValue = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\Length(max: 50, maxMessage: 'Phone number cannot be longer than 50 characters')]
    #[Assert\Regex(
        pattern: '/^\+?[1-9]\d{1,14}$/',
        message: 'Please provide a valid phone number'
    )]
    private ?string $phoneNumberValue = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Length(min: 8, minMessage: 'Password must be at least 8 characters long')]
    private string $password;

    #[ORM\Column(type: 'json')]
    #[Groups(['user:read'])]
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Choice(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_MANAGER', 'ROLE_TREASURER'])
    ])]
    private array $roles = [];

    #[ORM\Column(type: 'boolean')]
    #[Groups(['user:read'])]
    private bool $active = true;

    #[ORM\Column(type: 'json')]
    private array $preferences = [];

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['user:read'])]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups(['user:read'])]
    private DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: TeamUser::class)]
    private Collection $teamUsers;

    public function __construct(
        PersonName $name,
        string $password,
        ?Email $email = null,
        ?PhoneNumber $phoneNumber = null,
        array $roles = ['ROLE_USER']
    ) {
        $this->id = Uuid::uuid7();
        $this->name = $name;
        $this->password = $password;
        $this->emailValue = $email?->getValue();
        $this->phoneNumberValue = $phoneNumber?->getValue();
        $this->roles = $roles;
        $this->teamUsers = new ArrayCollection();
    }

    public function updateProfile(
        PersonName $name,
        ?Email $email = null,
        ?PhoneNumber $phoneNumber = null
    ): void {
        $this->name = $name;
        $this->emailValue = $email?->getValue();
        $this->phoneNumberValue = $phoneNumber?->getValue();
    }

    public function setPreference(string $key, mixed $value): void
    {
        $this->preferences[$key] = $value;
    }

    public function getPreference(string $key, mixed $default = null): mixed
    {
        return $this->preferences[$key] ?? $default;
    }

    // Getters
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): PersonName
    {
        return $this->name;
    }

    public function getEmail(): ?Email
    {
        return $this->emailValue ? new Email($this->emailValue) : null;
    }

    public function getPhoneNumber(): ?PhoneNumber
    {
        return $this->phoneNumberValue ? new PhoneNumber($this->phoneNumberValue) : null;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPreferences(): array
    {
        return $this->preferences;
    }

    // Legacy compatibility methods - to be removed in future versions
    public function getFirstName(): string
    {
        return $this->name->getFirstName();
    }

    public function setFirstName(string $firstName): self
    {
        $this->name = new PersonName($firstName, $this->name->getLastName());
        return $this;
    }

    public function getLastName(): string
    {
        return $this->name->getLastName();
    }

    public function setLastName(string $lastName): self
    {
        $this->name = new PersonName($this->name->getFirstName(), $lastName);
        return $this;
    }

    public function getFullName(): string
    {
        return $this->name->getFullName();
    }

    public function setEmail(?string $email): self
    {
        $this->emailValue = $email;
        return $this;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumberValue = $phoneNumber;
        return $this;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    // UserInterface implementation
    public function getUserIdentifier(): string
    {
        return $this->emailValue ?? $this->id->toString();
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    // PasswordAuthenticatedUserInterface implementation
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
}
