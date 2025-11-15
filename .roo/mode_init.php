<?php
/**
 * Mode initialization file - runs before any mode execution
 * Enforces project rules and prevents banned content
 */

// Load memory bank files first

require_once __DIR__ . '/../core/taskvalidator.php';

// Validate task before proceeding
if (!TaskValidator::validateTask($currentMode)) {
    die("Task validation failed - check error logs");
}

// Additional mode-specific initialization
switch ($currentMode) {
    case 'architect':
        require_once __DIR__.'/rules-architect.php';
        break;
    case 'code':
        require_once __DIR__.'/rules-code.php';
        break;
    // Other modes...
}
