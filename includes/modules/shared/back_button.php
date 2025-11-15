<?php
/**
 * Shared Back Button Component
 * Renders consistent back navigation
 */

function render_back_button($text, $url) {
    return '
<div class="back-button-container">
<a href="'.htmlspecialchars(
$url).'" class="back-button">
            ← '.htmlspecialchars($text).'
        </a>
    </div>';
}
