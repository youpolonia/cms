<?php

namespace Includes\Multisite;

use Includes\ContentManager;
use Includes\ContentVersioning;
use Includes\Database\DatabaseConnection;
use Includes\ErrorHandler;

/**
 * ContentDistributor - Handles content distribution across multiple sites
 */
class ContentDistributor
{
    /**
     * @var SiteManager
     */
    private SiteManager $siteManager;
    
    /**
     * @var ContentManager
     */
    private ContentManager $contentManager;
    
    /**
     * @var ContentVersioning
     */
    private ContentVersioning $versionManager;
    
    /**
     * @var DatabaseConnection
     */
    private DatabaseConnection $db;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->siteManager = new SiteManager();
        $this->contentManager = new ContentManager();
        $this->versionManager = new ContentVersioning();
        $this->db = \core\Database::connection();
    }
    
    /**
     * Distribute content to target sites
     *
     * @param int $contentId
     * @param array $targetSites
     * @return array Status of distribution to each site
     */
    public function distributeContent(int $contentId, array $targetSites): array
    {
        $results = [];
        $sourceSite = $this->siteManager->getCurrentSite();
        
        // Get content data
        $content = $this->contentManager->getContent($contentId);
        if (!$content) {
            return ['error' => 'Content not found'];
        }
        
        // Get content metadata
        $metadata = $this->versionManager->getVersionMetadata($content['version_id']);
        
        // Add distribution metadata
        $metadata['distributed_from'] = $sourceSite;
        $metadata['distribution_date'] = date('Y-m-d H:i:s');
        
        foreach ($targetSites as $siteId) {
            // Skip if target is the same as source
            if ($siteId === $sourceSite) {
                $results[$siteId] = ['status' => 'skipped', 'reason' => 'Source and target are the same'];
                continue;
            }
            
            // Check if site exists
            if (!$this->siteManager->siteExists($siteId)) {
                $results[$siteId] = ['status' => 'error', 'reason' => 'Site does not exist'];
                continue;
            }
            
            try {
                // Determine distribution method
                if ($this->isLocalSite($siteId)) {
                    $results[$siteId] = $this->distributeToLocalSite($content, $metadata, $siteId);
                } else {
                    $results[$siteId] = $this->distributeToRemoteSite($content, $metadata, $siteId);
                }
            } catch (\Exception $e) {
                ErrorHandler::logError("Content distribution error: " . $e->getMessage());
                $results[$siteId] = ['status' => 'error', 'reason' => $e->getMessage()];
            }
        }
        
        return $results;
    }
    
    /**
     * Check if a site is local (same database)
     *
     * @param string $siteId
     * @return bool
     */
    private function isLocalSite(string $siteId): bool
    {
        // In this implementation, all sites are considered local
        // In a more complex setup, this would check if the site is on the same server
        return true;
    }
    
    /**
     * Distribute content to a local site
     *
     * @param array $content
     * @param array $metadata
     * @param string $targetSite
     * @return array
     */
    private function distributeToLocalSite(array $content, array $metadata, string $targetSite): array
    {
        // Save original site
        $originalSite = $this->siteManager->getCurrentSite();
        
        try {
            // Switch to target site context
            $this->siteManager->setCurrentSite($targetSite);
            
            // Check if content already exists in target site
            $existingContent = $this->contentManager->getContentBySlug($content['slug']);
            
            if ($existingContent) {
                // Update existing content
                $newVersionId = $this->versionManager->createVersion(
                    $existingContent['id'],
                    $content['content'],
                    $metadata
                );
                
                $this->contentManager->updateContent(
                    $existingContent['id'],
                    [
                        'title' => $content['title'],
                        'slug' => $content['slug'],
                        'status' => $content['status'],
                        'version_id' => $newVersionId
                    ]
                );
                
                $result = [
                    'status' => 'updated',
                    'content_id' => $existingContent['id'],
                    'version_id' => $newVersionId
                ];
            } else {
                // Create new content
                $newContentId = $this->contentManager->createContent(
                    $content['title'],
                    $content['slug'],
                    $content['content'],
                    $content['status'],
                    $metadata
                );
                
                $result = [
                    'status' => 'created',
                    'content_id' => $newContentId
                ];
            }
            
            // Switch back to original site
            $this->siteManager->setCurrentSite($originalSite);
            
            return $result;
        } catch (\Exception $e) {
            // Switch back to original site in case of error
            $this->siteManager->setCurrentSite($originalSite);
            throw $e;
        }
    }
    
    /**
     * Distribute content to a remote site via API
     *
     * @param array $content
     * @param array $metadata
     * @param string $targetSite
     * @return array
     */
    private function distributeToRemoteSite(array $content, array $metadata, string $targetSite): array
    {
        // Get site configuration
        $siteConfig = $this->siteManager->getSiteConfig($targetSite);
        if (!$siteConfig || !isset($siteConfig['domain'])) {
            throw new \Exception("Invalid site configuration for $targetSite");
        }
        
        $domain = $siteConfig['domain'];
        $apiEndpoint = "https://$domain/api/content/distribute";
        
        // Prepare data for API request
        $data = [
            'content' => $content,
            'metadata' => $metadata,
            'source_site' => $this->siteManager->getCurrentSite(),
            'api_key' => $this->getApiKey($targetSite)
        ];
        
        // In a real implementation, this would make an HTTP request to the remote site
        // For now, we'll simulate a successful response
        
        return [
            'status' => 'api_request_simulated',
            'target_site' => $targetSite,
            'endpoint' => $apiEndpoint
        ];
    }
    
    /**
     * Get API key for a target site
     *
     * @param string $targetSite
     * @return string
     */
    private function getApiKey(string $targetSite): string
    {
        // In a real implementation, this would retrieve the API key from a secure storage
        return md5($targetSite . '_api_key');
    }
    
    /**
     * Schedule content distribution
     *
     * @param int $contentId
     * @param array $targetSites
     * @param string $scheduleDate
     * @return bool
     */
    public function scheduleDistribution(int $contentId, array $targetSites, string $scheduleDate): bool
    {
        // Validate content exists
        $content = $this->contentManager->getContent($contentId);
        if (!$content) {
            return false;
        }
        
        // Validate target sites
        foreach ($targetSites as $siteId) {
            if (!$this->siteManager->siteExists($siteId)) {
                return false;
            }
        }
        
        // Store schedule in database
        $this->db->insert('content_distribution_schedule', [
            'content_id' => $contentId,
            'source_site' => $this->siteManager->getCurrentSite(),
            'target_sites' => json_encode($targetSites),
            'schedule_date' => $scheduleDate,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return true;
    }
    
    /**
     * Process scheduled distributions
     *
     * @return array
     */
    public function processScheduledDistributions(): array
    {
        $results = [];
        
        // Get pending distributions that are due
        $schedules = $this->db->query(
            "SELECT * FROM content_distribution_schedule 
             WHERE status = 'pending' AND schedule_date <= ?",
            [date('Y-m-d H:i:s')]
        )->fetchAll();
        
        foreach ($schedules as $schedule) {
            $contentId = $schedule['content_id'];
            $sourceSite = $schedule['source_site'];
            $targetSites = json_decode($schedule['target_sites'], true);
            
            // Switch to source site context
            $originalSite = $this->siteManager->getCurrentSite();
            $this->siteManager->setCurrentSite($sourceSite);
            
            try {
                // Distribute content
                $distributionResult = $this->distributeContent($contentId, $targetSites);
                
                // Update schedule status
                $this->db->update(
                    'content_distribution_schedule',
                    ['status' => 'completed', 'processed_at' => date('Y-m-d H:i:s')],
                    ['id' => $schedule['id']]
                );
                
                $results[] = [
                    'schedule_id' => $schedule['id'],
                    'status' => 'completed',
                    'results' => $distributionResult
                ];
            } catch (\Exception $e) {
                // Update schedule status
                $this->db->update(
                    'content_distribution_schedule',
                    [
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                        'processed_at' => date('Y-m-d H:i:s')
                    ],
                    ['id' => $schedule['id']]
                );
                
                $results[] = [
                    'schedule_id' => $schedule['id'],
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            } finally {
                // Switch back to original site
                $this->siteManager->setCurrentSite($originalSite);
            }
        }
        
        return $results;
    }
}
