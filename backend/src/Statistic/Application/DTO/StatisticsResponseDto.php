<?php

namespace App\Statistic\Application\DTO;

final readonly class StatisticsResponseDto
{
    /**
     * @param StatisticOverviewDto[] $summary
     * @param StatisticHabitCategoryGroupDto[] $habit_category_groups
     * @param StatisticGoalCategoryGroupDto[] $goal_category_groups
     * @param StatisticTaskCategoryGroupDto[] $task_category_groups
     * @param StatisticFinanceCategoryGroupDto[] $finance_category_groups
     */
    public function __construct(
        public array $summary,
        public array $habit_category_groups,
        public array $goal_category_groups,
        public array $task_category_groups,
        public array $finance_category_groups,
    ) {
    }
}
