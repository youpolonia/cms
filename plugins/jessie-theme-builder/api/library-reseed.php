<?php
/**
 * JTB Library API - Reseed templates
 * POST /api/jtb/library-reseed
 *
 * Clears existing premade templates and re-seeds them
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Ensure tables exist
if (!JTB_Library::tablesExist()) {
    JTB_Library::createTables();
}

try {
    $db = \core\Database::connection();

    // Delete existing premade templates
    $db->exec("DELETE FROM jtb_library_templates WHERE is_premade = 1");

    // Re-seed
    $count = JTB_Library_Seeder::seed();

    echo json_encode([
        'success' => true,
        'message' => "Re-seeded $count templates",
        'count' => $count
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to reseed: ' . $e->getMessage()
    ]);
}
