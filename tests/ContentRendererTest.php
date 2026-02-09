<?php
/**
 * ContentRenderer Tests
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/content_renderer.php';
require_once __DIR__ . '/TestRunner.php';

$runner = new TestRunner();

// Test 1: Empty content
$runner->addTest('render empty content returns empty', function() {
    $result = ContentRenderer::render('');
    TestRunner::assert($result === '', 'Expected empty');
});

// Test 2: Plain HTML passes through
$runner->addTest('render plain HTML passes through', function() {
    $html = '<h1>Hello World</h1><p>This is a test.</p>';
    $result = ContentRenderer::render($html);
    TestRunner::assert($result === $html, 'Should match');
});

// Test 3: toText strips HTML
$runner->addTest('toText strips tags correctly', function() {
    $html = '<h1>Title</h1><p>Paragraph one.</p>';
    $result = ContentRenderer::toText($html);
    TestRunner::assert(str_contains($result, 'Title'), 'Should contain Title');
    TestRunner::assert(str_contains($result, 'Paragraph one'), 'Should contain paragraph');
    TestRunner::assert(!str_contains($result, '<h1>'), 'Should not contain HTML tags');
});

// Test 4: toText empty
$runner->addTest('toText empty returns empty', function() {
    $result = ContentRenderer::toText('');
    TestRunner::assert($result === '', 'Expected empty');
});

// Test 5: Legacy TB content cleaned
$runner->addTest('legacy TB content stripped of builder markup', function() {
    $tb = '<section id="section_abc" class="tb-section" style="background-color:#fff"><div class="tb-section-inner"><div id="row_abc" class="tb-row"><div id="col_abc" class="tb-column tb-col-100"><div id="mod_abc" class="tb-module tb-module-text"><div class="tb-text-content"><h2>About Us</h2><p>We are great.</p></div></div></div></div></div></section>';
    $result = ContentRenderer::render($tb);
    TestRunner::assert(str_contains($result, 'About Us'), 'Should keep heading');
    TestRunner::assert(str_contains($result, 'We are great'), 'Should keep paragraph');
    TestRunner::assert(!str_contains($result, 'tb-section'), 'No tb-section class');
    TestRunner::assert(!str_contains($result, 'tb-module'), 'No tb-module class');
});

// Test 6: hasRenderers
$runner->addTest('hasRenderers returns true after registration', function() {
    TestRunner::assert(ContentRenderer::hasRenderers(), 'Should have renderers');
});

// Test 7: getRegisteredNames
$runner->addTest('getRegisteredNames includes legacy-tb', function() {
    $names = ContentRenderer::getRegisteredNames();
    TestRunner::assert(in_array('legacy-tb', $names), 'Should include legacy-tb');
});

// Test 8: toText from TB
$runner->addTest('toText extracts text from TB markup', function() {
    $tb = '<section class="tb-section"><div class="tb-section-inner"><div class="tb-row"><div class="tb-column"><h2>Contact</h2><p>Call 555-1234.</p></div></div></div></section>';
    $text = ContentRenderer::toText($tb);
    TestRunner::assert(str_contains($text, 'Contact'), 'Should have Contact');
    TestRunner::assert(str_contains($text, '555-1234'), 'Should have phone');
});

$runner->run();
