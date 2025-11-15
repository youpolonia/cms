<?php
declare(strict_types=1);

namespace Api\Content;

class ContentEntry
{
    private const VALID_STATUSES = ['draft', 'published'];
    private const FIELD_TYPES = ['string', 'text', 'number', 'boolean', 'date'];

    /**
     * Create new content entry
     * @param array $data Content data including title and fields
     * @return array Created content data with ID and slug
     */
    public static function create(array $data): array
    {
        self::validateData($data);
        $data['slug'] = self::generateSlug($data['title']);
        $data['status'] = $data['status'] ?? 'draft';
        
        // Database insert would go here
        $data['id'] = uniqid(); // Temporary ID generation
        
        return $data;
    }

    /**
     * Read content entry by ID
     * @param string $id Content entry ID
     * @return array|null Content data or null if not found
     */
    public static function read(string $id): ?array
    {
        // Database query would go here
        return [
            'id' => $id,
            'title' => 'Example Content',
            'slug' => 'example-content',
            'status' => 'published',
            'fields' => []
        ];
    }

    /**
     * Update content entry
     * @param string $id Content entry ID
     * @param array $data Updated content data
     * @return array Updated content data
     */
    public static function update(string $id, array $data): array
    {
        self::validateData($data);
        if (isset($data['title'])) {
            $data['slug'] = self::generateSlug($data['title']);
        }
        
        // Database update would go here
        return array_merge(['id' => $id], $data);
    }

    /**
     * Delete content entry
     * @param string $id Content entry ID
     * @return bool True if deleted successfully
     */
    public static function delete(string $id): bool
    {
        // Database delete would go here
        return true;
    }

    /**
     * Validate content data structure and field types
     * @throws \InvalidArgumentException On validation failure
     */
    private static function validateData(array $data): void
    {
        if (empty($data['title'])) {
            throw new \InvalidArgumentException('Title is required');
        }

        if (isset($data['status']) && !in_array($data['status'], self::VALID_STATUSES)) {
            throw new \InvalidArgumentException('Invalid status value');
        }

        if (!empty($data['fields'])) {
            foreach ($data['fields'] as $field) {
                if (!isset($field['type']) || !in_array($field['type'], self::FIELD_TYPES)) {
                    throw new \InvalidArgumentException('Invalid field type');
                }
                if (!isset($field['name']) || !is_string($field['name'])) {
                    throw new \InvalidArgumentException('Field name must be a string');
                }
            }
        }
    }

    /**
     * Generate URL slug from title
     */
    private static function generateSlug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        return preg_replace('/-+/', '-', $slug);
    }
}
