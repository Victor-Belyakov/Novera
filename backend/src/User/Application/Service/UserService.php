<?php

namespace App\User\Application\Service;

use App\User\Application\DTO\UserResponseDto;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Service\UserServiceInterface;
use App\User\Infrastructure\Persistence\UserEntity;

readonly class UserService implements UserServiceInterface
{
    /**
     * @param UserRepositoryInterface $repository
     */
    public function __construct(
        private UserRepositoryInterface $repository
    ) {
    }

    /**
     * @param int $id
     * @return UserResponseDto|null
     */
    public function getUser(int $id): ?UserResponseDto
    {
        /** @var UserEntity|null $user */
        $user = $this->repository->find($id);

        if (!$user instanceof UserEntity) {
            return null;
        }

        return new UserResponseDto(
            id: $user->getId(),
            email: $user->getEmail(),
            name: $user->getName(),
            last_name: $user->getLastName(),
            middle_name: $user->getMiddleName(),
            date_of_birth: $user->getDateOfBirth()->format('Y-m-d')
        );
    }
}
