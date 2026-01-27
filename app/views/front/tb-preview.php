<?php
/**
 * Theme Builder Preview Template
 * @var string $pageTitle
 * @var string $pageContent
 * @var bool $isPreview
 */

$title = htmlspecialchars($pageTitle ?? 'Preview');
$content = $pageContent ?? '';

// Get active theme from settings (simple approach without loading full theme system)
$activeTheme = 'default';
try {
    $settingsFile = CMS_ROOT . '/config/settings.json';
    if (file_exists($settingsFile)) {
        $settings = json_decode(file_get_contents($settingsFile), true);
        $activeTheme = $settings['active_theme'] ?? 'default';
    }
} catch (Throwable $e) {
    $activeTheme = 'default';
}
$themePath = '/themes/' . $activeTheme;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?><?= $isPreview ? ' - Preview' : '' ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    
    <!-- Theme Builder Frontend Styles -->
    <link rel="stylesheet" href="/assets/css/tb-frontend.css">
    
    <!-- Active Theme Styles -->
    <?php if (file_exists(CMS_ROOT . '/themes/' . $activeTheme . '/css/style.css')): ?>
    <link rel="stylesheet" href="<?= $themePath ?>/css/style.css">
    <?php endif; ?>
    <?php if (file_exists(CMS_ROOT . '/themes/' . $activeTheme . '/css/main.css')): ?>
    <link rel="stylesheet" href="<?= $themePath ?>/css/main.css">
    <?php endif; ?>
    
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            line-height: 1.6; 
            margin: 0;
            padding: 0;
        }
        <?php if ($isPreview): ?>
        body::before {
            content: 'PREVIEW MODE';
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            background: #f59e0b;
            color: #000;
            padding: 4px 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 9999;
            border-radius: 0 0 6px 6px;
        }
        <?php endif; ?>
        
        /* Animation keyframes */
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInLeft { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes fadeInRight { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes slideInUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
        @keyframes slideInDown { from { transform: translateY(-100%); } to { transform: translateY(0); } }
        @keyframes slideInLeft { from { transform: translateX(-100%); } to { transform: translateX(0); } }
        @keyframes slideInRight { from { transform: translateX(100%); } to { transform: translateX(0); } }
        @keyframes zoomIn { from { opacity: 0; transform: scale(0.5); } to { opacity: 1; transform: scale(1); } }
        @keyframes zoomOut { from { opacity: 0; transform: scale(1.5); } to { opacity: 1; transform: scale(1); } }
        @keyframes bounce { 0%, 20%, 50%, 80%, 100% { transform: translateY(0); } 40% { transform: translateY(-30px); } 60% { transform: translateY(-15px); } }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); } 20%, 40%, 60%, 80% { transform: translateX(10px); } }
    </style>
</head>
<body>
    <?php echo $content; ?>
</body>
</html>