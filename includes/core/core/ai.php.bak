<?php
/**
 * Core AI Handler for CMS
 * 
 * Provides framework-free AI integration for content generation and processing
 * Supports OpenAI and Hugging Face APIs
 */

class AIHandler {
    private $apiKey;
    private $cacheDir;
    private $rateLimit = 5; // Requests per minute
    
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        $this->cacheDir = __DIR__ . '/../../storage/ai_cache/';
        
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Generate text content using AI
     */
    public function generateText($prompt, $model = 'gpt-3.5-turbo') {
        $cacheKey = md5($prompt . $model);
        if ($cached = $this->getFromCache($cacheKey)) {
            return $cached;
        }

        $response = $this->callOpenAI([
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => $prompt]]
        ]);

        $this->saveToCache($cacheKey, $response);
        return $response;
    }

    private function callOpenAI($data) {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('API Error: ' . curl_error($ch));
        }
        curl_close($ch);

        return json_decode($response, true);
    }

    private function getFromCache($key) {
        $file = $this->cacheDir . $key . '.json';
        if (file_exists($file) && filemtime($file) > time() - 3600) {
            return json_decode(file_get_contents($file), true);
        }
        return false;
    }

    private function saveToCache($key, $data) {
        file_put_contents(
            $this->cacheDir . $key . '.json',
            json_encode($data)
        );
    }
}
