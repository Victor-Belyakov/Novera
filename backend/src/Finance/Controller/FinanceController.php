<?php

namespace App\Finance\Controller;

use App\Finance\Application\DTO\FinanceCreateRequestDto;
use App\Finance\Application\DTO\FinanceUpdateRequestDto;
use App\Finance\Domain\Service\FinanceServiceInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/finances')]
class FinanceController extends AbstractController
{
    public function __construct(
        private FinanceServiceInterface $financeService,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json($this->financeService->getListByUser($user), Response::HTTP_OK);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            /** @var FinanceCreateRequestDto $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                FinanceCreateRequestDto::class,
                'json'
            );

            return $this->json(
                $this->financeService->create($dto, $user),
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function update(int $id, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $raw = $request->toArray();
            /** @var FinanceUpdateRequestDto $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                FinanceUpdateRequestDto::class,
                'json'
            );

            $finance = $this->financeService->update($id, $dto, $user, $raw);
            if ($finance === null) {
                return $this->json(['error' => 'Finance not found'], Response::HTTP_NOT_FOUND);
            }

            return $this->json($finance, Response::HTTP_OK);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
