<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once __DIR__.'/../../builderengine/blockmanager.php';
require_once __DIR__.'/../../builderengine/blockrenderer.php';

class BlockTest {
    public static function run(): void {
        // Test basic block rendering
        $textBlock = json_encode([
            'version' => '2.2',
            'blocks' => [[
                'type' => 'text',
                'data' => ['content' => 'Test content']
            ]]
        ]);

        $result = BlockRenderer::render($textBlock);
        echo "Text Block Test: " . ($result === '
<div class="builder-text">Test content</div>' ? 'PASS' : 'FAIL') . "\n";

        // Test legacy HTML fallback
        $legacy = '
<p>Legacy HTML</p>';
        $result = BlockRenderer::render($legacy);
        echo "Legacy Fallback Test: " . ($result === $legacy ? 'PASS' : 'FAIL') . "\n";

        // Test conversion
        $converted = BlockRenderer::convertLegacy($legacy);
        $expected = '{"version":"2.2","blocks":[{"type":"text","data":{"content":"
<p>Legacy HTML<\/p>"}}]}';
        echo "Conversion Test: " . ($converted === $expected ? 'PASS' : 'FAIL') . "\n";
    }
}

BlockTest::run();
