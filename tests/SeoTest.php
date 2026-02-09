<?php
/**
 * SEO Tests
 * Tests for core/seo.php SEO settings
 */

require_once __DIR__ . '/TestRunner.php';

if (!defined('CMS_ROOT')) define('CMS_ROOT', realpath(__DIR__ . '/..'));
require_once CMS_ROOT . '/core/seo.php';

$runner = new TestRunner();

$runner->addTest('seo_get_settings returns array', function () {
    $settings = seo_get_settings();
    TestRunner::assert(is_array($settings), 'Should return array');
});

$runner->addTest('seo_get_settings has required keys', function () {
    $settings = seo_get_settings();
    $required = ['site_name', 'meta_description', 'meta_keywords', 'robots_index', 'robots_follow'];
    foreach ($required as $key) {
        TestRunner::assert(array_key_exists($key, $settings), "Missing key: $key");
    }
});

$runner->addTest('seo_get_settings has valid robots defaults', function () {
    $settings = seo_get_settings();
    // robots_index should be 'index' or 'noindex'
    TestRunner::assert(
        in_array($settings['robots_index'], ['index', 'noindex']),
        'robots_index should be index or noindex'
    );
    TestRunner::assert(
        in_array($settings['robots_follow'], ['follow', 'nofollow']),
        'robots_follow should be follow or nofollow'
    );
});

$runner->run();
