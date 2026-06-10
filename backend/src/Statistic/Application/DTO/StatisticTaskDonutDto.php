<?php

namespace App\Statistic\Application\DTO;

final readonly class StatisticTaskDonutDto
{
    public function __construct(
        public string $label,
        public int $new,
        public int $in_progress,
        public int $done,
        public int $closed,
        public int $total,
        public int $success_percent,
    ) {
    }
}
