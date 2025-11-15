<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/database.php'; // Assuming you have a Database connection class
require_once __DIR__ . '/../../includes/pagebuilder/component.php';

header('Content-Type: text/html'); // Components will render HTML

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo "Component ID is required.";
    exit;
}

$componentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($componentId === false || $componentId <= 0) {
    http_response_code(400);
    echo "Invalid Component ID.";
    exit;
}

try {
    $dbConnection = \core\Database::connection(); // Get PDO instance
    
    // Fetch the specific component's HTML structure
    // This part needs a new method in the Component class or direct query
    $stmt = $dbConnection->prepare("SELECT html FROM page_builder_components WHERE id = ?");
    $stmt->execute([$componentId]);
    $componentHtml = $stmt->fetchColumn();

    if ($componentHtml === false) {
        http_response_code(404);
        echo "Component not found.";
        exit;
    }

    // Output the raw HTML of the component
    // The client-side JS will handle placing this into the editor
    echo $componentHtml;

} catch (PDOException $e) {
    http_response_code(500);
    error_log("API Error (get.php): " . $e->getMessage());
    echo "Error fetching component data.";
} catch (Exception $e) {
    http_response_code(500);
    error_log("API Error (get.php): " . $e->getMessage());
    echo "An unexpected error occurred.";
}
