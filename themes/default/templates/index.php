<?php
/**
 * Main template file
 * 
 * This is the default template that handles content display
 */
TemplateEngine::render('header', [
    'title' => $pageTitle ?? 'Welcome',
    'siteName' => $siteName ?? 'CMS'
]);

// Main content area
echo $content ?? '';

TemplateEngine::render('footer', [
    'siteName' => $siteName ?? 'CMS'
]);
