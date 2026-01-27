<?php
/**
 * Debug: Test hover CSS generation
 * Access via: /debug-hover.php
 */

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../core/theme-builder/renderer.php";

// Sample content with hover enabled
$testContent = [
    "sections" => [
        [
            "rows" => [
                [
                    "columns" => [
                        [
                            "modules" => [
                                [
                                    "type" => "text",
                                    "content" => ["text" => "Hover over me to test!"],
                                    "design" => [],
                                    "settings" => [
                                        "hover_enabled" => true,
                                        "hover_transition_duration" => "0.5",
                                        "hover_transition_easing" => "ease-in-out",
                                        "background_color_hover" => "#ff0000",
                                        "text_color_hover" => "#ffffff",
                                        "transform_scale_x_hover" => "110",
                                        "transform_translate_y_hover" => "-10",
                                        "box_shadow_hover_enabled" => true,
                                        "box_shadow_hover_vertical" => "10",
                                        "box_shadow_hover_blur" => "25",
                                        "box_shadow_hover_color" => "rgba(0,0,0,0.3)"
                                    ],
                                    "advanced" => []
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];

header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hover CSS Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a2e; color: #eee; }
        .section { background: #16213e; padding: 15px; margin: 10px 0; border-radius: 8px; }
        h2 { color: #8b5cf6; }
        pre { background: #0f3460; padding: 15px; border-radius: 6px; overflow-x: auto; white-space: pre-wrap; }
        .result { background: #1a472a; padding: 10px; border-radius: 6px; margin: 5px 0; }
        .error { background: #4a1a1a; }
    </style>
</head>
<body>
    <h1>Theme Builder Hover CSS Debug</h1>

    <div class="section">
        <h2>1. Module Settings Check</h2>
        <?php
        $module = $testContent["sections"][0]["rows"][0]["columns"][0]["modules"][0];
        $settings = $module["settings"] ?? [];
        echo "<pre>";
        echo "hover_enabled: " . var_export($settings["hover_enabled"] ?? "NOT SET", true) . "\n";
        echo "empty(hover_enabled): " . var_export(empty($settings["hover_enabled"]), true) . "\n";
        echo "hover_transition_duration: " . ($settings["hover_transition_duration"] ?? "NOT SET") . "\n";
        echo "background_color_hover: " . ($settings["background_color_hover"] ?? "NOT SET") . "\n";
        echo "</pre>";
        ?>
    </div>

    <div class="section">
        <h2>2. Generated Hover CSS</h2>
        <?php
        $hoverCss = tb_generate_hover_css($testContent);
        if ($hoverCss) {
            echo "<div class=\"result\"><pre>" . htmlspecialchars($hoverCss) . "</pre></div>";
        } else {
            echo "<div class=\"result error\">NO hover CSS generated!</div>";
        }
        ?>
    </div>

    <div class="section">
        <h2>3. Full Rendered HTML (excerpt)</h2>
        <?php
        $html = tb_render_page($testContent, ["preview_mode" => true]);
        echo "<pre>" . htmlspecialchars(substr($html, 0, 3000)) . "</pre>";
        ?>
    </div>

    <div class="section">
        <h2>4. Live Preview</h2>
        <div style="background: #0a0a0f; padding: 20px; border-radius: 8px;">
            <?php echo $html; ?>
        </div>
        <p style="margin-top: 10px; color: #a1a1aa;">Hover over the text above to test effects</p>
    </div>
</body>
</html>
