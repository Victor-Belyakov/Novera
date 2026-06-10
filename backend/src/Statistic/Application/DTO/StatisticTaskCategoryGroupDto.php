<?php

namespace App\Statistic\Application\DTO;

final readonly class StatisticTaskCategoryGroupDto
{
    /**
     * @param StatisticTaskDonutDto[] $items
     */
    public function __construct(
        public string $category,
        public array $items,
    ) {
    }
}
