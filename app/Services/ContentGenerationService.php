<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ContentGenerationService
{
    protected array $config;
    protected bool $useNodeServer;
    protected PhpContentGenerationService $phpService;

    public function __construct(PhpContentGenerationService $phpService)
    {
        $this->config = config('mcp.servers')['content-generation'];
        $this->phpService = $phpService;
        $this->useNodeServer = $this->detectNodeServer();
    }

    public function detectNodeServer(): bool
    {
        try {
            $response = Http::timeout(2)
                ->get("{$this->config['base_uri']}/ping");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function generateContent(string $prompt, string $model = 'gpt-3.5-turbo'): string
    {
        try {
            if ($this->useNodeServer) {
                $response = Http::withHeaders([
                    'Authorization' => $this->config['api_key']
                ])->timeout($this->config['timeout'])
                  ->post("{$this->config['base_uri']}/generate/content", [
                      'prompt' => $prompt,
                      'model' => $model,
                      'max_tokens' => 1000
                  ]);

                $content = $response->json('content');
                if (!empty($content)) {
                    return $content;
                }
            }
            
            return $this->phpService->generateContent($prompt, $model);
        } catch (\Exception $e) {
            return $this->phpService->generateContent($prompt, $model);
        }
    }

    public function summarizeText(string $text): string
    {
        if ($this->useNodeServer) {
            $response = Http::withHeaders([
                'Authorization' => $this->config['api_key']
            ])->timeout($this->config['timeout'])
              ->post("{$this->config['base_uri']}/summarize", [
                  'text' => $text
              ]);
        } else {
            return $this->phpService->summarizeText($text);
        }

        return $response->json('summary');
    }

    public function generateSeo(string $topic): array
    {
        if ($this->useNodeServer) {
            $response = Http::withHeaders([
                'Authorization' => $this->config['api_key']
            ])->timeout($this->config['timeout'])
              ->post("{$this->config['base_uri']}/generate-seo", [
                  'topic' => $topic
              ]);
        } else {
            return $this->phpService->generateSeo($topic);
        }

        return [
            'keywords' => $response->json('seo'),
            'description' => $response->json('meta_description')
        ];
    }
}