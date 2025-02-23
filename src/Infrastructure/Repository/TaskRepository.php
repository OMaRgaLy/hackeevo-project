<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Task;
use PDO;

class TaskRepository
{
    public function __construct(private PDO $pdo) {}

    public function save(Task $task): Task
    {
        if ($task->getId() === null) {
            $sql = "INSERT INTO tasks (title, description, status, priority, user_id) 
                    VALUES (:title, :description, :status, :priority, :userId)";
        } else {
            $sql = "UPDATE tasks 
                    SET title = :title, 
                        description = :description, 
                        status = :status, 
                        priority = :priority, 
                        user_id = :userId 
                    WHERE id = :id";
        }

        $stmt = $this->pdo->prepare($sql);

        $params = [
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
            'priority' => $task->getPriority(),
            'userId' => $task->getUserId()
        ];

        if ($task->getId() !== null) {
            $params['id'] = $task->getId();
        }

        $stmt->execute($params);

        if ($task->getId() === null) {
            return new Task(
                (int)$this->pdo->lastInsertId(),
                $task->getTitle(),
                $task->getDescription(),
                $task->getStatus(),
                $task->getPriority(),
                $task->getUserId(),
                $task->getCreatedAt(),
                $task->getUpdatedAt()
            );
        }

        return $task;
    }

    public function findById(int $id): ?Task
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->createTaskFromData($data);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
        return $this->hydrateTasks($stmt);
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE user_id = :userId ORDER BY created_at DESC");
        $stmt->execute(['userId' => $userId]);
        return $this->hydrateTasks($stmt);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    private function createTaskFromData(array $data): Task
    {
        return new Task(
            (int)$data['id'],
            $data['title'],
            $data['description'],
            $data['status'],
            $data['priority'],
            (int)$data['user_id'],
            new \DateTime($data['created_at']),
            new \DateTime($data['updated_at'])
        );
    }

    private function hydrateTasks(\PDOStatement $stmt): array
    {
        $tasks = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = $this->createTaskFromData($data);
        }
        return $tasks;
    }
}