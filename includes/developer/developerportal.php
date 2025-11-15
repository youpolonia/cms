<?php
declare(strict_types=1);

/**
 * Developer Platform - Developer Portal
 * Generates and manages API documentation UI
 */
class DeveloperPortal {
    private static array $apiSpecs = [];
    private static string $docsDir = __DIR__ . '/../../public/docs/';
    private static string $templateDir = __DIR__ . '/templates/';

    /**
     * Register an API specification
     */
    public static function registerApiSpec(
        string $apiName,
        array $spec,
        string $version = '1.0.0'
    ): void {
        self::$apiSpecs[$apiName] = [
            'spec' => $spec,
            'version' => $version,
            'last_updated' => time()
        ];
        self::generateDocs($apiName);
    }

    /**
     * Generate documentation files
     */
    private static function generateDocs(string $apiName): void {
        if (!isset(self::$apiSpecs[$apiName])) {
            throw new InvalidArgumentException("API not registered: $apiName");
        }

        $spec = self::$apiSpecs[$apiName];
        $html = self::renderTemplate('api_docs', [
            'api_name' => $apiName,
            'spec' => $spec['spec'],
            'version' => $spec['version']
        ]);

        file_put_contents(
            self::$docsDir . $apiName . '.html',
            $html
        );
    }

    /**
     * Render documentation template
     */
    private static function renderTemplate(string $template, array $data): string {
        ob_start();
        extract($data);
        require_once self::$templateDir . $template . '.php';
        return ob_get_clean();
    }

    /**
     * Get interactive API console HTML
     */
    public static function getApiConsole(string $apiName): string {
        if (!isset(self::$apiSpecs[$apiName])) {
            throw new InvalidArgumentException("API not registered: $apiName");
        }

        return self::renderTemplate('api_console', [
            'api_name' => $apiName,
            'endpoints' => self::$apiSpecs[$apiName]['spec']['paths'] ?? []
        ]);
    }

    /**
     * Generate code samples
     */
    public static function generateCodeSample(
        string $apiName,
        string $endpoint,
        string $language = 'php'
    ): string {
        // Implementation for code sample generation
        return "// Sample code for $apiName $endpoint in $language";
    }

    // BREAKPOINT: Continue with documentation features
}
