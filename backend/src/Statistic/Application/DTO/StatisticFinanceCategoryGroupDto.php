<?php

namespace App\Statistic\Application\DTO;

final readonly class StatisticFinanceCategoryGroupDto
{
    /**
     * @param StatisticFinanceDonutDto[] $items
     */
    public function __construct(
        public string $category,
        public array $items,
    ) {
    }
}
