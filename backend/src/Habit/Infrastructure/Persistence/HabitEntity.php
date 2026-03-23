<?php

namespace App\Habit\Infrastructure\Persistence;

use App\Category\Infrastructure\Persistence\CategoryEntity;
use App\Goal\Infrastructure\Persistence\GoalEntity;
use App\Infrastructure\Persistence\Trait\TimestampableTrait;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'habits')]
#[ORM\HasLifecycleCallbacks]
class HabitEntity
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /** Название привычки */
    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    /** Подробное описание */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /** Категория, если нужно */
    #[ORM\ManyToOne(targetEntity: CategoryEntity::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?CategoryEntity $category = null;

    /** Пользователь */
    #[ORM\ManyToOne(targetEntity: UserEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private UserEntity $user;

    /** Периодичность: daily / 3_per_week / 2_per_week / weekly / monthly */
    #[ORM\Column(type: 'string', length: 32)]
    private string $frequency;

    /** Время выполнения (опционально) */
    #[ORM\Column(type: 'time_immutable', nullable: true)]
    private ?DateTimeImmutable $preferredTime = null;

    /** Сколько раз выполнено (денормализованный счётчик логов) */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $progress = 0;

    /** Цель по количеству выполнений за период (для progress_percent). null = без цели */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $target = null;

    /** Логи выполнений (HabitLog) */
    #[ORM\OneToMany(mappedBy: 'habit', targetEntity: HabitLogEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['loggedAt' => 'DESC'])]
    private Collection $logs;

    /** Цель привычки (optional link) */
    #[ORM\ManyToOne(targetEntity: GoalEntity::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?GoalEntity $goal = null;

    /** Статус: active / paused / archived */
    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'active';

    public function __construct()
    {
        $this->logs = new ArrayCollection();
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

    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?CategoryEntity $category): self
    {
        $this->category = $category;
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

    public function getFrequency(): string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): self
    {
        $this->frequency = $frequency;
        return $this;
    }

    public function getPreferredTime(): ?DateTimeImmutable
    {
        return $this->preferredTime;
    }

    public function setPreferredTime(?DateTimeImmutable $preferredTime): self
    {
        $this->preferredTime = $preferredTime;
        return $this;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function setProgress(int $progress): self
    {
        $this->progress = $progress;
        return $this;
    }

    public function getTarget(): ?int
    {
        return $this->target;
    }

    public function setTarget(?int $target): self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @return Collection<int, HabitLogEntity>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLogEntry(HabitLogEntity $log): self
    {
        $log->setHabit($this);
        $this->logs->add($log);
        return $this;
    }

    public function getLogByDate(DateTimeImmutable $date): ?HabitLogEntity
    {
        $dateStr = $date->format('Y-m-d');
        foreach ($this->logs as $log) {
            if ($log->getLoggedAt()->format('Y-m-d') === $dateStr) {
                return $log;
            }
        }
        return null;
    }

    public function hasLogForDate(DateTimeImmutable $date): bool
    {
        return $this->getLogByDate($date) !== null;
    }

    /** Удалить лог за указанную дату. Возвращает удалённую сущность или null. */
    public function removeLogByDate(DateTimeImmutable $date): ?HabitLogEntity
    {
        $log = $this->getLogByDate($date);
        if ($log !== null) {
            $this->logs->removeElement($log);
            return $log;
        }
        return null;
    }

    /** Процент выполнения: только completed-логи / target * 100. Без target = 0. */
    public function getProgressPercent(): int
    {
        if ($this->target === null || $this->target <= 0) {
            return 0;
        }
        $completedCount = 0;
        foreach ($this->logs as $log) {
            if ($log->isCompleted()) {
                $completedCount++;
            }
        }
        $percent = ($completedCount / $this->target) * 100;
        return min(100, (int) round($percent));
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
