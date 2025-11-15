<?php
class WorkflowStorage {
    const WORKFLOWS_DIR = '/workflows/';
    const SCHEMA = [
        'name' => 'string',
        'version' => 'string',
        'description' => 'string',
        'steps' => 'array',
        'triggers' => 'array',
        'variables' => 'array'
    ];

    public static function validate(array $workflow): bool {
        foreach (self::SCHEMA as $key => $type) {
            if (!isset($workflow[$key])) {
                throw new InvalidArgumentException("Missing required field: $key");
            }
            if (gettype($workflow[$key]) !== $type) {
                throw new InvalidArgumentException("Invalid type for $key, expected $type");
            }
        }
        return true;
    }

    public static function save(string $name, array $workflow): bool {
        self::validate($workflow);
        $path = ROOT_PATH . self::WORKFLOWS_DIR . "$name.json";
        $tempPath = $path . '.tmp';
        
        if (!file_put_contents($tempPath, json_encode($workflow, JSON_PRETTY_PRINT))) {
            throw new RuntimeException("Failed to write workflow file");
        }
        
        if (!rename($tempPath, $path)) {
            unlink($tempPath);
            throw new RuntimeException("Failed to commit workflow file");
        }
        
        return true;
    }

    public static function load(string $name): array {
        $path = ROOT_PATH . self::WORKFLOWS_DIR . "$name.json";
        if (!file_exists($path)) {
            throw new RuntimeException("Workflow not found: $name");
        }
        
        $data = json_decode(file_get_contents($path), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON in workflow file");
        }
        
        self::validate($data);
        return $data;
    }

    public static function list(): array {
        $files = glob(ROOT_PATH . self::WORKFLOWS_DIR . '*.json');
        return array_map(fn($f) => basename($f, '.json'), $files);
    }
}
