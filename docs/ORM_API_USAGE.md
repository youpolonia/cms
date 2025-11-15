# ORM API Documentation

## BaseModel Features

The new ORM system provides these core features:

- Automatic table mapping via `protected static $table`
- Column whitelisting via `protected static $columns`
- CRUD operations:
  - `save()` - Creates or updates records
  - `delete()` - Soft/hard deletes records
  - `find($id)` - Finds record by primary key
  - `all()` - Gets all records

## Creating Models

1. Extend `BaseModel`:
```php
class User extends BaseModel {
    protected static $table = 'users';
    protected static $columns = ['id', 'name', 'email'];
}
```

2. Define properties matching your columns:
```php
public ?int $id = null;
public string $name = '';
public string $email = '';
```

## QueryBuilder Usage

Access the query builder via `Model::query()`:

```php
// Basic queries
User::query()->where('active', 1)->get();

// Complex queries
Content::query()
    ->where('status', 'published')
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();
```

## Relationships

Define relationships using `RelationshipBuilder`:

```php
public function posts(): RelationshipBuilder {
    return $this->hasMany(Post::class, 'author_id');
}

// Usage:
$user->posts()->get();
```

## AI Integration

Enable AI features by setting static flags:

```php
CompanyModel::$enhanceDescriptions = true;
```

Then implement in your save() method:
```php
if (self::$enhanceDescriptions) {
    $this->description = AiDescriptionService::enhance($this->description);
}
```

## Best Practices

- Always whitelist columns
- Use type hints for properties
- Implement custom logic in model methods
- Keep business logic in models