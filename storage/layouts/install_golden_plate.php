<?php
/**
 * ============================================================================
 * GOLDEN PLATE - Fine Dining Restaurant Layout Installer
 * ============================================================================
 * 
 * Complete luxury restaurant template with 5 pages:
 * - Home, About, Menu, Gallery, Contact
 * + Header Template + Footer Template
 * 
 * AUTOMATICALLY:
 * - Downloads images from Pexels to /uploads/media/
 * - Creates header/footer in tb_site_templates
 * - Creates full layout in tb_layout_library
 * 
 * Usage: Navigate to /storage/layouts/install_golden_plate.php
 * ============================================================================
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/theme-builder/database.php';

// Only allow in DEV_MODE
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Forbidden: DEV_MODE required');
}

echo "<pre style='background:#1e1e2e;color:#cdd6f4;padding:20px;font-family:monospace;'>\n";
echo "============================================================\n";
echo "  🍽️  GOLDEN PLATE - Fine Dining Layout Installer\n";
echo "============================================================\n\n";

$db = \core\Database::connection();

// ============================================================================
// STEP 1: DOWNLOAD IMAGES TO LOCAL STORAGE
// ============================================================================
echo "📥 Step 1: Downloading images...\n";

$mediaDir = dirname(__DIR__, 2) . '/uploads/media/';
if (!is_dir($mediaDir)) {
    mkdir($mediaDir, 0755, true);
}

$images = [
    'gp-hero.jpg' => 'https://images.pexels.com/photos/1579739/pexels-photo-1579739.jpeg?auto=compress&cs=tinysrgb&w=1920',
    'gp-chef.jpg' => 'https://images.pexels.com/photos/3814446/pexels-photo-3814446.jpeg?auto=compress&cs=tinysrgb&w=800',
    'gp-interior.jpg' => 'https://images.pexels.com/photos/262978/pexels-photo-262978.jpeg?auto=compress&cs=tinysrgb&w=1920',
    'gp-dish1.jpg' => 'https://images.pexels.com/photos/3535383/pexels-photo-3535383.jpeg?auto=compress&cs=tinysrgb&w=600',
    'gp-dish2.jpg' => 'https://images.pexels.com/photos/699953/pexels-photo-699953.jpeg?auto=compress&cs=tinysrgb&w=600',
    'gp-dish3.jpg' => 'https://images.pexels.com/photos/1435904/pexels-photo-1435904.jpeg?auto=compress&cs=tinysrgb&w=600',
    'gp-dish4.jpg' => 'https://images.pexels.com/photos/2097090/pexels-photo-2097090.jpeg?auto=compress&cs=tinysrgb&w=600',
    'gp-dish5.jpg' => 'https://images.pexels.com/photos/1640772/pexels-photo-1640772.jpeg?auto=compress&cs=tinysrgb&w=600',
    'gp-dish6.jpg' => 'https://images.pexels.com/photos/769289/pexels-photo-769289.jpeg?auto=compress&cs=tinysrgb&w=600',
    'gp-gallery1.jpg' => 'https://images.pexels.com/photos/260922/pexels-photo-260922.jpeg?auto=compress&cs=tinysrgb&w=800',
    'gp-gallery2.jpg' => 'https://images.pexels.com/photos/941861/pexels-photo-941861.jpeg?auto=compress&cs=tinysrgb&w=800',
    'gp-gallery3.jpg' => 'https://images.pexels.com/photos/1267320/pexels-photo-1267320.jpeg?auto=compress&cs=tinysrgb&w=800',
    'gp-gallery4.jpg' => 'https://images.pexels.com/photos/6270541/pexels-photo-6270541.jpeg?auto=compress&cs=tinysrgb&w=800',
    'gp-team1.jpg' => 'https://images.pexels.com/photos/887827/pexels-photo-887827.jpeg?auto=compress&cs=tinysrgb&w=400',
    'gp-team2.jpg' => 'https://images.pexels.com/photos/3771120/pexels-photo-3771120.jpeg?auto=compress&cs=tinysrgb&w=400',
    'gp-team3.jpg' => 'https://images.pexels.com/photos/4252137/pexels-photo-4252137.jpeg?auto=compress&cs=tinysrgb&w=400',
    'gp-testimonial1.jpg' => 'https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=200',
    'gp-testimonial2.jpg' => 'https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=200',
    'gp-testimonial3.jpg' => 'https://images.pexels.com/photos/415829/pexels-photo-415829.jpeg?auto=compress&cs=tinysrgb&w=200',
];

$downloadedImages = [];
$ctx = stream_context_create(['http' => ['timeout' => 30, 'user_agent' => 'Mozilla/5.0']]);

foreach ($images as $filename => $url) {
    $localPath = $mediaDir . $filename;
    $webPath = '/uploads/media/' . $filename;
    
    if (file_exists($localPath)) {
        echo "   ✓ {$filename} (exists)\n";
        $downloadedImages[$filename] = $webPath;
        continue;
    }
    
    $imageData = @file_get_contents($url, false, $ctx);
    if ($imageData !== false) {
        file_put_contents($localPath, $imageData);
        @chmod($localPath, 0644);
        echo "   ✓ {$filename} (downloaded)\n";
        $downloadedImages[$filename] = $webPath;
    } else {
        echo "   ✗ {$filename} (FAILED - using URL)\n";
        $downloadedImages[$filename] = $url;
    }
}

echo "\n";

// Helper to get image path
function img($key) {
    global $downloadedImages;
    return $downloadedImages[$key] ?? '';
}

// Load layout parts
require_once __DIR__ . '/gp_part1_meta.php';
require_once __DIR__ . '/gp_part2_header.php';
require_once __DIR__ . '/gp_part3_footer.php';
require_once __DIR__ . '/gp_part4_home.php';
require_once __DIR__ . '/gp_part5_about.php';
require_once __DIR__ . '/gp_part6_menu.php';
require_once __DIR__ . '/gp_part7_gallery.php';
require_once __DIR__ . '/gp_part8_contact.php';

// ============================================================================
// STEP 2: INSTALL HEADER TEMPLATE
// ============================================================================
echo "📐 Step 2: Installing Header Template...\n";

tb_ensure_templates_table();

$existingHeader = $db->prepare("SELECT id FROM tb_site_templates WHERE type = 'header' AND name = ?");
$existingHeader->execute(['Golden Plate Header']);
$headerRow = $existingHeader->fetch(PDO::FETCH_ASSOC);

$headerData = [
    'type' => 'header',
    'name' => 'Golden Plate Header',
    'content' => $headerContent,
    'conditions' => null,
    'priority' => 10,
    'is_active' => 0,
    'created_by' => 1
];

if ($headerRow) {
    tb_save_template($headerData, $headerRow['id']);
    echo "   ✓ Header updated (ID: {$headerRow['id']})\n";
} else {
    $headerId = tb_save_template($headerData);
    echo "   ✓ Header created (ID: {$headerId})\n";
}

// ============================================================================
// STEP 3: INSTALL FOOTER TEMPLATE
// ============================================================================
echo "📋 Step 3: Installing Footer Template...\n";

$existingFooter = $db->prepare("SELECT id FROM tb_site_templates WHERE type = 'footer' AND name = ?");
$existingFooter->execute(['Golden Plate Footer']);
$footerRow = $existingFooter->fetch(PDO::FETCH_ASSOC);

$footerData = [
    'type' => 'footer',
    'name' => 'Golden Plate Footer',
    'content' => $footerContent,
    'conditions' => null,
    'priority' => 10,
    'is_active' => 0,
    'created_by' => 1
];

if ($footerRow) {
    tb_save_template($footerData, $footerRow['id']);
    echo "   ✓ Footer updated (ID: {$footerRow['id']})\n";
} else {
    $footerId = tb_save_template($footerData);
    echo "   ✓ Footer created (ID: {$footerId})\n";
}

// ============================================================================
// STEP 4: INSTALL LAYOUT TO LIBRARY
// ============================================================================
echo "📚 Step 4: Installing Layout to Library...\n";

$contentJson = [
    'meta' => $layoutMeta,
    'pages' => [
        $homePage,
        $aboutPage,
        $menuPage,
        $galleryPage,
        $contactPage
    ]
];

$stmt = $db->prepare("SELECT id FROM tb_layout_library WHERE slug = ?");
$stmt->execute([$layoutData['slug']]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $stmt = $db->prepare("
        UPDATE tb_layout_library SET
            name = ?, description = ?, category = ?, industry = ?, style = ?,
            page_count = ?, thumbnail = ?, content_json = ?, is_premium = ?,
            is_ai_generated = ?, updated_at = NOW()
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
        json_encode($contentJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        $layoutData['is_premium'],
        $layoutData['is_ai_generated'],
        $existing['id']
    ]);
    echo "   ✓ Layout updated (ID: {$existing['id']})\n";
    $layoutId = $existing['id'];
} else {
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
        json_encode($contentJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        $layoutData['is_premium'],
        $layoutData['is_ai_generated'],
        $layoutData['downloads'],
        $layoutData['created_by']
    ]);
    $layoutId = $db->lastInsertId();
    echo "   ✓ Layout created (ID: {$layoutId})\n";
}

// ============================================================================
// DONE!
// ============================================================================
echo "\n============================================================\n";
echo "  ✅ GOLDEN PLATE INSTALLED SUCCESSFULLY!\n";
echo "============================================================\n";
echo "\n";
echo "📊 Layout Details:\n";
echo "   Name: {$layoutData['name']}\n";
echo "   Slug: {$layoutData['slug']}\n";
echo "   Pages: Home, About, Menu, Gallery, Contact\n";
echo "   Style: Luxury Dark + Gold (#d4af37)\n";
echo "\n";
echo "🎨 Design System:\n";
echo "   Primary: #0a0a0a (Deep Black)\n";
echo "   Accent: #d4af37 (Elegant Gold)\n";
echo "   Typography: Playfair Display + Inter\n";
echo "\n";
echo "📁 Templates Created:\n";
echo "   • Golden Plate Header (tb_site_templates)\n";
echo "   • Golden Plate Footer (tb_site_templates)\n";
echo "\n";
echo "🖼️ Images: " . count($downloadedImages) . " downloaded to /uploads/media/\n";
echo "\n";
echo "👉 Next Steps:\n";
echo "   1. Go to Theme Builder > Layout Library\n";
echo "   2. Find 'Golden Plate Fine Dining'\n";
echo "   3. Click 'Use Layout' to apply to pages\n";
echo "   4. Activate Header/Footer in TB Themes\n";
echo "\n";
echo "</pre>";
