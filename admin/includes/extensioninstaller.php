<?php
require_once __DIR__ . '/../../core/logger/LoggerFactory.php';
require_once __DIR__ . '/../core/csrf.php';

csrf_boot('admin');

class ExtensionInstaller {
    private $extensionsDir;
    private $allowedExtensions = ['php', 'js', 'css', 'html', 'json', 'md'];
    private $requiredFiles = ['bootstrap.php', 'extension.json'];
    private $logger;

    public function __construct() {
        $this->extensionsDir = __DIR__ . '/../../extensions/';
        $this->logger = LoggerFactory::create('file', [
            'file_path' => __DIR__ . '/../../logs/extension_install.log'
        ]);
    }

    public function install($uploadedFile) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }
        // Validate file upload
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed with error code: ' . $uploadedFile['error']);
        }

        // Verify file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $uploadedFile['tmp_name']);
        finfo_close($finfo);
        
        if ($mime !== 'application/zip') {
            throw new Exception('Invalid file type - must be ZIP archive');
        }

        $extensionName = pathinfo($uploadedFile['name'], PATHINFO_FILENAME);
        $targetDir = $this->extensionsDir . $extensionName;

        // Validate ZIP contents
        $zip = new ZipArchive;
        if ($zip->open($uploadedFile['tmp_name']) !== TRUE) {
            throw new Exception('Cannot open extension file');
        }

        $hasRequiredFiles = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            
            // Check for path traversal
            if (strpos($filename, '../') !== false || strpos($filename, '..\\') !== false) {
                $zip->close();
                throw new Exception('Invalid path in ZIP file');
            }
            
            // Check file extensions
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if (!empty($extension) && !in_array(strtolower($extension), $this->allowedExtensions)) {
                $zip->close();
                throw new Exception('Disallowed file type: .' . $extension);
            }
            
            // Check for required files
            if (in_array(basename($filename), $this->requiredFiles)) {
                $hasRequiredFiles = true;
            }
        }
        
        if (!$hasRequiredFiles) {
            $zip->close();
            throw new Exception('Extension missing required files');
        }

        // Create target directory
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Extract files
        $zip->extractTo($targetDir);
        $zip->close();

        // Log installation
        $this->logger->log('Extension installed', [
            'name' => $extensionName,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        return true;
    }
}

class FileLogger {
    private $logFile;

    public function __construct($filePath) {
        $this->logFile = $filePath;
    }

    public function log($message, $context = []) {
        $entry = date('[Y-m-d H:i:s]') . ' ' . $message . PHP_EOL;
        if (!empty($context)) {
            $entry .= 'Context: ' . json_encode($context) . PHP_EOL;
        }
        file_put_contents($this->logFile, $entry, FILE_APPEND);
    }
}
