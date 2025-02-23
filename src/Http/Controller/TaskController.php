<?php

namespace App\Http\Controller;

use App\Application\Service\TaskService;
use App\Domain\Entity\Task;

class TaskController
{
    public function __construct(
        private TaskService $taskService
    ) {}

    public function create(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $task = $this->taskService->createTask(
            $data['title'],
            $data['description'],
            $_REQUEST['user']->getId(),
            $data['priority'] ?? Task::PRIORITY_MEDIUM
        );

        return $task->toArray();
    }

    public function update(array $params): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $task = $this->taskService->updateTask((int)$params['id'], $data);

        if (!$task) {
            throw new \RuntimeException('Task not found');
        }

        return $task->toArray();
    }

    public function delete(array $params): array
    {
        $this->taskService->deleteTask((int)$params['id']);
        return ['message' => 'Task deleted successfully'];
    }

    public function getTaskById(array $params): array
    {
        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            return ['message' => 'Invalid task ID'];
        }

        $task = $this->taskService->getTask($id);
        if (!$task) {
            http_response_code(404);
            return ['message' => 'Task not found'];
        }

        return $task->toArray();
    }

    public function getAll(): array
    {
        $tasks = $this->taskService->getAllTasks();
        return array_map(fn(Task $task) => $task->toArray(), $tasks);
    }

    public function getMyTasks(): array
    {
        $tasks = $this->taskService->getUserTasks($_REQUEST['user']->getId());
        return array_map(fn(Task $task) => $task->toArray(), $tasks);
    }
}