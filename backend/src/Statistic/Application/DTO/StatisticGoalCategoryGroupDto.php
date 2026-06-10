<?php

namespace App\Statistic\Application\DTO;

final readonly class StatisticGoalCategoryGroupDto
{
    /**
     * @param StatisticGoalDonutDto[] $items
     */
    public function __construct(
        public string $category,
        public array $items,
    ) {
    }
}
