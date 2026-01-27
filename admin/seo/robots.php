<?php
/**
 * Robots.txt Management Controller
 */

// Verify admin access
require_once __DIR__ . '/../../admin/includes/auth.php';
if (!checkAdminAccess('seo_tools')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Load config
require_once __DIR__ . '/../../../config/robots.php';
require_once __DIR__ . '/../../core/csrf.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    try {
        $rules = [];
        
        // Process form data
        foreach ($_POST['rules'] as $rule) {
            $processedRule = [
                'User-agent' => $rule['user_agent'] ?? '*'
            ];
            
            if (!empty($rule['disallow'])) {
                $processedRule['Disallow'] = array_filter(
                    explode("\n", $rule['disallow']),
                    fn($line) => !empty(trim($line))
                );
            }
            
            if (!empty($rule['allow'])) {
                $processedRule['Allow'] = array_filter(
                    explode("\n", $rule['allow']),
                    fn($line) => !empty(trim($line))
                );
            }
            
            if (!empty($rule['crawl_delay'])) {
                $processedRule['Crawl-delay'] = (int)$rule['crawl_delay'];
            }
            
            if (!empty($rule['sitemap'])) {
                $processedRule['Sitemap'] = $rule['sitemap'];
            }
            
            $rules[] = $processedRule;
        }
        
        // Save rules
        setRobotsRules($rules);
        
        // Redirect to avoid form resubmission
        header('Location: ?saved=1');
        exit;
    } catch (Exception $e) {
        $error = 'Failed to save rules: ' . $e->getMessage();
    }
}

// Get current rules
$currentRules = getRobotsRules();

// Include view
require_once __DIR__ . '/../../admin/views/seo/robots.php';
