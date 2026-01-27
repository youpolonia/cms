<?php
/**
 * MediaSanitizer - Validates and sanitizes media files
 */
class MediaSanitizer {
    private $allowedMimeTypes = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'application/pdf' => ['pdf'],
        'video/mp4' => ['mp4'],
        'audio/mpeg' => ['mp3']
    ];

    /**
     * Validate file meets security requirements
     */
    public function validateFile(string $filePath, string $originalName): void {
        $this->validateExtension($originalName);
        $this->validateMimeType($filePath);
        $this->scanForMaliciousContent($filePath);
    }

    /**
     * Verify file extension matches allowed types
     */
    private function validateExtension(string $filename): void {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $validExtensions = array_merge(...array_values($this->allowedMimeTypes));

        if (!in_array($ext, $validExtensions)) {
            throw new RuntimeException("Invalid file extension: $ext");
        }
    }

    /**
     * Verify actual file content matches declared MIME type
     */
    private function validateMimeType(string $filePath): void {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        if (!array_key_exists($detectedType, $this->allowedMimeTypes)) {
            throw new RuntimeException("Invalid MIME type: $detectedType");
        }
    }

    /**
     * Basic content scanning (placeholder for actual virus scanning)
     */
    private function scanForMaliciousContent(string $filePath): void {
        // This is a placeholder - in production you would integrate with:
        // 1. Virus scanning API
        // 2. File content analysis
        // 3. Malware detection service

        $content = file_get_contents($filePath, false, null, 0, 1000);
        if (preg_match('/<\?php/i', $content)) {
            throw new RuntimeException('Potential PHP code detected');
        }

        if (preg_match('/
<script/i',
 $content)) {
            throw new RuntimeException('Potential script injection detected');
        }
    }

    /**
     * Get allowed MIME types for upload
     */
    public
 function getAllowedTypes(): array {
        return array_keys($this->allowedMimeTypes);
    }
}
