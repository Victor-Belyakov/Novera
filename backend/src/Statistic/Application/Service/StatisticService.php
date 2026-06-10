<?php

namespace App\Statistic\Application\Service;

use App\Category\Infrastructure\Persistence\CategoryEntity;
use App\Finance\Domain\Enum\FinanceTypeEnum;
use App\Finance\Domain\Repository\FinanceRepositoryInterface;
use App\Finance\Infrastructure\Persistence\FinanceEntity;
use App\FinanceCategory\Infrastructure\Persistence\FinanceCategoryEntity;
use App\FinancePlan\Domain\Repository\FinancePlanRepositoryInterface;
use App\FinancePlan\Infrastructure\Persistence\FinancePlanEntity;
use App\Statistic\Application\DTO\StatisticFinanceCategoryGroupDto;
use App\Statistic\Application\DTO\StatisticFinanceDonutDto;
use App\Goal\Domain\Repository\GoalRepositoryInterface;
use App\Goal\Infrastructure\Persistence\GoalEntity;
use App\Statistic\Application\DTO\StatisticGoalCategoryGroupDto;
use App\Statistic\Application\DTO\StatisticGoalDonutDto;
use App\Habit\Domain\Repository\HabitRepositoryInterface;
use App\Habit\Infrastructure\Persistence\HabitEntity;
use App\Statistic\Application\DTO\StatisticHabitCategoryGroupDto;
use App\Statistic\Application\DTO\StatisticHabitDonutDto;
use App\Statistic\Application\DTO\StatisticOverviewDto;
use App\Statistic\Application\DTO\StatisticTaskCategoryGroupDto;
use App\Statistic\Application\DTO\StatisticTaskDonutDto;
use App\Statistic\Application\DTO\StatisticsResponseDto;
use App\Statistic\Domain\Service\StatisticServiceInterface;
use App\Task\Domain\Enum\TaskStatusEnum;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use App\Task\Infrastructure\Persistence\TaskEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;

final readonly class StatisticService implements StatisticServiceInterface
{
    public function __construct(
        private GoalRepositoryInterface $goalRepository,
        private HabitRepositoryInterface $habitRepository,
        private TaskRepositoryInterface $taskRepository,
        private FinanceRepositoryInterface $financeRepository,
        private FinancePlanRepositoryInterface $financePlanRepository,
    ) {
    }

    public function getOverviewByUser(UserEntity $user): StatisticsResponseDto
    {
        $goals = $this->goalRepository->findAllByUser($user);
        $habits = $this->habitRepository->findAllByUser($user);
        $tasks = $this->taskRepository->findAllByUser($user);
        [$currentMonthStart, $currentMonthEnd] = $this->resolveCurrentMonthPeriod();
        $finances = $this->financeRepository->findAllByUserAndPeriod($user, $currentMonthStart, $currentMonthEnd);
        $financePlans = $this->financePlanRepository->findAllByUserAndPeriod($user, $currentMonthStart, $currentMonthEnd);

        $completedGoals = array_filter(
            $goals,
            static fn (GoalEntity $goal) => $goal->getCompleted() === true
        );

        $activeHabits = array_filter(
            $habits,
            static fn (HabitEntity $habit) => $habit->getStatus() === 'active'
        );

        $completedHabitLogs = 0;
        $pendingHabitLogs = 0;
        foreach ($habits as $habit) {
            foreach ($habit->getLogs() as $log) {
                if ($log->getStatus() === 'completed') {
                    $completedHabitLogs++;
                } elseif ($log->getStatus() === 'pending') {
                    $pendingHabitLogs++;
                }
            }
        }

        $summary = [
            new StatisticOverviewDto('goals', 'total', 'Всего целей', count($goals)),
            new StatisticOverviewDto('goals', 'completed', 'Выполнено целей', count($completedGoals)),
            new StatisticOverviewDto('habits', 'total', 'Всего привычек', count($habits)),
            new StatisticOverviewDto('habits', 'active', 'Активных привычек', count($activeHabits)),
            new StatisticOverviewDto('habits', 'completed_logs', 'Выполненных отметок привычек', $completedHabitLogs),
            new StatisticOverviewDto('habits', 'pending_logs', 'Ожидающих отметок привычек', $pendingHabitLogs),
            new StatisticOverviewDto('tasks', 'total', 'Всего задач', count($tasks)),
            new StatisticOverviewDto('tasks', 'new', 'Новых задач', $this->countTasksByStatus($tasks, TaskStatusEnum::NEW)),
            new StatisticOverviewDto('tasks', 'in_progress', 'Задач в работе', $this->countTasksByStatus($tasks, TaskStatusEnum::IN_PROGRESS)),
            new StatisticOverviewDto('tasks', 'done', 'Выполненных задач', $this->countTasksByStatus($tasks, TaskStatusEnum::DONE)),
            new StatisticOverviewDto('tasks', 'closed', 'Закрытых задач', $this->countTasksByStatus($tasks, TaskStatusEnum::CLOSED)),
            new StatisticOverviewDto('finance', 'planned_income', 'План доходов', (int) round($this->sumPlanAmountsByType($financePlans, FinanceTypeEnum::INCOME))),
            new StatisticOverviewDto('finance', 'actual_income', 'Факт доходов', (int) round($this->sumFinanceAmountsByType($finances, FinanceTypeEnum::INCOME))),
            new StatisticOverviewDto('finance', 'planned_expense', 'План расходов', (int) round($this->sumPlanAmountsByType($financePlans, FinanceTypeEnum::EXPENSE))),
            new StatisticOverviewDto('finance', 'actual_expense', 'Факт расходов', (int) round($this->sumFinanceAmountsByType($finances, FinanceTypeEnum::EXPENSE))),
        ];

        $habitCategoryBuckets = [];
        foreach ($habits as $habit) {
            $categoryTitle = $this->resolveHabitCategoryTitle($habit);
            $habitCategoryBuckets[$categoryTitle] ??= [];

            $success = 0;
            $fail = 0;

            foreach ($habit->getLogs() as $log) {
                if ($log->getStatus() === 'completed') {
                    $success++;
                } else {
                    $fail++;
                }
            }

            $total = $success + $fail;
            $successPercent = $total > 0 ? (int) round(($success / $total) * 100) : 0;

            $habitCategoryBuckets[$categoryTitle][] = new StatisticHabitDonutDto(
                label: $habit->getTitle(),
                success: $success,
                fail: $fail,
                total: $total,
                success_percent: $successPercent,
            );
        }

        $habitCategoryGroups = [];
        foreach ($habitCategoryBuckets as $category => $items) {
            $habitCategoryGroups[] = new StatisticHabitCategoryGroupDto(
                category: $category,
                items: $items,
            );
        }

        usort(
            $habitCategoryGroups,
            static fn (StatisticHabitCategoryGroupDto $a, StatisticHabitCategoryGroupDto $b) => strcmp($a->category, $b->category)
        );

        $goalCategoryBuckets = [];
        foreach ($goals as $goal) {
            $categoryTitle = $this->resolveGoalCategoryTitle($goal);
            $goalCategoryBuckets[$categoryTitle] ??= [];

            $success = 0;
            $fail = 0;
            $inProgress = 0;

            if ($goal->getCompleted() === true) {
                $success = 1;
            } elseif ($goal->getCompleted() === null) {
                $inProgress = 1;
            } else {
                $fail = 1;
            }

            $total = $success + $fail + $inProgress;
            $successPercent = $total > 0 ? (int) round(($success / $total) * 100) : 0;

            $goalCategoryBuckets[$categoryTitle][] = new StatisticGoalDonutDto(
                label: $goal->getTitle(),
                success: $success,
                fail: $fail,
                in_progress: $inProgress,
                total: $total,
                success_percent: $successPercent,
            );
        }

        $goalCategoryGroups = [];
        foreach ($goalCategoryBuckets as $category => $items) {
            $goalCategoryGroups[] = new StatisticGoalCategoryGroupDto(
                category: $category,
                items: $items,
            );
        }

        usort(
            $goalCategoryGroups,
            static fn (StatisticGoalCategoryGroupDto $a, StatisticGoalCategoryGroupDto $b) => strcmp($a->category, $b->category)
        );

        $taskCategoryBuckets = [];
        foreach ($tasks as $task) {
            $categoryTitle = $this->resolveTaskCategoryTitle($task);
            $taskCategoryBuckets[$categoryTitle] ??= [];

            $new = 0;
            $inProgress = 0;
            $done = 0;
            $closed = 0;

            switch ($task->getStatus()) {
                case TaskStatusEnum::NEW:
                    $new = 1;
                    break;
                case TaskStatusEnum::IN_PROGRESS:
                    $inProgress = 1;
                    break;
                case TaskStatusEnum::DONE:
                    $done = 1;
                    break;
                case TaskStatusEnum::CLOSED:
                    $closed = 1;
                    break;
            }

            $total = $new + $inProgress + $done + $closed;
            $successPercent = $total > 0 ? (int) round(($done / $total) * 100) : 0;

            $taskCategoryBuckets[$categoryTitle][] = new StatisticTaskDonutDto(
                label: $task->getTitle(),
                new: $new,
                in_progress: $inProgress,
                done: $done,
                closed: $closed,
                total: $total,
                success_percent: $successPercent,
            );
        }

        $taskCategoryGroups = [];
        foreach ($taskCategoryBuckets as $category => $items) {
            $taskCategoryGroups[] = new StatisticTaskCategoryGroupDto(
                category: $category,
                items: $items,
            );
        }

        usort(
            $taskCategoryGroups,
            static fn (StatisticTaskCategoryGroupDto $a, StatisticTaskCategoryGroupDto $b) => strcmp($a->category, $b->category)
        );

        $financeCategoryBuckets = $this->buildFinanceCategoryBuckets($financePlans, $finances);
        $financeCategoryGroups = [];
        foreach ($financeCategoryBuckets as $category => $items) {
            $financeCategoryGroups[] = new StatisticFinanceCategoryGroupDto(
                category: $category,
                items: $items,
            );
        }

        usort(
            $financeCategoryGroups,
            static fn (StatisticFinanceCategoryGroupDto $a, StatisticFinanceCategoryGroupDto $b) => strcmp($a->category, $b->category)
        );

        return new StatisticsResponseDto(
            summary: $summary,
            habit_category_groups: $habitCategoryGroups,
            goal_category_groups: $goalCategoryGroups,
            task_category_groups: $taskCategoryGroups,
            finance_category_groups: $financeCategoryGroups,
        );
    }

    /**
     * @param TaskEntity[] $tasks
     */
    private function countTasksByStatus(array $tasks, TaskStatusEnum $status): int
    {
        return count(array_filter(
            $tasks,
            static fn (TaskEntity $task) => $task->getStatus() === $status
        ));
    }

    private function resolveHabitCategoryTitle(HabitEntity $habit): string
    {
        $goal = $habit->getGoal();
        $category = $goal?->getCategory();

        return $category instanceof CategoryEntity
            ? $category->getTitle()
            : 'Без категории';
    }

    private function resolveGoalCategoryTitle(GoalEntity $goal): string
    {
        $category = $goal->getCategory();

        return $category instanceof CategoryEntity
            ? $category->getTitle()
            : 'Без категории';
    }

    private function resolveTaskCategoryTitle(TaskEntity $task): string
    {
        $category = $task->getGoal()?->getCategory();

        return $category instanceof CategoryEntity
            ? $category->getTitle()
            : 'Без категории';
    }

    /**
     * @param FinancePlanEntity[] $plans
     * @param FinanceEntity[] $finances
     * @return array<string, list<StatisticFinanceDonutDto>>
     */
    private function buildFinanceCategoryBuckets(array $plans, array $finances): array
    {
        $actualByTypeAndCategory = [];
        foreach ($finances as $finance) {
            $type = $finance->getType()->value;
            $categoryId = $finance->getCategory()?->getId() ?? 0;
            $actualByTypeAndCategory[$type][$categoryId] ??= 0.0;
            $actualByTypeAndCategory[$type][$categoryId] += (float) $finance->getAmount();
        }

        $dtoBuckets = [];

        foreach ($plans as $plan) {
            $categoryTitle = $this->resolveFinancePlanCategoryTitle($plan);
            $dtoBuckets[$categoryTitle] ??= [];

            $type = $plan->getType()->value;
            $categoryId = $plan->getCategory()?->getId() ?? 0;
            $planned = (float) $plan->getPlannedAmount();
            $actual = $actualByTypeAndCategory[$type][$categoryId] ?? 0.0;
            $difference = $plan->getType() === FinanceTypeEnum::EXPENSE
                ? $planned - $actual
                : $actual - $planned;
            $progressPercent = $planned > 0 ? (int) round(($actual / $planned) * 100) : 0;

            $dtoBuckets[$categoryTitle][] = new StatisticFinanceDonutDto(
                label: $plan->getTitle(),
                type: $type,
                type_label: $plan->getType()->label(),
                planned_amount: $this->formatFinanceAmount($planned),
                actual_amount: $this->formatFinanceAmount($actual),
                difference: $this->formatFinanceAmount($difference),
                success_percent: $progressPercent,
                has_plan: true,
            );
        }

        return $dtoBuckets;
    }

    /**
     * @param FinanceEntity[] $finances
     */
    private function sumFinanceAmountsByType(array $finances, FinanceTypeEnum $type): float
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
    private function sumPlanAmountsByType(array $plans, FinanceTypeEnum $type): float
    {
        return array_reduce(
            $plans,
            static fn (float $sum, FinancePlanEntity $plan) => $plan->getType() === $type ? $sum + (float) $plan->getPlannedAmount() : $sum,
            0.0
        );
    }

    private function resolveFinancePlanCategoryTitle(FinancePlanEntity $plan): string
    {
        $category = $plan->getCategory();

        return $category instanceof FinanceCategoryEntity
            ? $category->getTitle()
            : 'Без категории';
    }

    private function formatFinanceAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    /**
     * @return array{0: DateTimeImmutable, 1: DateTimeImmutable}
     */
    private function resolveCurrentMonthPeriod(): array
    {
        $periodStart = new DateTimeImmutable('first day of this month');
        $periodEnd = new DateTimeImmutable('last day of this month');

        return [$periodStart, $periodEnd];
    }
}
