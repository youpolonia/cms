<?php
/**
 * JTB AI Save Website Endpoint
 * 
 * Saves generated website to CMS templates
 * POST /api/jtb/ai/save-website
 * 
 * @package JessieThemeBuilder
 * @since 2.0.0
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Parse request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    jtb_json_response(false, [], 'Invalid request data', 400);
    exit;
}

$sessionId = $data['session_id'] ?? '';
$mapping = $data['mapping'] ?? [];
$clearExisting = $data['clear_existing'] ?? false;

if (empty($sessionId)) {
    jtb_json_response(false, [], 'Session ID is required', 400);
    exit;
}

// Get session
$session = JTB_AI_MultiAgent::getSession($sessionId);
if (!$session) {
    jtb_json_response(false, [], 'Session not found or expired', 404);
    exit;
}

// Get final website
$website = $session['final_website'] ?? null;
if (!$website) {
    jtb_json_response(false, [], 'No website data to save. Complete the build first.', 400);
    exit;
}

$savedCount = 0;
$savedItems = [];
$deletedCount = 0;

try {
    $db = \core\Database::connection();
    
    // Clear existing templates if requested
    if ($clearExisting) {
        // Delete all existing body templates (pages)
        $stmt = $db->prepare("DELETE FROM jtb_templates WHERE type = 'body'");
        $stmt->execute();
        $deletedCount += $stmt->rowCount();
        
        // Optionally keep default header/footer or delete all
        // For now, delete non-default ones
        $stmt = $db->prepare("DELETE FROM jtb_templates WHERE type IN ('header', 'footer') AND is_default = 0");
        $stmt->execute();
        $deletedCount += $stmt->rowCount();
    }
    
    // Save header
    if (!empty($website['header'])) {
        $headerContent = $website['header'];
        $headerName = $mapping['header']['name'] ?? 'AI Generated Header';
        
        $templateData = [
            'name' => $headerName,
            'type' => 'header',
            'content' => [
                'version' => '1.0',
                'content' => $headerContent['sections'] ?? [$headerContent]
            ],
            'is_default' => true, // Make it default
            'priority' => 10
        ];
        
        // Update existing default or create new
        $existing = JTB_Templates::getDefault('header');
        if ($existing) {
            $templateData['id'] = $existing['id'];
        }
        
        $templateId = JTB_Templates::save($templateData);
        if ($templateId) {
            $savedCount++;
            $savedItems[] = ['type' => 'header', 'id' => $templateId, 'name' => $headerName];
        }
    }
    
    // Save footer
    if (!empty($website['footer'])) {
        $footerContent = $website['footer'];
        $footerName = $mapping['footer']['name'] ?? 'AI Generated Footer';
        
        $templateData = [
            'name' => $footerName,
            'type' => 'footer',
            'content' => [
                'version' => '1.0',
                'content' => $footerContent['sections'] ?? [$footerContent]
            ],
            'is_default' => true,
            'priority' => 10
        ];
        
        $existing = JTB_Templates::getDefault('footer');
        if ($existing) {
            $templateData['id'] = $existing['id'];
        }
        
        $templateId = JTB_Templates::save($templateData);
        if ($templateId) {
            $savedCount++;
            $savedItems[] = ['type' => 'footer', 'id' => $templateId, 'name' => $footerName];
        }
    }
    
    // Save pages as body templates
    if (!empty($website['pages'])) {
        foreach ($website['pages'] as $pageKey => $pageContent) {
            if (empty($pageContent)) continue;
            
            $pageName = $mapping['pages'][$pageKey]['name'] ?? ucfirst($pageKey);
            $pageSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $pageKey));
            
            $templateData = [
                'name' => $pageName,
                'type' => 'body',
                'content' => [
                    'version' => '1.0',
                    'content' => $pageContent['sections'] ?? [$pageContent]
                ],
                'is_default' => ($pageKey === 'home'), // Home is default
                'priority' => 10,
                'slug' => '/' . $pageSlug
            ];
            
            $templateId = JTB_Templates::save($templateData);
            if ($templateId) {
                $savedCount++;
                $savedItems[] = ['type' => 'page', 'id' => $templateId, 'name' => $pageName, 'slug' => $pageSlug];
            }
        }
    }
    
    jtb_json_response(true, [
        'saved_count' => $savedCount,
        'deleted_count' => $deletedCount,
        'saved_items' => $savedItems,
        'message' => $clearExisting 
            ? "Replaced existing website with {$savedCount} new items"
            : "Saved {$savedCount} items to CMS"
    ]);
    
} catch (\Exception $e) {
    jtb_json_response(false, [], 'Save failed: ' . $e->getMessage(), 500);
}
