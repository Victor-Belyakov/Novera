<?php

namespace App\Statistic\Application\DTO;

final readonly class StatisticFinanceDonutDto
{
    public function __construct(
        public string $label,
        public string $type,
        public string $type_label,
        public string $planned_amount,
        public string $actual_amount,
        public string $difference,
        public int $success_percent,
        public bool $has_plan,
    ) {
    }
}
