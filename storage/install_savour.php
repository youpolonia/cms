<?php
/**
 * ============================================================================
 * SAVOUR - Fine Dining Restaurant Layout Installer
 * ============================================================================
 * 
 * Complete luxury restaurant template with 5 pages:
 * - Home (Hero, Features, Menu Preview, Testimonials, CTA)
 * - About (Story, Philosophy, Team, Awards)
 * - Menu (Appetizers, Mains, Desserts)
 * - Gallery (Photo Grid)
 * - Contact (Info, Form, Map)
 * 
 * Design: Dark luxury theme with gold accents
 * Style: 2025/2026 trends - minimalist, elegant typography
 * 
 * Usage: php install_savour.php
 * ============================================================================
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/database.php';

// Only allow in DEV_MODE
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Forbidden: DEV_MODE required');
}

// Include all parts
require_once __DIR__ . '/layout_savour_part1.php';
require_once __DIR__ . '/layout_savour_part2.php';
require_once __DIR__ . '/layout_savour_part3.php';

// ============================================================================
// BUILD COMPLETE LAYOUT JSON
// ============================================================================
$contentJson = [
    'meta' => [
        'name' => 'Savour - Fine Dining',
        'version' => '1.0.0',
        'created' => date('Y-m-d H:i:s'),
        'brief' => 'Luxurious dark-themed restaurant template with gold accents. Perfect for fine dining establishments.',
        'design_system' => $designSystem
    ],
    'pages' => [
        $homePage,
        $aboutPage,
        $menuPage,
        $galleryPage,
        $contactPage
    ]
];

// ============================================================================
// INSERT INTO DATABASE
// ============================================================================
$db = \core\Database::connection();

try {
    // Check if layout already exists
    $stmt = $db->prepare("SELECT id FROM tb_layout_library WHERE slug = ?");
    $stmt->execute([$layoutData['slug']]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing
        $stmt = $db->prepare("
            UPDATE tb_layout_library SET
                name = ?,
                description = ?,
                category = ?,
                industry = ?,
                style = ?,
                page_count = ?,
                thumbnail = ?,
                content_json = ?,
                is_premium = ?,
                is_ai_generated = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $layoutData['name'],
            $layoutData['description'],
            $layoutData['category'],
            $layoutData['industry'],
            $layoutData['style'],
            $layoutData['page_count'],
            $layoutData['thumbnail'],
            json_encode($contentJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            $layoutData['is_premium'],
            $layoutData['is_ai_generated'],
            $existing['id']
        ]);
        
        echo "✓ Layout updated successfully!\n";
        echo "  ID: {$existing['id']}\n";
        echo "  Name: {$layoutData['name']}\n";
    } else {
        // Insert new
        $stmt = $db->prepare("
            INSERT INTO tb_layout_library 
            (name, slug, description, category, industry, style, page_count, thumbnail, content_json, is_premium, is_ai_generated, downloads, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $layoutData['name'],
            $layoutData['slug'],
            $layoutData['description'],
            $layoutData['category'],
            $layoutData['industry'],
            $layoutData['style'],
            $layoutData['page_count'],
            $layoutData['thumbnail'],
            json_encode($contentJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            $layoutData['is_premium'],
            $layoutData['is_ai_generated'],
            $layoutData['downloads'],
            $layoutData['created_by']
        ]);
        
        $newId = $db->lastInsertId();
        
        echo "✓ Layout installed successfully!\n";
        echo "  ID: {$newId}\n";
        echo "  Name: {$layoutData['name']}\n";
        echo "  Slug: {$layoutData['slug']}\n";
        echo "  Pages: {$layoutData['page_count']}\n";
    }
    
    echo "\n=== SAVOUR LAYOUT DETAILS ===\n";
    echo "Category: {$layoutData['category']}\n";
    echo "Industry: {$layoutData['industry']}\n";
    echo "Style: {$layoutData['style']}\n";
    echo "Pages: Home, About, Menu, Gallery, Contact\n";
    echo "\nDesign System:\n";
    echo "  Primary: #0f0f0f (Deep Black)\n";
    echo "  Accent: #d4af37 (Gold)\n";
    echo "  Typography: Playfair Display + Lato\n";
    
} catch (PDOException $e) {
    echo "✗ Error installing layout:\n";
    echo "  " . $e->getMessage() . "\n";
    exit(1);
}
