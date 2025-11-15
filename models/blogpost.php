<?php
/**
 * Blog Post Model - Represents a blog post entity
 */
class BlogPost {
    public int $id;
    public string $title;
    public string $slug;
    public string $content;
    public int $author_id;
    public string $status;
    public string $created_at;
    public string $updated_at;

    /**
     * Get excerpt of content
     * @param int $length Max length of excerpt
     * @return string Excerpt text
     */
    public function getExcerpt(int $length = 200): string {
        $text = strip_tags($this->content);
        if (strlen($text) > $length) {
            $text = substr($text, 0, $length) . '...';
        }
        return $text;
    }

    /**
     * Get formatted creation date
     * @param string $format Date format
     * @return string Formatted date
     */
    public function getCreatedDate(string $format = 'Y-m-d H:i'): string {
        return date($format, strtotime($this->created_at));
    }

    /**
     * Check if post is published
     * @return bool True if published
     */
    public function isPublished(): bool {
        return $this->status === 'published';
    }
}
