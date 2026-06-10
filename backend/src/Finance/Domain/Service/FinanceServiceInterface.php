<?php

namespace App\Finance\Domain\Service;

use App\Finance\Application\DTO\FinanceCreateRequestDto;
use App\Finance\Application\DTO\FinanceResponseDto;
use App\Finance\Application\DTO\FinanceUpdateRequestDto;
use App\User\Infrastructure\Persistence\UserEntity;

interface FinanceServiceInterface
{
    /**
     * @return FinanceResponseDto[]
     */
    public function getListByUser(UserEntity $user): array;

    public function create(FinanceCreateRequestDto $dto, UserEntity $user): FinanceResponseDto;

    public function update(int $id, FinanceUpdateRequestDto $dto, UserEntity $user, array $rawPayload = []): ?FinanceResponseDto;
}
