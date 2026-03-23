<?php

namespace App\Task\Controller;

use App\Task\Application\DTO\TaskCreateRequestDto;
use App\Task\Application\DTO\TaskUpdateRequestDto;
use App\Task\Domain\Service\TaskServiceInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/tasks')]
class TaskController extends AbstractController
{
    public function __construct(
        private TaskServiceInterface $taskService,
        private SerializerInterface $serializer,
    ) {
    }

    /** Список задач (листинг) */
    #[Route('', methods: ['GET'])]
    public function list(): Response
    {
        $list = $this->taskService->getList();

        return $this->json($list, Response::HTTP_OK);
    }

    /** Просмотр одной задачи */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $task = $this->taskService->getTask($id);

        if ($task === null) {
            return $this->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($task, Response::HTTP_OK);
    }

    /** Создание задачи */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            /** @var TaskCreateRequestDto $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                TaskCreateRequestDto::class,
                'json'
            );
            $task = $this->taskService->create($dto, $user);

            return $this->json($task, Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /** Редактирование задачи */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function update(int $id, Request $request): Response
    {
        try {
            $raw = $request->toArray();
            /** @var TaskUpdateRequestDto $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                TaskUpdateRequestDto::class,
                'json'
            );
            $task = $this->taskService->update($id, $dto, $raw);

            if ($task === null) {
                return $this->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
            }

            return $this->json($task, Response::HTTP_OK);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
