<?php
/**
 * Migration: Add Pexels API key setting
 * Adds setting for Pexels video API integration
 */

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../core/database.php';

$db = \core\Database::connection();

// Check if setting already exists
$stmt = $db->prepare("SELECT id FROM settings WHERE `key` = ?");
$stmt->execute(['pexels_api_key']);

if (!$stmt->fetch()) {
    // Insert new setting
    $stmt = $db->prepare("INSERT INTO settings (`key`, value, group_name, updated_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([
        'pexels_api_key',
        '',
        'integrations'
    ]);
    echo "Added pexels_api_key setting\n";
} else {
    echo "pexels_api_key setting already exists\n";
}

// Also add openai_api_key if not exists (for AI features)
$stmt = $db->prepare("SELECT id FROM settings WHERE `key` = ?");
$stmt->execute(['openai_api_key']);

if (!$stmt->fetch()) {
    $stmt = $db->prepare("INSERT INTO settings (`key`, value, group_name, updated_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([
        'openai_api_key',
        '',
        'integrations'
    ]);
    echo "Added openai_api_key setting\n";
}

echo "Migration complete!\n";
