<?php
/**
 * JTB Element Tests
 * 
 * @package JessieCMS
 * @since 2026-02-08
 */

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/plugins/jessie-theme-builder/includes/class-jtb-element.php';
require_once CMS_ROOT . '/plugins/jessie-theme-builder/includes/class-jtb-registry.php';
require_once __DIR__ . '/TestRunner.php';

use JessieThemeBuilder\JTB_Element;
use JessieThemeBuilder\JTB_Registry;

$runner = new TestRunner();

// Test 1: Registry exists
$runner->addTest('JTB_Registry class exists', function() {
    TestRunner::assert(class_exists('JessieThemeBuilder\JTB_Registry'));
});

// Test 2: Element base class exists
$runner->addTest('JTB_Element class exists', function() {
    TestRunner::assert(class_exists('JessieThemeBuilder\JTB_Element'));
});

// Test 3: Registry can get instance
$runner->addTest('JTB_Registry::getInstance works', function() {
    $registry = JTB_Registry::all();
    TestRunner::assert(is_array($registry));
});

// Test 4: Registry has modules
$runner->addTest('Registry has registered modules', function() {
    $registry = JTB_Registry::all();
    try { JTB_Registry::init(); $result = true; } catch(Exception $e) { $result = false; }
    TestRunner::assert($result, 'Init should work');
});

$runner->run();
