<?php
/**
 * Lightweight MP4/Video Metadata Extractor
 * Safe extraction without exec/shell commands
 * Parses MP4 atom/box structure to extract duration
 */

if (!function_exists('extractMP4Duration')) {
    /**
     * Extract duration from MP4 file by parsing atoms
     * @param string $filePath Full path to MP4 file
     * @return float|null Duration in seconds, or null on failure
     */
    function extractMP4Duration(string $filePath): ?float {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return null;
        }

        $fileSize = filesize($filePath);
        if ($fileSize === false || $fileSize < 8) {
            return null;
        }

        $handle = @fopen($filePath, 'rb');
        if ($handle === false) {
            return null;
        }

        try {
            // Look for 'mvhd' (movie header) atom which contains duration
            $duration = null;
            $offset = 0;

            while ($offset < $fileSize) {
                if (fseek($handle, $offset) !== 0) {
                    break;
                }

                $atomHeader = fread($handle, 8);
                if (strlen($atomHeader) < 8) {
                    break;
                }

                // Read atom size (big-endian 32-bit)
                $sizeData = unpack('N', substr($atomHeader, 0, 4));
                $atomSize = $sizeData[1];

                // Read atom type (4 bytes ASCII)
                $atomType = substr($atomHeader, 4, 4);

                // Handle extended size (size=1)
                if ($atomSize === 1) {
                    $extSize = fread($handle, 8);
                    if (strlen($extSize) === 8) {
                        $extData = unpack('J', $extSize);
                        $atomSize = $extData[1];
                    }
                }

                // Skip invalid atoms
                if ($atomSize < 8 || $atomSize > $fileSize) {
                    break;
                }

                // Found mvhd atom - extract duration
                if ($atomType === 'mvhd') {
                    $mvhdData = fread($handle, min(100, $atomSize - 8));
                    if (strlen($mvhdData) >= 20) {
                        // Version byte at offset 0
                        $version = ord($mvhdData[0]);

                        if ($version === 0) {
                            // Version 0: timescale at offset 12, duration at offset 16
                            $timescaleData = unpack('N', substr($mvhdData, 12, 4));
                            $durationData = unpack('N', substr($mvhdData, 16, 4));
                            $timescale = $timescaleData[1];
                            $durationUnits = $durationData[1];

                            if ($timescale > 0) {
                                $duration = $durationUnits / $timescale;
                            }
                        } elseif ($version === 1) {
                            // Version 1: timescale at offset 20, duration at offset 24 (64-bit)
                            $timescaleData = unpack('N', substr($mvhdData, 20, 4));
                            $durationData = unpack('J', substr($mvhdData, 24, 8));
                            $timescale = $timescaleData[1];
                            $durationUnits = $durationData[1];

                            if ($timescale > 0) {
                                $duration = $durationUnits / $timescale;
                            }
                        }
                    }
                    break;
                }

                // Move to next atom
                $offset += $atomSize;

                // Safety limit: don't scan more than first 10MB
                if ($offset > 10 * 1024 * 1024) {
                    break;
                }
            }

            fclose($handle);
            return $duration;

        } catch (Exception $e) {
            @fclose($handle);
            return null;
        }
    }
}

if (!function_exists('formatDuration')) {
    /**
     * Format duration in seconds to mm:ss
     * @param float $seconds Duration in seconds
     * @return string Formatted duration
     */
    function formatDuration(float $seconds): string {
        $minutes = floor($seconds / 60);
        $secs = floor($seconds % 60);
        return sprintf('%02d:%02d', $minutes, $secs);
    }
}
