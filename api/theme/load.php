<?php
header('Content-Type: application/json');

// Define the path to theme presets
$presetsDir = __DIR__ . '/../../../themes/presets/';
$presets = [];

// Scan the presets directory
$presetFiles = glob($presetsDir . '*.json');

// Load each preset file
foreach ($presetFiles as $file) {
    $presetName = basename($file, '.json');
    $presetData = json_decode(file_get_contents($file), true);
    
    if ($presetData) {
        $presets[$presetName] = $presetData;
    }
}

// Return the combined presets
echo json_encode($presets);
