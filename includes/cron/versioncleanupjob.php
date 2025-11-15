<?php

declare(strict_types=1);

namespace Includes\Cron;

use Includes\Content\VersionCleaner;
use PDO;

class VersionCleanupJob
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        VersionCleaner::initialize($db);
    }

    public function run(): array
    {
        $contentIds = $this->getAllContentIds();
        $results = [];
        
        foreach ($contentIds as $contentId) {
            $deleted = VersionCleaner::cleanVersions($contentId);
            $results[$contentId] = $deleted;
        }

        return [
            'status' => 'completed',
            'results' => $results,
            'timestamp' => time()
        ];
    }

    private function getAllContentIds(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT content_id FROM content_versions");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
