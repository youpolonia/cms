<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenApi\Generator;

class GenerateApiDocumentation extends Command
{
    protected $signature = 'api:docs:generate 
        {--format=json : Output format (json or yaml)}
        {--output=storage/api-docs : Output directory}';

    protected $description = 'Generate API documentation using OpenAPI/Swagger';

    public function handle()
    {
        $format = $this->option('format');
        $outputDir = $this->option('output');

        if (!in_array($format, ['json', 'yaml'])) {
            $this->error('Invalid format. Must be json or yaml');
            return 1;
        }

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $openapi = Generator::scan([app_path('Http/Controllers')]);

        $filename = $outputDir . '/openapi.' . $format;
        file_put_contents($filename, $openapi->toJson());

        $this->info("API documentation generated successfully at: $filename");
        return 0;
    }
}