<?php
require_once __DIR__ . '/../../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['prompt'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Prompt is required']);
    exit;
}

// TODO: Implement actual AI generation logic
// This is a placeholder - replace with your AI service integration
$generatedLayout = '
<div class="ai-generated-layout">
    <h3>Generated Layout</h3>
    <p>This is a placeholder for the layout generated from prompt: ' . htmlspecialchars(
$input['prompt']) . '</p>
    <div class="columns">
        <div class="column">Column 1</div>
        <div class="column">Column 2</div>
    </div>
</div>';

echo json_encode([
    'success' => true,
    'layout' => $generatedLayout
]);
