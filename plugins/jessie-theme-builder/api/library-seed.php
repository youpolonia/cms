<?php
/**
 * JTB Library API - Seed premade templates
 * POST /api/jtb/library-seed
 *
 * Seeds the library with premade templates if not already done
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

header('Content-Type: application/json');

// Ensure tables exist
if (!JTB_Library::tablesExist()) {
    JTB_Library::createTables();
}

try {
    $count = JTB_Library_Seeder::seed();

    echo json_encode([
        'success' => true,
        'seeded' => $count,
        'message' => $count > 0 ? "Seeded {$count} templates" : 'Templates already seeded'
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to seed templates: ' . $e->getMessage()
    ]);
}
