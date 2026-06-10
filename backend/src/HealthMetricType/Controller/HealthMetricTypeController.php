<?php

namespace App\HealthMetricType\Controller;

use App\HealthMetricType\Application\DTO\HealthMetricTypeCreateRequestDto;
use App\HealthMetricType\Domain\Service\HealthMetricTypeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/health-metric-types')]
class HealthMetricTypeController extends AbstractController
{
    public function __construct(
        private HealthMetricTypeServiceInterface $service,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $activeOnly = filter_var($request->query->get('active_only', false), FILTER_VALIDATE_BOOL);

        return $this->json($this->service->getList($activeOnly), Response::HTTP_OK);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): Response
    {
        try {
            /** @var HealthMetricTypeCreateRequestDto $dto */
            $dto = $this->serializer->deserialize($request->getContent(), HealthMetricTypeCreateRequestDto::class, 'json');
            return $this->json($this->service->create($dto), Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
