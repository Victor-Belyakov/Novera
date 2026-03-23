<?php

namespace App\Category\Application\Service;

use App\Category\Application\DTO\CategoryCreateRequestDto;
use App\Category\Application\DTO\CategoryResponseDto;
use App\Category\Domain\Repository\CategoryRepositoryInterface;
use App\Category\Domain\Service\CategoryServiceInterface;
use App\Category\Infrastructure\Persistence\CategoryEntity;

final class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    public function getList(): array
    {
        $entities = $this->categoryRepository->findAll();
        return array_map(
            fn (CategoryEntity $c) => $this->toDto($c),
            $entities
        );
    }

    public function create(CategoryCreateRequestDto $dto): CategoryResponseDto
    {
        $entity = new CategoryEntity();
        $entity->setTitle($dto->title);
        $this->categoryRepository->save($entity);
        return $this->toDto($entity);
    }

    private function toDto(CategoryEntity $c): CategoryResponseDto
    {
        return new CategoryResponseDto(
            id: $c->getId(),
            title: $c->getTitle(),
            created_at: $c->getCreatedAt()->format('Y-m-d H:i:s'),
            updated_at: $c->getUpdatedAt()->format('Y-m-d H:i:s'),
            deleted_at: $c->getDeletedAt()?->format('Y-m-d H:i:s'),
        );
    }
}
