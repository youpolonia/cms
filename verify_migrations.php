<?php

if (file_exists(__DIR__ . '/includes/database/Migrations/migration.php')) {
    require_once __DIR__ . '/includes/database/Migrations/migration.php';
} else {
    error_log('Missing includes/database/Migrations/migration.php');
}
if (file_exists(__DIR__ . "/includes/database/Migrations/2025_05_16_140900_add_recurrence_fields_to_scheduled_events.php")) {
    require_once __DIR__ . "/includes/database/Migrations/2025_05_16_140900_add_recurrence_fields_to_scheduled_events.php";
} else {
    error_log("Missing migration 2025_05_16_140900_add_recurrence_fields_to_scheduled_events.php");
}
if (file_exists(__DIR__ . "/includes/database/Migrations/2025_05_16_141000_add_error_message_to_scheduled_events.php")) {
    require_once __DIR__ . "/includes/database/Migrations/2025_05_16_141000_add_error_message_to_scheduled_events.php";
} else {
    error_log("Missing migration 2025_05_16_141000_add_error_message_to_scheduled_events.php");
}
if (file_exists(__DIR__ . "/includes/database/Migrations/2025_05_16_141100_add_conditions_to_scheduled_events.php")) {
    require_once __DIR__ . "/includes/database/Migrations/2025_05_16_141100_add_conditions_to_scheduled_events.php";
} else {
    error_log("Missing migration 2025_05_16_141100_add_conditions_to_scheduled_events.php");
}

// Execute migrations
$migrations = [
    new AddRecurrenceFieldsToScheduledEvents(),
    new AddErrorMessageToScheduledEvents(),
    new AddConditionsToScheduledEvents()
];

echo "Starting migration verification...\n";

foreach ($migrations as $migration) {
    $className = get_class($migration);
    echo "Executing migration: {$className}...\n";
    
    try {
        $result = $migration->apply();
        if ($result) {
            echo "Migration {$className} applied successfully.\n";
        } else {
            echo "Migration {$className} failed to apply.\n";
        }
    } catch (Exception $e) {
        echo "Error executing migration {$className}: " . $e->getMessage() . "\n";
    }
}

// Verify schema
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} else {
    error_log('Missing config.php');
}
if (file_exists(__DIR__ . '/core/database.php')) {
    require_once __DIR__ . '/core/database.php';
} else {
    error_log('Missing core/database.php');
}
$db = \core\Database::connection();


$stmt = $db->query("DESCRIBE scheduled_events");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\nVerifying scheduled_events table schema:\n";
$columnNames = array_column($columns, 'Field');

$requiredColumns = [
    'id', 'content_id', 'version_id', 'user_id', 'scheduled_at', 'status',
    'error_message', 'recurrence_pattern', 'recurrence_params', 'conditions',
    'created_at', 'updated_at'
];

$missingColumns = array_diff($requiredColumns, $columnNames);
if (empty($missingColumns)) {
    echo "All required columns exist in the scheduled_events table.\n";
} else {
    echo "Missing columns in scheduled_events table: " . implode(', ', $missingColumns) . "\n";
}

echo "\nSchema verification complete.\n";
