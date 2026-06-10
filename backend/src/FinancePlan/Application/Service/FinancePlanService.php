<?php

namespace App\FinancePlan\Application\Service;

use App\Finance\Domain\Enum\FinanceTypeEnum;
use App\Finance\Domain\Repository\FinanceRepositoryInterface;
use App\Finance\Infrastructure\Persistence\FinanceEntity;
use App\FinanceCategory\Domain\Repository\FinanceCategoryRepositoryInterface;
use App\FinanceCategory\Infrastructure\Persistence\FinanceCategoryEntity;
use App\FinancePlan\Application\DTO\FinancePlanCreateRequestDto;
use App\FinancePlan\Application\DTO\FinancePlanResponseDto;
use App\FinancePlan\Application\DTO\FinancePlanSummaryResponseDto;
use App\FinancePlan\Application\DTO\FinancePlanSummaryRowDto;
use App\FinancePlan\Domain\Repository\FinancePlanRepositoryInterface;
use App\FinancePlan\Domain\Service\FinancePlanServiceInterface;
use App\FinancePlan\Infrastructure\Persistence\FinancePlanEntity;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;

final readonly class FinancePlanService implements FinancePlanServiceInterface
{
    public function __construct(
        private FinancePlanRepositoryInterface $financePlanRepository,
        private FinanceCategoryRepositoryInterface $financeCategoryRepository,
        private FinanceRepositoryInterface $financeRepository,
    ) {
    }

    public function getListByUserAndMonth(UserEntity $user, string $month): array
    {
        [$periodStart, $periodEnd] = $this->resolveMonthPeriod($month);
        $plans = $this->financePlanRepository->findAllByUserAndPeriod($user, $periodStart, $periodEnd);

        return array_map(
            fn (FinancePlanEntity $plan) => $this->toDto($plan),
            $plans
        );
    }

    public function create(FinancePlanCreateRequestDto $dto, UserEntity $user): FinancePlanResponseDto
    {
        [$periodStart, $periodEnd] = $this->resolveMonthPeriod($dto->month);

        $entity = new FinancePlanEntity();
        $entity->setTitle(trim($dto->title));
        $entity->setType(FinanceTypeEnum::from($dto->type));
        $entity->setPlannedAmount($this->normalizeAmount($dto->planned_amount));
        $entity->setCategory($this->resolveCategory($dto->category_id));
        $entity->setPeriodStart($periodStart);
        $entity->setPeriodEnd($periodEnd);
        $entity->setCreatedBy($user);

        $this->financePlanRepository->save($entity);

        return $this->toDto($entity);
    }

    public function getSummaryByUserAndMonth(UserEntity $user, string $month): FinancePlanSummaryResponseDto
    {
        [$periodStart, $periodEnd] = $this->resolveMonthPeriod($month);
        $plans = $this->financePlanRepository->findAllByUserAndPeriod($user, $periodStart, $periodEnd);
        $finances = $this->financeRepository->findAllByUserAndPeriod($user, $periodStart, $periodEnd);

        $actualByTypeAndCategory = [];
        foreach ($finances as $finance) {
            $type = $finance->getType()->value;
            $categoryId = $finance->getCategory()?->getId() ?? 0;
            $actualByTypeAndCategory[$type][$categoryId] ??= 0.0;
            $actualByTypeAndCategory[$type][$categoryId] += (float) $finance->getAmount();
        }

        $rows = [];
        $plannedIncome = 0.0;
        $actualIncome = 0.0;
        $plannedExpense = 0.0;
        $actualExpense = 0.0;

        foreach ($plans as $plan) {
            $type = $plan->getType()->value;
            $categoryId = $plan->getCategory()?->getId() ?? 0;
            $planned = (float) $plan->getPlannedAmount();
            $actual = $actualByTypeAndCategory[$type][$categoryId] ?? 0.0;
            $difference = $plan->getType() === FinanceTypeEnum::EXPENSE
                ? $planned - $actual
                : $actual - $planned;

            if ($plan->getType() === FinanceTypeEnum::INCOME) {
                $plannedIncome += $planned;
                $actualIncome += $actual;
            } else {
                $plannedExpense += $planned;
                $actualExpense += $actual;
            }

            $rows[] = new FinancePlanSummaryRowDto(
                title: $plan->getTitle(),
                type: $type,
                type_label: $plan->getType()->label(),
                category_id: $plan->getCategory()?->getId(),
                category_title: $plan->getCategory()?->getTitle(),
                planned_amount: $this->formatDecimal($planned),
                actual_amount: $this->formatDecimal($actual),
                difference: $this->formatDecimal($difference),
            );
        }

        return new FinancePlanSummaryResponseDto(
            month: $periodStart->format('Y-m'),
            planned_income: $this->formatDecimal($plannedIncome),
            actual_income: $this->formatDecimal($actualIncome),
            planned_expense: $this->formatDecimal($plannedExpense),
            actual_expense: $this->formatDecimal($actualExpense),
            balance_plan: $this->formatDecimal($plannedIncome - $plannedExpense),
            balance_actual: $this->formatDecimal($actualIncome - $actualExpense),
            rows: $rows,
        );
    }

    private function resolveCategory(?int $categoryId): ?FinanceCategoryEntity
    {
        if ($categoryId === null) {
            return null;
        }

        return $this->financeCategoryRepository->findById($categoryId);
    }

    /**
     * @return array{0: DateTimeImmutable, 1: DateTimeImmutable}
     */
    private function resolveMonthPeriod(string $month): array
    {
        $monthValue = preg_match('/^\d{4}-\d{2}$/', $month) === 1
            ? $month
            : (new DateTimeImmutable('today'))->format('Y-m');

        $periodStart = new DateTimeImmutable($monthValue . '-01');
        $periodEnd = $periodStart->modify('last day of this month');

        return [$periodStart, $periodEnd];
    }

    private function normalizeAmount(string $amount): string
    {
        $normalized = str_replace(',', '.', trim($amount));

        if (!is_numeric($normalized)) {
            throw new \InvalidArgumentException('Planned amount must be numeric.');
        }

        return number_format((float) $normalized, 2, '.', '');
    }

    private function formatDecimal(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    private function toDto(FinancePlanEntity $plan): FinancePlanResponseDto
    {
        return new FinancePlanResponseDto(
            id: $plan->getId(),
            title: $plan->getTitle(),
            type: $plan->getType()->value,
            type_label: $plan->getType()->label(),
            planned_amount: $plan->getPlannedAmount(),
            category_id: $plan->getCategory()?->getId(),
            category_title: $plan->getCategory()?->getTitle(),
            month: $plan->getPeriodStart()->format('Y-m'),
            period_start: $plan->getPeriodStart()->format('Y-m-d'),
            period_end: $plan->getPeriodEnd()->format('Y-m-d'),
            created_at: $plan->getCreatedAt()->format('Y-m-d H:i:s'),
            updated_at: $plan->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
