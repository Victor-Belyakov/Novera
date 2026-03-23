<?php

namespace App\Task\Application\Service;

use App\Task\Application\DTO\TaskCreateRequestDto;
use App\Task\Application\DTO\TaskResponseDto;
use App\Task\Application\DTO\TaskUpdateRequestDto;
use App\Task\Domain\Enum\TaskStatusEnum;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use App\Task\Domain\Service\TaskServiceInterface;
use App\Task\Infrastructure\Persistence\TaskEntity;
use App\Goal\Domain\Repository\GoalRepositoryInterface;
use App\Goal\Infrastructure\Persistence\GoalEntity;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use DateTimeImmutable;

readonly class TaskService implements TaskServiceInterface
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private UserRepositoryInterface $userRepository,
        private GoalRepositoryInterface $goalRepository,
    ) {
    }

    /**
     * @return TaskResponseDto[]
     */
    public function getList(): array
    {
        $tasks = $this->taskRepository->findAllNotDeleted();

        return array_map($this->taskToDto(...), $tasks);
    }

    public function getTask(int $id): ?TaskResponseDto
    {
        $task = $this->taskRepository->findById($id);

        if (!$task instanceof TaskEntity || $task->isDeleted()) {
            return null;
        }

        return $this->taskToDto($task);
    }

    public function create(TaskCreateRequestDto $dto, UserEntity $createdBy): TaskResponseDto
    {
        $task = new TaskEntity();
        $task->setTitle($dto->title);
        $task->setDescription($dto->description);
        $task->setStatus(TaskStatusEnum::from($dto->status));
        $task->setPriority($dto->priority);
        $task->setCreatedBy($createdBy);
        if ($dto->goal_id !== null) {
            $goal = $this->goalRepository->findById($dto->goal_id);
            $task->setGoal($goal instanceof GoalEntity ? $goal : null);
        }

        if ($dto->due_date !== null && $dto->due_date !== '') {
            $task->setDueDate(new DateTimeImmutable($dto->due_date));
        }

        if ($dto->assignee_id !== null) {
            $assignee = $this->userRepository->findById($dto->assignee_id);
            if ($assignee instanceof UserEntity) {
                $task->setAssignee($assignee);
            }
        }

        if ($dto->parent_id !== null) {
            $parent = $this->taskRepository->findById($dto->parent_id);
            if ($parent instanceof TaskEntity) {
                $task->setParent($parent);
            }
        }

        $this->taskRepository->save($task);

        return $this->taskToDto($task);
    }

    /**
     * @param array<string, mixed> $rawPayload
     */
    public function update(int $id, TaskUpdateRequestDto $dto, array $rawPayload = []): ?TaskResponseDto
    {
        $task = $this->taskRepository->findById($id);

        if (!$task instanceof TaskEntity || $task->isDeleted()) {
            return null;
        }

        if ($dto->title !== null) {
            $task->setTitle($dto->title);
        }
        if ($dto->description !== null) {
            $task->setDescription($dto->description);
        }
        if ($dto->status !== null) {
            $task->setStatus(TaskStatusEnum::from($dto->status));
        }
        if ($dto->priority !== null) {
            $task->setPriority($dto->priority);
        }
        if (array_key_exists('goal_id', $rawPayload)) {
            if ($rawPayload['goal_id'] === null) {
                $task->setGoal(null);
            } else {
                $goal = $this->goalRepository->findById((int) $rawPayload['goal_id']);
                $task->setGoal($goal instanceof GoalEntity ? $goal : null);
            }
        }
        if ($dto->due_date !== null) {
            $task->setDueDate($dto->due_date === '' ? null : new DateTimeImmutable($dto->due_date));
        }
        if ($dto->assignee_id !== null) {
            $assignee = $this->userRepository->findById($dto->assignee_id);
            $task->setAssignee($assignee instanceof UserEntity ? $assignee : null);
        }
        if ($dto->parent_id !== null) {
            $parent = $this->taskRepository->findById($dto->parent_id);
            $task->setParent($parent instanceof TaskEntity ? $parent : null);
        }

        $this->taskRepository->save($task);

        return $this->taskToDto($task);
    }

    private function taskToDto(TaskEntity $task): TaskResponseDto
    {
        $assignee = $task->getAssignee();
        $createdBy = $task->getCreatedBy();
        $parent = $task->getParent();
        $goal = $task->getGoal();

        return new TaskResponseDto(
            id: $task->getId(),
            title: $task->getTitle(),
            description: $task->getDescription(),
            assignee_id: $assignee?->getId(),
            assignee_name: $assignee !== null ? $assignee->getFullName() : null,
            created_by_id: $createdBy?->getId(),
            created_by_name: $createdBy !== null ? $createdBy->getFullName() : null,
            parent_id: $parent?->getId(),
            goal_id: $goal?->getId(),
            goal_title: $goal !== null ? $goal->getTitle() : null,
            status: $task->getStatus()->value,
            due_date: $task->getDueDate()?->format('Y-m-d'),
            priority: $task->getPriority(),
            created_at: $task->getCreatedAt()->format('Y-m-d H:i:s'),
            updated_at: $task->getUpdatedAt()->format('Y-m-d H:i:s'),
            deleted_at: $task->getDeletedAt()?->format('Y-m-d H:i:s'),
        );
    }
}
