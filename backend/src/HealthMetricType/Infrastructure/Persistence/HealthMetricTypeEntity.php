<?php

namespace App\HealthMetricType\Infrastructure\Persistence;

use App\Infrastructure\Persistence\Trait\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'health_metric_types')]
#[ORM\HasLifecycleCallbacks]
class HealthMetricTypeEntity
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $slug;

    #[ORM\Column(type: 'string', length: 32)]
    private string $valueKind = 'number';

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $unit = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getValueKind(): string
    {
        return $this->valueKind;
    }

    public function setValueKind(string $valueKind): self
    {
        $this->valueKind = $valueKind;
        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }
}
