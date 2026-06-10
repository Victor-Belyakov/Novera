<?php

namespace App\PersonalState\Domain\Service;

use App\PersonalState\Application\DTO\PersonalStateResponseDto;
use App\User\Infrastructure\Persistence\UserEntity;

interface PersonalStateServiceInterface
{
    public function getByUser(UserEntity $user): PersonalStateResponseDto;
}
