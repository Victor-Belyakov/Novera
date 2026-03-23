<?php

namespace App\Goal\Controller;

use App\Goal\Application\DTO\GoalCreateRequestDto;
use App\Goal\Application\DTO\GoalUpdateRequestDto;
use App\Goal\Domain\Service\GoalServiceInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/goals')]
class GoalController extends AbstractController
{
    public function __construct(
        private GoalServiceInterface $goalService,
        private SerializerInterface $serializer,
    ) {
    }

    /** Список целей */
    #[Route('', methods: ['GET'])]
    public function list(): Response
    {
        $list = $this->goalService->getList();
        return $this->json($list, Response::HTTP_OK);
    }

    /** Создание цели */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        try {
            /** @var GoalCreateRequestDto $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                GoalCreateRequestDto::class,
                'json'
            );
            $goal = $this->goalService->create($dto, $user);
            return $this->json($goal, Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /** Обновление цели (статус выполнена) */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function update(int $id, Request $request): Response
    {
        try {
            /** @var GoalUpdateRequestDto $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                GoalUpdateRequestDto::class,
                'json'
            );
            $goal = $this->goalService->update($id, $dto);
            if ($goal === null) {
                return $this->json(['error' => 'Goal not found'], Response::HTTP_NOT_FOUND);
            }
            return $this->json($goal, Response::HTTP_OK);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
