<?php

namespace App\User\Domain\Service;

use App\User\Application\DTO\UserResponseDto;

interface UserServiceInterface
{
    /**
     * @param int $id
     * @return UserResponseDto|null
     */
    public function getUser(int $id): ?UserResponseDto;
}
