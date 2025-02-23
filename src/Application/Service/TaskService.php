<?php

namespace App\Application\Service;
use App\Domain\Entity\Task;
use App\Infrastructure\Repository\TaskRepository;

class TaskService
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {}

    public function createTask(string $title, string $description, int $userId, string $priority = Task::PRIORITY_MEDIUM): Task
    {
        $task = new Task(
            null,
            $title,
            $description,
            Task::STATUS_NEW,
            $priority,
            $userId
        );

        return $this->taskRepository->save($task);
    }

    public function updateTask(int $id, array $data): ?Task
    {
        $task = $this->taskRepository->findById($id);

        if (!$task) {
            return null;
        }

        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }
        if (isset($data['status'])) {
            $task->setStatus($data['status']);
        }
        if (isset($data['priority'])) {
            $task->setPriority($data['priority']);
        }

        return $this->taskRepository->save($task);
    }

    public function deleteTask(int $id): void
    {
        $this->taskRepository->delete($id);
    }

    public function getTask(int $id): ?Task
    {
        return $this->taskRepository->findById($id);
    }

    public function getAllTasks(): array
    {
        return $this->taskRepository->findAll();
    }

    public function getUserTasks(int $userId): array
    {
        return $this->taskRepository->findByUserId($userId);
    }
}