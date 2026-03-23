<?php

namespace App\Habit\Infrastructure\Repository;

use App\Habit\Domain\Repository\HabitRepositoryInterface;
use App\Habit\Infrastructure\Persistence\HabitEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class HabitRepository implements HabitRepositoryInterface
{
    private EntityRepository $repository;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(HabitEntity::class);
    }

    public function findById(int $id): ?HabitEntity
    {
        $entity = $this->repository->find($id);
        return $entity instanceof HabitEntity ? $entity : null;
    }

    public function findByIdWithLogs(int $id): ?HabitEntity
    {
        $entity = $this->repository->createQueryBuilder('h')
            ->leftJoin('h.logs', 'logs')->addSelect('logs')
            ->where('h.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
        return $entity instanceof HabitEntity ? $entity : null;
    }

    public function findAllByUser(UserEntity $user): array
    {
        return $this->repository->createQueryBuilder('h')
            ->leftJoin('h.goal', 'g')->addSelect('g')
            ->leftJoin('h.logs', 'logs')->addSelect('logs')
            ->where('h.user = :user')
            ->andWhere('h.deletedAt IS NULL')
            ->setParameter('user', $user)
            ->orderBy('h.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function save(HabitEntity $entity): void
    {
        $this->em->persist($entity);
        $this->em->flush();
    }
}
