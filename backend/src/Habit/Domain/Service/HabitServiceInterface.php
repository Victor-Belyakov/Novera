<?php

namespace App\Habit\Domain\Service;

use App\Habit\Application\DTO\HabitCreateRequestDto;
use App\Habit\Application\DTO\HabitResponseDto;
use App\User\Infrastructure\Persistence\UserEntity;

interface HabitServiceInterface
{
    /**
     * @return list<HabitResponseDto>
     */
    public function getListByUser(UserEntity $user): array;

    public function create(HabitCreateRequestDto $dto, UserEntity $user): HabitResponseDto;

    /** Отметить выполнение привычки за дату. Возвращает true если лог добавлен, false если уже отмечено за эту дату. */
    public function addLog(int $habitId, UserEntity $user, ?string $date = null): bool;

    /** Семь слотов из HabitLog по frequency: ['slots' => [['date' => 'Y-m-d', 'status' => 'completed'|'skipped'|'pending'], ...]]. null если нет доступа. */
    public function getSlots(int $habitId, UserEntity $user): ?array;

    /** Отметить дату как пропущенную (фейл). Только если за эту дату ещё нет лога. */
    public function skipLog(int $habitId, UserEntity $user, string $date): bool;

    /** Удалить отметку за дату. Возвращает true если лог удалён. */
    public function removeLog(int $habitId, UserEntity $user, string $date): bool;
}
