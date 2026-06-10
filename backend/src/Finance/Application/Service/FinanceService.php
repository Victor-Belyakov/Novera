<?php

namespace App\Finance\Application\Service;

use App\Finance\Application\DTO\FinanceCreateRequestDto;
use App\Finance\Application\DTO\FinanceResponseDto;
use App\Finance\Application\DTO\FinanceUpdateRequestDto;
use App\Finance\Domain\Enum\FinanceTypeEnum;
use App\Finance\Domain\Repository\FinanceRepositoryInterface;
use App\Finance\Domain\Service\FinanceServiceInterface;
use App\Finance\Infrastructure\Persistence\FinanceEntity;
use App\FinanceCategory\Domain\Repository\FinanceCategoryRepositoryInterface;
use App\FinanceCategory\Infrastructure\Persistence\FinanceCategoryEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;

final readonly class FinanceService implements FinanceServiceInterface
{
    public function __construct(
        private FinanceRepositoryInterface $financeRepository,
        private FinanceCategoryRepositoryInterface $financeCategoryRepository,
    ) {
    }

    public function getListByUser(UserEntity $user): array
    {
        $items = $this->financeRepository->findAllByUser($user);

        return array_map(
            fn (FinanceEntity $finance) => $this->toDto($finance),
            $items
        );
    }

    public function create(FinanceCreateRequestDto $dto, UserEntity $user): FinanceResponseDto
    {
        $entity = new FinanceEntity();
        $entity->setTitle(trim($dto->title));
        $entity->setDescription($dto->description);
        $entity->setAmount($this->normalizeAmount($dto->amount));
        $entity->setType(FinanceTypeEnum::from($dto->type));
        $entity->setCategory($this->resolveCategory($dto->category_id));
        $entity->setOperationDate($this->resolveOperationDate($dto->operation_date));
        $entity->setCreatedBy($user);

        $this->financeRepository->save($entity);

        return $this->toDto($entity);
    }

    public function update(int $id, FinanceUpdateRequestDto $dto, UserEntity $user, array $rawPayload = []): ?FinanceResponseDto
    {
        $entity = $this->financeRepository->findById($id);

        if (!$entity instanceof FinanceEntity || $entity->getCreatedBy()->getId() !== $user->getId()) {
            return null;
        }

        if ($dto->title !== null) {
            $entity->setTitle(trim($dto->title));
        }

        if ($dto->description !== null) {
            $entity->setDescription($dto->description);
        }

        if ($dto->amount !== null) {
            $entity->setAmount($this->normalizeAmount($dto->amount));
        }

        if ($dto->type !== null) {
            $entity->setType(FinanceTypeEnum::from($dto->type));
        }

        if (array_key_exists('category_id', $rawPayload)) {
            $entity->setCategory($this->resolveCategory($rawPayload['category_id']));
        }

        if ($dto->operation_date !== null) {
            $entity->setOperationDate($this->resolveOperationDate($dto->operation_date));
        }

        $this->financeRepository->save($entity);

        return $this->toDto($entity);
    }

    private function resolveCategory(mixed $categoryId): ?FinanceCategoryEntity
    {
        if ($categoryId === null || $categoryId === '') {
            return null;
        }

        return $this->financeCategoryRepository->findById((int) $categoryId);
    }

    private function resolveOperationDate(string $operationDate): DateTimeImmutable
    {
        if ($operationDate === '') {
            return new DateTimeImmutable('today');
        }

        return new DateTimeImmutable($operationDate);
    }

    private function normalizeAmount(string $amount): string
    {
        $normalized = str_replace(',', '.', trim($amount));

        if (!is_numeric($normalized)) {
            throw new \InvalidArgumentException('Amount must be numeric.');
        }

        return number_format((float) $normalized, 2, '.', '');
    }

    private function toDto(FinanceEntity $finance): FinanceResponseDto
    {
        $category = $finance->getCategory();
        $createdBy = $finance->getCreatedBy();

        return new FinanceResponseDto(
            id: $finance->getId(),
            title: $finance->getTitle(),
            description: $finance->getDescription(),
            amount: $finance->getAmount(),
            type: $finance->getType()->value,
            type_label: $finance->getType()->label(),
            category_id: $category?->getId(),
            category_title: $category?->getTitle(),
            operation_date: $finance->getOperationDate()->format('Y-m-d'),
            created_by_id: $createdBy->getId(),
            created_by_name: $createdBy->getFullName(),
            created_at: $finance->getCreatedAt()->format('Y-m-d H:i:s'),
            updated_at: $finance->getUpdatedAt()->format('Y-m-d H:i:s'),
            deleted_at: $finance->getDeletedAt()?->format('Y-m-d H:i:s'),
        );
    }
}
