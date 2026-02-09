<?php
/**
 * Theme Demo Content Installer
 * 
 * Installs demo pages, menu, and articles when activating a theme.
 * Each theme can have content/demo.json with sample data.
 */

/**
 * Install demo content for a theme
 * 
 * @param string $themeName Theme directory name
 * @param array $options ['clear_existing' => bool, 'install_menu' => bool]
 * @return array ['success' => bool, 'pages_created' => int, 'message' => string]
 */
function theme_install_demo_content(string $themeName, array $options = []): array {
    $clearExisting = $options['clear_existing'] ?? false;
    $installMenu = $options['install_menu'] ?? true;
    
    $themeDir = CMS_ROOT . '/themes/' . $themeName;
    $demoFile = $themeDir . '/content/demo.json';
    
    if (!file_exists($demoFile)) {
        return ['success' => false, 'pages_created' => 0, 'message' => 'No demo content found for this theme'];
    }
    
    $demo = json_decode(file_get_contents($demoFile), true);
    if (!$demo || json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'pages_created' => 0, 'message' => 'Invalid demo.json format'];
    }
    
    $pdo = \core\Database::connection();
    $pagesCreated = 0;
    $pageMap = []; // slug => id mapping for menu creation
    
    try {
        $pdo->beginTransaction();
        
        // Optionally clear existing demo pages (those with template matching theme pages)
        if ($clearExisting) {
            // Delete pages created by theme demos (marked with meta_description containing theme tag)
            $stmt = $pdo->prepare("DELETE FROM pages WHERE meta_description LIKE ?");
            $stmt->execute(['%[demo:' . $themeName . ']%']);
            
            // Delete demo menu if exists
            $stmt = $pdo->prepare("DELETE mi FROM menu_items mi JOIN menus m ON mi.menu_id = m.id WHERE m.slug = 'header'");
            $stmt->execute();
        }
        
        // Install pages
        if (!empty($demo['pages'])) {
            foreach ($demo['pages'] as $i => $page) {
                $slug = $page['slug'];
                
                // Check if slug already exists
                $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
                $stmt->execute([$slug]);
                $existing = $stmt->fetchColumn();
                
                if ($existing) {
                    // Update existing page
                    $stmt = $pdo->prepare("
                        UPDATE pages SET 
                            title = ?, content = ?, excerpt = ?, template = ?,
                            featured_image = ?, meta_title = ?, meta_description = ?,
                            status = 'published', menu_order = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $page['title'],
                        $page['content'] ?? '',
                        $page['excerpt'] ?? null,
                        ($page['slug'] === 'gallery') ? 'gallery' : ($page['template'] ?? 'default'),
                        $page['featured_image'] ?? null,
                        $page['meta_title'] ?? $page['title'],
                        ($page['meta_description'] ?? '') . ' [demo:' . $themeName . ']',
                        $i,
                        $existing
                    ]);
                    $pageMap[$slug] = (int)$existing;
                } else {
                    // Insert new page
                    $stmt = $pdo->prepare("
                        INSERT INTO pages (slug, title, content, excerpt, template, featured_image, 
                            meta_title, meta_description, status, menu_order, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published', ?, NOW(), NOW())
                    ");
                    $stmt->execute([
                        $slug,
                        $page['title'],
                        $page['content'] ?? '',
                        $page['excerpt'] ?? null,
                        ($page['slug'] === 'gallery') ? 'gallery' : ($page['template'] ?? 'default'),
                        $page['featured_image'] ?? null,
                        $page['meta_title'] ?? $page['title'],
                        ($page['meta_description'] ?? '') . ' [demo:' . $themeName . ']',
                        $i
                    ]);
                    $pageMap[$slug] = (int)$pdo->lastInsertId();
                    $pagesCreated++;
                }
            }
        }
        
        // Install articles
        if (!empty($demo['articles'])) {
            foreach ($demo['articles'] as $article) {
                $stmt = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
                $stmt->execute([$article['slug']]);
                if (!$stmt->fetchColumn()) {
                    $stmt = $pdo->prepare("
                        INSERT INTO articles (slug, title, content, excerpt, featured_image, featured_image_alt,
                            meta_title, meta_description, status, published_at, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published', NOW(), NOW(), NOW())
                    ");
                    $stmt->execute([
                        $article['slug'],
                        $article['title'],
                        $article['content'] ?? '',
                        $article['excerpt'] ?? null,
                        $article['featured_image'] ?? null,
                        $article['featured_image_alt'] ?? null,
                        $article['meta_title'] ?? $article['title'],
                        $article['meta_description'] ?? null
                    ]);
                }
            }
        }
        
        // Install menu
        if ($installMenu && !empty($demo['menu'])) {
            // Create or update header menu
            $stmt = $pdo->prepare("SELECT id FROM menus WHERE slug = 'header'");
            $stmt->execute();
            $menuId = $stmt->fetchColumn();
            
            if (!$menuId) {
                $stmt = $pdo->prepare("INSERT INTO menus (name, slug, location, is_active) VALUES ('Header Menu', 'header', 'header', 1)");
                $stmt->execute();
                $menuId = (int)$pdo->lastInsertId();
            }
            
            // Clear existing menu items
            $stmt = $pdo->prepare("DELETE FROM menu_items WHERE menu_id = ?");
            $stmt->execute([$menuId]);
            
            // Add menu items
            foreach ($demo['menu'] as $i => $item) {
                $pageId = null;
                $url = $item['url'] ?? null;
                
                // Link to page by slug if specified
                if (!empty($item['page_slug']) && isset($pageMap[$item['page_slug']])) {
                    $pageId = $pageMap[$item['page_slug']];
                    $url = '/' . $item['page_slug'];
                } elseif (!empty($item['page_slug'])) {
                    // Try to find existing page
                    $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
                    $stmt->execute([$item['page_slug']]);
                    $foundId = $stmt->fetchColumn();
                    if ($foundId) {
                        $pageId = (int)$foundId;
                        $url = '/' . $item['page_slug'];
                    }
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO menu_items (menu_id, title, url, page_id, sort_order, is_active)
                    VALUES (?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([
                    $menuId,
                    $item['title'],
                    $url ?? '#',
                    $pageId,
                    $i
                ]);
            }
        }
        
        // Update theme settings if provided
        if (!empty($demo['settings'])) {
            foreach ($demo['settings'] as $key => $value) {
                $stmt = $pdo->prepare("SELECT id FROM settings WHERE `key` = ?");
                $stmt->execute([$key]);
                if ($stmt->fetchColumn()) {
                    $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE `key` = ?");
                    $stmt->execute([$value, $key]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO settings (`key`, value, group_name) VALUES (?, ?, 'theme')");
                    $stmt->execute([$key, $value]);
                }
            }
        }
        
        $pdo->commit();
        
        return [
            'success' => true, 
            'pages_created' => $pagesCreated,
            'total_pages' => count($demo['pages'] ?? []),
            'message' => "Demo content installed: {$pagesCreated} new pages created"
        ];
        
    } catch (\Throwable $e) {
        $pdo->rollBack();
        error_log("Theme demo content install failed: " . $e->getMessage());
        return ['success' => false, 'pages_created' => 0, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Check if a theme has demo content available
 */
function theme_has_demo_content(string $themeName): bool {
    return file_exists(CMS_ROOT . '/themes/' . $themeName . '/content/demo.json');
}

/**
 * Get demo content info without installing
 */
function theme_get_demo_info(string $themeName): ?array {
    $demoFile = CMS_ROOT . '/themes/' . $themeName . '/content/demo.json';
    if (!file_exists($demoFile)) return null;
    
    $demo = json_decode(file_get_contents($demoFile), true);
    if (!$demo) return null;
    
    return [
        'pages' => count($demo['pages'] ?? []),
        'articles' => count($demo['articles'] ?? []),
        'has_menu' => !empty($demo['menu']),
        'page_list' => array_map(fn($p) => $p['title'], $demo['pages'] ?? []),
    ];
}

/**
 * Remove demo content for a theme
 */
function theme_remove_demo_content(string $themeName): array {
    $pdo = \core\Database::connection();
    
    try {
        // Remove demo-tagged pages
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pages WHERE meta_description LIKE ?");
        $stmt->execute(['%[demo:' . $themeName . ']%']);
        $count = (int)$stmt->fetchColumn();
        
        $stmt = $pdo->prepare("DELETE FROM pages WHERE meta_description LIKE ?");
        $stmt->execute(['%[demo:' . $themeName . ']%']);
        
        return ['success' => true, 'pages_removed' => $count];
    } catch (\Throwable $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
