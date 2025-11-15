<?php

namespace AI\SEO;

require_once CMS_ROOT . '/includes/api/Response.php';
require_once CMS_ROOT . '/includes/database/DatabaseConnection.php';
require_once CMS_ROOT . '/includes/ai/AIManager.php';
require_once CMS_ROOT . '/includes/ai/seo/SEOAnalyzer.php';
require_once CMS_ROOT . '/includes/ai/seo/SEORecommendationEngine.php';
require_once CMS_ROOT . '/includes/ai/seo/SEOMetaGenerator.php';
use API\Response;
use Database\DatabaseConnection;
use AI\AIManager;

class SEOApiHandler {
    private SEOAnalyzer $analyzer;
    private SEORecommendationEngine $recommendationEngine;
    private SEOMetaGenerator $metaGenerator;
    private DatabaseConnection $db;

    public function __construct(
        AIManager $aiManager,
        DatabaseConnection $db
    ) {
        $this->analyzer = new SEOAnalyzer($aiManager);
        $this->recommendationEngine = new SEORecommendationEngine($aiManager, $db);
        $this->metaGenerator = new SEOMetaGenerator($aiManager);
        $this->db = $db;
    }

    /**
     * Handle SEO analysis request
     */
    public function analyze(array $data): Response {
        if (empty($data['content'])) {
            return new Response(400, ['error' => 'Content is required']);
        }

        $analysis = $this->analyzer->analyzeContent($data['content']);
        return new Response(200, $analysis);
    }

    /**
     * Get SEO recommendations for a version
     */
    public function getRecommendations(int $versionId): Response {
        $stmt = $this->db->prepare(
            "SELECT recommendations FROM seo_analysis 
             WHERE version_id = ? ORDER BY analysis_date DESC LIMIT 1"
        );
        $stmt->execute([$versionId]);
        
        $result = $stmt->fetch();
        if (!$result) {
            return new Response(404, ['error' => 'No analysis found']);
        }

        return new Response(200, json_decode($result['recommendations'], true));
    }

    /**
     * Apply SEO recommendations
     */
    public function applyRecommendations(int $versionId, array $selected): Response {
        // Get the latest analysis
        $stmt = $this->db->prepare(
            "SELECT * FROM seo_analysis 
             WHERE version_id = ? ORDER BY analysis_date DESC LIMIT 1"
        );
        $stmt->execute([$versionId]);
        $analysis = $stmt->fetch();

        if (!$analysis) {
            return new Response(404, ['error' => 'No analysis found']);
        }

        // Update version_metadata with applied recommendations
        $updateStmt = $this->db->prepare(
            "UPDATE version_metadata SET
             meta_title = ?,
             meta_description = ?,
             keywords = ?,
             seo_score = ?,
             readability_score = ?
             WHERE version_id = ?"
        );

        $updateStmt->execute([
            $selected['meta_title'] ?? '',
            $selected['meta_description'] ?? '',
            json_encode($selected['keywords'] ?? []),
            $selected['seo_score'] ?? 0,
            $selected['readability_score'] ?? 0,
            $versionId
        ]);

        // Record applied improvements
        $this->recordImprovements($analysis['id'], $selected);

        return new Response(200, ['success' => true]);
    }

    private function recordImprovements(int $analysisId, array $improvements): bool {
        $stmt = $this->db->prepare(
            "UPDATE seo_analysis SET improvements_made = ? WHERE id = ?"
        );
        return $stmt->execute([json_encode($improvements), $analysisId]);
    }
}
