<?php

namespace App\Habit\Controller;

use App\Habit\Application\DTO\HabitCreateRequestDto;
use App\Habit\Domain\Service\HabitServiceInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/habits')]
class HabitController extends AbstractController
{
    public function __construct(
        private HabitServiceInterface $habitService,
        private SerializerInterface $serializer,
    ) {
    }

    /** Список привычек текущего пользователя */
    #[Route('', methods: ['GET'])]
    public function list(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $list = $this->habitService->getListByUser($user);
        return $this->json($list, Response::HTTP_OK);
    }

    /** Создание привычки */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        try {
            /** @var HabitCreateRequestDto $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                HabitCreateRequestDto::class,
                'json'
            );
            $habit = $this->habitService->create($dto, $user);
            return $this->json($habit, Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /** Отметить выполнение привычки за сегодня или за указанную дату (body: {"date": "YYYY-MM-DD"}). */
    #[Route('/{id}/log', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addLog(int $id, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $body = json_decode($request->getContent(), true) ?: [];
        $date = isset($body['date']) && is_string($body['date']) ? $body['date'] : null;
        $added = $this->habitService->addLog($id, $user, $date);
        if (!$added) {
            return $this->json(
                ['error' => 'Привычка не найдена, нет доступа или выполнение уже отмечено за эту дату'],
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->json(['ok' => true], Response::HTTP_CREATED);
    }

    /** Семь слотов трекера из HabitLog (по frequency: daily — 7 дней, weekly — 7 недель). */
    #[Route('/{id}/logs', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getLogs(int $id): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $result = $this->habitService->getSlots($id, $user);
        if ($result === null) {
            return $this->json(['error' => 'Привычка не найдена'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($result, Response::HTTP_OK);
    }

    /** Отметить дату как пропущенную (body: {"date": "YYYY-MM-DD"}). */
    #[Route('/{id}/logs/skip', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function skipLog(int $id, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $body = json_decode($request->getContent(), true) ?: [];
        $date = isset($body['date']) && is_string($body['date']) ? $body['date'] : '';
        if ($date === '') {
            return $this->json(['error' => 'Укажите date (Y-m-d)'], Response::HTTP_BAD_REQUEST);
        }
        $added = $this->habitService->skipLog($id, $user, $date);
        if (!$added) {
            return $this->json(
                ['error' => 'Привычка не найдена, нет доступа или за эту дату уже есть запись'],
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->json(['ok' => true], Response::HTTP_CREATED);
    }

    /** Удалить отметку за дату (query: date=Y-m-d) */
    #[Route('/{id}/logs', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function deleteLog(int $id, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $date = $request->query->get('date');
        if (!is_string($date) || $date === '') {
            return $this->json(['error' => 'Укажите параметр date (Y-m-d)'], Response::HTTP_BAD_REQUEST);
        }
        $removed = $this->habitService->removeLog($id, $user, $date);
        if (!$removed) {
            return $this->json(['error' => 'Привычка не найдена или лог за эту дату отсутствует'], Response::HTTP_BAD_REQUEST);
        }
        return $this->json(['ok' => true], Response::HTTP_OK);
    }
}
