<?php

namespace App\FinancePlan\Controller;

use App\FinancePlan\Application\DTO\FinancePlanCreateRequestDto;
use App\FinancePlan\Domain\Service\FinancePlanServiceInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/finance-plans')]
class FinancePlanController extends AbstractController
{
    public function __construct(
        private FinancePlanServiceInterface $financePlanService,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $month = (string) $request->query->get('month', '');

        return $this->json($this->financePlanService->getListByUserAndMonth($user, $month), Response::HTTP_OK);
    }

    #[Route('/summary', methods: ['GET'])]
    public function summary(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $month = (string) $request->query->get('month', '');

        return $this->json($this->financePlanService->getSummaryByUserAndMonth($user, $month), Response::HTTP_OK);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            /** @var FinancePlanCreateRequestDto $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                FinancePlanCreateRequestDto::class,
                'json'
            );

            return $this->json($this->financePlanService->create($dto, $user), Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
