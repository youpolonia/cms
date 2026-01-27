<?php
chdir("/var/www/cms");
require_once "/var/www/cms/core/bootstrap.php";
if (!defined("CMS_ROOT")) define("CMS_ROOT", "/var/www/cms");

$pluginPath = "/var/www/cms/plugins/jessie-theme-builder";
require_once $pluginPath . "/includes/class-jtb-element.php";
require_once $pluginPath . "/includes/class-jtb-registry.php";
require_once $pluginPath . "/includes/class-jtb-fonts.php";
require_once $pluginPath . "/includes/class-jtb-css-generator.php";
require_once $pluginPath . "/includes/class-jtb-renderer.php";
require_once $pluginPath . "/includes/class-jtb-library.php";
require_once $pluginPath . "/includes/class-jtb-library-seeder.php";

$dirs = array("structure", "content", "interactive", "media", "forms", "blog", "fullwidth", "theme");
foreach ($dirs as $d) {
    $p = $pluginPath . "/modules/" . $d;
    if (is_dir($p)) {
        foreach (glob($p . "/*.php") as $f) {
            require_once $f;
        }
    }
}

// First seed the templates
echo "Seeding templates...\n";
$count = JessieThemeBuilder\JTB_Library_Seeder::seed();
echo "Seeded: " . $count . " templates\n\n";

// Get the BuildPro template
$templates = JessieThemeBuilder\JTB_Library::getAll(['is_premade' => true]);
echo "Templates in database:\n";
foreach ($templates as $t) {
    echo "  - ID: " . $t['id'] . ", Name: " . $t['name'] . "\n";
}

if (!empty($templates)) {
    $template = JessieThemeBuilder\JTB_Library::get($templates[0]['id']);
    if ($template) {
        $content = is_string($template["content"]) ? json_decode($template["content"], true) : $template["content"];
        $html = JessieThemeBuilder\JTB_Renderer::render($content);

        $fullHtml = '<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($template["name"]) . '</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/frontend.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Plus Jakarta Sans", sans-serif; background: #fff; color: #1f2937; line-height: 1.6; }
        img { max-width: 100%; height: auto; }
        .jtb-section { position: relative; }
        .jtb-section-inner { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .jtb-row { display: flex; flex-wrap: wrap; margin: 0 -15px; }
        .jtb-column { padding: 0 15px; }
        .jtb-column-1-1 { width: 100%; }
        .jtb-column-1-2 { width: 50%; }
        .jtb-column-1-3 { width: 33.333%; }
        .jtb-column-2-3 { width: 66.666%; }
        .jtb-column-1-4 { width: 25%; }
        .jtb-column-3-4 { width: 75%; }
        .jtb-button { display: inline-block; text-decoration: none; cursor: pointer; transition: all 0.3s ease; }
        .jtb-heading { margin: 0; }
        .jtb-text { margin: 0; }
        .jtb-image img { display: block; }
        @media (max-width: 768px) {
            .jtb-column { width: 100% !important; margin-bottom: 20px; }
        }
    </style>
</head>
<body>
' . $html . '
</body>
</html>';

        file_put_contents("/var/www/cms/test_buildpro.html", $fullHtml);
        echo "\nTemplate: " . $template["name"] . "\n";
        echo "HTML saved to /var/www/cms/test_buildpro.html\n";
        echo "Open: http://localhost/test_buildpro.html\n\n";

        // Show first 2000 chars of generated HTML
        echo "Generated HTML preview (first 2000 chars):\n";
        echo substr($html, 0, 2000) . "\n...\n";
    }
}
