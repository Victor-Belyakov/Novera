<?php

namespace App\Reminder\Application\Service;

use App\Reminder\Application\DTO\ReminderCreateRequestDto;
use App\Reminder\Application\DTO\ReminderResponseDto;
use App\Reminder\Application\DTO\ReminderUpdateRequestDto;
use App\Reminder\Domain\Repository\ReminderRepositoryInterface;
use App\Reminder\Domain\Service\ReminderServiceInterface;
use App\Reminder\Infrastructure\Persistence\ReminderEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;

final class ReminderService implements ReminderServiceInterface
{
    public function __construct(
        private ReminderRepositoryInterface $reminderRepository,
    ) {
    }

    public function getListByUser(UserEntity $user): array
    {
        $entities = $this->reminderRepository->findAllByUser($user);
        return array_map($this->toDto(...), $entities);
    }

    public function getById(int $id, UserEntity $user): ?ReminderResponseDto
    {
        $entity = $this->reminderRepository->findById($id);
        if ($entity === null || $entity->getUser()->getId() !== $user->getId()) {
            return null;
        }
        return $this->toDto($entity);
    }

    public function create(ReminderCreateRequestDto $dto, UserEntity $user): ReminderResponseDto
    {
        $notifyAt = DateTimeImmutable::createFromFormat(
            \DateTimeInterface::ATOM,
            $dto->notify_at
        ) ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dto->notify_at);
        if ($notifyAt === false) {
            $notifyAt = new DateTimeImmutable($dto->notify_at);
        }

        $entity = new ReminderEntity();
        $entity->setTitle($dto->title);
        $entity->setDescription($dto->description);
        $entity->setUser($user);
        $entity->setEntityType($dto->entity_type);
        $entity->setEntityId($dto->entity_id);
        $entity->setNotifyAt($notifyAt);
        $entity->setFrequency($dto->frequency);
        $entity->setWeekDays($dto->week_days);

        $this->reminderRepository->save($entity);
        return $this->toDto($entity);
    }

    public function update(int $id, ReminderUpdateRequestDto $dto, UserEntity $user): ?ReminderResponseDto
    {
        $entity = $this->reminderRepository->findById($id);
        if ($entity === null || $entity->getUser()->getId() !== $user->getId()) {
            return null;
        }

        if ($dto->title !== null) {
            $entity->setTitle($dto->title);
        }
        if ($dto->description !== null) {
            $entity->setDescription($dto->description);
        }
        if ($dto->notify_at !== null && $dto->notify_at !== '') {
            $notifyAt = DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $dto->notify_at)
                ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dto->notify_at);
            if ($notifyAt !== false) {
                $entity->setNotifyAt($notifyAt);
            }
        }
        if ($dto->frequency !== null) {
            $entity->setFrequency($dto->frequency);
        }
        if ($dto->week_days !== null) {
            $entity->setWeekDays($dto->week_days);
        }
        if ($dto->status !== null) {
            $entity->setStatus($dto->status);
        }

        $this->reminderRepository->save($entity);
        return $this->toDto($entity);
    }

    public function delete(int $id, UserEntity $user): bool
    {
        $entity = $this->reminderRepository->findById($id);
        if ($entity === null || $entity->getUser()->getId() !== $user->getId()) {
            return false;
        }
        $entity->setDeletedAt(new DateTimeImmutable());
        $this->reminderRepository->save($entity);
        return true;
    }

    private function toDto(ReminderEntity $r): ReminderResponseDto
    {
        return new ReminderResponseDto(
            id: $r->getId(),
            title: $r->getTitle(),
            description: $r->getDescription(),
            entity_type: $r->getEntityType(),
            entity_id: $r->getEntityId(),
            notify_at: $r->getNotifyAt()->format(\DateTimeInterface::ATOM),
            frequency: $r->getFrequency(),
            week_days: $r->getWeekDays(),
            status: $r->getStatus(),
            created_at: $r->getCreatedAt()->format('Y-m-d H:i:s'),
            updated_at: $r->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
