<?php

namespace App\HealthMetricEntry\Application\Service;

use App\HealthMetricEntry\Application\DTO\HealthMetricEntryCreateRequestDto;
use App\HealthMetricEntry\Application\DTO\HealthMetricEntryResponseDto;
use App\HealthMetricEntry\Domain\Repository\HealthMetricEntryRepositoryInterface;
use App\HealthMetricEntry\Domain\Service\HealthMetricEntryServiceInterface;
use App\HealthMetricEntry\Infrastructure\Persistence\HealthMetricEntryEntity;
use App\HealthMetricType\Domain\Repository\HealthMetricTypeRepositoryInterface;
use App\HealthMetricType\Infrastructure\Persistence\HealthMetricTypeEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;

final readonly class HealthMetricEntryService implements HealthMetricEntryServiceInterface
{
    public function __construct(
        private HealthMetricEntryRepositoryInterface $repository,
        private HealthMetricTypeRepositoryInterface $typeRepository,
    ) {
    }

    public function getListByUser(UserEntity $user): array
    {
        $items = $this->repository->findAllByUser($user);
        return array_map(fn (HealthMetricEntryEntity $item) => $this->toDto($item), $items);
    }

    public function create(HealthMetricEntryCreateRequestDto $dto, UserEntity $user): HealthMetricEntryResponseDto
    {
        $type = $this->typeRepository->findById($dto->metric_type_id);
        if (!$type instanceof HealthMetricTypeEntity) {
            throw new \InvalidArgumentException('Health metric type not found.');
        }

        $entity = new HealthMetricEntryEntity();
        $entity->setUser($user);
        $entity->setMetricType($type);
        $entity->setRecordedAt($dto->recorded_at !== '' ? new DateTimeImmutable($dto->recorded_at) : new DateTimeImmutable());
        $entity->setValueNumber($dto->value_number !== null && $dto->value_number !== '' ? $this->normalizeNumber($dto->value_number) : null);
        $entity->setValueText($dto->value_text !== null && $dto->value_text !== '' ? trim($dto->value_text) : null);
        $entity->setNote($dto->note !== null && $dto->note !== '' ? trim($dto->note) : null);

        $this->repository->save($entity);

        return $this->toDto($entity);
    }

    private function normalizeNumber(string|int|float $value): string
    {
        $normalized = str_replace(',', '.', trim((string) $value));
        if (!is_numeric($normalized)) {
            throw new \InvalidArgumentException('Value number must be numeric.');
        }
        return number_format((float) $normalized, 2, '.', '');
    }

    private function toDto(HealthMetricEntryEntity $entity): HealthMetricEntryResponseDto
    {
        $type = $entity->getMetricType();
        return new HealthMetricEntryResponseDto(
            id: $entity->getId(),
            metric_type_id: $type->getId(),
            metric_type_title: $type->getTitle(),
            metric_type_slug: $type->getSlug(),
            value_kind: $type->getValueKind(),
            unit: $type->getUnit(),
            recorded_at: $entity->getRecordedAt()->format('Y-m-d H:i:s'),
            value_number: $entity->getValueNumber(),
            value_text: $entity->getValueText(),
            note: $entity->getNote(),
            created_at: $entity->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
