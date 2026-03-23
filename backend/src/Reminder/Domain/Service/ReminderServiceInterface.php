<?php

namespace App\Reminder\Domain\Service;

use App\Reminder\Application\DTO\ReminderCreateRequestDto;
use App\Reminder\Application\DTO\ReminderResponseDto;
use App\Reminder\Application\DTO\ReminderUpdateRequestDto;
use App\User\Infrastructure\Persistence\UserEntity;

interface ReminderServiceInterface
{
    /**
     * @return list<ReminderResponseDto>
     */
    public function getListByUser(UserEntity $user): array;

    public function getById(int $id, UserEntity $user): ?ReminderResponseDto;

    public function create(ReminderCreateRequestDto $dto, UserEntity $user): ReminderResponseDto;

    public function update(int $id, ReminderUpdateRequestDto $dto, UserEntity $user): ?ReminderResponseDto;

    public function delete(int $id, UserEntity $user): bool;
}
