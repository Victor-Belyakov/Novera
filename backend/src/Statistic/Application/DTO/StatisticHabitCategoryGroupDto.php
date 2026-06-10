<?php

namespace App\Statistic\Application\DTO;

final readonly class StatisticHabitCategoryGroupDto
{
    /**
     * @param StatisticHabitDonutDto[] $items
     */
    public function __construct(
        public string $category,
        public array $items,
    ) {
    }
}
