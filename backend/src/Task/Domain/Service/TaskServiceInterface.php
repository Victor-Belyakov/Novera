<?php

namespace App\Task\Domain\Service;

use App\Task\Application\DTO\TaskCreateRequestDto;
use App\Task\Application\DTO\TaskResponseDto;
use App\Task\Application\DTO\TaskUpdateRequestDto;
use App\User\Infrastructure\Persistence\UserEntity;

interface TaskServiceInterface
{
    /**
     * @return TaskResponseDto[]
     */
    public function getList(): array;

    public function getTask(int $id): ?TaskResponseDto;

    public function create(TaskCreateRequestDto $dto, UserEntity $createdBy): TaskResponseDto;

    /**
     * @param array<string, mixed> $rawPayload Raw request body (to detect explicit null for goal_id)
     */
    public function update(int $id, TaskUpdateRequestDto $dto, array $rawPayload = []): ?TaskResponseDto;
}
