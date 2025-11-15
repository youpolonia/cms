<?php
/**
 * Standard page template
 * Uses theme rendering system
 */

declare(strict_types=1);

if (!isset($content)) {
    throw new RuntimeException('Content variable not set for page template');
}

$templateVars = [
    'content' => $content,
    'pageTitle' => $pageTitle ?? 'Untitled Page',
    'subtitle' => $subtitle ?? null,
    'sidebar' => $sidebar ?? null
];

render_theme_view('page', $templateVars);
