<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class FileModificationService
{
    /**
     * Apply diff resolutions to a file
     *
     * @param string $filePath
     * @param array $diff
     * @param array $resolutions
     * @return bool
     */
    public function applyResolutions(string $filePath, array $diff, array $resolutions): bool
    {
        try {
            if (!File::exists($filePath)) {
                throw new \Exception("File does not exist: {$filePath}");
            }

            $originalLines = explode("\n", File::get($filePath));
            $newLines = [];

            foreach ($originalLines as $i => $line) {
                $lineNumber = $i + 1;
                
                if (isset($resolutions[$lineNumber])) {
                    $resolution = $resolutions[$lineNumber];
                    
                    // For rejections, keep the original line
                    if ($resolution === 'reject') {
                        $newLines[] = $line;
                    }
                    // Accept actions are handled by the diff processing below
                } else {
                    $newLines[] = $line;
                }
            }

            // Process the diff to apply accepted changes
            foreach ($diff['diff'] as $diffLine) {
                if (($diffLine['applied'] ?? false) && 
                    ($resolutions[$diffLine['line_number']] ?? null) === 'accept') {
                    if ($diffLine['type'] === 'added') {
                        // Insert new line at the specified position
                        array_splice($newLines, $diffLine['line_number'] - 1, 0, $diffLine['line']);
                    } elseif ($diffLine['type'] === 'removed') {
                        // Remove the line
                        unset($newLines[$diffLine['line_number'] - 1]);
                    }
                }
            }

            // Re-index array after potential unset operations
            $newLines = array_values($newLines);

            // Write the modified content back to the file
            File::put($filePath, implode("\n", $newLines));

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to apply file modifications: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a backup of the file before modification
     *
     * @param string $filePath
     * @return string|null Path to backup file or null if failed
     */
    public function createBackup(string $filePath): ?string
    {
        try {
            $backupPath = $filePath . '.bak_' . date('YmdHis');
            File::copy($filePath, $backupPath);
            return $backupPath;
        } catch (\Exception $e) {
            Log::error("Failed to create backup: " . $e->getMessage());
            return null;
        }
    }
}
