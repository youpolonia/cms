<?php

interface AIProviderInterface
{
    /**
     * Generate content using the AI provider
     * 
     * @param string $template The template identifier
     * @param array $variables Template variables
     * @param array $options Generation options
     * @return string Generated content
     * @throws AIProviderException
     */
    public function generateContent(string $template, array $variables = [], array $options = []): string;

    /**
     * Get available models for this provider
     * 
     * @return array List of available models
     */
    public function getAvailableModels(): array;

    /**
     * Get provider configuration
     * 
     * @return array Provider configuration
     */
    public function getConfig(): array;

    /**
     * Check if provider is available/configured
     * 
     * @return bool True if available
     */
    public function isAvailable(): bool;

    /**
     * Get usage statistics for current period
     * 
     * @return array Usage statistics
     */
    public function getUsageStats(): array;

    /**
     * Get remaining quota for current period
     * 
     * @return int Remaining tokens/quota
     */
    public function getRemainingQuota(): int;
}

class AIProviderException extends \Exception {}
