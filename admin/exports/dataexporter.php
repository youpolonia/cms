<?php
/**
 * Data Export Handler for CMS
 *
 * Provides secure data export functionality in multiple formats (JSON, XML)
 * with batch processing support for large datasets and AI enhancements.
 */

class DataExporter {
    const EXPORT_JSON = 'json';
    const EXPORT_XML = 'xml';
    
    private $auth;
    private $db;
    
    public function __construct($auth, $db) {
        $this->auth = $auth;
        $this->db = $db;
    }
    
    /**
     * Export data in specified format with batch processing
     */
    public function export($data, $format, $batchSize = 1000, $enhanceOptions = null) {
        if (!$this->auth->hasPermission('export_data')) {
            throw new Exception('Export permission required');
        }
        
        if ($enhanceOptions) {
            if (!$this->auth->hasPermission('export_enhance')) {
                throw new Exception('AI enhancement permission required');
            }
            $data = $this->enhanceData($data, $enhanceOptions);
        }
        
        switch ($format) {
            case self::EXPORT_JSON:
                return $this->exportJson($data, $batchSize);
            case self::EXPORT_XML:
                return $this->exportXml($data, $batchSize);
            default:
                throw new Exception('Unsupported export format');
        }
    }
    
    /**
     * Enhance data using AI services
     */
    private function enhanceData($data, $options) {
        try {
            require_once __DIR__.'/../../services/aiexportenhancer.php';
            return AIExportEnhancer::enhance($data, $options['strategy'] ?? 'summarize');
        } catch (Exception $e) {
            throw new Exception('AI enhancement failed: '.$e->getMessage());
        }
    }
    
    /**
     * Export data as JSON with batch processing
     */
    private function exportJson($data, $batchSize) {
        $result = [];
        $batches = array_chunk($data, $batchSize);
        
        foreach ($batches as $batch) {
            $result = array_merge($result, $batch);
        }
        
        return json_encode($result, JSON_PRETTY_PRINT);
    }
    
    /**
     * Export data as XML with batch processing
     */
    private function exportXml($data, $batchSize) {
        $xml = new SimpleXMLElement('<data/>');
        $batches = array_chunk($data, $batchSize);
        
        foreach ($batches as $batch) {
            foreach ($batch as $item) {
                $entry = $xml->addChild('entry');
                foreach ($item as $key => $value) {
                    $entry->addChild($key, htmlspecialchars($value));
                }
            }
        }
        
        return $xml->asXML();
    }
    
    /**
     * Generate secure download endpoint
     */
    public function getDownloadUrl($data, $format, $enhanceOptions = null) {
        $token = bin2hex(random_bytes(32));
        $_SESSION['export_token_'.$token] = [
            'data' => $data,
            'format' => $format,
            'enhance' => $enhanceOptions,
            'expires' => time() + 3600 // 1 hour expiration
        ];
        
        return '/admin/exports/download.php?token=' . urlencode($token);
    }
    
    /**
     * Direct AI-enhanced export (convenience method)
     */
    public function exportEnhanced($data, $format, $strategy = 'summarize', $batchSize = 1000) {
        return $this->export($data, $format, $batchSize, ['strategy' => $strategy]);
    }
}
