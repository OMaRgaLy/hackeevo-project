<?php

namespace App\Domain\Repository;

use App\Domain\Entity\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;
    public function findByEmail(string $email): ?User;
    public function findAll(): array;
    public function findById(int $id): ?User;
    public function updateUser(int $id, array $data): bool;
    public function deleteUser(int $id): bool;
}