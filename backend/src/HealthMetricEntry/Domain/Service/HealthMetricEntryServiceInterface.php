<?php

namespace App\HealthMetricEntry\Domain\Service;

use App\HealthMetricEntry\Application\DTO\HealthMetricEntryCreateRequestDto;
use App\HealthMetricEntry\Application\DTO\HealthMetricEntryResponseDto;
use App\User\Infrastructure\Persistence\UserEntity;

interface HealthMetricEntryServiceInterface
{
    /**
     * @return HealthMetricEntryResponseDto[]
     */
    public function getListByUser(UserEntity $user): array;

    public function create(HealthMetricEntryCreateRequestDto $dto, UserEntity $user): HealthMetricEntryResponseDto;
}
