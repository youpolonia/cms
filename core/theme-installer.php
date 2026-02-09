<?php
/**
 * JTB Theme Installer — Imports theme templates into jtb_templates DB table
 * Called when theme is activated from admin/themes
 */

function jtb_install_theme(string $themeSlug, PDO $pdo): array {
    $themesDir = CMS_ROOT . '/themes/' . $themeSlug;
    $result = ['success' => false, 'message' => '', 'imported' => 0];
    
    if (!is_dir($themesDir)) {
        $result['message'] = 'Theme directory not found';
        return $result;
    }
    
    // Load theme.json
    $themeJson = $themesDir . '/theme.json';
    if (!file_exists($themeJson)) {
        $result['message'] = 'theme.json not found';
        return $result;
    }
    $theme = json_decode(file_get_contents($themeJson), true);
    if (!$theme) {
        $result['message'] = 'Invalid theme.json';
        return $result;
    }
    
    // Check for templates directory
    $templatesDir = $themesDir . '/templates';
    if (!is_dir($templatesDir)) {
        $result['message'] = 'No templates directory found';
        return $result;
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    try {
        // Backup existing templates
        $existing = $pdo->query("SELECT id, name, type, content, is_default FROM jtb_templates")->fetchAll(PDO::FETCH_ASSOC);
        $backupFile = CMS_ROOT . '/themes/.backup_' . date('Ymd_His') . '.json';
        file_put_contents($backupFile, json_encode($existing, JSON_PRETTY_PRINT));
        
        // Clear existing templates
        $pdo->exec("DELETE FROM jtb_templates");
        
        // Import header
        $headerFile = $templatesDir . '/header.json';
        if (file_exists($headerFile)) {
            $content = file_get_contents($headerFile);
            $stmt = $pdo->prepare("INSERT INTO jtb_templates (name, type, content, is_default, is_active, priority, created_at, updated_at) VALUES (?, 'header', ?, 1, 1, 0, NOW(), NOW())");
            $stmt->execute([$theme['name'] . ' Header', $content]);
            $result['imported']++;
        }
        
        // Import footer
        $footerFile = $templatesDir . '/footer.json';
        if (file_exists($footerFile)) {
            $content = file_get_contents($footerFile);
            $stmt = $pdo->prepare("INSERT INTO jtb_templates (name, type, content, is_default, is_active, priority, created_at, updated_at) VALUES (?, 'footer', ?, 1, 1, 0, NOW(), NOW())");
            $stmt->execute([$theme['name'] . ' Footer', $content]);
            $result['imported']++;
        }
        
        // Import body templates (pages)
        $pageFiles = glob($templatesDir . '/page-*.json');
        // Sort: page-home first = default
        usort($pageFiles, function($a, $b) { return (str_contains($a, "home") ? 0 : 1) - (str_contains($b, "home") ? 0 : 1); });
        $isFirst = true;
        foreach ($pageFiles as $pageFile) {
            $pageData = json_decode(file_get_contents($pageFile), true);
            if (!$pageData) continue;
            
            $pageName = $pageData['name'] ?? basename($pageFile, '.json');
            $content = json_encode($pageData['content'] ?? $pageData);
            
            $stmt = $pdo->prepare("INSERT INTO jtb_templates (name, type, content, is_default, is_active, priority, created_at, updated_at) VALUES (?, 'body', ?, ?, 1, 0, NOW(), NOW())");
            $stmt->execute([$pageName, $content, $isFirst ? 1 : 0]);
            $result['imported']++;
            $isFirst = false;
        }
        
        // Save theme colors/typography to settings
        if (!empty($theme['colors'])) {
            $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`, group_name, updated_at) VALUES ('theme_colors', ?, 'theme', NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = NOW()");
            $stmt->execute([json_encode($theme['colors'])]);
        }
        if (!empty($theme['typography'])) {
            $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`, group_name, updated_at) VALUES ('theme_typography', ?, 'theme', NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = NOW()");
            $stmt->execute([json_encode($theme['typography'])]);
        }
        
        // Create pages if theme defines them
        if (!empty($theme['pages'])) {
            foreach ($theme['pages'] as $page) {
                $slug = $page['slug'] ?? sanitize_slug($page['title'] ?? 'page');
                // Check if page exists
                $check = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
                $check->execute([$slug]);
                if (!$check->fetch()) {
                    $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, status, meta_title, meta_description, created_at, updated_at) VALUES (?, ?, '', 'published', ?, ?, NOW(), NOW())");
                    $stmt->execute([
                        $page['title'] ?? 'Page',
                        $slug,
                        $page['meta_title'] ?? $page['title'] ?? '',
                        $page['meta_description'] ?? ''
                    ]);
                }
            }
        }
        
        $pdo->commit();
        $result['success'] = true;
        $result['message'] = "Imported {$result['imported']} templates from {$theme['name']}";
        
    } catch (\Throwable $e) {
        $pdo->rollBack();
        $result['message'] = 'Import failed: ' . $e->getMessage();
    }
    
    return $result;
}

function sanitize_slug(string $text): string {
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}
