<?php

namespace App\FinanceCategory\Controller;

use App\FinanceCategory\Application\DTO\FinanceCategoryCreateRequestDto;
use App\FinanceCategory\Domain\Service\FinanceCategoryServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/finance-categories')]
class FinanceCategoryController extends AbstractController
{
    public function __construct(
        private FinanceCategoryServiceInterface $financeCategoryService,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(): Response
    {
        return $this->json($this->financeCategoryService->getList(), Response::HTTP_OK);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): Response
    {
        try {
            /** @var FinanceCategoryCreateRequestDto $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                FinanceCategoryCreateRequestDto::class,
                'json'
            );

            return $this->json(
                $this->financeCategoryService->create($dto),
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
