<?php
/**
 * Helpers Tests
 * Tests for core/helpers.php utility functions
 */

require_once __DIR__ . '/TestRunner.php';
require_once __DIR__ . '/../core/helpers.php';

$runner = new TestRunner();

$runner->addTest('h() escapes HTML entities', function () {
    $result = h('<script>alert("xss")</script>');
    TestRunner::assert(strpos($result, '<script>') === false, 'Should escape < and >');
    TestRunner::assert(strpos($result, '&lt;script&gt;') !== false, 'Should contain escaped entities');
});

$runner->addTest('h() escapes quotes', function () {
    $result = h('"double" & \'single\'');
    TestRunner::assert(strpos($result, '&quot;') !== false, 'Should escape double quotes');
    TestRunner::assert(strpos($result, '&#039;') !== false, 'Should escape single quotes');
    TestRunner::assert(strpos($result, '&amp;') !== false, 'Should escape ampersand');
});

$runner->addTest('h() handles empty and normal strings', function () {
    TestRunner::assertEquals('', h(''), 'Empty string should return empty');
    TestRunner::assertEquals('Hello World', h('Hello World'), 'Normal string should pass through');
});

$runner->run();
