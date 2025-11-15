<?php
/**
 * CPT Editor - Admin interface for managing custom post types
 */
class CPTEditor {
    private $registry;

    public function __construct() {
        $this->registry = CPTRegistry::getInstance();
    }

    /**
     * Render the CPT editor interface
     */
    public function renderEditor() {
        echo '
<div class="cpt-editor">';
        echo '
<h2>Custom Post Types</h2>';
        
        // List existing CPTs
        $post_types = $this->registry->getAll();
        if (!empty($post_types)) {
            echo '
<ul class="cpt-list">';
            foreach ($post_types as $name => $config) {
                echo '
<li>';
                echo '
<h3>' . htmlspecialchars($config['label']) . '</h3>';
                echo '
<p>' . htmlspecialchars(
$config['description']) . '</p>';
                echo '
<button class="edit-cpt" data-cpt="' . htmlspecialchars($name) . '">Edit</button>';
                echo '
</li>';
            }
            echo '</ul>';
        }

        // Add new CPT form
        echo '
<div class="cpt-form">';
        echo '
<h3>Add New Post Type</h3>';
        echo '
<form id="new-cpt-form">';
        echo '
<input type="text" name="name" placeholder="Post type name" required>';
        echo '
<input type="text" name="label" placeholder="Display label"
 required>';
        echo '
<button type="submit">Create</button>';
        echo '
</form>';
        echo '</div>';

        echo '
</div>';
    }
}
