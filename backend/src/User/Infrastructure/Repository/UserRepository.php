<?php

namespace App\User\Infrastructure\Repository;

use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserEntity::class);
    }

    /**
     * @param UserEntity $user
     * @return void
     */
    public function save(UserEntity $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @param UserEntity $user
     * @return void
     */
    public function remove(UserEntity $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }
}
