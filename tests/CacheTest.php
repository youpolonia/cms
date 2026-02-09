<?php
/**
 * Cache Tests
 * Tests for core/cache.php file-based caching
 */

require_once __DIR__ . '/TestRunner.php';
require_once __DIR__ . '/../core/cache.php';

$runner = new TestRunner();

$runner->addTest('Cache set returns true', function () {
    $result = Cache::set('test_cache_key', 'hello', 60);
    TestRunner::assert($result === true, 'Cache::set should return true');
    Cache::clear('test_cache_key');
});

$runner->addTest('Cache get returns stored value', function () {
    Cache::set('test_get_key', ['foo' => 'bar'], 60);
    $result = Cache::get('test_get_key');
    TestRunner::assert(is_array($result), 'Cache::get should return array');
    TestRunner::assertEquals('bar', $result['foo'], 'Cached array value mismatch');
    Cache::clear('test_get_key');
});

$runner->addTest('Cache get returns null for missing key', function () {
    $result = Cache::get('nonexistent_key_xyz_123');
    TestRunner::assert($result === null, 'Cache::get should return null for missing key');
});

$runner->addTest('Cache clear removes entry', function () {
    Cache::set('test_clear_key', 'data', 60);
    Cache::clear('test_clear_key');
    $result = Cache::get('test_clear_key');
    TestRunner::assert($result === null, 'Cache::get should return null after clear');
});

$runner->addTest('Cache expired entry returns null', function () {
    Cache::set('test_expired_key', 'old data', 1);
    // Directly write expired cache file
    $file = (new ReflectionClass(Cache::class))->getConstant('CACHE_DIR') . 'test_expired_key.cache';
    $expired = json_encode(['data' => 'old', 'expires' => time() - 10, 'created' => time() - 20]);
    file_put_contents($file, $expired);
    $result = Cache::get('test_expired_key');
    TestRunner::assert($result === null, 'Expired cache should return null');
});

$runner->run();
