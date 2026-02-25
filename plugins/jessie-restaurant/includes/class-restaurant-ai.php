<?php
declare(strict_types=1);

class RestaurantAI
{
    public static function generateDescription(string $itemName, string $category = '', string $cuisine = ''): array
    {
        if (!function_exists('ai_universal_generate')) require_once CMS_ROOT . '/core/ai_content.php';
        $prompt = "Write a mouth-watering menu item description for a restaurant.\n\nDish: {$itemName}\n"
            . ($category ? "Category: {$category}\n" : '') . ($cuisine ? "Cuisine: {$cuisine}\n" : '')
            . "\nReturn JSON: {\"description\": \"2-3 sentences, vivid and appetizing\", \"short_description\": \"1 sentence\", \"suggested_allergens\": \"e.g. gluten, dairy\", \"suggested_price\": 12.99}\nReturn ONLY valid JSON.";
        $response = ai_universal_generate($prompt, ['max_tokens' => 400, 'temperature' => 0.6]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'AI generation failed'];
    }

    public static function generateMenu(string $restaurantName, string $cuisine, int $itemCount = 10): array
    {
        if (!function_exists('ai_universal_generate')) require_once CMS_ROOT . '/core/ai_content.php';
        $prompt = "Generate a restaurant menu.\n\nRestaurant: {$restaurantName}\nCuisine: {$cuisine}\nItems needed: {$itemCount}\n\n"
            . "Return JSON array: [{\"category\": \"Starters\", \"category_icon\": \"🥗\", \"name\": \"...\", \"description\": \"...\", \"price\": 9.99, \"is_vegetarian\": false, \"is_spicy\": false, \"allergens\": \"gluten, dairy\"}]\nReturn ONLY valid JSON array.";
        $response = ai_universal_generate($prompt, ['max_tokens' => 2000, 'temperature' => 0.7]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'items' => $data] : ['ok' => false, 'error' => 'Menu generation failed'];
    }

    public static function suggestPricing(string $itemName, string $category, string $city = ''): array
    {
        if (!function_exists('ai_universal_generate')) require_once CMS_ROOT . '/core/ai_content.php';
        $prompt = "Suggest pricing for a restaurant menu item.\n\nItem: {$itemName}\nCategory: {$category}\n"
            . ($city ? "Location: {$city}\n" : '')
            . "\nReturn JSON: {\"suggested_price\": 12.99, \"price_range\": \"£10-£15\", \"reasoning\": \"...\"}\nReturn ONLY valid JSON.";
        $response = ai_universal_generate($prompt, ['max_tokens' => 300, 'temperature' => 0.3]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'Pricing suggestion failed'];
    }

    private static function parseJson(string $response): mixed
    {
        $response = trim($response);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $response, $m)) $response = $m[1];
        $data = json_decode($response, true);
        if ($data !== null) return $data;
        if (preg_match('/[\[{][\s\S]*[\]}]/', $response, $m)) return json_decode($m[0], true);
        return null;
    }
}
