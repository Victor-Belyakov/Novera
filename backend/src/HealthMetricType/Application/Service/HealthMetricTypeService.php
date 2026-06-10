<?php

namespace App\HealthMetricType\Application\Service;

use App\HealthMetricType\Application\DTO\HealthMetricTypeCreateRequestDto;
use App\HealthMetricType\Application\DTO\HealthMetricTypeResponseDto;
use App\HealthMetricType\Domain\Repository\HealthMetricTypeRepositoryInterface;
use App\HealthMetricType\Domain\Service\HealthMetricTypeServiceInterface;
use App\HealthMetricType\Infrastructure\Persistence\HealthMetricTypeEntity;

final class HealthMetricTypeService implements HealthMetricTypeServiceInterface
{
    public function __construct(
        private HealthMetricTypeRepositoryInterface $repository,
    ) {
    }

    public function getList(bool $activeOnly = false): array
    {
        $items = $activeOnly ? $this->repository->findAllActive() : $this->repository->findAll();

        return array_map(fn (HealthMetricTypeEntity $item) => $this->toDto($item), $items);
    }

    public function create(HealthMetricTypeCreateRequestDto $dto): HealthMetricTypeResponseDto
    {
        $entity = new HealthMetricTypeEntity();
        $entity->setTitle(trim($dto->title));
        $entity->setSlug(trim($dto->slug));
        $entity->setValueKind($dto->value_kind);
        $entity->setUnit($dto->unit !== null && $dto->unit !== '' ? trim($dto->unit) : null);

        $this->repository->save($entity);

        return $this->toDto($entity);
    }

    private function toDto(HealthMetricTypeEntity $entity): HealthMetricTypeResponseDto
    {
        return new HealthMetricTypeResponseDto(
            id: $entity->getId(),
            title: $entity->getTitle(),
            slug: $entity->getSlug(),
            value_kind: $entity->getValueKind(),
            unit: $entity->getUnit(),
            is_active: $entity->isActive(),
            created_at: $entity->getCreatedAt()->format('Y-m-d H:i:s'),
            updated_at: $entity->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
