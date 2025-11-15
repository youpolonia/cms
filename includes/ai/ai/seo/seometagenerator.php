<?php

namespace AI\SEO;

use AI\AIManager;

class SEOMetaGenerator {
    private AIManager $aiManager;
    
    public function __construct(AIManager $aiManager) {
        $this->aiManager = $aiManager;
    }

    /**
     * Generate optimized meta tags for content
     */
    public function generateMetaTags(string $content, array $existingMeta = []): array {
        $tags = [
            'title' => $this->generateTitle($content, $existingMeta['title'] ?? ''),
            'description' => $this->generateDescription($content, $existingMeta['description'] ?? ''),
            'og_tags' => $this->generateOpenGraphTags($content)
        ];

        return $tags;
    }

    private function generateTitle(string $content, string $existingTitle = ''): string {
        if (!empty($existingTitle)) {
            return $this->optimizeTitle($existingTitle);
        }
        
        return $this->aiManager->getProvider('openai')
            ->analyze('meta_title', $content);
    }

    private function generateDescription(string $content, string $existingDesc = ''): string {
        if (!empty($existingDesc)) {
            return $this->optimizeDescription($existingDesc);
        }
        
        return $this->aiManager->getProvider('openai')
            ->analyze('meta_description', $content);
    }

    private function generateOpenGraphTags(string $content): array {
        return $this->aiManager->getProvider('openai')
            ->analyze('og_tags', $content);
    }

    private function optimizeTitle(string $title): string {
        // Ensure title is between 30-60 characters
        $title = trim($title);
        if (strlen($title) > 60) {
            return substr($title, 0, 57) . '...';
        }
        return $title;
    }

    private function optimizeDescription(string $desc): string {
        // Ensure description is between 120-160 characters
        $desc = trim($desc);
        if (strlen($desc) > 160) {
            return substr($desc, 0, 157) . '...';
        }
        return $desc;
    }
}
