<?php
declare(strict_types=1);

class AssetOptimizer {
    public static function optimizeImage(string $path): string {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        if ($ext === 'png' || $ext === 'jpg' || $ext === 'jpeg') {
            return self::convertToWebP($path);
        }
        
        return $path;
    }

    private static function convertToWebP(string $path): string {
        $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $path);
        
        // In production this would use actual image conversion
        // For now we just simulate the conversion
        if (!file_exists($webpPath)) {
            touch($webpPath);
        }
        
        return $webpPath;
    }

    public static function getCDNUrl(string $path): string {
        $cdnDomain = self::determineOptimalCDN();
        return "https://$cdnDomain/" . ltrim($path, '/');
    }

    private static function determineOptimalCDN(): string {
        // Simple geographic simulation
        $region = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'US';
        
        return match($region) {
            'EU' => 'cdn-eu.example.com',
            'AS' => 'cdn-asia.example.com',
            default => 'cdn-us.example.com'
        };
    }
}
