<?php

namespace App\User\Domain\Repository;

use App\User\Infrastructure\Persistence\UserEntity;

interface UserRepositoryInterface
{
    /**
     * @param UserEntity $user
     * @return void
     */
    public function save(UserEntity $user): void;

    /**
     * @param UserEntity $user
     * @return void
     */
    public function remove(UserEntity $user): void;
}
