<?php

class AIExportEnhancer {
    private static $providers = [
        'openai' => [
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
            'headers' => [
                'Content-Type: application/json',
                'Authorization: Bearer %s'
            ]
        ],
        'huggingface' => [
            'endpoint' => 'https://api-inference.huggingface.co/models/%s',
            'headers' => [
                'Content-Type: application/json',
                'Authorization: Bearer %s'
            ]
        ]
    ];

    public static function enhance(array $data, string $strategy = 'summarize'): array {
        $provider = config('ai.default_provider');
        $config = config("ai.providers.$provider");
        
        if (empty($config['key'])) {
            throw new \RuntimeException("No API key configured for $provider");
        }

        $payload = self::preparePayload($data, $strategy, $provider);
        $response = self::makeRequest($provider, $config, $payload);

        return self::processResponse($response, $provider);
    }

    private static function preparePayload(array $data, string $strategy, string $provider): array {
        $input = self::formatInput($data);
        
        if ($provider === 'openai') {
            return [
                'model' => config('ai.providers.openai.models.default'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => self::getSystemPrompt($strategy)
                    ],
                    [
                        'role' => 'user',
                        'content' => $input
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000
            ];
        }

        return [
            'inputs' => $input,
            'parameters' => [
                'max_length' => 2000,
                'strategy' => $strategy
            ]
        ];
    }

    private static function makeRequest(string $provider, array $config, array $payload): string {
        $endpoint = self::$providers[$provider]['endpoint'];
        $headers = array_map(function($header) use ($config) {
            return sprintf($header, $config['key']);
        }, self::$providers[$provider]['headers']);

        if ($provider === 'huggingface') {
            $endpoint = sprintf($endpoint, $config['models']['default']);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $config['timeout']
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \RuntimeException('AI API request failed: ' . curl_error($ch));
        }
        curl_close($ch);

        return $response;
    }

    private static function processResponse(string $response, string $provider): array {
        $data = json_decode($response, true);
        
        if ($provider === 'openai') {
            return [
                'content' => $data['choices'][0]['message']['content'] ?? '',
                'usage' => $data['usage'] ?? []
            ];
        }

        return [
            'content' => $data[0]['generated_text'] ?? '',
            'usage' => ['total_tokens' => strlen($data[0]['generated_text'] ?? '') / 4]
        ];
    }

    private static function formatInput(array $data): string {
        // Convert array data to text format for AI processing
        $output = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $output[] = "$key: $value";
        }
        return implode("\n", $output);
    }

    private static function getSystemPrompt(string $strategy): string {
        $prompts = [
            'summarize' => 'Summarize this data into concise bullet points highlighting key information.',
            'analyze' => 'Analyze this data and provide insights about trends and patterns.',
            'simplify' => 'Simplify this technical data for a non-technical audience.'
        ];
        return $prompts[$strategy] ?? $prompts['summarize'];
    }
}
