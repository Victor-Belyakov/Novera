<?php

namespace App\PersonalState\Application\Service;

use App\Finance\Domain\Enum\FinanceTypeEnum;
use App\Finance\Domain\Repository\FinanceRepositoryInterface;
use App\Finance\Infrastructure\Persistence\FinanceEntity;
use App\FinancePlan\Domain\Repository\FinancePlanRepositoryInterface;
use App\FinancePlan\Infrastructure\Persistence\FinancePlanEntity;
use App\Goal\Domain\Repository\GoalRepositoryInterface;
use App\Goal\Infrastructure\Persistence\GoalEntity;
use App\Habit\Domain\Repository\HabitRepositoryInterface;
use App\Habit\Infrastructure\Persistence\HabitEntity;
use App\HealthMetricEntry\Domain\Repository\HealthMetricEntryRepositoryInterface;
use App\HealthMetricEntry\Infrastructure\Persistence\HealthMetricEntryEntity;
use App\PersonalState\Application\DTO\PersonalStateResponseDto;
use App\PersonalState\Domain\Service\PersonalStateServiceInterface;
use App\Task\Domain\Enum\TaskStatusEnum;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use App\Task\Infrastructure\Persistence\TaskEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;

final readonly class PersonalStateService implements PersonalStateServiceInterface
{
    public function __construct(
        private FinanceRepositoryInterface $financeRepository,
        private FinancePlanRepositoryInterface $financePlanRepository,
        private GoalRepositoryInterface $goalRepository,
        private HabitRepositoryInterface $habitRepository,
        private TaskRepositoryInterface $taskRepository,
        private HealthMetricEntryRepositoryInterface $healthMetricEntryRepository,
    ) {
    }

    public function getByUser(UserEntity $user): PersonalStateResponseDto
    {
        $allFinances = $this->financeRepository->findAllByUser($user);
        [$monthStart, $monthEnd] = $this->resolveCurrentMonthPeriod();
        $monthFinances = $this->financeRepository->findAllByUserAndPeriod($user, $monthStart, $monthEnd);
        $monthPlans = $this->financePlanRepository->findAllByUserAndPeriod($user, $monthStart, $monthEnd);
        $goals = $this->goalRepository->findAllByUser($user);
        $habits = $this->habitRepository->findAllByUser($user);
        $tasks = $this->taskRepository->findAllByUser($user);

        $incomeAll = $this->sumFinancesByType($allFinances, FinanceTypeEnum::INCOME);
        $expenseAll = $this->sumFinancesByType($allFinances, FinanceTypeEnum::EXPENSE);

        $monthIncomePlan = $this->sumPlansByType($monthPlans, FinanceTypeEnum::INCOME);
        $monthExpensePlan = $this->sumPlansByType($monthPlans, FinanceTypeEnum::EXPENSE);
        $monthIncomeActual = $this->sumFinancesByType($monthFinances, FinanceTypeEnum::INCOME);
        $monthExpenseActual = $this->sumFinancesByType($monthFinances, FinanceTypeEnum::EXPENSE);

        $activeGoals = count(array_filter(
            $goals,
            static fn (GoalEntity $goal) => $goal->getCompleted() !== true
        ));

        $activeHabits = count(array_filter(
            $habits,
            static fn (HabitEntity $habit) => $habit->getStatus() === 'active'
        ));

        $tasksInProgress = count(array_filter(
            $tasks,
            static fn (TaskEntity $task) => $task->getStatus() === TaskStatusEnum::IN_PROGRESS
        ));

        $today = new DateTimeImmutable('today');
        $overdueTasks = count(array_filter(
            $tasks,
            static fn (TaskEntity $task) => $task->getDueDate() !== null
                && $task->getDueDate() < $today
                && !in_array($task->getStatus(), [TaskStatusEnum::DONE, TaskStatusEnum::CLOSED], true)
        ));

        $habitSuccessRate = $this->calculateHabitSuccessRate($habits);
        $lastWeight = $this->healthMetricEntryRepository->findLatestByUserAndSlug($user, 'weight');
        $lastSystolic = $this->healthMetricEntryRepository->findLatestByUserAndSlug($user, 'blood_pressure_systolic');
        $lastDiastolic = $this->healthMetricEntryRepository->findLatestByUserAndSlug($user, 'blood_pressure_diastolic');

        return new PersonalStateResponseDto(
            full_name: $user->getFullName(),
            age: $user->getDateOfBirth()->diff(new DateTimeImmutable('today'))->y,
            current_balance: $this->formatDecimal($incomeAll - $expenseAll),
            month_income_plan: $this->formatDecimal($monthIncomePlan),
            month_income_actual: $this->formatDecimal($monthIncomeActual),
            month_expense_plan: $this->formatDecimal($monthExpensePlan),
            month_expense_actual: $this->formatDecimal($monthExpenseActual),
            month_balance_plan: $this->formatDecimal($monthIncomePlan - $monthExpensePlan),
            month_balance_actual: $this->formatDecimal($monthIncomeActual - $monthExpenseActual),
            active_goals: $activeGoals,
            active_habits: $activeHabits,
            tasks_in_progress: $tasksInProgress,
            overdue_tasks: $overdueTasks,
            habit_success_rate: $habitSuccessRate,
            last_weight: $lastWeight?->getValueNumber(),
            last_weight_recorded_at: $lastWeight?->getRecordedAt()->format('Y-m-d H:i:s'),
            last_blood_pressure: $this->buildBloodPressureValue($lastSystolic, $lastDiastolic),
            last_blood_pressure_recorded_at: $this->resolveBloodPressureRecordedAt($lastSystolic, $lastDiastolic),
        );
    }

    /**
     * @param FinanceEntity[] $finances
     */
    private function sumFinancesByType(array $finances, FinanceTypeEnum $type): float
    {
        return array_reduce(
            $finances,
            static fn (float $sum, FinanceEntity $finance) => $finance->getType() === $type ? $sum + (float) $finance->getAmount() : $sum,
            0.0
        );
    }

    /**
     * @param FinancePlanEntity[] $plans
     */
    private function sumPlansByType(array $plans, FinanceTypeEnum $type): float
    {
        return array_reduce(
            $plans,
            static fn (float $sum, FinancePlanEntity $plan) => $plan->getType() === $type ? $sum + (float) $plan->getPlannedAmount() : $sum,
            0.0
        );
    }

    /**
     * @param HabitEntity[] $habits
     */
    private function calculateHabitSuccessRate(array $habits): int
    {
        $from = new DateTimeImmutable('-6 days');
        $completed = 0;
        $total = 0;

        foreach ($habits as $habit) {
            foreach ($habit->getLogs() as $log) {
                if ($log->getLoggedAt() < $from) {
                    continue;
                }

                $total++;
                if ($log->getStatus() === 'completed') {
                    $completed++;
                }
            }
        }

        if ($total === 0) {
            return 0;
        }

        return (int) round(($completed / $total) * 100);
    }

    private function formatDecimal(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private function buildBloodPressureValue(?HealthMetricEntryEntity $systolic, ?HealthMetricEntryEntity $diastolic): ?string
    {
        if ($systolic === null && $diastolic === null) {
            return null;
        }

        return sprintf('%s / %s', $systolic?->getValueNumber() ?? '—', $diastolic?->getValueNumber() ?? '—');
    }

    private function resolveBloodPressureRecordedAt(?HealthMetricEntryEntity $systolic, ?HealthMetricEntryEntity $diastolic): ?string
    {
        $dates = array_filter([
            $systolic?->getRecordedAt(),
            $diastolic?->getRecordedAt(),
        ]);

        if ($dates === []) {
            return null;
        }

        usort($dates, static fn (DateTimeImmutable $a, DateTimeImmutable $b) => $b <=> $a);

        return $dates[0]->format('Y-m-d H:i:s');
    }

    /**
     * @return array{0: DateTimeImmutable, 1: DateTimeImmutable}
     */
    private function resolveCurrentMonthPeriod(): array
    {
        return [
            new DateTimeImmutable('first day of this month'),
            new DateTimeImmutable('last day of this month'),
        ];
    }
}
