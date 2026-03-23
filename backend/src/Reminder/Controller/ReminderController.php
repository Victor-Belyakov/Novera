<?php

namespace App\Reminder\Controller;

use App\Reminder\Application\DTO\ReminderCreateRequestDto;
use App\Reminder\Application\DTO\ReminderUpdateRequestDto;
use App\Reminder\Domain\Service\ReminderServiceInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/reminders')]
class ReminderController extends AbstractController
{
    public function __construct(
        private ReminderServiceInterface $reminderService,
        private SerializerInterface $serializer,
    ) {
    }

    /** Список напоминаний текущего пользователя */
    #[Route('', methods: ['GET'])]
    public function list(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $list = $this->reminderService->getListByUser($user);
        return $this->json($list, Response::HTTP_OK);
    }

    /** Одно напоминание по ID */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $reminder = $this->reminderService->getById($id, $user);
        if ($reminder === null) {
            return $this->json(['error' => 'Reminder not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($reminder, Response::HTTP_OK);
    }

    /** Создание напоминания */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        try {
            /** @var ReminderCreateRequestDto $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                ReminderCreateRequestDto::class,
                'json'
            );
            $reminder = $this->reminderService->create($dto, $user);
            return $this->json($reminder, Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /** Обновление напоминания */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function update(int $id, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        try {
            /** @var ReminderUpdateRequestDto $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                ReminderUpdateRequestDto::class,
                'json'
            );
            $reminder = $this->reminderService->update($id, $dto, $user);
            if ($reminder === null) {
                return $this->json(['error' => 'Reminder not found'], Response::HTTP_NOT_FOUND);
            }
            return $this->json($reminder, Response::HTTP_OK);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /** Удаление напоминания (soft delete) */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        if (!$this->reminderService->delete($id, $user)) {
            return $this->json(['error' => 'Reminder not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json(['ok' => true], Response::HTTP_OK);
    }
}
