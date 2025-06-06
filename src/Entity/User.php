<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\UserRepository;
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
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']]
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('email')]
class User
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
    private ?string $emailValue = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $phoneNumberValue = null;

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
        ?Email $email = null,
        ?PhoneNumber $phoneNumber = null
    ) {
        $this->id = Uuid::uuid7();
        $this->name = $name;
        $this->emailValue = $email?->getValue();
        $this->phoneNumberValue = $phoneNumber?->getValue();
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

    public function setPreference(string $key, string|int|float|bool|array|null $value): void
    {
        $this->preferences[$key] = $value;
    }

    public function getPreference(string $key, string|int|float|bool|array|null $default = null): string|int|float|bool|array|null
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
}
