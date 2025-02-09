<?php

namespace App\Service;

use App\Model\Task;
use App\Validation\TaskValidator;

class TaskService
{
    private Task $taskModel;

    public function __construct()
    {
        $this->taskModel = new Task();
    }

    public function getAllTasks(array $params = []): array
    {
        // Валидация параметров
        $validatedParams = $this->validateListParams($params);
        return $this->taskModel->findAll($validatedParams);
    }

    private function validateListParams(array $params): array
    {
        $validated = [];

        // Валидация поиска
        if (isset($params['search'])) {
            $validated['search'] = trim($params['search']);
        }

        // Валидация статуса
        if (isset($params['status']) && in_array($params['status'], ['new', 'in_progress', 'completed'])) {
            $validated['status'] = $params['status'];
        }

        // Валидация приоритета
        if (isset($params['priority']) && in_array($params['priority'], ['low', 'medium', 'high'])) {
            $validated['priority'] = $params['priority'];
        }

        // Валидация дедлайна
        if (isset($params['deadline']) && strtotime($params['deadline'])) {
            $validated['deadline'] = date('Y-m-d', strtotime($params['deadline']));
        }

        // Валидация сортировки
        if (isset($params['sort']) && in_array($params['sort'], ['title', 'status', 'priority', 'deadline', 'created_at'])) {
            $validated['sort'] = $params['sort'];
            $validated['order'] = isset($params['order']) && strtoupper($params['order']) === 'ASC' ? 'ASC' : 'DESC';
        }

        // Валидация пагинации
        $validated['page'] = isset($params['page']) ? max(1, (int)$params['page']) : 1;
        $validated['per_page'] = isset($params['per_page']) ? max(1, min(100, (int)$params['per_page'])) : 10;

        return $validated;
    }

    public function createTask(array $data): array
    {
        // Валидация
        $validation = TaskValidator::validate($data, 'create');
        if (!$validation['isValid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        // Создание задачи
        $taskId = $this->taskModel->create($data);
        if (!$taskId) {
            return [
                'success' => false,
                'errors' => ['general' => ['Failed to create task']]
            ];
        }

        // Получаем созданную задачу
        $task = $this->taskModel->findById($taskId);
        return [
            'success' => true,
            'data' => $task
        ];
    }

    public function updateTask(int $id, array $data): array
    {
        // Проверяем существование задачи
        $existingTask = $this->taskModel->findById($id);
        if (!$existingTask) {
            return [
                'success' => false,
                'errors' => ['general' => ['Task not found']]
            ];
        }

        // Валидация
        $validation = TaskValidator::validate($data, 'update');
        if (!$validation['isValid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        // Обновление задачи
        $success = $this->taskModel->update($id, $data);
        if (!$success) {
            return [
                'success' => false,
                'errors' => ['general' => ['Failed to update task']]
            ];
        }

        // Получаем обновленную задачу
        $task = $this->taskModel->findById($id);
        return [
            'success' => true,
            'data' => $task
        ];
    }

    public function getTaskById(int $id): ?array
    {
        return $this->taskModel->findById($id);
    }

    public function deleteTask(int $id): bool
    {
        return $this->taskModel->delete($id);
    }
}