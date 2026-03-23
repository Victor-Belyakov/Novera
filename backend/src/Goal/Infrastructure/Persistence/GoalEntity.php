<?php

namespace App\Goal\Infrastructure\Persistence;

use App\Category\Infrastructure\Persistence\CategoryEntity;
use App\Infrastructure\Persistence\Trait\TimestampableTrait;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'goals')]
#[ORM\HasLifecycleCallbacks]
class GoalEntity
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /** Название цели */
    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    /** Описание / формулировка */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /** Категория жизни (Здоровье, Финансы…) */
    #[ORM\ManyToOne(targetEntity: CategoryEntity::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?CategoryEntity $category = null;

    /**
     * Тип цели:
     * habit    — привычка
     * result   — результат
     * learning — обучение
     * project  — проект
     */
    #[ORM\Column(type: 'string', length: 32)]
    private string $type = 'result';

    /** Дедлайн (если есть) */
    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?DateTimeImmutable $dueDate = null;

    /**
     * Статус цели:
     * active    — в работе
     * completed — выполнена
     * failed    — провалена
     * paused    — на паузе
     */
    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'active';

    /** Процент выполнения (для ИИ и аналитики) */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $progress = 0;

    /** Приоритет */
    #[ORM\Column(type: 'smallint', options: ['default' => 0])]
    private int $priority = 0;

    /** Кто создал */
    #[ORM\ManyToOne(targetEntity: UserEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private UserEntity $createdBy;

    /** Архив */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $archived = false;

    /**
     * Статус цели: null — только поставлена, true — выполнена, false — не выполнена.
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $completed = null;

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

    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?CategoryEntity $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getDueDate(): ?DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?DateTimeImmutable $dueDate): self
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getCreatedBy(): ?UserEntity
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?UserEntity $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getCompleted(): ?bool
    {
        return $this->completed;
    }

    public function setCompleted(?bool $completed): self
    {
        $this->completed = $completed;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }
}
