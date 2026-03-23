<?php

namespace App\Reminder\Domain\Repository;

use App\Reminder\Infrastructure\Persistence\ReminderEntity;
use App\User\Infrastructure\Persistence\UserEntity;

interface ReminderRepositoryInterface
{
    public function findById(int $id): ?ReminderEntity;

    /**
     * @return list<ReminderEntity>
     */
    public function findAllByUser(UserEntity $user): array;

    /**
     * Активные напоминания с notify_at <= $until (для cron/бота)
     * @return list<ReminderEntity>
     */
    public function findDueActive(UserEntity $user, \DateTimeImmutable $until): array;

    /**
     * Все активные напоминания с notify_at <= $until (для рассылки писем по cron).
     * Пользователь подгружается (join), чтобы не было N+1.
     *
     * @return list<ReminderEntity>
     */
    public function findAllDueActive(\DateTimeImmutable $until): array;

    public function save(ReminderEntity $entity, bool $flush = true): void;
}
