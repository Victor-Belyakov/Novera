<?php

namespace App\User\Domain\Repository;

use App\User\Infrastructure\Persistence\UserEntity;

interface UserRepositoryInterface
{
    public function findById(int $id): ?UserEntity;

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

    /**
     * @param string|null $fioFilter
     * @param string|null $phoneFilter
     * @param string $sortBy
     * @param string $sortOrder
     * @return UserEntity[]
     */
    public function findForListing(
        ?string $fioFilter,
        ?string $phoneFilter,
        string $sortBy = 'id',
        string $sortOrder = 'ASC'
    ): array;
}
