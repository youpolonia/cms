<?php

namespace App\Contracts;

interface ContentGenerationServiceInterface
{
    public function generateContent(string $prompt, array $parameters = []): string;
    
    public function getUsageCost(): float;
    
    public function getUsageCount(): int;
    
    public function getRemainingCredits(): float;
    
    public function isWithinRateLimit(): bool;
}