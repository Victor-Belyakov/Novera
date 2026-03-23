<?php

namespace App\User\Application\Service;

use App\User\Application\DTO\UserListDto;
use App\User\Application\DTO\UserResponseDto;
use App\User\Application\DTO\UserUpdateRequestDto;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Service\UserServiceInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use DateMalformedStringException;
use DateTimeImmutable;

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
        $user = $this->repository->findById($id);

        if (!$user instanceof UserEntity) {
            return null;
        }

        return $this->userToDto($user);
    }

    /**
     * @param UserEntity $user
     * @param UserUpdateRequestDto $dto
     * @return UserResponseDto
     * @throws DateMalformedStringException
     */
    public function update(UserEntity $user, UserUpdateRequestDto $dto): UserResponseDto
    {
        if ($dto->email !== null) {
            $user->setEmail($dto->email);
        }
        if ($dto->phone !== null) {
            $user->setPhone($dto->phone);
        }
        if ($dto->name !== null) {
            $user->setName($dto->name);
        }
        if ($dto->last_name !== null) {
            $user->setLastName($dto->last_name);
        }
        if ($dto->middle_name !== null) {
            $user->setMiddleName($dto->middle_name === '' ? null : $dto->middle_name);
        }
        if ($dto->date_of_birth !== null) {
            $user->setDateOfBirth(new DateTimeImmutable($dto->date_of_birth));
        }
        if ($dto->telegram_id !== null) {
            $user->setTelegramId($dto->telegram_id === '' ? null : $dto->telegram_id);
        }

        $this->repository->save($user);

        return $this->userToDto($user);
    }

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
    ): array {
        $users = $this->repository->findForListing($fioFilter, $phoneFilter, $sortBy, $sortOrder);
        return array_map(
            fn (UserEntity $u) => new UserListDto(
                id: $u->getId(),
                fio: $u->getFullName(),
                email: $u->getEmail(),
                phone: $u->getPhone(),
                created_at: $u->getCreatedAt()->format('Y-m-d H:i:s'),
                telegram_id: $u->getTelegramId()
            ),
            $users
        );
    }

    private function userToDto(UserEntity $user): UserResponseDto
    {
        return new UserResponseDto(
            id: $user->getId(),
            email: $user->getEmail(),
            phone: $user->getPhone(),
            name: $user->getName(),
            last_name: $user->getLastName(),
            middle_name: $user->getMiddleName(),
            date_of_birth: $user->getDateOfBirth()->format('Y-m-d'),
            telegram_id: $user->getTelegramId()
        );
    }
}
