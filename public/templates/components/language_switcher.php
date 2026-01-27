<?php
/**
 * Language Switcher Component
 * 
 * Displays available languages and handles switching
 */
$currentLang = $_GET['lang'] ?? 'en';
$availableLangs = ['en', 'es', 'fr', 'de', 'it'];

?><div class="language-switcher">
    <button class="current-language">
        <?= strtoupper($currentLang)  ?>
    </button>
    <ul class="language-dropdown">
        <?php foreach ($availableLangs as $lang): ?>
            <li>
                <a href="?lang=<?= $lang ?>">
                    <?= strtoupper($lang)  ?>
                </a>
            </li>
        <?php endforeach;  ?>
    </ul>
</div>

<style>
.language-switcher {
    position: relative;
    display: inline-block;
}
.current-language {
    padding: 8px 16px;
    background: #f0f0f0;
    border: 1px solid #ddd;
    cursor: pointer;
}
.language-dropdown {
    position: absolute;
    display: none;
    list-style: none;
    padding: 0;
    margin: 0;
    background: white;
    border: 1px solid #ddd;
    width: 100%;
}
.language-switcher:hover .language-dropdown {
    display: block;
}
.language-dropdown li a {
    display: block;
    padding: 8px 16px;
    text-decoration: none;
    color: #333;
}
.language-dropdown li a:hover {
    background: #f0f0f0;
}
</style>
