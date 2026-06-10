<?php

namespace App\Task\Infrastructure\Repository;

use App\Task\Domain\Repository\TaskRepositoryInterface;
use App\Task\Infrastructure\Persistence\TaskEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRepository extends ServiceEntityRepository implements TaskRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskEntity::class);
    }

    public function findById(int $id): ?TaskEntity
    {
        $result = parent::find($id);

        return $result instanceof TaskEntity ? $result : null;
    }

    /**
     * @return TaskEntity[]
     */
    public function findAllNotDeleted(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.goal', 'g')->addSelect('g')
            ->where('t.deletedAt IS NULL')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return TaskEntity[]
     */
    public function findAllByUser(UserEntity $user): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.goal', 'g')->addSelect('g')
            ->where('t.deletedAt IS NULL')
            ->andWhere('(t.createdBy = :user OR t.assignee = :user)')
            ->setParameter('user', $user)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function save(TaskEntity $task): void
    {
        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush();
    }

    public function remove(TaskEntity $task): void
    {
        $this->getEntityManager()->remove($task);
        $this->getEntityManager()->flush();
    }
}
