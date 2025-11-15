<?php
/**
 * CPT Renderer - Frontend display logic for custom post types
 */
class CPTRenderer {
    private $registry;

    public function __construct() {
        $this->registry = CPTRegistry::getInstance();
    }

    /**
     * Render a single post of custom type
     */
    public function renderPost(string $type, array $post_data) {
        $config = $this->registry->get($type);
        if (!$config) {
            throw new InvalidArgumentException("Post type '$type' not registered");
        }

        echo '
<article class="cpt-post cpt-' . htmlspecialchars(
$type) . '">';
        echo '
<h2>' . htmlspecialchars($post_data['title'] ?? '') . '</h2>';
        
        foreach (
$config['fields'] as $field) {
            $value = $post_data[$field['name']] ?? null;
            if ($value !== null) {
                echo '
<div class="cpt-field field-' . htmlspecialchars(
$field['name']) . '">';
                echo '
<h3>' . htmlspecialchars($field['label'] ?? $field['name']) . '</h3>';
                echo '
<div class="field-value">' . htmlspecialchars(
$value) . '</div>';
                echo '</div>';
            }
        }

        echo '
</article>';
    }

    /**
     * Render a list of posts
     */
    public
 function renderList(string $type, array $posts) {
        foreach ($posts as $post) {
            $this->renderPost($type, $post);
        }
    }
}
