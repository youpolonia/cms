<?php
/**
 * Shared Heading Component
 * Renders consistent heading styles
 */

function render_heading($text, $level = 2) {
    $tag = "h{$level}";
    $class = "module-heading level-{$level}";
    return "<{$tag} class=\"{$class}\">{$text}</{$tag}>";
}
