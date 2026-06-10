<?php

namespace App\HealthMetricEntry\Infrastructure\Persistence;

use App\HealthMetricType\Infrastructure\Persistence\HealthMetricTypeEntity;
use App\Infrastructure\Persistence\Trait\TimestampableTrait;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'health_metric_entries')]
#[ORM\HasLifecycleCallbacks]
class HealthMetricEntryEntity
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: UserEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private UserEntity $user;

    #[ORM\ManyToOne(targetEntity: HealthMetricTypeEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private HealthMetricTypeEntity $metricType;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $recordedAt;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $valueNumber = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $valueText = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): UserEntity
    {
        return $this->user;
    }

    public function setUser(UserEntity $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getMetricType(): HealthMetricTypeEntity
    {
        return $this->metricType;
    }

    public function setMetricType(HealthMetricTypeEntity $metricType): self
    {
        $this->metricType = $metricType;
        return $this;
    }

    public function getRecordedAt(): DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function setRecordedAt(DateTimeImmutable $recordedAt): self
    {
        $this->recordedAt = $recordedAt;
        return $this;
    }

    public function getValueNumber(): ?string
    {
        return $this->valueNumber;
    }

    public function setValueNumber(?string $valueNumber): self
    {
        $this->valueNumber = $valueNumber;
        return $this;
    }

    public function getValueText(): ?string
    {
        return $this->valueText;
    }

    public function setValueText(?string $valueText): self
    {
        $this->valueText = $valueText;
        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;
        return $this;
    }
}
