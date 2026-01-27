<?php
define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';

require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/security/ensure_admin.php';
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/core/models/mediamodel.php';

header('Content-Type: application/json; charset=UTF-8');

function sendError(string $message): void {
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
}

function sendSuccess(array $fileData): void {
    echo json_encode(['ok' => true, 'file' => $fileData]);
    exit;
}

// Accept POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    sendError('Method not allowed');
}

// Validate CSRF
csrf_validate_or_403();

// Check file upload
if (!isset($_FILES['file'])) {
    sendError('No file uploaded');
}

$file = $_FILES['file'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
    ];
    $errorMsg = $errorMessages[$file['error']] ?? 'Unknown upload error';
    sendError($errorMsg);
}

// Check file size (5MB max)
$maxSize = 5 * 1024 * 1024; // 5MB in bytes
if ($file['size'] === 0) {
    sendError('Empty file not allowed');
}
if ($file['size'] > $maxSize) {
    sendError('File size exceeds 5MB limit');
}

// Validate MIME type using finfo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimeTypes = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif',
    'video/mp4',
    'application/pdf'
];

if (!in_array($mimeType, $allowedMimeTypes, true)) {
    sendError('File type not allowed: ' . $mimeType);
}

// Ensure upload directory exists
$uploadDir = CMS_ROOT . '/uploads/media/';
if (!is_dir($uploadDir)) {
    sendError('Upload directory does not exist: ' . $uploadDir);
}

// Generate random filename
$originalName = $file['name'];
$extension = pathinfo($originalName, PATHINFO_EXTENSION);
$sanitizedExt = preg_replace('/[^a-z0-9]/i', '', $extension);
if ($sanitizedExt === '') {
    $sanitizedExt = 'bin';
}
$randomFilename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $sanitizedExt;
$targetPath = $uploadDir . $randomFilename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    sendError('Failed to move uploaded file');
}

// Generate thumbnail for images
$thumbName = null;
$isImage = strpos($mimeType, 'image/') === 0;

if ($isImage) {
    // Ensure thumbnail directory exists
    $thumbDir = CMS_ROOT . '/uploads/media/thumbs/';
    if (!is_dir($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }

    // Load source image using GD
    $sourceImage = null;
    if ($mimeType === 'image/jpeg') {
        $sourceImage = @imagecreatefromjpeg($targetPath);
    } elseif ($mimeType === 'image/png') {
        $sourceImage = @imagecreatefrompng($targetPath);
    } elseif ($mimeType === 'image/webp') {
        $sourceImage = @imagecreatefromwebp($targetPath);
    } elseif ($mimeType === 'image/gif') {
        $sourceImage = @imagecreatefromgif($targetPath);
    }

    // Generate thumbnail if image loaded successfully
    if ($sourceImage !== false && $sourceImage !== null) {
        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Calculate thumbnail dimensions (max width 300px, preserve aspect ratio)
        $maxWidth = 300;
        if ($origWidth > $maxWidth) {
            $thumbWidth = $maxWidth;
            $thumbHeight = (int)round(($origHeight / $origWidth) * $maxWidth);
        } else {
            // Image already smaller than max width
            $thumbWidth = $origWidth;
            $thumbHeight = $origHeight;
        }

        // Create thumbnail canvas
        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);

        // Preserve transparency for PNG
        if ($mimeType === 'image/png') {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
        }

        // Resize image
        imagecopyresampled(
            $thumbImage, $sourceImage,
            0, 0, 0, 0,
            $thumbWidth, $thumbHeight,
            $origWidth, $origHeight
        );

        // Generate thumbnail filename
        $baseFilename = pathinfo($randomFilename, PATHINFO_FILENAME);
        $thumbName = $baseFilename . '_thumb.jpg';
        $thumbPath = $thumbDir . $thumbName;

        // Save as JPEG with quality 82
        imagejpeg($thumbImage, $thumbPath, 82);

        // Free memory
        imagedestroy($thumbImage);
        imagedestroy($sourceImage);
    }
}

// Save to database
$mediaModel = new MediaModel(db());
try {
    $mediaId = $mediaModel->create([
        'filename' => $randomFilename,
        'original_name' => $originalName,
        'mime_type' => $mimeType,
        'size' => $file['size'],
        'path' => 'uploads/media/' . $randomFilename,
        'folder' => 'media'
    ]);
} catch (Exception $e) {
    // Clean up uploaded file on database error
    @unlink($targetPath);
    if ($thumbName !== null) {
        @unlink($thumbDir . $thumbName);
    }
    sendError('Database error: ' . $e->getMessage());
}

// Return success with file data
sendSuccess([
    'id' => $mediaId,
    'name' => $randomFilename,
    'original' => $originalName,
    'mime' => $mimeType,
    'size' => $file['size'],
    'thumb' => $thumbName,
    'url' => '/uploads/media/' . $randomFilename
]);
