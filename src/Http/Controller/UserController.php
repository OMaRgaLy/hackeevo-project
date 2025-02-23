<?php

namespace App\Http\Controller;

use App\Application\Service\UserService;
use App\Domain\Entity\User;

class UserController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Получить всех пользователей
     */
    public function findAll(): array
    {
        $users = $this->userService->getAllUsers();
        return array_map(fn(User $user) => $this->mapUserToArray($user), $users);
    }

    /**
     * Получить пользователя по ID
     */
    public function findById(array $params): ?array
    {
        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            return ['message' => 'Invalid user ID'];
        }

        $user = $this->userService->getUserById($id);
        if (!$user) {
            http_response_code(404);
            return ['message' => 'User not found'];
        }

        return $this->mapUserToArray($user);
    }

    /**
     * Создать нового пользователя
     */
    public function create(): array
    {
        // Получаем данные из запроса
        $data = json_decode(file_get_contents('php://input'), true);

        // Валидация данных
        $requiredFields = ['nickname', 'email', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                return ['message' => "Field '$field' is required"];
            }
        }

        // Проверяем, существует ли пользователь с таким email
        if ($this->userService->findUserByEmail($data['email'])) {
            http_response_code(409); // Conflict
            return ['message' => 'User with this email already exists'];
        }

        try {
            // Создаем нового пользователя
            $user = new User(
                $data['nickname'],
                $data['firstname'] ?? '',
                $data['lastname'] ?? '',
                $data['phone'] ?? '',
                $data['email'],
                $data['password'],
                $data['bio'] ?? '',
                $data['role'] ?? 'base',
                $data['avatar'] ?? ''
            );

            $this->userService->createUser($user);

            http_response_code(201); // Created
            return $this->mapUserToArray($user, false);
        } catch (\Exception $e) {
            http_response_code(500);
            return ['message' => 'Failed to create user: ' . $e->getMessage()];
        }
    }

    /**
     * Обновить пользователя
     */
    public function update(array $params): array
    {
        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            return ['message' => 'Invalid user ID'];
        }

        // Проверяем существование пользователя
        $user = $this->userService->getUserById($id);
        if (!$user) {
            http_response_code(404);
            return ['message' => 'User not found'];
        }

        // Получаем данные из запроса
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // Обновляем пользователя
        $success = $this->userService->updateUser($id, $data);
        if (!$success) {
            http_response_code(400);
            return ['message' => 'Failed to update user'];
        }

        // Получаем обновленного пользователя
        $updatedUser = $this->userService->getUserById($id);
        return $this->mapUserToArray($updatedUser);
    }

    /**
     * Удалить пользователя
     */
    public function delete(array $params): array
    {
        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            return ['message' => 'Invalid user ID'];
        }

        // Проверяем существование пользователя
        $user = $this->userService->getUserById($id);
        if (!$user) {
            http_response_code(404);
            return ['message' => 'User not found'];
        }

        // Удаляем пользователя
        $success = $this->userService->deleteUser($id);
        if (!$success) {
            http_response_code(500);
            return ['message' => 'Failed to delete user'];
        }

        return ['message' => 'User successfully deleted'];
    }

    /**
     * Маппинг объекта User в массив для ответа
     */
    private function mapUserToArray(User $user, bool $excludePassword = true): array
    {
        $userData = [
            'id' => $user->getId(),
            'nickname' => $user->getNickname(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'phone' => $user->getPhone(),
            'email' => $user->getEmail(),
            'bio' => $user->getBio(),
            'role' => $user->getRole(),
            'avatar' => $user->getAvatar(),
        ];

        if (!$excludePassword) {
            $userData['password'] = '***'; // Не возвращаем реальный пароль
        }

        return $userData;
    }
}