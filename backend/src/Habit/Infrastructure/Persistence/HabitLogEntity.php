<?php

namespace App\Habit\Infrastructure\Persistence;

use App\Infrastructure\Persistence\Trait\TimestampableTrait;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'habit_logs')]
#[ORM\UniqueConstraint(name: 'habit_logged_at_unique', columns: ['habit_id', 'logged_at'])]
#[ORM\HasLifecycleCallbacks]
class HabitLogEntity
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: HabitEntity::class, inversedBy: 'logs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private HabitEntity $habit;

    /** День, за который отмечено выполнение (дата без времени) */
    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $loggedAt;

    /** completed = выполнено, skipped = пропущено, pending = слот создан, ещё не отмечен */
    #[ORM\Column(type: 'string', length: 16, options: ['default' => 'completed'])]
    private string $status = 'completed';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHabit(): HabitEntity
    {
        return $this->habit;
    }

    public function setHabit(HabitEntity $habit): self
    {
        $this->habit = $habit;
        return $this;
    }

    public function getLoggedAt(): DateTimeImmutable
    {
        return $this->loggedAt;
    }

    public function setLoggedAt(DateTimeImmutable $loggedAt): self
    {
        $this->loggedAt = $loggedAt;
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

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
