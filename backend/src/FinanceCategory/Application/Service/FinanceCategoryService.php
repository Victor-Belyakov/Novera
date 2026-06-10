<?php

namespace App\FinanceCategory\Application\Service;

use App\FinanceCategory\Application\DTO\FinanceCategoryCreateRequestDto;
use App\FinanceCategory\Application\DTO\FinanceCategoryResponseDto;
use App\FinanceCategory\Domain\Repository\FinanceCategoryRepositoryInterface;
use App\FinanceCategory\Domain\Service\FinanceCategoryServiceInterface;
use App\FinanceCategory\Infrastructure\Persistence\FinanceCategoryEntity;

final class FinanceCategoryService implements FinanceCategoryServiceInterface
{
    public function __construct(
        private FinanceCategoryRepositoryInterface $financeCategoryRepository,
    ) {
    }

    public function getList(): array
    {
        $entities = $this->financeCategoryRepository->findAll();

        return array_map(
            fn (FinanceCategoryEntity $category) => $this->toDto($category),
            $entities
        );
    }

    public function create(FinanceCategoryCreateRequestDto $dto): FinanceCategoryResponseDto
    {
        $entity = new FinanceCategoryEntity();
        $entity->setTitle(trim($dto->title));

        $this->financeCategoryRepository->save($entity);

        return $this->toDto($entity);
    }

    private function toDto(FinanceCategoryEntity $category): FinanceCategoryResponseDto
    {
        return new FinanceCategoryResponseDto(
            id: $category->getId(),
            title: $category->getTitle(),
            created_at: $category->getCreatedAt()->format('Y-m-d H:i:s'),
            updated_at: $category->getUpdatedAt()->format('Y-m-d H:i:s'),
            deleted_at: $category->getDeletedAt()?->format('Y-m-d H:i:s'),
        );
    }
}
