<?php

namespace App\Reminder\Infrastructure\Repository;

use App\Reminder\Domain\Repository\ReminderRepositoryInterface;
use App\Reminder\Infrastructure\Persistence\ReminderEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class ReminderRepository implements ReminderRepositoryInterface
{
    private EntityRepository $repository;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(ReminderEntity::class);
    }

    public function findById(int $id): ?ReminderEntity
    {
        $entity = $this->repository->find($id);
        return $entity instanceof ReminderEntity ? $entity : null;
    }

    /** @return list<ReminderEntity> */
    public function findAllByUser(UserEntity $user): array
    {
        return $this->repository->createQueryBuilder('r')
            ->where('r.user = :user')
            ->andWhere('r.deletedAt IS NULL')
            ->setParameter('user', $user)
            ->orderBy('r.notifyAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<ReminderEntity> */
    public function findDueActive(UserEntity $user, \DateTimeImmutable $until): array
    {
        return $this->repository->createQueryBuilder('r')
            ->where('r.user = :user')
            ->andWhere('r.deletedAt IS NULL')
            ->andWhere('r.status = :status')
            ->andWhere('r.notifyAt <= :until')
            ->setParameter('user', $user)
            ->setParameter('status', 'active')
            ->setParameter('until', $until)
            ->orderBy('r.notifyAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<ReminderEntity> */
    public function findAllDueActive(\DateTimeImmutable $until): array
    {
        return $this->repository->createQueryBuilder('r')
            ->innerJoin('r.user', 'u')
            ->addSelect('u')
            ->andWhere('r.deletedAt IS NULL')
            ->andWhere('r.status = :status')
            ->andWhere('r.notifyAt <= :until')
            ->setParameter('status', 'active')
            ->setParameter('until', $until)
            ->orderBy('r.notifyAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(ReminderEntity $entity, bool $flush = true): void
    {
        $this->em->persist($entity);
        if ($flush) {
            $this->em->flush();
        }
    }
}
