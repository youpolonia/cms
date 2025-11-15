<?php
class EXIFHandler {
    public static function extract(string $imagePath): array {
        if (!function_exists('exif_read_data')) {
            throw new Exception('EXIF extension not available');
        }

        $exif = @exif_read_data($imagePath);
        return $exif ?: [];
    }

    public static function formatForDisplay(array $exifData): array {
        $formatted = [];
        
        // Basic EXIF data
        if (!empty($exifData['DateTime'])) {
            $formatted['basic']['Date Taken'] = $exifData['DateTime'];
        }
        if (!empty($exifData['Make'])) {
            $formatted['basic']['Camera Make'] = $exifData['Make'];
        }
        if (!empty($exifData['Model'])) {
            $formatted['basic']['Camera Model'] = $exifData['Model'];
        }

        // Technical data
        if (!empty($exifData['ExposureTime'])) {
            $formatted['technical']['Exposure'] = $exifData['ExposureTime'];
        }
        if (!empty($exifData['FNumber'])) {
            $formatted['technical']['Aperture'] = 'f/' . $exifData['FNumber'];
        }
        if (!empty($exifData['ISOSpeedRatings'])) {
            $formatted['technical']['ISO'] = $exifData['ISOSpeedRatings'];
        }

        // Location data
        if (!empty($exifData['GPSLatitude']) && !empty($exifData['GPSLongitude'])) {
            $formatted['location']['Coordinates'] = 
                self::convertGpsToDecimal($exifData['GPSLatitude'], $exifData['GPSLatitudeRef']) . ', ' .
                self::convertGpsToDecimal($exifData['GPSLongitude'], $exifData['GPSLongitudeRef']);
        }

        return $formatted;
    }

    private static function convertGpsToDecimal(array $gps, string $hemisphere): float {
        $degrees = count($gps) > 0 ? self::gpsToNum($gps[0]) : 0;
        $minutes = count($gps) > 1 ? self::gpsToNum($gps[1]) : 0;
        $seconds = count($gps) > 2 ? self::gpsToNum($gps[2]) : 0;
        
        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);
        return ($hemisphere === 'S' || $hemisphere === 'W') ? -$decimal : $decimal;
    }

    private static function gpsToNum(string $coordPart): float {
        $parts = explode('/', $coordPart);
        return count($parts) <= 0 ? 0 : (float)$parts[0] / (float)($parts[1] ?? 1);
    }
}
