<?php
/**
 * FileValidator - Validates file uploads
 */
class FileValidator {
    private $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'text/plain'
    ];
    
    private $maxSize = 5 * 1024 * 1024; // 5MB
    private $dangerousExtensions = ['exe', 'bat', 'sh', 'php', 'js'];

    /**
     * Validate a file upload
     */
    public function validate(array $file): array {
        $errors = [];

        // Check basic upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'errors' => ['Upload error: ' . $this->getUploadError($file['error'])]
            ];
        }

        // Check MIME type
        if (!in_array($file['type'], $this->allowedTypes)) {
            $errors[] = 'Invalid file type: ' . $file['type'];
        }

        // Check size
        if ($file['size'] > $this->maxSize) {
            $errors[] = 'File too large (max ' . ($this->maxSize / 1024 / 1024) . 'MB)';
        }

        // Check dangerous extensions
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($extension), $this->dangerousExtensions)) {
            $errors[] = 'Potentially dangerous file extension: ' . $extension;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get upload error message
     */
    private function getUploadError(int $errorCode): string {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];

        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    /**
     * Set allowed MIME types
     */
    public function setAllowedTypes(array $types): void {
        $this->allowedTypes = $types;
    }

    /**
     * Set maximum file size
     */
    public function setMaxSize(int $bytes): void {
        $this->maxSize = $bytes;
    }
}
