<?php

namespace App\Goal\Application\Service;

use App\Category\Domain\Repository\CategoryRepositoryInterface;
use App\Category\Infrastructure\Persistence\CategoryEntity;
use App\Goal\Application\DTO\GoalCreateRequestDto;
use App\Goal\Application\DTO\GoalResponseDto;
use App\Goal\Application\DTO\GoalUpdateRequestDto;
use App\Goal\Domain\Repository\GoalRepositoryInterface;
use App\Goal\Domain\Service\GoalServiceInterface;
use App\Goal\Infrastructure\Persistence\GoalEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;

final class GoalService implements GoalServiceInterface
{
    public function __construct(
        private GoalRepositoryInterface $goalRepository,
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    public function getList(): array
    {
        $entities = $this->goalRepository->findAll();
        return array_map(
            fn (GoalEntity $g) => $this->toDto($g),
            $entities
        );
    }

    public function create(GoalCreateRequestDto $dto, UserEntity $createdBy): GoalResponseDto
    {
        $entity = new GoalEntity();
        $entity->setTitle($dto->title);
        $entity->setCreatedBy($createdBy);
        if ($dto->description !== null && $dto->description !== '') {
            $entity->setDescription($dto->description);
        }
        $dueDateStr = trim($dto->due_date ?? '');
        if ($dueDateStr !== '') {
            $entity->setDueDate(new DateTimeImmutable($dueDateStr));
        }
        if ($dto->category_id !== null) {
            $category = $this->categoryRepository->findById($dto->category_id);
            $entity->setCategory($category instanceof CategoryEntity ? $category : null);
        }
        if (\in_array($dto->type, ['habit', 'result', 'learning', 'project'], true)) {
            $entity->setType($dto->type);
        }
        $entity->setPriority($dto->priority);
        $this->goalRepository->save($entity);
        return $this->toDto($entity);
    }

    public function update(int $id, GoalUpdateRequestDto $dto): ?GoalResponseDto
    {
        $entity = $this->goalRepository->findById($id);
        if (!$entity instanceof GoalEntity || $entity->isDeleted()) {
            return null;
        }
        if ($dto->completed !== null) {
            $entity->setCompleted($dto->completed);
        }
        $this->goalRepository->save($entity);
        return $this->toDto($entity);
    }

    private function toDto(GoalEntity $g): GoalResponseDto
    {
        $category = $g->getCategory();
        $createdBy = $g->getCreatedBy();
        return new GoalResponseDto(
            id: $g->getId(),
            title: $g->getTitle(),
            category_id: $category?->getId(),
            category_title: $category !== null ? $category->getTitle() : null,
            due_date: $g->getDueDate()?->format('Y-m-d'),
            created_by_id: $createdBy?->getId(),
            created_by_name: $createdBy !== null ? $createdBy->getFullName() : null,
            created_at: $g->getCreatedAt()->format('Y-m-d H:i:s'),
            updated_at: $g->getUpdatedAt()->format('Y-m-d H:i:s'),
            deleted_at: $g->getDeletedAt()?->format('Y-m-d H:i:s'),
            completed: $g->getCompleted(),
        );
    }
}
