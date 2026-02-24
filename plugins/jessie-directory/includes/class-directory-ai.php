<?php
declare(strict_types=1);

class DirectoryAI
{
    /**
     * Generate a listing description from basic info.
     */
    public static function generateDescription(string $businessName, string $category = '', string $city = '', string $language = 'en'): array
    {
        if (!function_exists('ai_universal_generate')) { require_once CMS_ROOT . '/core/ai_content.php'; }

        $prompt = "Write a professional business listing description.\n\n"
            . "Business: {$businessName}\n"
            . ($category ? "Category: {$category}\n" : '')
            . ($city ? "Location: {$city}\n" : '')
            . "Language: {$language}\n\n"
            . "Return JSON: {\"description\": \"2-3 paragraph professional description\", \"short_description\": \"1 sentence\", \"suggested_tags\": \"tag1, tag2, tag3, tag4, tag5\"}\n"
            . "Return ONLY valid JSON.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 500, 'temperature' => 0.4]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'AI generation failed'];
    }

    /**
     * Generate SEO-optimized listing content.
     */
    public static function optimizeListing(int $listingId): array
    {
        $listing = \DirectoryListing::get($listingId);
        if (!$listing) return ['ok' => false, 'error' => 'Listing not found'];

        if (!function_exists('ai_universal_generate')) { require_once CMS_ROOT . '/core/ai_content.php'; }

        $prompt = "Optimize this business listing for search engines.\n\n"
            . "Business: {$listing['title']}\nCategory: {$listing['category_name']}\nCity: {$listing['city']}\nCurrent description: " . substr($listing['description'], 0, 500) . "\n\n"
            . "Return JSON: {\"optimized_description\": \"...\", \"meta_title\": \"60 chars max\", \"meta_description\": \"160 chars max\", \"suggested_tags\": \"tag1, tag2\"}\n"
            . "Return ONLY valid JSON.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 500, 'temperature' => 0.3]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'Optimization failed'];
    }

    private static function parseJson(string $response): ?array
    {
        $response = trim($response);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $response, $m)) $response = $m[1];
        $data = json_decode($response, true);
        if ($data) return $data;
        if (preg_match('/\{[\s\S]*\}/', $response, $m)) return json_decode($m[0], true);
        return null;
    }
}
