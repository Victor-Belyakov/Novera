<?php

namespace App\Auth\Domain\Repository;

use App\Auth\Infrastructure\Persistence\RefreshTokenEntity;
use App\User\Infrastructure\Persistence\UserEntity;

interface RefreshTokenRepositoryInterface
{
    /**
     * @param RefreshTokenEntity $token
     * @return void
     */
    public function save(RefreshTokenEntity $token): void;

    /**
     * @param RefreshTokenEntity $token
     * @return void
     */
    public function remove(RefreshTokenEntity $token): void;

    /**
     * @param string $token
     * @return object|null
     */
    public function findByToken(string $token): ?object;

    /**
     * @param UserEntity $user
     * @param string $expiresIn
     * @return RefreshTokenEntity
     */
    public function createForUser(UserEntity $user, string $expiresIn = '+14 days'): RefreshTokenEntity;
}
