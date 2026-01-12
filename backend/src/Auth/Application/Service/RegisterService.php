<?php

namespace App\Auth\Application\Service;

use App\Auth\Application\DTO\RegisterRequestDto;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class RegisterService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    public function register(RegisterRequestDto $dto): UserEntity
    {
        $user = new UserEntity();
        $user->setEmail($dto->email);
        $user->setRoles(['ROLE_USER']);
        $user->setName($dto->name);
        $user->setMiddleName($dto->middle_name);
        $user->setLastName($dto->last_name);
        $user->setDateOfBirth($dto->date_birth);
        $user->setPassword($this->hasher->hashPassword($user, $dto->password));

        $this->userRepository->save($user);

        return $user;
    }
}


