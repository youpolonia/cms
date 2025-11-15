<?php

class FileRouter {
    public static function routeFile(string $filePath): string {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $directory = pathinfo($filePath, PATHINFO_DIRNAME);

        // Route by directory first
        if (str_contains($directory, 'database')) {
            return 'db-support';
        } elseif (str_contains($directory, 'analytics') ||
                 str_contains($directory, 'logs') ||
                 str_contains($directory, 'error_logs')) {
            return 'pattern-reader';
        }

        // Route by file extension
        if (in_array($extension, ['php', 'js'])) {
            return 'code';
        } elseif ($extension === 'sql') {
            return 'db-support';
        }

        // Default fallback
        return 'code';
    }
}

// Example usage:
// $agent = FileRouter::routeFile('/path/to/file.php');
