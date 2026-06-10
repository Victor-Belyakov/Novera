<?php

namespace App\Statistic\Application\DTO;

final readonly class StatisticGoalDonutDto
{
    public function __construct(
        public string $label,
        public int $success,
        public int $fail,
        public int $in_progress,
        public int $total,
        public int $success_percent,
    ) {
    }
}
