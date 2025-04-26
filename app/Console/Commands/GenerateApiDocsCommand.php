<?php

namespace App\Console\Commands;

use App\Services\DocumentationGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateApiDocsCommand extends Command
{
    protected $signature = 'api:docs 
        {--format=json : Output format (json or yaml)}
        {--output= : Output file path}';

    protected $description = 'Generate API documentation';

    public function handle(DocumentationGenerator $generator)
    {
        $docs = $generator->generate();
        $output = $this->option('output') ?? storage_path('api-docs/api-docs.json');
        
        // Ensure directory exists
        File::ensureDirectoryExists(dirname($output));

        if ($this->option('format') === 'yaml') {
            $content = yaml_emit($docs);
        } else {
            $content = json_encode($docs, JSON_PRETTY_PRINT);
        }

        File::put($output, $content);

        $this->info("API documentation generated successfully at: {$output}");
    }
}