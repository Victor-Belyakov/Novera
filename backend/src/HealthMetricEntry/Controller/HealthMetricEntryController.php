<?php

namespace App\HealthMetricEntry\Controller;

use App\HealthMetricEntry\Application\DTO\HealthMetricEntryCreateRequestDto;
use App\HealthMetricEntry\Domain\Service\HealthMetricEntryServiceInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/health-metrics')]
class HealthMetricEntryController extends AbstractController
{
    public function __construct(
        private HealthMetricEntryServiceInterface $service,
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

        return $this->json($this->service->getListByUser($user), Response::HTTP_OK);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntity) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            /** @var HealthMetricEntryCreateRequestDto $dto */
            $dto = $this->serializer->deserialize($request->getContent(), HealthMetricEntryCreateRequestDto::class, 'json');
            return $this->json($this->service->create($dto, $user), Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
