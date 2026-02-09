<?php
/**
 * JTB Templates Test
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/bootstrap.php';
require_once __DIR__ . '/TestRunner.php';
if (!function_exists('esc')) {
    function esc(?string $str): string { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
}

// Boot JTB
require_once CMS_ROOT . '/plugins/jessie-theme-builder/includes/jtb-frontend-boot.php';

use JessieThemeBuilder\JTB_Templates;
use JessieThemeBuilder\JTB_Renderer;
use JessieThemeBuilder\JTB_Theme_Integration;
use JessieThemeBuilder\JTB_Registry;

$runner = new TestRunner('JtbTemplateTest');

// Test 1: JTB_Templates class exists
$runner->addTest('JTB_Templates class exists', function() {
    return class_exists('\\JessieThemeBuilder\\JTB_Templates');
});

// Test 2: JTB_Renderer class exists
$runner->addTest('JTB_Renderer class exists', function() {
    return class_exists('\\JessieThemeBuilder\\JTB_Renderer');
});

// Test 3: JTB_Theme_Integration class exists
$runner->addTest('JTB_Theme_Integration class exists', function() {
    return class_exists('\\JessieThemeBuilder\\JTB_Theme_Integration');
});

// Test 4: Can query templates
$runner->addTest('getDefault returns array or null', function() {
    $result = JTB_Templates::getDefault('header');
    return $result === null || is_array($result);
});

// Test 5: Can query footer templates
$runner->addTest('getDefault footer returns array or null', function() {
    $result = JTB_Templates::getDefault('footer');
    return $result === null || is_array($result);
});

// Test 6: Renderer can render empty content
$runner->addTest('Renderer handles empty content', function() {
    $result = JTB_Renderer::render(['content' => []]);
    return is_string($result);
});

// Test 7: Renderer renders section with heading
$runner->addTest('Renderer renders section with heading module', function() {
    $content = [
        'content' => [
            [
                'type' => 'section',
                'attrs' => [],
                'children' => [
                    [
                        'type' => 'row',
                        'attrs' => [],
                        'children' => [
                            [
                                'type' => 'column',
                                'attrs' => ['width' => '100'],
                                'children' => [
                                    [
                                        'type' => 'heading',
                                        'attrs' => [
                                            'text' => 'Test Heading',
                                            'tag' => 'h2'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
    $result = JTB_Renderer::render($content);
    return str_contains($result, 'Test Heading') && str_contains($result, '<h2');
});

// Test 8: Theme Integration renderHeader returns string
$runner->addTest('Theme Integration renderHeader returns string', function() {
    $result = JTB_Theme_Integration::renderHeader();
    return is_string($result);
});

// Test 9: Theme Integration renderFooter returns string
$runner->addTest('Theme Integration renderFooter returns string', function() {
    $result = JTB_Theme_Integration::renderFooter();
    return is_string($result);
});

// Test 10: Registry has modules loaded
$runner->addTest('Registry has 79+ modules', function() {
    $modules = JTB_Registry::all();
    return count($modules) >= 79;
});

$runner->run();
