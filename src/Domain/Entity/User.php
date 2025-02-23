<?php

// src/Domain/Entity/User.php
namespace App\Domain\Entity;

class User
{
    private ?int $id;
    private string $nickname;
    private string $firstname;
    private ?string $lastname;
    private ?string $phone;
    private string $email;
    private ?string $bio;
    private string $role;
    private ?string $avatar;
    private string $password;
    private string $created_at;

    public function __construct(
        string $nickname,
        string $firstname,
        ?string $lastname = null,
        ?string $phone = null,
        string $email,
        string $password,
        ?string $bio = null,
        string $role = 'base',
        ?string $avatar = null
    ) {
        $this->nickname = $nickname;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->phone = $phone;
        $this->email = $email;
        $this->password = $password;
        $this->bio = $bio;
        $this->role = $role;
        $this->avatar = $avatar;
    }

    // Геттеры и сеттеры
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): void
    {
        $this->nickname = $nickname;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): void
    {
        $this->bio = $bio;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}