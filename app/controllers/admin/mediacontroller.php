<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class MediaController
{
    private string $uploadDir;
    private string $uploadUrl;
    private array $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'text/plain' => 'txt',
        'text/csv' => 'csv',
        'application/zip' => 'zip'
    ];
    private int $maxFileSize = 10485760; // 10MB

    public function __construct()
    {
        $this->uploadDir = \CMS_ROOT . '/uploads/media';
        $this->uploadUrl = '/uploads/media';
    }

    public function index(Request $request): void
    {
        $pdo = db();
        $type = $request->get('type', '');
        $search = trim($request->get('search', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 24;
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        if ($type === 'images') {
            $where[] = "mime_type LIKE 'image/%'";
        } elseif ($type === 'documents') {
            $where[] = "mime_type NOT LIKE 'image/%'";
        }

        if ($search) {
            $where[] = "(original_name LIKE ? OR title LIKE ? OR alt_text LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total
        $countSql = "SELECT COUNT(*) FROM media {$whereClause}";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        // Get files
        $sql = "SELECT * FROM media {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $files = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Check file existence and add URLs
        foreach ($files as &$file) {
            $file['exists'] = file_exists($this->uploadDir . '/' . $file['filename']);
            $file['url'] = $this->uploadUrl . '/' . $file['filename'];
            $file['is_image'] = strpos($file['mime_type'], 'image/') === 0;
        }

        // Get stats
        $stats = $pdo->query("SELECT
            COUNT(*) as total,
            SUM(CASE WHEN mime_type LIKE 'image/%' THEN 1 ELSE 0 END) as images,
            SUM(CASE WHEN mime_type NOT LIKE 'image/%' THEN 1 ELSE 0 END) as documents,
            SUM(size) as total_size
        FROM media")->fetch(\PDO::FETCH_ASSOC);

        render('admin/media/index', [
            'files' => $files,
            'stats' => $stats,
            'currentType' => $type,
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function upload(Request $request): void
    {
        render('admin/media/upload', [
            'maxFileSize' => $this->maxFileSize,
            'allowedTypes' => array_keys($this->allowedTypes),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function store(Request $request): void
    {
        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server limit',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
                UPLOAD_ERR_PARTIAL => 'File partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temp directory',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
            ];
            $error = $errorMessages[$_FILES['file']['error'] ?? 0] ?? 'Upload failed';
            Session::flash('error', $error);
            Response::redirect('/admin/media/upload');
        }

        $file = $_FILES['file'];

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($this->allowedTypes[$mimeType])) {
            Session::flash('error', 'File type not allowed: ' . $mimeType);
            Response::redirect('/admin/media/upload');
        }

        // Validate size
        if ($file['size'] > $this->maxFileSize) {
            Session::flash('error', 'File too large. Maximum: ' . $this->formatBytes($this->maxFileSize));
            Response::redirect('/admin/media/upload');
        }

        // Generate unique filename
        $ext = $this->allowedTypes[$mimeType];
        $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $filepath = $this->uploadDir . '/' . $filename;

        // Ensure directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        // Move file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            Session::flash('error', 'Failed to save file.');
            Response::redirect('/admin/media/upload');
        }

        // Save to database
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO media (filename, original_name, mime_type, size, path, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $filename,
            $file['name'],
            $mimeType,
            $file['size'],
            'uploads/media/' . $filename
        ]);

        Session::flash('success', 'File uploaded successfully.');
        Response::redirect('/admin/media');
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $file = $this->findFile($id);

        if (!$file) {
            Session::flash('error', 'File not found.');
            Response::redirect('/admin/media');
        }

        $file['url'] = $this->uploadUrl . '/' . $file['filename'];
        $file['is_image'] = strpos($file['mime_type'], 'image/') === 0;

        render('admin/media/edit', [
            'file' => $file,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $file = $this->findFile($id);

        if (!$file) {
            Session::flash('error', 'File not found.');
            Response::redirect('/admin/media');
        }

        $title = trim($request->post('title', ''));
        $altText = trim($request->post('alt_text', ''));
        $description = trim($request->post('description', ''));

        $pdo = db();
        $stmt = $pdo->prepare("UPDATE media SET title = ?, alt_text = ?, description = ? WHERE id = ?");
        $stmt->execute([$title ?: null, $altText ?: null, $description ?: null, $id]);

        Session::flash('success', 'File details updated.');
        Response::redirect('/admin/media');
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');
        $file = $this->findFile($id);

        if (!$file) {
            Session::flash('error', 'File not found.');
            Response::redirect('/admin/media');
        }

        // Delete physical file
        $filepath = $this->uploadDir . '/' . $file['filename'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        // Delete from database
        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM media WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'File deleted.');
        Response::redirect('/admin/media');
    }

    public function bulkDelete(Request $request): void
    {
        $ids = $request->post('ids', []);

        if (empty($ids) || !is_array($ids)) {
            Session::flash('error', 'No files selected.');
            Response::redirect('/admin/media');
        }

        $pdo = db();
        $deleted = 0;

        foreach ($ids as $id) {
            $file = $this->findFile((int)$id);
            if ($file) {
                $filepath = $this->uploadDir . '/' . $file['filename'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
                $stmt = $pdo->prepare("DELETE FROM media WHERE id = ?");
                $stmt->execute([(int)$id]);
                $deleted++;
            }
        }

        Session::flash('success', "Deleted {$deleted} file(s).");
        Response::redirect('/admin/media');
    }

    private function findFile(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM media WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $file ?: null;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
        return $bytes . ' bytes';
    }

    /**
     * Search stock photos from Pexels API
     */
    public function stockSearch(Request $request): void
    {
        header('Content-Type: application/json');
        
        $query = trim($_GET['q'] ?? '');
        if (empty($query)) {
            echo json_encode(['success' => false, 'error' => 'Please enter a search query']);
            return;
        }

        // Load Pexels API key from config
        $aiSettingsPath = \CMS_ROOT . '/config/ai_settings.json';
        $pexelsKey = '';
        if (file_exists($aiSettingsPath)) {
            $settings = json_decode(file_get_contents($aiSettingsPath), true);
            $pexelsKey = $settings['pexels_api_key'] ?? '';
        }

        if (empty($pexelsKey)) {
            echo json_encode([
                'success' => false, 
                'error' => 'Pexels API key not configured. Go to Admin → AI Settings to add your free API key from pexels.com'
            ]);
            return;
        }

        try {
            $url = 'https://api.pexels.com/v1/search?query=' . urlencode($query) . '&per_page=20';
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Authorization: ' . $pexelsKey],
                CURLOPT_TIMEOUT => 10
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                echo json_encode(['success' => false, 'error' => 'Pexels API error (HTTP ' . $httpCode . ')']);
                return;
            }

            $data = json_decode($response, true);
            $photos = [];
            foreach ($data['photos'] ?? [] as $photo) {
                $photos[] = [
                    'id' => $photo['id'],
                    'src' => $photo['src']['large'] ?? $photo['src']['original'],
                    'thumb' => $photo['src']['medium'] ?? $photo['src']['small'],
                    'alt' => $photo['alt'] ?? '',
                    'photographer' => $photo['photographer'] ?? ''
                ];
            }

            echo json_encode(['success' => true, 'photos' => $photos]);
        } catch (\Exception $e) {
            error_log('Pexels search error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Search failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate image using AI (DALL-E)
     */
    public function aiGenerate(Request $request): void
    {
        header('Content-Type: application/json');
        
        $data = get_json_input();
        $prompt = trim($data['prompt'] ?? '');
        $style = $data['style'] ?? 'photorealistic';
        $size = $data['size'] ?? '1024x1024';

        if (empty($prompt)) {
            echo json_encode(['success' => false, 'error' => 'Please enter a description']);
            return;
        }

        // Load OpenAI API key
        $aiSettingsPath = \CMS_ROOT . '/config/ai_settings.json';
        $openaiKey = '';
        if (file_exists($aiSettingsPath)) {
            $settings = json_decode(file_get_contents($aiSettingsPath), true);
            $openaiKey = $settings['openai_api_key'] ?? '';
        }

        if (empty($openaiKey)) {
            echo json_encode([
                'success' => false,
                'error' => 'OpenAI API key not configured. Go to Admin → AI Settings to add your API key.'
            ]);
            return;
        }

        // Enhance prompt with style
        $stylePrompts = [
            'photorealistic' => 'Photorealistic, high quality photograph, ',
            'digital-art' => 'Digital art style, vibrant colors, ',
            'illustration' => 'Professional illustration, clean lines, ',
            '3d-render' => '3D rendered, high quality render, '
        ];
        $fullPrompt = ($stylePrompts[$style] ?? '') . $prompt;

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://api.openai.com/v1/images/generations',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $openaiKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'dall-e-3',
                    'prompt' => $fullPrompt,
                    'n' => 1,
                    'size' => $size,
                    'quality' => 'standard'
                ]),
                CURLOPT_TIMEOUT => 60
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);

            if ($httpCode !== 200 || !isset($result['data'][0]['url'])) {
                $error = $result['error']['message'] ?? 'Image generation failed';
                echo json_encode(['success' => false, 'error' => $error]);
                return;
            }

            // Download and save the image locally
            $imageUrl = $result['data'][0]['url'];
            $imageData = file_get_contents($imageUrl);
            if ($imageData) {
                $filename = 'ai-' . date('YmdHis') . '-' . substr(md5($prompt), 0, 8) . '.png';
                $savePath = \CMS_ROOT . '/uploads/media/' . $filename;
                file_put_contents($savePath, $imageData);
                
                echo json_encode([
                    'success' => true,
                    'url' => '/uploads/media/' . $filename
                ]);
            } else {
                echo json_encode(['success' => true, 'url' => $imageUrl]);
            }
        } catch (\Exception $e) {
            error_log('AI image generation error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Generation failed: ' . $e->getMessage()]);
        }
    }
}
