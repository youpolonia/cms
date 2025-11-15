# ContentFields API Reference

## Code Examples

### Adding a Field
```php
$field = new ContentFields();
$field->addField('article', [
    'name' => 'Body',
    'machine_name' => 'body',
    'field_type' => 'text_long',
    'is_required' => true
]);
```

### Retrieving Fields
```php
// Get all fields for a content type
$fields = ContentFields::getFieldsForType('article');

// Get specific field by ID
$field = ContentFields::getFieldById(42);
```

### Updating a Field
```php
ContentFields::updateField(42, [
    'name' => 'Article Body',
    'is_required' => false
]);
```

## Overview
The `ContentFields` class manages field definitions for content types, including CRUD operations and field ordering.

## Class Definition
```php
class ContentFields {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }
    
    // ... methods ...
}
```

## Constructor
### `__construct($db)`
Initializes the ContentFields instance with a database connection.

**Parameters:**
- `$db` - Database connection (PDO-compatible)

## Methods

### `addField($content_type_id, $name, $machine_name, $field_type, $settings = [], $is_required = false, $weight = 0)`
Adds a new field to a content type.

**Parameters:**
- `$content_type_id` - ID of the content type
- `$name` - Human-readable field name
- `$machine_name` - Machine-readable identifier
- `$field_type` - Field type identifier
- `$settings` (array) - Optional field settings (JSON encoded)
- `$is_required` (bool) - Whether field is required
- `$weight` (int) - Display order weight

**Returns:**
- (bool) - True on success

**SQL Query:**
```sql
INSERT INTO content_fields 
(content_type_id, name, machine_name, field_type, settings, is_required, weight) 
VALUES (?, ?, ?, ?, ?, ?, ?)
```

### `getFieldsForType($content_type_id)`
Gets all fields for a content type, ordered by weight and name.

**Parameters:**
- `$content_type_id` - Content type ID

**Returns:**
- (array) - Array of field definitions

**SQL Query:**
```sql
SELECT * FROM content_fields 
WHERE content_type_id = ? 
ORDER BY weight, name
```

### `getFieldById($id)`
Gets a single field by its ID.

**Parameters:**
- `$id` - Field ID

**Returns:**
- (array) - Field definition or false if not found

**SQL Query:**
```sql
SELECT * FROM content_fields WHERE id = ?
```

### `updateField($id, $name, $machine_name, $field_type, $settings = [], $is_required = false, $weight = 0)`
Updates an existing field.

**Parameters:** (same as addField plus)
- `$id` - Field ID to update

**Returns:**
- (bool) - True on success

**SQL Query:**
```sql
UPDATE content_fields 
SET name = ?, machine_name = ?, field_type = ?, 
    settings = ?, is_required = ?, weight = ? 
WHERE id = ?
```

### `deleteField($id)`
Deletes a field.

**Parameters:**
- `$id` - Field ID to delete

**Returns:**
- (bool) - True on success

**SQL Query:**
```sql
DELETE FROM content_fields WHERE id = ?
```

### `reorderFields($content_type_id, $new_order)`
Updates field weights in a transaction.

**Parameters:**
- `$content_type_id` - Content type ID
- `$new_order` (array) - Array of [weight => field_id] pairs

**Returns:**
- (bool) - True on success

**Transaction Handling:**
- Uses beginTransaction()/commit()/rollBack()
- Updates multiple records in a single transaction