<?php

namespace App\HealthMetricType\Domain\Service;

use App\HealthMetricType\Application\DTO\HealthMetricTypeCreateRequestDto;
use App\HealthMetricType\Application\DTO\HealthMetricTypeResponseDto;

interface HealthMetricTypeServiceInterface
{
    /**
     * @return HealthMetricTypeResponseDto[]
     */
    public function getList(bool $activeOnly = false): array;

    public function create(HealthMetricTypeCreateRequestDto $dto): HealthMetricTypeResponseDto;
}
