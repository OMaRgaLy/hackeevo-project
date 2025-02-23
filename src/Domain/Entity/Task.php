<?php

namespace App\Domain\Entity;

class Task
{
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    public function __construct(
        private ?int $id,
        private string $title,
        private string $description,
        private string $status,
        private string $priority,
        private int $userId,
        private ?\DateTime $createdAt = null,
        private ?\DateTime $updatedAt = null
    ) {
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->updatedAt = $updatedAt ?? new \DateTime();

        $this->validateStatus($status);
        $this->validatePriority($priority);
    }

    private function validateStatus(string $status): void
    {
        $validStatuses = [self::STATUS_NEW, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Invalid status value');
        }
    }

    private function validatePriority(string $priority): void
    {
        $validPriorities = [self::PRIORITY_LOW, self::PRIORITY_MEDIUM, self::PRIORITY_HIGH];
        if (!in_array($priority, $validPriorities)) {
            throw new \InvalidArgumentException('Invalid priority value');
        }
    }

    // Геттеры
    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getStatus(): string { return $this->status; }
    public function getPriority(): string { return $this->priority; }
    public function getUserId(): int { return $this->userId; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }

    // Сеттеры
    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function setStatus(string $status): void {
        $this->validateStatus($status);
        $this->status = $status;
    }

    public function setPriority(string $priority): void {
        $this->validatePriority($priority);
        $this->priority = $priority;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'userId' => $this->userId,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}