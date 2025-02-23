<?php

namespace App\Infrastructure\Repository;

use App\Config\Database;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use PDO;
use PDOException;

class UserRepository implements UserRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function save(User $user): void
    {
        $sql = "INSERT INTO users (nickname, firstname, lastname, phone, email, bio, role, avatar, password)
                VALUES (:nickname, :firstname, :lastname, :phone, :email, :bio, :role, :avatar, :password)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nickname' => $user->getNickname(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'phone' => $user->getPhone(),
            'email' => $user->getEmail(),
            'bio' => $user->getBio(),
            'role' => $user->getRole(),
            'avatar' => $user->getAvatar(),
            'password' => password_hash($user->getPassword(), PASSWORD_DEFAULT)
        ]);

        $user->setId((int)$this->db->lastInsertId());
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        $userData = $stmt->fetch();
        if (!$userData) {
            return null;
        }

        return $this->createUserFromData($userData);
    }

    private function createUserFromData(array $data): User
    {
        $user = new User(
            $data['nickname'],
            $data['firstname'],
            $data['lastname'],
            $data['phone'],
            $data['email'],
            $data['password'],
            $data['bio'],
            $data['role'],
            $data['avatar']
        );
        $user->setId($data['id']);
        return $user;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY id");
        $users = [];

        while ($userData = $stmt->fetch()) {
            $users[] = $this->createUserFromData($userData);
        }

        return $users;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $userData = $stmt->fetch();
        if (!$userData) {
            return null;
        }

        return $this->createUserFromData($userData);
    }

    public function updateUser(int $id, array $data): bool
    {
        $allowedFields = [
            'nickname', 'firstname', 'lastname',
            'phone', 'email', 'bio', 'avatar'
        ];

        $updates = array_intersect_key($data, array_flip($allowedFields));

        if (empty($updates)) {
            return false;
        }

        $setParts = [];
        foreach ($updates as $field => $value) {
            $setParts[] = "$field = :$field";
        }

        $sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = :id";

        $updates['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($updates);
    }

    public function deleteUser(int $id): bool
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("DELETE FROM tasks WHERE user_id = :id");
            $stmt->execute(['id' => $id]);

            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $result = $stmt->execute(['id' => $id]);
            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}