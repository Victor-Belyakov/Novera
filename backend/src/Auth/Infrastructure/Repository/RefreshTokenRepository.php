<?php

namespace App\Auth\Infrastructure\Repository;

use App\Auth\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Auth\Infrastructure\Persistence\RefreshTokenEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Random\RandomException;

class RefreshTokenRepository extends ServiceEntityRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshTokenEntity::class);
    }

    /**
     * @param RefreshTokenEntity $token
     * @return void
     */
    public function save(RefreshTokenEntity $token): void
    {
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
    }

    /**
     * @param RefreshTokenEntity $token
     * @return void
     */
    public function remove(RefreshTokenEntity $token): void
    {
        $this->getEntityManager()->remove($token);
        $this->getEntityManager()->flush();
    }

    /**
     * @param UserEntity $user
     * @return void
     */
    public function removeAllForUser(UserEntity $user): void
    {
        $tokens = $this->findBy(['user' => $user]);
        foreach ($tokens as $token) {
            $this->getEntityManager()->remove($token);
        }
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $token
     * @return object
     */
    public function findByToken(string $token): object
    {
        return $this->findOneBy(['token' => $token]);
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function createForUser(UserEntity $user, string $expiresIn = '+14 days'): RefreshTokenEntity
    {
        $token = bin2hex(random_bytes(32));
        $refresh = new RefreshTokenEntity(
            $user,
            $token,
            new DateTimeImmutable($expiresIn)
        );

        $this->save($refresh);

        return $refresh;
    }
}
