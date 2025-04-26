<?php

namespace App\Services;

use App\Models\ErrorCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AutoClassificationService
{
    protected $patterns = [
        'connection' => ['timeout', 'connection refused', 'failed to connect'],
        'authentication' => ['unauthorized', 'forbidden', 'invalid credentials'],
        'validation' => ['invalid', 'missing field', 'required', 'malformed'],
        'resource' => ['limit exceeded', 'out of memory', 'quota reached']
    ];

    public function classify(string $errorMessage): ?array
    {
        $bestMatch = null;
        $highestConfidence = 0;

        foreach (ErrorCategory::all() as $category) {
            $confidence = $this->calculateConfidence($errorMessage, $category);
            
            if ($confidence > $highestConfidence) {
                $highestConfidence = $confidence;
                $bestMatch = $category;
            }
        }

        if ($highestConfidence >= 0.5) {
            Log::info("Auto-classified error with confidence {$highestConfidence}", [
                'error' => $errorMessage,
                'category' => $bestMatch->name
            ]);

            return [
                'category' => $bestMatch,
                'confidence' => $highestConfidence
            ];
        }

        return null;
    }

    protected function calculateConfidence(string $errorMessage, ErrorCategory $category): float
    {
        $confidence = 0;
        $errorMessage = Str::lower($errorMessage);

        // Check category name match
        if (Str::contains($errorMessage, Str::lower($category->name))) {
            $confidence += 0.4;
        }

        // Check description keywords
        if (Str::contains($errorMessage, Str::lower($category->description))) {
            $confidence += 0.3;
        }

        // Check predefined patterns
        foreach ($this->patterns[$category->slug] ?? [] as $pattern) {
            if (Str::contains($errorMessage, $pattern)) {
                $confidence += 0.2;
            }
        }

        return min($confidence, 1.0);
    }
}