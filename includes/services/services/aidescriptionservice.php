<?php
/**
 * AI Description Service
 * Handles generating and enhancing text content using AI services
 */
class AiDescriptionService
{
    /**
     * @var string API endpoint for AI service
     */
    private static string $apiEndpoint = '';

    /**
     * @var string API key for AI service
     */
    private static string $apiKey = '';

    /**
     * Initialize service with configuration
     * @param array $config ['api_endpoint', 'api_key']
     */
    public static function init(array $config): void
    {
        self::$apiEndpoint = $config['api_endpoint'] ?? '';
        self::$apiKey = $config['api_key'] ?? '';
    }

    /**
     * Generate enhanced description text
     * @param string $input Original description text
     * @param string $companyName Company name for context
     * @return string Enhanced description
     */
    public static function enhanceDescription(string $input, string $companyName): string
    {
        if (empty(self::$apiEndpoint)) {
            return $input;
        }

        $data = [
            'prompt' => "Enhance this company description for $companyName: $input",
            'max_tokens' => 200
        ];

        $response = self::makeApiRequest($data);
        return $response['choices'][0]['text'] ?? $input;
    }

    /**
     * Make API request to AI service
     * @param array $data Request payload
     * @return array API response
     */
    private static function makeApiRequest(array $data): array
    {
        $ch = curl_init(self::$apiEndpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . self::$apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?: [];
    }
}
