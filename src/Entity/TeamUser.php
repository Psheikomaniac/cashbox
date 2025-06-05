<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\DTO\TeamUser\CreateTeamUserDTO;
use App\DTO\TeamUser\UpdateTeamUserDTO;
use App\Enum\UserRoleEnum;
use App\Repository\TeamUserRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'TeamUser',
    operations: [
        new GetCollection(
            uriTemplate: '/team-users',
            security: "is_granted('ROLE_USER')",
            name: 'get_team_users'
        ),
        new Get(
            uriTemplate: '/team-users/{id}',
            requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'],
            security: "is_granted('TEAM_USER_VIEW', object)"
        ),
        new Post(
            uriTemplate: '/team-users',
            security: "is_granted('ROLE_ADMIN')",
            input: CreateTeamUserDTO::class
        ),
        new Put(
            uriTemplate: '/team-users/{id}',
            requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'],
            security: "is_granted('TEAM_USER_EDIT', object)",
            input: UpdateTeamUserDTO::class
        )
    ],
    formats: ['jsonld', 'json', 'csv'],
    normalizationContext: ['groups' => ['team_user:read']],
    denormalizationContext: ['groups' => ['team_user:write']],
    paginationItemsPerPage: 20
)]
#[ORM\Entity(repositoryClass: TeamUserRepository::class)]
#[ORM\Table(name: 'team_users')]
#[ORM\UniqueConstraint(columns: ['team_id', 'user_id'])]
class TeamUser
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['team_user:read'])]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'teamUsers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['team_user:read', 'team_user:write'])]
    private Team $team;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'teamUsers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['team_user:read', 'team_user:write'])]
    private User $user;

    #[ORM\Column(type: 'json')]
    #[Groups(['team_user:read', 'team_user:write'])]
    private array $roles = [];

    #[ORM\Column(type: 'boolean')]
    #[Groups(['team_user:read'])]
    private bool $active = true;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['team_user:read'])]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups(['team_user:read'])]
    private DateTimeImmutable $updatedAt;

    public function __construct(Team $team, User $user, array $roles = [])
    {
        $this->id = Uuid::uuid7();
        $this->team = $team;
        $this->user = $user;
        $this->setRoles($roles ?: [UserRoleEnum::MEMBER]);
    }

    /**
     * @return UserRoleEnum[]
     */
    public function getRoles(): array
    {
        return array_map(
            fn (string $role) => UserRoleEnum::from($role),
            $this->roles
        );
    }

    /**
     * @param UserRoleEnum[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = array_map(
            fn (UserRoleEnum $role) => $role->value,
            $roles
        );

        return $this;
    }

    public function addRole(UserRoleEnum $role): self
    {
        if (!in_array($role->value, $this->roles, true)) {
            $this->roles[] = $role->value;
        }

        return $this;
    }

    public function removeRole(UserRoleEnum $role): self
    {
        $key = array_search($role->value, $this->roles, true);
        if ($key !== false) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function hasRole(UserRoleEnum $role): bool
    {
        return in_array($role->value, $this->roles, true);
    }

    public function hasPermission(string $permission): bool
    {
        foreach ($this->getRoles() as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function getHighestRole(): UserRoleEnum
    {
        $roles = $this->getRoles();
        if (empty($roles)) {
            return UserRoleEnum::MEMBER;
        }

        usort($roles, fn(UserRoleEnum $a, UserRoleEnum $b) => $b->getPriority() <=> $a->getPriority());
        
        return $roles[0];
    }

    // Getters
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function getUser(): User
    {
        return $this->user;
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

    // Legacy compatibility methods - to be removed in future versions
    public function setTeam(Team $team): self
    {
        $this->team = $team;
        return $this;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

}
