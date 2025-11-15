<?php
declare(strict_types=1);

class ScheduledContentExecutor {
    private const SCHEDULED_DIR = __DIR__ . '/../../../scheduled/';
    private const PROCESSED_DIR = __DIR__ . '/../../../scheduled/processed/';
    private const ERROR_DIR = __DIR__ . '/../../../scheduled/errors/';

    private ContentModel $contentModel;
    private Logger $logger;

    public function __construct() {
        require_once __DIR__ . '/../../models/contentmodel.php';
        require_once __DIR__ . '/../../core/logger.php';

        require_once __DIR__ . '/../core/logger/LoggerFactory.php';
        $this->contentModel = new ContentModel();
        $this->logger = LoggerFactory::create('file', [
            'file_path' => __DIR__ . '/../../logs/scheduled_content.log',
            'type' => 'file'
        ]);
        $this->ensureDirectoriesExist();
    }

    private function ensureDirectoriesExist(): void {
        foreach ([self::SCHEDULED_DIR, self::PROCESSED_DIR, self::ERROR_DIR] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    public function execute(): void {
        $files = glob(self::SCHEDULED_DIR . '*.json');
        $now = new DateTime('now', new DateTimeZone('UTC'));

        foreach ($files as $file) {
            try {
                $this->processFile($file, $now);
            } catch (Exception $e) {
                $this->logger->error("Failed to process scheduled content: " . $e->getMessage(), [
                    'file' => basename($file),
                    'error' => $e->getTraceAsString()
                ]);
                $this->moveFile($file, self::ERROR_DIR);
            }
        }
    }

    private function processFile(string $file, DateTime $now): void {
        $content = file_get_contents($file);
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (!isset($data['publish_at']) || !isset($data['type']) || !isset($data['content'])) {
            throw new InvalidArgumentException("Invalid JSON structure in scheduled content file");
        }

        $publishAt = new DateTime($data['publish_at'], new DateTimeZone('UTC'));

        if ($publishAt > $now) {
            return; // Not time to publish yet
        }

        $this->publishContent($data);
        $this->moveFile($file, self::PROCESSED_DIR);
        $this->logger->info("Published scheduled content", ['file' => basename($file)]);
    }

    private function publishContent(array $data): void {
        $contentData = [
            'title' => $data['title'] ?? 'Scheduled Content',
            'content' => $data['content'],
            'status' => 'published'
        ];

        if ($data['type'] === 'blog') {
            // Additional blog-specific processing if needed
            $contentId = $this->contentModel->create($contentData);
        } else {
            // Default to page type
            $contentId = $this->contentModel->create($contentData);
        }

        if (!$contentId) {
            throw new RuntimeException("Failed to create content");
        }
    }

    private function moveFile(string $source, string $destination): void {
        $filename = basename($source);
        $target = $destination . $filename;
        
        if (!rename($source, $target)) {
            throw new RuntimeException("Failed to move file to $destination");
        }
    }
}
