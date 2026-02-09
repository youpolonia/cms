<?php
/**
 * AI Configuration Tests
 * Tests for AI settings and model selector
 */

require_once __DIR__ . '/TestRunner.php';

if (!defined('CMS_ROOT')) define('CMS_ROOT', realpath(__DIR__ . '/..'));
require_once CMS_ROOT . '/core/ai_model_selector.php';

$runner = new TestRunner();

$runner->addTest('ai_model_selector_load_settings returns array', function () {
    $settings = ai_model_selector_load_settings();
    TestRunner::assert(is_array($settings), 'Should return array');
    TestRunner::assert(isset($settings['default_provider']), 'Should have default_provider');
});

$runner->addTest('ai_model_selector_load_settings has providers', function () {
    $settings = ai_model_selector_load_settings();
    TestRunner::assert(
        isset($settings['providers']) && is_array($settings['providers']),
        'Should have providers array'
    );
});

$runner->addTest('config/ai.php returns valid config', function () {
    $config = require CMS_ROOT . '/config/ai.php';
    TestRunner::assert(is_array($config), 'Should return array');
    TestRunner::assert(isset($config['default_provider']), 'Should have default_provider');
    TestRunner::assert(isset($config['providers']), 'Should have providers');
    $providerNames = array_keys($config['providers']);
    TestRunner::assert(in_array('openai', $providerNames), 'Should have openai provider');
});

$runner->run();
