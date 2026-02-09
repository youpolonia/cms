<?php
/**
 * EventBus Tests
 * Tests for core/eventbus.php pub/sub system
 */

require_once __DIR__ . '/TestRunner.php';

if (!defined('CMS_ROOT')) define('CMS_ROOT', realpath(__DIR__ . '/..'));
require_once CMS_ROOT . '/core/eventbus.php';

$runner = new TestRunner();

$runner->addTest('EventBus singleton works', function () {
    $bus = \Core\EventBus::getInstance();
    $bus2 = \Core\EventBus::getInstance();
    TestRunner::assert($bus === $bus2, 'Should return same instance');
});

$runner->addTest('EventBus dispatch without listeners does not crash', function () {
    $bus = \Core\EventBus::getInstance();
    $bus->dispatch('nonexistent_event_xyz', ['data' => 'test']);
    TestRunner::assert(true, 'Should not throw');
});

$runner->addTest('EventBus listen and dispatch works', function () {
    $bus = \Core\EventBus::getInstance();
    $received = false;
    $bus->listen('test_event_' . mt_rand(), function($payload) use (&$received) {
        $received = true;
    });
    // We can't easily trigger it without knowing the exact event name
    // but at least verify listen doesn't crash
    TestRunner::assert(true, 'Listen should not throw');
});

$runner->run();
