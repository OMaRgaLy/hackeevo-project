<?php

namespace App\Validation;

class TaskValidator
{
    private const ALLOWED_STATUSES = ['new', 'in_progress', 'completed'];
    private const ALLOWED_PRIORITIES = ['low', 'medium', 'high'];

    public static function validate(array $data, string $action = 'create'): array
    {
        $errors = [];

        // Валидация title
        if ($action === 'create' && empty($data['title'])) {
            $errors['title'][] = 'Title is required';
        } elseif (isset($data['title'])) {
            if (strlen($data['title']) < 3) {
                $errors['title'][] = 'Title must be at least 3 characters long';
            }
            if (strlen($data['title']) > 255) {
                $errors['title'][] = 'Title must not exceed 255 characters';
            }
        }

        // Валидация description
        if (isset($data['description']) && strlen($data['description']) > 65535) {
            $errors['description'][] = 'Description is too long';
        }

        // Валидация status
        if (isset($data['status']) && !in_array($data['status'], self::ALLOWED_STATUSES)) {
            $errors['status'][] = 'Invalid status. Allowed values: ' . implode(', ', self::ALLOWED_STATUSES);
        }

        // Валидация priority
        if (isset($data['priority']) && !in_array($data['priority'], self::ALLOWED_PRIORITIES)) {
            $errors['priority'][] = 'Invalid priority. Allowed values: ' . implode(', ', self::ALLOWED_PRIORITIES);
        }

        // Валидация deadline
        if (isset($data['deadline'])) {
            $deadline = strtotime($data['deadline']);
            if ($deadline === false) {
                $errors['deadline'][] = 'Invalid deadline format. Use YYYY-MM-DD HH:mm:ss';
            }
        }

        return [
            'isValid' => empty($errors),
            'errors' => $errors
        ];
    }
}