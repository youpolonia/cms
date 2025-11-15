<?php

namespace Core;

/**
 * Immutable User model representing authenticated users
 */
class User implements \JsonSerializable
{
    private int $id;
    private string $email;
    private array $roles;

    /**
     * @param array $data User data (id, email, roles)
     * @throws \InvalidArgumentException If required fields missing
     */
    public function __construct(array $data)
    {
        if (!isset($data['id'], $data['email'], $data['roles'])) {
            throw new \InvalidArgumentException('Missing required user fields');
        }

        $this->id = (int)$data['id'];
        $this->email = $data['email'];
        $this->roles = (array)$data['roles'];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'roles' => $this->roles
        ];
    }
}
