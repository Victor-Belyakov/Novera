<?php

namespace App\Habit\Application\Service;

use App\Category\Domain\Repository\CategoryRepositoryInterface;
use App\Category\Infrastructure\Persistence\CategoryEntity;
use App\Goal\Domain\Repository\GoalRepositoryInterface;
use App\Goal\Infrastructure\Persistence\GoalEntity;
use App\Habit\Application\DTO\HabitCreateRequestDto;
use App\Habit\Application\DTO\HabitResponseDto;
use App\Habit\Domain\Repository\HabitRepositoryInterface;
use App\Habit\Domain\Service\HabitServiceInterface;
use App\Habit\Infrastructure\Persistence\HabitEntity;
use App\Habit\Infrastructure\Persistence\HabitLogEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;

final class HabitService implements HabitServiceInterface
{
    public function __construct(
        private HabitRepositoryInterface $habitRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private GoalRepositoryInterface $goalRepository,
    ) {
    }

    public function getListByUser(UserEntity $user): array
    {
        $entities = $this->habitRepository->findAllByUser($user);
        return array_map($this->toDto(...), $entities);
    }

    public function create(HabitCreateRequestDto $dto, UserEntity $user): HabitResponseDto
    {
        $entity = new HabitEntity();
        $entity->setTitle($dto->title);
        $entity->setDescription($dto->description);
        $entity->setUser($user);
        $entity->setFrequency($dto->frequency);
        if ($dto->goal_id !== null) {
            $goal = $this->goalRepository->findById($dto->goal_id);
            $entity->setGoal($goal instanceof GoalEntity ? $goal : null);
        }
        if ($dto->category_id !== null) {
            $category = $this->categoryRepository->findById($dto->category_id);
            $entity->setCategory($category instanceof CategoryEntity ? $category : null);
        }
        if ($dto->preferred_time !== null && $dto->preferred_time !== '') {
            $entity->setPreferredTime(DateTimeImmutable::createFromFormat('H:i', $dto->preferred_time) ?: null);
        }
        if ($dto->target !== null && $dto->target > 0) {
            $entity->setTarget($dto->target);
        }
        $this->habitRepository->save($entity);
        return $this->toDto($entity);
    }

    public function addLog(int $habitId, UserEntity $user, ?string $date = null): bool
    {
        $habit = $this->habitRepository->findById($habitId);
        if ($habit === null || $habit->getUser()->getId() !== $user->getId()) {
            return false;
        }
        $loggedAt = $date !== null && $date !== ''
            ? DateTimeImmutable::createFromFormat('Y-m-d', $date)
            : new DateTimeImmutable('today');
        if ($loggedAt === false) {
            return false;
        }
        $loggedAt = $loggedAt->setTime(0, 0);
        $existing = $habit->getLogByDate($loggedAt);
        if ($existing !== null) {
            if ($existing->isCompleted()) {
                return false;
            }
            $existing->setStatus('completed');
            $habit->setProgress($habit->getProgress() + 1);
            $this->habitRepository->save($habit);
            return true;
        }
        $log = new HabitLogEntity();
        $log->setLoggedAt($loggedAt);
        $log->setStatus('completed');
        $habit->addLogEntry($log);
        $habit->setProgress($habit->getProgress() + 1);
        $this->habitRepository->save($habit);
        return true;
    }

    public function skipLog(int $habitId, UserEntity $user, string $date): bool
    {
        $habit = $this->habitRepository->findByIdWithLogs($habitId);
        if ($habit === null || $habit->getUser()->getId() !== $user->getId()) {
            return false;
        }
        $dateObj = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if ($dateObj === false) {
            return false;
        }
        $dateObj = $dateObj->setTime(0, 0);
        $existing = $habit->getLogByDate($dateObj);
        if ($existing !== null) {
            if ($existing->isPending()) {
                $existing->setStatus('skipped');
                $this->habitRepository->save($habit);
                return true;
            }
            return false;
        }
        $log = new HabitLogEntity();
        $log->setLoggedAt($dateObj);
        $log->setStatus('skipped');
        $habit->addLogEntry($log);
        $this->habitRepository->save($habit);
        return true;
    }

    /**
     * Возвращает 7 слотов из HabitLog: сегодня + 6 вперёд.
     * НЕ создаёт логи автоматически — они должны быть созданы кроном.
     * Если лога нет, возвращает статус 'pending' без создания записи в БД.
     *
     * @return array{slots: list<array{date: string, status: string}>}|null
     */
    public function getSlots(int $habitId, UserEntity $user): ?array
    {
        $habit = $this->habitRepository->findByIdWithLogs($habitId);
        if ($habit === null || $habit->getUser()->getId() !== $user->getId()) {
            return null;
        }
        $today = new DateTimeImmutable('today');

        $slotDates = $this->computeSlotDates($habit->getFrequency(), $today);
        $slots = [];
        foreach ($slotDates as $slotDate) {
            $log = $habit->getLogByDate($slotDate);
            // Если лога нет — возвращаем 'pending', но НЕ создаём запись в БД
            $status = $log !== null ? $log->getStatus() : 'pending';
            $slots[] = [
                'date' => $slotDate->format('Y-m-d'),
                'status' => $status,
            ];
        }
        return ['slots' => $slots];
    }

    /**
     * 7 слотов (дат для логов). Неделя = Пн–Пт.
     * daily: 1 лог в день = сегодня..+6 дней;
     * 2_per_week: 2 лога в неделю = Вт и Чт каждой недели (7 слотов подряд);
     * 3_per_week: 3 лога в неделю = Пн, Ср, Пт каждой недели (7 слотов);
     * weekly: 1 лог в неделю = 7 понедельников;
     * monthly: 1 лог в месяц = 7 первых чисел месяцев.
     *
     * @return list<DateTimeImmutable>
     */
    private function computeSlotDates(string $frequency, DateTimeImmutable $today): array
    {
        $dates = [];
        $dayOfWeek = (int) $today->format('w');
        $mondayOffset = $dayOfWeek === 0 ? 6 : $dayOfWeek - 1;
        $thisWeekMonday = $today->modify("-{$mondayOffset} days");

        if ($frequency === 'weekly') {
            for ($w = 0; $w < 7; $w++) {
                $dates[] = $thisWeekMonday->modify("+{$w} weeks");
            }
        } elseif ($frequency === 'monthly') {
            $thisMonthFirst = $today->modify('first day of this month');
            for ($m = 0; $m < 7; $m++) {
                $dates[] = $thisMonthFirst->modify("+{$m} months");
            }
        } elseif ($frequency === '2_per_week') {
            // 2 лога в неделю (Пн–Пт): Вт и Чт
            $slotOffsetsInWeek = [1, 3];
            $w = 0;
            while (\count($dates) < 7) {
                foreach ($slotOffsetsInWeek as $offset) {
                    $d = $thisWeekMonday->modify("+{$w} weeks")->modify("+{$offset} days");
                    if ($d >= $today) {
                        $dates[] = $d;
                        if (\count($dates) >= 7) {
                            break 2;
                        }
                    }
                }
                $w++;
            }
        } elseif ($frequency === '3_per_week') {
            // 3 лога в неделю (Пн–Пт): Пн, Ср, Пт
            $slotOffsetsInWeek = [0, 2, 4];
            $w = 0;
            while (\count($dates) < 7) {
                foreach ($slotOffsetsInWeek as $offset) {
                    $d = $thisWeekMonday->modify("+{$w} weeks")->modify("+{$offset} days");
                    if ($d >= $today) {
                        $dates[] = $d;
                        if (\count($dates) >= 7) {
                            break 2;
                        }
                    }
                }
                $w++;
            }
        } else {
            // daily и прочее: 1 лог в день
            for ($i = 0; $i < 7; $i++) {
                $dates[] = $today->modify("+{$i} days");
            }
        }
        return $dates;
    }

    public function removeLog(int $habitId, UserEntity $user, string $date): bool
    {
        $habit = $this->habitRepository->findByIdWithLogs($habitId);
        if ($habit === null || $habit->getUser()->getId() !== $user->getId()) {
            return false;
        }
        $dateObj = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if ($dateObj === false) {
            return false;
        }
        $dateObj = $dateObj->setTime(0, 0);
        $removed = $habit->removeLogByDate($dateObj);
        if ($removed === null) {
            return false;
        }
        if ($removed->isCompleted()) {
            $habit->setProgress(max(0, $habit->getProgress() - 1));
        }
        $this->habitRepository->save($habit);
        return true;
    }

    private function toDto(HabitEntity $h): HabitResponseDto
    {
        $goal = $h->getGoal();
        $resultTotal = 0;
        foreach ($h->getLogs() as $log) {
            if (!$log->isPending()) {
                $resultTotal++;
            }
        }
        return new HabitResponseDto(
            id: $h->getId(),
            title: $h->getTitle(),
            progress: $h->getProgress(),
            result_total: $resultTotal,
            frequency: $h->getFrequency(),
            target: $h->getTarget(),
            progress_percent: $h->getProgressPercent(),
            goal_id: $goal?->getId(),
            goal_title: $goal !== null ? $goal->getTitle() : null,
            created_at: $h->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
