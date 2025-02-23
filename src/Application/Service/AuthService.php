<?php

namespace App\Application\Service;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Application\Exception\ValidationException;
use App\Application\Exception\AuthenticationException;

class AuthService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ValidationException
     */
    public function register(array $userData): User
    {
        $this->validateRegistrationData($userData);

        if ($this->userRepository->findByEmail($userData['email'])) {
            throw new ValidationException('Email already exists');
        }

        $user = new User(
            $userData['nickname'],
            $userData['firstname'],
            $userData['lastname'],
            $userData['phone'] ?? null,
            $userData['email'],
            $userData['password'],
            $userData['bio'] ?? null
        );

        $this->userRepository->save($user);

        return $user;
    }

    /**
     * @throws AuthenticationException
     */
    public function login(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        return !$user || !password_verify($password, $user->getPassword()) ? throw new AuthenticationException('Invalid credentials') : $user;

    }

    /**
     * @throws ValidationException
     */
    private function validateRegistrationData(array $data): void
    {
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email');
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            throw new ValidationException('Password must be at least 8 characters long');
        }

        if (empty($data['nickname']) || strlen($data['nickname']) < 3) {
            throw new ValidationException('Nickname must be at least 3 characters long');
        }

        if (empty($data['firstname'])) {
            throw new ValidationException('First name is required');
        }
    }
}