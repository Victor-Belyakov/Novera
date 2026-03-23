<?php

namespace App\User\Domain\Service;

use App\User\Application\DTO\UserListDto;
use App\User\Application\DTO\UserResponseDto;
use App\User\Application\DTO\UserUpdateRequestDto;
use App\User\Infrastructure\Persistence\UserEntity;

interface UserServiceInterface
{
    /**
     * @param int $id
     * @return UserResponseDto|null
     */
    public function getUser(int $id): ?UserResponseDto;

    /**
     * @param UserEntity $user
     * @param UserUpdateRequestDto $dto
     * @return UserResponseDto
     */
    public function update(UserEntity $user, UserUpdateRequestDto $dto): UserResponseDto;

    /**
     * @param string|null $fioFilter
     * @param string|null $phoneFilter
     * @param string $sortBy
     * @param string $sortOrder
     * @return UserListDto[]
     */
    public function getList(
        ?string $fioFilter,
        ?string $phoneFilter,
        string $sortBy = 'id',
        string $sortOrder = 'ASC'
    ): array;
}
