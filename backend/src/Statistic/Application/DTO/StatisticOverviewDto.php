<?php

namespace App\Statistic\Application\DTO;

final readonly class StatisticOverviewDto
{
    public function __construct(
        public string $section,
        public string $key,
        public string $label,
        public int $value,
    ) {
    }
}
