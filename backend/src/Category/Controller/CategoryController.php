<?php

namespace App\Category\Controller;

use App\Category\Application\DTO\CategoryCreateRequestDto;
use App\Category\Domain\Service\CategoryServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/categories')]
class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryServiceInterface $categoryService,
        private SerializerInterface $serializer,
    ) {
    }

    /** Список категорий */
    #[Route('', methods: ['GET'])]
    public function list(): Response
    {
        $list = $this->categoryService->getList();
        return $this->json($list, Response::HTTP_OK);
    }

    /** Создание категории */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): Response
    {
        try {
            /** @var CategoryCreateRequestDto $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                CategoryCreateRequestDto::class,
                'json'
            );
            $category = $this->categoryService->create($dto);
            return $this->json($category, Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
