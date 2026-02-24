<?php
declare(strict_types=1);

class RealEstateAI
{
    public static function generateDescription(string $title, string $propertyType = '', string $city = '', int $bedrooms = 0, float $price = 0): array
    {
        if (!function_exists('ai_universal_generate')) require_once CMS_ROOT . '/core/ai_content.php';
        $prompt = "Write a compelling real estate property listing description.\n\n"
            . "Property: {$title}\n"
            . ($propertyType ? "Type: {$propertyType}\n" : '')
            . ($city ? "Location: {$city}\n" : '')
            . ($bedrooms ? "Bedrooms: {$bedrooms}\n" : '')
            . ($price > 0 ? "Price: £" . number_format($price, 0) . "\n" : '')
            . "\nReturn JSON: {\"description\": \"2-3 paragraphs, professional and enticing\", \"short_description\": \"1 sentence summary\", \"suggested_features\": [\"feature1\", \"feature2\", \"feature3\", \"feature4\", \"feature5\"]}\nReturn ONLY valid JSON.";
        $response = ai_universal_generate($prompt, ['max_tokens' => 600, 'temperature' => 0.5]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'AI generation failed'];
    }

    public static function generateValuation(string $title, string $propertyType, string $city, int $bedrooms = 0, int $bathrooms = 0, int $areaSqft = 0): array
    {
        if (!function_exists('ai_universal_generate')) require_once CMS_ROOT . '/core/ai_content.php';
        $prompt = "Estimate the market value of this property based on UK real estate data.\n\n"
            . "Property: {$title}\nType: {$propertyType}\nLocation: {$city}\n"
            . ($bedrooms ? "Bedrooms: {$bedrooms}\n" : '')
            . ($bathrooms ? "Bathrooms: {$bathrooms}\n" : '')
            . ($areaSqft ? "Area: {$areaSqft} sq ft\n" : '')
            . "\nReturn JSON: {\"estimated_value\": 350000, \"price_range\": \"£300,000 - £400,000\", \"price_per_sqft\": 250, \"reasoning\": \"Brief explanation\", \"market_trend\": \"rising/stable/falling\"}\nReturn ONLY valid JSON.";
        $response = ai_universal_generate($prompt, ['max_tokens' => 400, 'temperature' => 0.3]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'Valuation failed'];
    }

    private static function parseJson(string $response): ?array
    {
        $response = trim($response);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $response, $m)) $response = $m[1];
        $data = json_decode($response, true);
        if ($data !== null) return $data;
        if (preg_match('/[\[{][\s\S]*[\]}]/', $response, $m)) return json_decode($m[0], true);
        return null;
    }
}
