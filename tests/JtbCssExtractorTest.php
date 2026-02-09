<?php
/**
 * JTB CSS Extractor Tests
 * Tests for CSS parsing and attribute mapping
 */

require_once __DIR__ . '/TestRunner.php';

if (!defined('CMS_ROOT')) define('CMS_ROOT', realpath(__DIR__ . '/..'));

// Load JTB CSS Extractor
require_once CMS_ROOT . '/plugins/jessie-theme-builder/includes/ai/class-jtb-css-extractor.php';

use JessieThemeBuilder\JTB_CSS_Extractor;

$runner = new TestRunner();

$runner->addTest('CSS Extractor can be instantiated', function () {
    $extractor = new JTB_CSS_Extractor();
    TestRunner::assertInstanceOf(JTB_CSS_Extractor::class, $extractor);
});

$runner->addTest('CSS Extractor extracts CSS variables', function () {
    $html = '<style>:root { --primary: #ff6600; --bg: #1e293b; }</style><div>test</div>';
    $extractor = new JTB_CSS_Extractor();
    $result = $extractor->extract($html);
    TestRunner::assert(isset($result['variables']), 'Should have variables key');
    TestRunner::assertEquals('#ff6600', $result['variables']['primary'] ?? '', 'Should extract --primary');
    TestRunner::assertEquals('#1e293b', $result['variables']['bg'] ?? '', 'Should extract --bg');
});

$runner->addTest('CSS Extractor extracts rules', function () {
    $html = '<style>.hero { background: #000; padding: 20px; }</style><div class="hero">test</div>';
    $extractor = new JTB_CSS_Extractor();
    $result = $extractor->extract($html);
    TestRunner::assert(!empty($result['rules']), 'Should have rules');
    $heroRules = $extractor->getRulesFor('.hero');
    TestRunner::assertEquals('#000', $heroRules['background'] ?? '', 'Should extract background');
    TestRunner::assertEquals('20px', $heroRules['padding'] ?? '', 'Should extract padding');
});

$runner->addTest('CSS Extractor resolves variables in values', function () {
    $html = '<style>:root { --main: #abcdef; } .box { color: var(--main); }</style><div>test</div>';
    $extractor = new JTB_CSS_Extractor();
    $result = $extractor->extract($html);
    $boxRules = $extractor->getRulesFor('.box');
    TestRunner::assertEquals('#abcdef', $boxRules['color'] ?? '', 'Should resolve var(--main) to #abcdef');
});

$runner->addTest('CSS Extractor maps gradient to section attrs', function () {
    $css = ['background' => 'linear-gradient(135deg, #ff6600 0%, #cc3300 100%)'];
    $extractor = new JTB_CSS_Extractor();
    $attrs = $extractor->mapToSectionAttrs($css);
    TestRunner::assertEquals('gradient', $attrs['background_type'] ?? '', 'Should detect gradient type');
    TestRunner::assertEquals('linear', $attrs['background_gradient_type'] ?? '', 'Should detect linear');
    TestRunner::assertEquals(135, $attrs['background_gradient_direction'] ?? 0, 'Should parse 135deg');
});

$runner->run();
