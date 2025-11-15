# User Model Design Proposal

## Class Structure

```php
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
```

## Migration Plan

1. **Phase 1: Implementation**
   - Create `Core/User.php` with above class
   - Update `Auth::user()` to return User object
   - Modify `AdminAuth` to use new methods

2. **Phase 2: Testing**
   - Verify backward compatibility
   - Test all role checks
   - Check session serialization

3. **Phase 3: Deployment**
   - Deploy in maintenance window
   - Monitor auth logs closely
   - Have rollback plan

## Backward Compatibility

The design maintains compatibility by:
- Accepting array input (current session format)
- Implementing JsonSerializable
- Providing getters for all properties

## Security Considerations

- Immutable design prevents modification
- Type safety with strict typing
- Input validation in constructor