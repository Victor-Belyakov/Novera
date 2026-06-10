<?php

namespace App\FinancePlan\Domain\Service;

use App\FinancePlan\Application\DTO\FinancePlanCreateRequestDto;
use App\FinancePlan\Application\DTO\FinancePlanResponseDto;
use App\FinancePlan\Application\DTO\FinancePlanSummaryResponseDto;
use App\User\Infrastructure\Persistence\UserEntity;

interface FinancePlanServiceInterface
{
    /**
     * @return FinancePlanResponseDto[]
     */
    public function getListByUserAndMonth(UserEntity $user, string $month): array;

    public function create(FinancePlanCreateRequestDto $dto, UserEntity $user): FinancePlanResponseDto;

    public function getSummaryByUserAndMonth(UserEntity $user, string $month): FinancePlanSummaryResponseDto;
}
