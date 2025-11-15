<?php
require_once __DIR__ . '/blogpost.php';

class BlogManager {
    private string $contentPath;

    public function __construct(string $contentPath = __DIR__ . '/../content/blog') {
        $this->contentPath = $contentPath;
        if (!file_exists($this->contentPath)) {
            mkdir($this->contentPath, 0755, true);
        }
    }

    public function savePost(BlogPost $post): bool {
        $filename = $this->contentPath . '/' . $post->slug . '.json';
        return file_put_contents($filename, json_encode($post->toArray(), JSON_PRETTY_PRINT)) !== false;
    }

    public function getPost(string $slug): ?BlogPost {
        $filename = $this->contentPath . '/' . $slug . '.json';
        if (!file_exists($filename)) {
            return null;
        }
        $data = json_decode(file_get_contents($filename), true);
        return $data ? BlogPost::fromArray($data) : null;
    }

    protected function saveVersion(string $slug): bool
    {
        $current = $this->getPost($slug);
        if (!$current) {
            return false;
        }

        $versionsDir = dirname($this->getPostFilename($slug)) . '/versions/';
        if (!is_dir($versionsDir)) {
            mkdir($versionsDir, 0755, true);
        }

        $versionFile = $versionsDir . $slug . '_' . time() . '.json';
        $data = json_encode([
            'title' => $current->title,
            'slug' => $current->slug,
            'body' => $current->body,
            'tags' => $current->tags,
            'date' => $current->date
        ], JSON_PRETTY_PRINT);

        return file_put_contents($versionFile, $data) !== false;
    }

    public function getVersions(string $slug): array
    {
        $versionsDir = dirname($this->getPostFilename($slug)) . '/versions/';
        if (!is_dir($versionsDir)) {
            return [];
        }

        $files = glob($versionsDir . $slug . '_*.json');
        $versions = [];
        foreach ($files as $file) {
            $timestamp = (int)substr(basename($file), strlen($slug) + 1, -5);
            $versions[$timestamp] = $file;
        }
        krsort($versions); // Newest first
        return $versions;
    }

    public function getAllPosts(bool $publishedOnly = true): array {
        $posts = [];
        $files = glob($this->contentPath . '/*.json');
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (!$data) continue;
            
            $post = BlogPost::fromArray($data);
            if (!$publishedOnly || $post->published) {
                $posts[] = $post;
            }
        }

        // Sort by date (newest first)
        usort($posts, fn($a, $b) => strtotime($b->date) - strtotime($a->date));
        
        return $posts;
    }

    public function deletePost(string $slug): bool {
        $filename = $this->contentPath . '/' . $slug . '.json';
        return file_exists($filename) ? unlink($filename) : false;
    }
}
