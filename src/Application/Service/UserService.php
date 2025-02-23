<?php

namespace App\Application\Service;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;

class UserService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function createUser(User $user): void
    {
        $this->userRepository->save($user);
    }

    public function updateUser(int $id, array $data): bool
    {
        return $this->userRepository->updateUser($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepository->deleteUser($id);
    }
}