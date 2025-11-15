<?php

declare(strict_types=1);

namespace Includes\Content;

use PDO;
use PDOException;
use JsonSchema\Validator;
use RuntimeException;

/**
 * Manages content type definitions and validation.
 */
class ContentTypeManager
{
    private PDO $pdo;
    private string $definitionsPath;

    /**
     * Constructor.
     *
     * @param PDO $pdo The database connection.
     * @param string $definitionsPath Path to the directory containing content type JSON schema files.
     *                                Defaults to 'config/content_types/'.
     */
    public function __construct(PDO $pdo, string $definitionsPath = 'config/content_types/')
    {
        $this->pdo = $pdo;
        $this->definitionsPath = rtrim($definitionsPath, '/') . '/';

        if (!is_dir($this->definitionsPath)) {
            if (!mkdir($this->definitionsPath, 0755, true) && !is_dir($this->definitionsPath)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $this->definitionsPath));
            }
        }
    }

    /**
     * Retrieves a content type definition by its name.
     * First, it tries to load from the database. If not found, it tries to load from a JSON file.
     *
     * @param string $name The name of the content type (e.g., "article").
     * @return array|null The content type definition as an associative array, or null if not found.
     */
    public function getContentTypeDefinition(string $name): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT definition_json FROM content_types WHERE name = :name");
            $stmt->execute([':name' => $name]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && !empty($row['definition_json'])) {
                $decodedJson = json_decode($row['definition_json'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decodedJson;
                }
            }
        } catch (PDOException $e) {
            // Log error or handle - for now, we'll fall through to file loading
        }

        // Fallback to loading from file if not in DB or DB version is invalid
        return $this->loadContentTypeSchemaFromFile($name);
    }

    /**
     * Loads a content type schema from its JSON file.
     *
     * @param string $name The name of the content type.
     * @return array|null The schema as an associative array, or null if file not found or invalid JSON.
     */
    private function loadContentTypeSchemaFromFile(string $name): ?array
    {
        $filePath = $this->definitionsPath . $name . '.json';
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return null;
        }

        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            return null;
        }

        $schema = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Optionally log an error here: "Invalid JSON in schema file: $filePath"
            return null;
        }
        return $schema;
    }

    /**
     * Validates content data against a given content type's schema.
     *
     * @param string $contentTypeName The name of the content type.
     * @param object|array $contentData The content data to validate (as an object or associative array).
     * @return array Returns an array of validation errors. Empty if valid.
     */
    public function validateContent(string $contentTypeName, $contentData): array
    {
        $schema = $this->getContentTypeDefinition($contentTypeName);

        if (!$schema) {
            return [['property' => '', 'message' => "Content type '{$contentTypeName}' definition not found."]];
        }

        // Ensure $contentData is an object for the validator, as it often expects objects.
        // If it's an array, convert it.
        if (is_array($contentData)) {
            $contentData = json_decode(json_encode($contentData));
        }

        $validator = new Validator();
        $validator->validate($contentData, (object)$schema); // Schema should also be an object

        if ($validator->isValid()) {
            return [];
        }

        $errors = [];
        foreach ($validator->getErrors() as $error) {
            $errors[] = [
                'property' => $error['property'],
                'message' => $error['message']
            ];
        }
        return $errors;
    }

    /**
     * Registers a new content type or updates an existing one in the database
     * from a JSON schema file.
     *
     * @param string $name The name of the content type.
     * @param string $schemaFilePath The absolute or relative path to the JSON schema file.
     * @param string $version The schema version (e.g., "1.0").
     * @return bool True on success, false on failure.
     * @throws RuntimeException If schema file is not found or readable, or contains invalid JSON.
     */
    public function registerContentTypeFromFile(string $name, string $schemaFilePath, string $version = '1.0'): bool
    {
        if (!file_exists($schemaFilePath) || !is_readable($schemaFilePath)) {
            throw new RuntimeException("Schema file not found or not readable: {$schemaFilePath}");
        }

        $jsonDefinition = file_get_contents($schemaFilePath);
        if ($jsonDefinition === false) {
            throw new RuntimeException("Could not read schema file: {$schemaFilePath}");
        }

        // Validate JSON structure
        $decodedJson = json_decode($jsonDefinition);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON in schema file {$schemaFilePath}: " . json_last_error_msg());
        }

        return $this->saveContentType($name, $jsonDefinition, $version);
    }

    /**
     * Saves or updates a content type definition in the database.
     *
     * @param string $name The name of the content type.
     * @param string $jsonDefinition The JSON schema definition as a string.
     * @param string $version The schema version.
     * @return bool True on success, false on failure.
     */
    public function saveContentType(string $name, string $jsonDefinition, string $version): bool
    {
        // Validate JSON before saving
        json_decode($jsonDefinition);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Log error: "Attempted to save invalid JSON for content type $name"
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT id FROM content_types WHERE name = :name");
            $stmt->execute([':name' => $name]);
            $existing = $stmt->fetchColumn();

            if ($existing) {
                $updateStmt = $this->pdo->prepare(
                    "UPDATE content_types SET definition_json = :definition, schema_version = :version, updated_at = CURRENT_TIMESTAMP WHERE name = :name"
                );
                $success = $updateStmt->execute([
                    ':definition' => $jsonDefinition,
                    ':version' => $version,
                    ':name' => $name
                ]);
            } else {
                $insertStmt = $this->pdo->prepare(
                    "INSERT INTO content_types (name, schema_version, definition_json, created_at, updated_at) 
                     VALUES (:name, :version, :definition, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
                );
                $success = $insertStmt->execute([
                    ':name' => $name,
                    ':version' => $version,
                    ':definition' => $jsonDefinition
                ]);
            }

            $this->pdo->commit();
            return $success;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            // Log error: $e->getMessage()
            return false;
        }
    }

    /**
     * Get all registered content type names from the database.
     *
     * @return array List of content type names.
     */
    public function getAllContentTypeNames(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT name FROM content_types ORDER BY name ASC");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            // Log error
            return [];
        }
    }
}
