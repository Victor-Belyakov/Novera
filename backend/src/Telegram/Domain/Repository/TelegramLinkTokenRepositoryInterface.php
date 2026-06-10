<?php

namespace App\Telegram\Domain\Repository;

use App\Telegram\Infrastructure\Persistence\TelegramLinkTokenEntity;
use App\User\Infrastructure\Persistence\UserEntity;

interface TelegramLinkTokenRepositoryInterface
{
    public function save(TelegramLinkTokenEntity $token): void;

    public function findByToken(string $token): ?TelegramLinkTokenEntity;

    public function removeActiveTokensForUser(UserEntity $user): void;
}
