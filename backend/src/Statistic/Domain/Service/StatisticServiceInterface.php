<?php

namespace App\Statistic\Domain\Service;

use App\Statistic\Application\DTO\StatisticsResponseDto;
use App\User\Infrastructure\Persistence\UserEntity;

interface StatisticServiceInterface
{
    public function getOverviewByUser(UserEntity $user): StatisticsResponseDto;
}
