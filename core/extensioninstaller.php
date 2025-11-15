<?php
/**
 * ExtensionInstaller - Handles CMS extension installation
 */
class ExtensionInstaller {
    private const TEMP_DIR = __DIR__ . '/../temp/extensions/';
    private const MAX_FILE_SIZE = 10485760; // 10MB
    private array $report = [];
    private array $errors = [];

    /**
     * Handles file upload and temporary storage
     * @param array $file $_FILES array entry
     * @return string|false Path to stored zip or false on failure
     */
    public function uploadZip(array $file): string|false {
        // Security checks
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $this->errors[] = 'Invalid file upload';
            return false;
        }

        // Validate file type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if ($mime !== 'application/zip') {
            $this->errors[] = 'Only ZIP files are allowed';
            return false;
        }

        // Check file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $this->errors[] = 'File exceeds maximum size of ' . (self::MAX_FILE_SIZE / 1024 / 1024) . 'MB';
            return false;
        }

        // Create temp directory if needed
        if (!is_dir(self::TEMP_DIR) && !mkdir(self::TEMP_DIR, 0755, true)) {
            $this->errors[] = 'Could not create temp directory';
            return false;
        }

        // Generate unique filename
        $targetPath = self::TEMP_DIR . uniqid('ext_') . '.zip';

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->errors[] = 'Failed to store uploaded file';
            return false;
        }

        $this->report[] = "File uploaded to: $targetPath";
        return $targetPath;
    }

    /**
     * Validates ZIP contents and structure
     * @param string $zipPath Path to zip file
     * @return bool True if valid
     */
    public function validate(string $zipPath): bool {
        if (!file_exists($zipPath)) {
            $this->errors[] = 'ZIP file not found';
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $this->errors[] = 'Could not open ZIP file';
            return false;
        }

        // Check for required files
        $requiredFiles = ['extension.json', 'install.php'];
        foreach ($requiredFiles as $file) {
            if ($zip->locateName($file) === false) {
                $this->errors[] = "Missing required file: $file";
                $zip->close();
                return false;
            }
        }

        $zip->close();
        $this->report[] = "ZIP validation passed";
        return true;
    }

    /**
     * Extracts and installs valid extensions
     * @param string $zipPath Path to validated zip file
     * @return bool True if installation succeeded
     */
    public function install(string $zipPath): bool {
        if (!$this->validate($zipPath)) {
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $this->errors[] = 'Could not open ZIP file for installation';
            return false;
        }

        $extractPath = dirname($zipPath) . '/' . pathinfo($zipPath, PATHINFO_FILENAME);
        if (!mkdir($extractPath, 0755)) {
            $this->errors[] = 'Could not create extraction directory';
            $zip->close();
            return false;
        }

        if (!$zip->extractTo($extractPath)) {
            $this->errors[] = 'Failed to extract ZIP contents';
            $zip->close();
            return false;
        }

        $zip->close();
        $this->report[] = "Files extracted to: $extractPath";

        // Run extension's install script
        $installScript = $extractPath . '/install.php';
        if (file_exists($installScript)) {
            try {
                require_once $installScript;
                $this->report[] = "Install script executed";
            } catch (Exception $e) {
                $this->errors[] = "Install script failed: " . $e->getMessage();
                return false;
            }
        }

        return true;
    }

    /**
     * Generates installation report
     * @return array Report data
     */
    public function report(): array {
        return [
            'success' => empty($this->errors),
            'report' => $this->report,
            'errors' => $this->errors
        ];
    }
}
