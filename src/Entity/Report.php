<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Enum\ReportTypeEnum;
use App\Event\ReportCreatedEvent;
use App\Event\ReportGeneratedEvent;
use App\Repository\ReportRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(
            paginationEnabled: true,
            paginationItemsPerPage: 30,
        ),
        new Post(
            denormalizationContext: ['groups' => ['report:create']],
            validationContext: ['groups' => ['Default', 'report:create']],
        ),
        new Put(
            denormalizationContext: ['groups' => ['report:update']],
        ),
        new Delete(),
        new Post(
            uriTemplate: '/reports/{id}/generate',
            controller: 'App\Controller\GenerateReportController',
            openapiContext: [
                'summary' => 'Generate report',
                'description' => 'Triggers asynchronous report generation',
            ]
        ),
    ],
    normalizationContext: ['groups' => ['report:read']],
    filters: [
        'report.search_filter',
        'report.date_filter',
        'report.order_filter',
    ],
    security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_MANAGER')"
)]
#[ORM\Entity(repositoryClass: ReportRepository::class)]
#[ORM\Table(name: 'reports')]
class Report implements AggregateRootInterface
{
    use EventRecorderTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['report:read'])]
    private UuidInterface $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['report:read', 'report:create', 'report:update'])]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, enumType: ReportTypeEnum::class)]
    #[Groups(['report:read', 'report:create'])]
    private ReportTypeEnum $type;

    #[ORM\Column(type: 'json')]
    #[Groups(['report:read', 'report:create', 'report:update'])]
    private array $parameters = [];

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['report:read'])]
    private ?array $result = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['report:read', 'report:create'])]
    private User $createdBy;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['report:read', 'report:create'])]
    private bool $scheduled = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\When(
        expression: 'this.scheduled === true',
        constraints: [new Assert\NotBlank()]
    )]
    #[Groups(['report:read', 'report:create', 'report:update'])]
    private ?string $cronExpression = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['report:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['report:read'])]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $name,
        ReportTypeEnum $type,
        array $parameters,
        User $createdBy,
        bool $scheduled = false,
        ?string $cronExpression = null
    ) {
        $this->id = Uuid::uuid7();
        $this->name = $name;
        $this->type = $type;
        $this->parameters = $parameters;
        $this->createdBy = $createdBy;
        $this->scheduled = $scheduled;
        $this->cronExpression = $cronExpression;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->validateParameters();

        $this->recordEvent(new ReportCreatedEvent($this));
    }

    public function generate(array $result): void
    {
        $this->result = $result;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new ReportGeneratedEvent($this));
    }

    public function update(string $name, array $parameters): void
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->updatedAt = new \DateTimeImmutable();

        $this->validateParameters();
    }

    public function schedule(string $cronExpression): void
    {
        $this->scheduled = true;
        $this->cronExpression = $cronExpression;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function unschedule(): void
    {
        $this->scheduled = false;
        $this->cronExpression = null;
        $this->updatedAt = new \DateTimeImmutable();
    }

    private function validateParameters(): void
    {
        $requiredParams = $this->type->getRequiredParameters();
        $providedParams = array_keys($this->parameters);

        foreach ($requiredParams as $param) {
            if (!in_array($param, $providedParams, true)) {
                throw new \InvalidArgumentException(
                    sprintf('Required parameter "%s" is missing for report type "%s"', $param, $this->type->value)
                );
            }
        }
    }

    // Property accessors
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ReportTypeEnum
    {
        return $this->type;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getResult(): ?array
    {
        return $this->result;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function isScheduled(): bool
    {
        return $this->scheduled;
    }

    public function getCronExpression(): ?string
    {
        return $this->cronExpression;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
