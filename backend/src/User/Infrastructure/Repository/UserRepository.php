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

    public function findById(int $id): ?UserEntity
    {
        $result = parent::find($id);

        return $result instanceof UserEntity ? $result : null;
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

    /**
     * @param string|null $fioFilter
     * @param string|null $phoneFilter
     * @param string $sortBy
     * @param string $sortOrder
     * @return UserEntity[]
     */
    public function findForListing(
        ?string $fioFilter,
        ?string $phoneFilter,
        string $sortBy = 'id',
        string $sortOrder = 'ASC'
    ): array {
        $qb = $this->createQueryBuilder('u');

        if ($fioFilter !== null && $fioFilter !== '') {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('u.last_name', ':fio'),
                    $qb->expr()->like('u.name', ':fio'),
                    $qb->expr()->like('u.middle_name', ':fio')
                )
            );
            $qb->setParameter('fio', '%' . $fioFilter . '%');
        }

        if ($phoneFilter !== null && $phoneFilter !== '') {
            $digits = preg_replace('/\D/', '', $phoneFilter);
            if ($digits !== '') {
                $qb->andWhere($qb->expr()->like('u.phone', ':phone'));
                $qb->setParameter('phone', '%' . $digits . '%');
            }
        }

        $allowedSort = ['id', 'fio', 'email', 'phone', 'created_at'];
        $orderBy = in_array($sortBy, $allowedSort, true) ? $sortBy : 'id';
        $direction = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        if ($orderBy === 'fio') {
            $qb->orderBy('u.last_name', $direction)
                ->addOrderBy('u.name', $direction)
                ->addOrderBy('u.middle_name', $direction);
        } elseif ($orderBy === 'created_at') {
            $qb->orderBy('u.createdAt', $direction);
        } else {
            $qb->orderBy('u.' . $orderBy, $direction);
        }

        return $qb->getQuery()->getResult();
    }
}
