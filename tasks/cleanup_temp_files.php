<?php
return [
    'interval_minutes' => 60, // Run hourly
    'active' => true,
    'callback' => function(string $tenantId) {
        $tempDir = "temp/$tenantId";
        $deletedFiles = 0;
        $deletedSize = 0;
        
        if (is_dir($tempDir)) {
            $files = glob("$tempDir/*");
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < time() - 3600) {
                    $deletedSize += filesize($file);
                    if (unlink($file)) {
                        $deletedFiles++;
                    }
                }
            }
        }

        return [
            'deleted_files' => $deletedFiles,
            'reclaimed_space' => $deletedSize,
            'status' => 'completed'
        ];
    }
];
