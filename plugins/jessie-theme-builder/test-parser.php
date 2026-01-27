<?php
/**
 * Parser Test Script
 * Run from WSL: php /var/www/cms/plugins/jessie-theme-builder/test-parser.php
 */

define('CMS_ROOT', '/var/www/cms');

// Only load the main parser (it will load dependencies)
require_once __DIR__ . '/includes/parser/class-jtb-html-parser.php';

use JessieThemeBuilder\JTB_HTML_Parser;

echo "=== JTB HTML Parser Test ===\n\n";

// Test 1: JTB Annotated HTML
echo "Test 1: JTB Annotated HTML\n";
echo str_repeat('-', 50) . "\n";

$annotatedHtml = <<<HTML
<section data-jtb-module="section" data-jtb-attr-fullwidth="true"
         style="background-color: #f5f5f5; padding: 60px 0;">
  <div data-jtb-module="row" data-jtb-attr-column_structure="1_3,2_3">
    <div data-jtb-module="column">
      <img data-jtb-module="image" src="/img.jpg"
           style="border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    </div>
    <div data-jtb-module="column">
      <h2 data-jtb-module="heading" data-jtb-attr-level="h2"
          style="font-size: 42px; color: #333; font-weight: 700;">
          Beautiful Title
      </h2>
      <p data-jtb-module="text" style="font-size: 16px; line-height: 1.8;">
          Some descriptive text here.
      </p>
    </div>
  </div>
</section>
HTML;

$parser = new JTB_HTML_Parser();
$result = $parser->parse($annotatedHtml);

if (!empty($result['success'])) {
    echo "✓ Parse successful\n";
    echo "  Mode: {$result['mode']}\n";
    echo "  Modules count: {$result['stats']['modules_count']}\n";

    // Check first section
    $section = $result['content'][0] ?? null;
    if ($section && $section['type'] === 'section') {
        echo "  ✓ Section detected\n";
        echo "    - fullwidth: " . ($section['attrs']['fullwidth'] ?? 'not set') . "\n";
        echo "    - background_color: " . ($section['attrs']['background_color'] ?? 'not set') . "\n";
        echo "    - padding_top: " . ($section['attrs']['padding_top'] ?? 'not set') . "\n";
    }
} else {
    echo "✗ Parse failed: " . ($result['error'] ?? 'Unknown error') . "\n";
}

echo "\n";

// Test 2: Generic HTML
echo "Test 2: Generic HTML\n";
echo str_repeat('-', 50) . "\n";

$genericHtml = <<<HTML
<section style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 80px 40px;">
    <div style="display: flex; gap: 30px; max-width: 1200px; margin: 0 auto;">
        <div style="flex: 1;">
            <h1 style="font-size: 48px; color: white; font-weight: 800;">
                Welcome to Our Site
            </h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 18px; line-height: 1.6;">
                This is a description paragraph with some content.
            </p>
            <a href="/contact" class="btn" style="background: #fff; color: #667eea; padding: 15px 30px; border-radius: 8px; display: inline-block;">
                Get Started
            </a>
        </div>
        <div style="flex: 1;">
            <img src="/hero-image.jpg" alt="Hero" style="width: 100%; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
        </div>
    </div>
</section>
HTML;

$result2 = $parser->parse($genericHtml);

if (!empty($result2['success'])) {
    echo "✓ Parse successful\n";
    echo "  Mode: {$result2['mode']}\n";
    echo "  Modules count: {$result2['stats']['modules_count']}\n";

    // Check structure
    $section = $result2['content'][0] ?? null;
    if ($section) {
        echo "  ✓ First module type: {$section['type']}\n";
        echo "    - background_type: " . ($section['attrs']['background_type'] ?? 'not set') . "\n";
        echo "    - has gradient stops: " . (isset($section['attrs']['background_gradient_stops']) ? 'yes (' . count($section['attrs']['background_gradient_stops']) . ' stops)' : 'no') . "\n";
        echo "    - gradient direction: " . ($section['attrs']['background_gradient_direction'] ?? 'not set') . "\n";
    }
} else {
    echo "✗ Parse failed: " . ($result2['error'] ?? 'Unknown error') . "\n";
}

echo "\n";

// Test 3: Responsive Styles
echo "Test 3: Responsive Styles\n";
echo str_repeat('-', 50) . "\n";

$responsiveHtml = <<<HTML
<section data-jtb-module="section">
  <div data-jtb-module="row">
    <div data-jtb-module="column">
      <h1 data-jtb-module="heading"
          style="font-size: 72px; color: #333;"
          data-jtb-tablet-style="font-size: 48px;"
          data-jtb-phone-style="font-size: 32px;">
          Responsive Heading
      </h1>
    </div>
  </div>
</section>
HTML;

$result3 = $parser->parse($responsiveHtml);

if (!empty($result3['success'])) {
    echo "✓ Parse successful\n";

    // Find heading module by traversing structure
    function findModuleByType($modules, $type) {
        foreach ($modules as $module) {
            if (isset($module['type']) && $module['type'] === $type) {
                return $module;
            }
            if (!empty($module['content'])) {
                $found = findModuleByType($module['content'], $type);
                if ($found) return $found;
            }
        }
        return null;
    }

    $heading = findModuleByType($result3['content'], 'heading');

    if ($heading) {
        echo "  ✓ Heading found\n";
        echo "    - font_size (desktop): " . ($heading['attrs']['font_size'] ?? 'not set') . "\n";
        echo "    - font_size__tablet: " . ($heading['attrs']['font_size__tablet'] ?? 'not set') . "\n";
        echo "    - font_size__phone: " . ($heading['attrs']['font_size__phone'] ?? 'not set') . "\n";
    } else {
        echo "  ? Heading not found in structure\n";
    }
} else {
    echo "✗ Parse failed: " . ($result3['error'] ?? 'Unknown error') . "\n";
}

echo "\n";

// Test 4: Complex Component (Accordion)
echo "Test 4: Complex Component Recognition\n";
echo str_repeat('-', 50) . "\n";

$accordionHtml = <<<HTML
<div class="accordion" style="border: 1px solid #ddd;">
    <div class="accordion-item">
        <div class="accordion-header" style="padding: 15px; background: #f5f5f5;">
            Question 1
        </div>
        <div class="accordion-content" style="padding: 15px;">
            Answer to question 1
        </div>
    </div>
    <div class="accordion-item">
        <div class="accordion-header" style="padding: 15px; background: #f5f5f5;">
            Question 2
        </div>
        <div class="accordion-content" style="padding: 15px;">
            Answer to question 2
        </div>
    </div>
</div>
HTML;

$result4 = $parser->parse($accordionHtml);

if (!empty($result4['success'])) {
    echo "✓ Parse successful\n";
    echo "  Mode: {$result4['mode']}\n";

    $accordion = findModuleByType($result4['content'], 'accordion');
    if ($accordion) {
        echo "  ✓ Accordion module detected\n";
        echo "    - children count: " . count($accordion['content'] ?? []) . "\n";
    } else {
        echo "  ? Accordion not detected (parsed as generic structure)\n";
    }
} else {
    echo "✗ Parse failed: " . ($result4['error'] ?? 'Unknown error') . "\n";
}

echo "\n";
echo "=== All Tests Complete ===\n";

// Optionally output full structure
if (in_array('--verbose', $argv ?? [])) {
    echo "\n\nFull structure of Test 1:\n";
    echo str_repeat('-', 50) . "\n";
    print_r($result);
}
