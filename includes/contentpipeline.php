<?php
/**
 * Content Rendering Pipeline
 */
require_once __DIR__ . '/FileCache.php';

class ContentPipeline {
    protected $processors = [];
    protected $cache;
    
    public function __construct() {
        $this->cache = new FileCache();
    }

    public function addProcessor(callable $processor) {
        $this->processors[] = $processor;
    }

    public function process($content, $context = []) {
        $cacheKey = 'content_'.md5(serialize([$content, $context]));
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        foreach ($this->processors as $processor) {
            $content = $processor($content, $context);
        }

        $this->cache->set($cacheKey, $content);
        return $content;
    }

    public function clearCache() {
        $this->cache->clear('content_');
    }
}
