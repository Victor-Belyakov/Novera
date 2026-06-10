<?php

namespace App\Telegram\Infrastructure\Repository;

use App\Telegram\Domain\Repository\TelegramLinkTokenRepositoryInterface;
use App\Telegram\Infrastructure\Persistence\TelegramLinkTokenEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TelegramLinkTokenRepository extends ServiceEntityRepository implements TelegramLinkTokenRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramLinkTokenEntity::class);
    }

    public function save(TelegramLinkTokenEntity $token): void
    {
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
    }

    public function findByToken(string $token): ?TelegramLinkTokenEntity
    {
        $result = $this->findOneBy(['token' => $token]);

        return $result instanceof TelegramLinkTokenEntity ? $result : null;
    }

    public function removeActiveTokensForUser(UserEntity $user): void
    {
        $tokens = $this->findBy(['user' => $user, 'consumedAt' => null]);

        foreach ($tokens as $token) {
            $this->getEntityManager()->remove($token);
        }

        $this->getEntityManager()->flush();
    }
}
