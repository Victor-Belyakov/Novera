<?php

namespace App\Statistic\Application\DTO;

final readonly class StatisticHabitDonutDto
{
    public function __construct(
        public string $label,
        public int $success,
        public int $fail,
        public int $total,
        public int $success_percent,
    ) {
    }
}
