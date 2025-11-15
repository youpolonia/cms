<?php
header('Content-Type: application/json');

// Read request body
$request = json_decode(file_get_contents('php://input'), true);

// Validate required parameters
if (empty($request['prompt'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Prompt is required']);
    exit;
}

// Extract prompt
$prompt = $request['prompt'];
$context = $request['context'] ?? '';
$style = $request['style'] ?? 'professional';
$length = $request['length'] ?? 'medium';

// Prepare data for MCP tool
$data = [
    'prompt' => $prompt,
    'context' => $context,
    'style' => $style,
    'length' => $length,
    'suggestions' => 3 // Number of content suggestions to generate
];

// Make cURL request to MCP server
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/generate-content');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $responseData = json_decode($response, true);
    if (isset($responseData['content']) && isset($responseData['suggestions'])) {
        echo json_encode([
            'content' => $responseData['content'],
            'suggestions' => $responseData['suggestions']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid response from MCP server']);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to generate content']);
}
