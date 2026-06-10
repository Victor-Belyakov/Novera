<?php

namespace App\FinancePlan\Infrastructure\Persistence;

use App\Finance\Domain\Enum\FinanceTypeEnum;
use App\FinanceCategory\Infrastructure\Persistence\FinanceCategoryEntity;
use App\Infrastructure\Persistence\Trait\TimestampableTrait;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'finance_plans')]
#[ORM\HasLifecycleCallbacks]
class FinancePlanEntity
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 32, enumType: FinanceTypeEnum::class)]
    private FinanceTypeEnum $type;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    private string $plannedAmount;

    #[ORM\ManyToOne(targetEntity: FinanceCategoryEntity::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?FinanceCategoryEntity $category = null;

    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $periodStart;

    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $periodEnd;

    #[ORM\ManyToOne(targetEntity: UserEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private UserEntity $createdBy;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getType(): FinanceTypeEnum
    {
        return $this->type;
    }

    public function setType(FinanceTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPlannedAmount(): string
    {
        return $this->plannedAmount;
    }

    public function setPlannedAmount(string $plannedAmount): self
    {
        $this->plannedAmount = $plannedAmount;

        return $this;
    }

    public function getCategory(): ?FinanceCategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?FinanceCategoryEntity $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getPeriodStart(): DateTimeImmutable
    {
        return $this->periodStart;
    }

    public function setPeriodStart(DateTimeImmutable $periodStart): self
    {
        $this->periodStart = $periodStart;

        return $this;
    }

    public function getPeriodEnd(): DateTimeImmutable
    {
        return $this->periodEnd;
    }

    public function setPeriodEnd(DateTimeImmutable $periodEnd): self
    {
        $this->periodEnd = $periodEnd;

        return $this;
    }

    public function getCreatedBy(): UserEntity
    {
        return $this->createdBy;
    }

    public function setCreatedBy(UserEntity $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
