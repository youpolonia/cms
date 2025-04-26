<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Contracts\DiffServiceInterface;
use Spatie\Watcher\Watch;

class MigrationWatcher
{
    protected $knowledgeServer;
    protected $diffService;
    protected $migrationPath;
    protected $watcher;

    public function __construct(DiffServiceInterface $diffService)
    {
        $this->diffService = $diffService;
        $this->migrationPath = database_path('migrations');
        $this->watcher = Watch::path($this->migrationPath)
            ->onAnyChange(function (string $path) {
                $this->handleMigrationChange($path);
            });
    }

    public function startWatching()
    {
        $this->cacheExistingMigrations();
        $this->watcher->start();
    }

    protected function cacheExistingMigrations()
    {
        $migrations = File::files($this->migrationPath);
        
        foreach ($migrations as $migration) {
            $this->cacheMigration($migration->getPathname());
        }
    }

    protected function handleMigrationChange(string $path)
    {
        if (!str_ends_with($path, '.php')) {
            return;
        }

        $this->cacheMigration($path);
        $this->generateSchemaDocs();
    }

    protected function cacheMigration(string $path)
    {
        $content = File::get($path);
        $metadata = $this->extractMetadata($content);
        
        // Use MCP knowledge server to cache
        $this->useKnowledgeServer('cache_file', [
            'path' => $path,
            'metadata' => $metadata
        ]);
    }

    protected function extractMetadata(string $content): array
    {
        // Extract schema changes from migration
        preg_match('/Schema::(create|table|drop|rename)[(](["\'])(.*?)\2/', $content, $matches);
        
        return [
            'operation' => $matches[1] ?? null,
            'table' => $matches[3] ?? null,
            'timestamp' => now()->toDateTimeString()
        ];
    }

    protected function generateSchemaDocs()
    {
        // Generate updated schema documentation
        $schema = $this->getCurrentSchema();
        $docsPath = docs_path('database-schema.md');
        
        File::put($docsPath, $this->formatSchemaDocs($schema));
    }

    protected function useKnowledgeServer(string $action, array $data)
    {
        try {
            $response = \Http::post(config('mcp.knowledge_server_url') . '/api/' . $action, [
                'data' => $data
            ]);

            if ($response->failed()) {
                Log::error('Failed to communicate with knowledge server', [
                    'action' => $action,
                    'error' => $response->body()
                ]);
                return false;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Knowledge server communication error', [
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    protected function getCurrentSchema(): array
    {
        $migrations = File::files($this->migrationPath);
        $schema = [];
        
        foreach ($migrations as $file) {
            $content = File::get($file->getPathname());
            $schema[] = $this->extractMetadata($content);
        }
        
        return $schema;
    }

    protected function formatSchemaDocs(array $schema): string
    {
        $changes = '';
        $tables = [];
        
        foreach ($schema as $migration) {
            if (empty($migration['table'])) {
                continue;
            }
            
            $changes .= sprintf(
                "| %s | %s | %s | %s |\n",
                $migration['timestamp'],
                $migration['operation'],
                $migration['table'],
                $this->getChangeDetails($migration)
            );
            
            if (!in_array($migration['table'], $tables)) {
                $tables[] = $migration['table'];
            }
        }
        
        $schemaMarkdown = "## Tables\n";
        foreach ($tables as $table) {
            $schemaMarkdown .= sprintf("- `%s`\n", $table);
        }
        
        $template = File::get(docs_path('database-schema.md'));
        return str_replace(
            ['{{schema}}', '{{changes}}'],
            [$schemaMarkdown, $changes],
            $template
        );
    }
    
    protected function getChangeDetails(array $migration): string
    {
        if ($migration['operation'] === 'create') {
            return 'Table created';
        } elseif ($migration['operation'] === 'table') {
            return 'Table altered';
        } elseif ($migration['operation'] === 'drop') {
            return 'Table dropped';
        }
        return 'Schema change';
    }
}