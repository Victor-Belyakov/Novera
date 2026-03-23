<?php

namespace App\Goal\Domain\Service;

use App\Goal\Application\DTO\GoalCreateRequestDto;
use App\Goal\Application\DTO\GoalResponseDto;
use App\Goal\Application\DTO\GoalUpdateRequestDto;
use App\User\Infrastructure\Persistence\UserEntity;

interface GoalServiceInterface
{
    /**
     * @return list<GoalResponseDto>
     */
    public function getList(): array;

    public function create(GoalCreateRequestDto $dto, UserEntity $createdBy): GoalResponseDto;

    public function update(int $id, GoalUpdateRequestDto $dto): ?GoalResponseDto;
}
