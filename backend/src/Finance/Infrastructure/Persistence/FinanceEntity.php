<?php

namespace App\Finance\Infrastructure\Persistence;

use App\Finance\Domain\Enum\FinanceTypeEnum;
use App\FinanceCategory\Infrastructure\Persistence\FinanceCategoryEntity;
use App\Infrastructure\Persistence\Trait\TimestampableTrait;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'finances')]
#[ORM\HasLifecycleCallbacks]
class FinanceEntity
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    private string $amount;

    #[ORM\Column(type: 'string', length: 32, enumType: FinanceTypeEnum::class)]
    private FinanceTypeEnum $type;

    #[ORM\ManyToOne(targetEntity: FinanceCategoryEntity::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?FinanceCategoryEntity $category = null;

    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $operationDate;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

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

    public function getCategory(): ?FinanceCategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?FinanceCategoryEntity $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getOperationDate(): DateTimeImmutable
    {
        return $this->operationDate;
    }

    public function setOperationDate(DateTimeImmutable $operationDate): self
    {
        $this->operationDate = $operationDate;

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
