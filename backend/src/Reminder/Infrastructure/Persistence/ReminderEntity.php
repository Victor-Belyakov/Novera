<?php

namespace App\Reminder\Infrastructure\Persistence;

use App\Infrastructure\Persistence\Trait\TimestampableTrait;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'reminders')]
#[ORM\HasLifecycleCallbacks]
class ReminderEntity
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /** Заголовок уведомления */
    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    /** Детали / текст уведомления */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /** Пользователь, которому напоминание */
    #[ORM\ManyToOne(targetEntity: UserEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private UserEntity $user;

    /** Привязка к сущности (habit, task, goal) */
    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $entityType = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $entityId = null;

    /** Дата и время следующего срабатывания */
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $notifyAt;

    /** Повторение: daily / weekly / custom / none */
    #[ORM\Column(type: 'string', length: 32)]
    private string $frequency = 'none';

    /** Опциональные дни недели для повторения (для weekly/custom), [1,3,5] = Пн/Ср/Пт */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $weekDays = null;

    /** Статус напоминания: active / done / skipped */
    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'active';

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

    public function getUser(): UserEntity
    {
        return $this->user;
    }

    public function setUser(UserEntity $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(?string $entityType): self
    {
        $this->entityType = $entityType;
        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): self
    {
        $this->entityId = $entityId;
        return $this;
    }

    public function getNotifyAt(): DateTimeImmutable
    {
        return $this->notifyAt;
    }

    public function setNotifyAt(DateTimeImmutable $notifyAt): self
    {
        $this->notifyAt = $notifyAt;
        return $this;
    }

    public function getFrequency(): string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): self
    {
        $this->frequency = $frequency;
        return $this;
    }

    /** @return list<int>|null */
    public function getWeekDays(): ?array
    {
        return $this->weekDays;
    }

    /** @param list<int>|null $weekDays */
    public function setWeekDays(?array $weekDays): self
    {
        $this->weekDays = $weekDays;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }
}
