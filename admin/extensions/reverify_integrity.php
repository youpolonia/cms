<?php
declare(strict_types=1);

/**
 * Extension Re-Verify Integrity Script
 *
 * This script re-verifies integrity of all extensions via HTTP requests:?>
 * 1. GET index.php to extract slugs from first <td> of each row
 * 2. For each slug: refresh index, extract csrf_token, POST verify.php action=check
 * 3. Follow redirects, parse class="flash ok" vs "flash err"
 * 4. Output: "SLUG: <slug>\nVERIFY: <ok|error>"
 */

// Global cookie jar
$cookies = [];

// HTTP client functions
function http_get(string $url): string {
    global $cookies;
    
    $cookieHeader = '';
    if (!empty($cookies)) {
        $cookieHeader = "Cookie: " . implode('; ', $cookies) . "\r\n";
    }
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => $cookieHeader,
            'ignore_errors' => true
        ]
    ]);
    
    $result = @file_get_contents($url, false, $context);
    
    // Parse cookies from response headers
    if ($result !== false) {
        parse_cookies_from_response($http_response_header);
    }
    
    return $result !== false ? $result : '';
}

function http_post(string $url, array $data): string {
    global $cookies;
    
    $postData = http_build_query($data);
    $cookieHeader = '';
    if (!empty($cookies)) {
        $cookieHeader = "Cookie: " . implode('; ', $cookies) . "\r\n";
    }
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-type: application/x-www-form-urlencoded\r\n" .
                       "Content-Length: " . strlen($postData) . "\r\n" .
                       $cookieHeader,
            'content' => $postData,
            'ignore_errors' => true,
            'follow_location' => 1,
            'max_redirects' => 3
        ]
    ]);
    
    $result = @file_get_contents($url, false, $context);
    
    // Parse cookies from response headers
    if ($result !== false) {
        parse_cookies_from_response($http_response_header);
    }
    
    return $result !== false ? $result : '';
}

function parse_cookies_from_response(array $headers): void {
    global $cookies;
    
    foreach ($headers as $header) {
        if (stripos($header, 'Set-Cookie:') === 0) {
            $cookie = trim(substr($header, 11));
            $parts = explode(';', $cookie);
            $cookiePair = explode('=', $parts[0], 2);
            if (count($cookiePair) === 2) {
                $cookieName = trim($cookiePair[0]);
                $cookieValue = trim($cookiePair[1]);
                $cookies[$cookieName] = "$cookieName=$cookieValue";
            }
        }
    }
}

function extract_csrf_token(string $html): ?string {
    if (preg_match('/
<input[^>]*name="csrf_token"[^>]*value="([^"]*)"/i',
 $html, $matches)) {
        return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }
    return null;
}

function extract_slugs_from_table(string $html): array {
    $slugs = [];
    if (preg_match_all('/
<tr>\s*<td>([^<]+)<\/td>/i',
 $html, $matches)) {
        $slugs = $matches[1];
    }
    return $slugs;
}

function parse_verify_result(string $html): string {
    if (strpos($html, 'class="flash ok"') !== false) {
        return 'ok';
    } elseif (strpos($html, 'class="flash err"') !== false) {
        return 'error';
    }
    return 'unknown';
}

// Base URL for admin extensions
$baseUrl = 'http://localhost:8001/admin/extensions/';

// Step 1: GET index.php to extract slugs
echo "Fetching extension list from index.php...\n";
$indexHtml = http_get($baseUrl . 'index.php');
$slugs = extract_slugs_from_table($indexHtml);

if (empty($slugs)) {
    echo "No extensions found in index.php.\n";
    exit(0);
}

echo "Found " . count($slugs) . " extensions to verify.\n";

// Step 2: For each slug, perform verification via HTTP
foreach ($slugs as $slug) {
    $slug = trim($slug);
    if (empty($slug)) continue;
    
    echo "Processing $slug...\n";
    
    // Refresh index to get fresh CSRF token
    $indexHtml = http_get($baseUrl . 'index.php');
    $csrfToken = extract_csrf_token($indexHtml);
    
    if (!$csrfToken) {
        echo "SLUG: $slug\nVERIFY: error (CSRF token not found)\n";
        continue;
    }
    
    // POST to verify.php with action=check
    $postData = [
        'csrf_token' => $csrfToken,
        'slug' => $slug,
        'action' => 'check'
    ];
    
    $verifyResultHtml = http_post($baseUrl . 'verify.php', $postData);
    $result = parse_verify_result($verifyResultHtml);
    
    // Debug output to see what we're getting
    if ($result === 'unknown') {
        echo "DEBUG: HTML response length: " . strlen($verifyResultHtml) . "\n";
        echo "DEBUG: Response content: " . var_export($verifyResultHtml, true) . "\n";
    }
    
    // Output in requested format
    echo "SLUG: $slug\nVERIFY: $result\n";
}
