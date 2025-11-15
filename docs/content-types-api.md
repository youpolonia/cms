# ContentTypes API Reference

## Code Examples

### Creating a Content Type
```php
$contentType = new ContentTypes();
$contentType->create([
    'name' => 'Article',
    'machine_name' => 'article',
    'description' => 'News articles and blog posts'
]);
```

### Retrieving Content Types
```php
// Get all content types
$contentTypes = ContentTypes::getAll();

// Get specific content type by machine name
$articleType = ContentTypes::getByMachineNames(['article']);
```

## Overview
The `ContentTypes` class provides basic operations for managing content types in the CMS.

## Class Definition
```php
class ContentTypes {
    protected $connection;

    public function __construct(PDO $connection) {
        $this->connection = $connection;
    }
    
    // ... methods ...
}
```

## Constructor
### `__construct(PDO $connection)`
Initializes the ContentTypes instance with a database connection.

**Parameters:**
- `$connection` (PDO) - An active PDO database connection

## Methods
### `getByMachineNames(array $machineNames): array`
Retrieves content types by their machine names.

**Parameters:**
- `$machineNames` (array) - Array of machine names to search for

**Returns:**
- (array) - Array of content type records matching the machine names

**Example:**
```php
$contentTypes = new ContentTypes($pdo);
$types = $contentTypes->getByMachineNames(['article', 'page']);
```

**SQL Query:**
```sql
SELECT * FROM content_types WHERE machine_name IN (?, ?)