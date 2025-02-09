<?php

namespace App\Controller;

use App\Service\TaskService;

class TaskController
{
    private TaskService $taskService;

    public function __construct()
    {
        $this->taskService = new TaskService();
    }

    public function index(): string
    {
        // Получаем все параметры запроса
        $params = $this->getQueryParams();

        // Получаем задачи с учетом параметров
        $result = $this->taskService->getAllTasks($params);

        header('Content-Type: application/json');
        return json_encode([
            'status' => 'success',
            'data' => $result['items'],
            'meta' => [
                'pagination' => $result['pagination'],
                'filters' => [
                    'search' => $params['search'] ?? null,
                    'status' => $params['status'] ?? null,
                    'priority' => $params['priority'] ?? null,
                    'deadline' => $params['deadline'] ?? null
                ],
                'sort' => [
                    'field' => $params['sort'] ?? 'created_at',
                    'order' => $params['order'] ?? 'DESC'
                ]
            ]
        ]);
    }

    private function getQueryParams(): array
    {
        $params = [];

        // Параметры поиска и фильтрации
        if (isset($_GET['search'])) $params['search'] = $_GET['search'];
        if (isset($_GET['status'])) $params['status'] = $_GET['status'];
        if (isset($_GET['priority'])) $params['priority'] = $_GET['priority'];
        if (isset($_GET['deadline'])) $params['deadline'] = $_GET['deadline'];

        // Параметры сортировки
        if (isset($_GET['sort'])) $params['sort'] = $_GET['sort'];
        if (isset($_GET['order'])) $params['order'] = $_GET['order'];

        // Параметры пагинации
        if (isset($_GET['page'])) $params['page'] = (int)$_GET['page'];
        if (isset($_GET['per_page'])) $params['per_page'] = (int)$_GET['per_page'];

        return $params;
    }

    public function create(): string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->taskService->createTask($data);

        if (!$result['success']) {
            http_response_code(400);
            return json_encode([
                'status' => 'error',
                'errors' => $result['errors']
            ]);
        }

        http_response_code(201);
        return json_encode([
            'status' => 'success',
            'data' => $result['data']
        ]);
    }

    public function show(int $id): string
    {
        $task = $this->taskService->getTaskById($id);

        if (!$task) {
            http_response_code(404);
            return json_encode([
                'status' => 'error',
                'message' => 'Task not found'
            ]);
        }

        return json_encode([
            'status' => 'success',
            'data' => $task
        ]);
    }

    public function update(int $id): string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            return json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->taskService->updateTask($id, $data);

        if (!$result['success']) {
            http_response_code(400);
            return json_encode([
                'status' => 'error',
                'errors' => $result['errors']
            ]);
        }

        return json_encode([
            'status' => 'success',
            'data' => $result['data']
        ]);
    }

    public function delete(int $id): string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            return json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        }

        if ($this->taskService->deleteTask($id)) {
            return json_encode(['status' => 'success']);
        }

        http_response_code(404);
        return json_encode([
            'status' => 'error',
            'message' => 'Task not found'
        ]);
    }
}