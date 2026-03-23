<?php

namespace App\Task\Infrastructure\Persistence;

use App\Infrastructure\Persistence\Trait\TimestampableTrait;
use App\Task\Domain\Enum\TaskStatusEnum;
use App\Goal\Infrastructure\Persistence\GoalEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tasks')]
#[ORM\HasLifecycleCallbacks]
class TaskEntity
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

    /** Ответственный */
    #[ORM\ManyToOne(targetEntity: UserEntity::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?UserEntity $assignee = null;

    /** Кто поставил задачу */
    #[ORM\ManyToOne(targetEntity: UserEntity::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?UserEntity $createdBy = null;

    /** Родительская задача */
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?TaskEntity $parent = null;

    /** Связь с целью */
    #[ORM\ManyToOne(targetEntity: GoalEntity::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?GoalEntity $goal = null;

    #[ORM\Column(type: 'string', length: 50, enumType: TaskStatusEnum::class)]
    private TaskStatusEnum $status;

    /** Срок выполнения */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $dueDate = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $priority;

    /**
     * @var Collection<int, TaskEntity>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    private Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

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

    public function getAssignee(): ?UserEntity
    {
        return $this->assignee;
    }

    public function setAssignee(?UserEntity $assignee): self
    {
        $this->assignee = $assignee;
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

    public function getParent(): ?TaskEntity
    {
        return $this->parent;
    }

    public function setParent(?TaskEntity $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /** @return Collection<int, TaskEntity> */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getGoal(): ?GoalEntity
    {
        return $this->goal;
    }

    public function setGoal(?GoalEntity $goal): self
    {
        $this->goal = $goal;
        return $this;
    }

    public function getStatus(): TaskStatusEnum
    {
        return $this->status;
    }

    public function setStatus(TaskStatusEnum $status): self
    {
        $this->status = $status;
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

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): self
    {
        $this->priority = $priority;
        return $this;
    }
}
