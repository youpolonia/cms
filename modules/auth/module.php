<?php
/**
 * Authentication Module Entry Point
 */

require_once __DIR__ . '/authmodule.php';
require_once __DIR__ . '/../../core/hooks.php';

// Register module initialization
add_action('cms_init', [AuthModule::class, 'init']);
