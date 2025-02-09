<?php

namespace App\Model;

use App\Core\Database;
use PDO;

class Task
{
    private PDO $db;
    private const PER_PAGE = 10;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll(array $params = []): array
    {
        try {
            // Базовый запрос
            $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM tasks WHERE 1=1";
            $bindings = [];

            // Поиск по названию и описанию
            if (!empty($params['search'])) {
                $sql .= " AND (title LIKE :search OR description LIKE :search)";
                $bindings['search'] = "%{$params['search']}%";
            }

            // Фильтр по статусу
            if (!empty($params['status'])) {
                $sql .= " AND status = :status";
                $bindings['status'] = $params['status'];
            }

            // Фильтр по приоритету
            if (!empty($params['priority'])) {
                $sql .= " AND priority = :priority";
                $bindings['priority'] = $params['priority'];
            }

            // Фильтр по дедлайну
            if (!empty($params['deadline'])) {
                $sql .= " AND DATE(deadline) = :deadline";
                $bindings['deadline'] = $params['deadline'];
            }

            // Сортировка
            $allowedSortFields = ['title', 'status', 'priority', 'deadline', 'created_at'];
            $sort = $params['sort'] ?? 'created_at';
            $order = $params['order'] ?? 'DESC';

            if (in_array($sort, $allowedSortFields)) {
                $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
                $sql .= " ORDER BY {$sort} {$order}";
            }

            // Пагинация
            $page = max(1, $params['page'] ?? 1);
            $perPage = $params['per_page'] ?? self::PER_PAGE;
            $offset = ($page - 1) * $perPage;

            $sql .= " LIMIT :limit OFFSET :offset";
            $bindings['limit'] = (int)$perPage;
            $bindings['offset'] = (int)$offset;

            // Выполняем запрос
            $stmt = $this->db->prepare($sql);
            foreach ($bindings as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $items = $stmt->fetchAll();

            // Получаем общее количество записей
            $totalStmt = $this->db->query("SELECT FOUND_ROWS()");
            $total = $totalStmt->fetchColumn();

            return [
                'items' => $items,
                'pagination' => [
                    'total' => (int)$total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total)
                ]
            ];
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return ['items' => [], 'pagination' => ['total' => 0]];
        }
    }

    public function create(array $data): ?int
    {
        $sql = "INSERT INTO tasks (title, description, status, priority, deadline) 
                VALUES (:title, :description, :status, :priority, :deadline)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'new',
                'priority' => $data['priority'] ?? 'medium',
                'deadline' => $data['deadline'] ?? null
            ]);

            return (int)$this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function update(int $id, array $data): bool
    {
        $allowedFields = ['title', 'description', 'status', 'priority', 'deadline'];
        $updates = array_intersect_key($data, array_flip($allowedFields));

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE tasks SET " .
            implode(', ', array_map(fn($field) => "$field = :$field", array_keys($updates))) .
            " WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([...$updates, 'id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}