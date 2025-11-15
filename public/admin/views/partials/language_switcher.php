<?php
use Admin\Core\Services\LanguageService;

$languageService = LanguageService::getInstance();
$currentLanguage = $languageService->detectLanguage();
$availableLanguages = $languageService->getAvailableLanguages();
$currentUrl = $_SERVER['REQUEST_URI'];
$urlParts = parse_url($currentUrl);
$path = $urlParts['path'] ?? '/';
$query = [];

if (isset($urlParts['query'])) {
    parse_str($urlParts['query'], $query);
}

?><div class="language-switcher">
    <?php foreach ($availableLanguages as $lang): ?>        <?php 
        $query['lang'] = $lang;
        $newUrl = $path . '?' . http_build_query($query);
?>        <a href="<?= htmlspecialchars($newUrl) ?>" 
           class="<?= $lang === $currentLanguage ? 'active' : '' ?>">
            <?= strtoupper($lang)  ?>
        </a>
    <?php endforeach;  ?>
</div>
