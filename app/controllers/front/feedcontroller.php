<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;

class FeedController
{
    /**
     * RSS 2.0 feed of latest articles
     * GET /feed, /rss, /feed.xml
     */
    public function rss(Request $request): void
    {
        $pdo = db();

        // Site info
        $siteSettings = [];
        foreach (['site_name', 'site_description', 'site_url'] as $key) {
            $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
            $stmt->execute([$key]);
            $siteSettings[$key] = $stmt->fetchColumn() ?: '';
        }

        $siteName = $siteSettings['site_name'] ?: 'Jessie CMS';
        $siteDesc = $siteSettings['site_description'] ?: '';
        $siteUrl = $siteSettings['site_url'] ?: ('https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $siteUrl = rtrim($siteUrl, '/');

        // Get latest 20 published articles
        $activeTheme = get_active_theme();
        $stmt = $pdo->prepare(
            "SELECT title, slug, excerpt, content, created_at, updated_at
             FROM articles
             WHERE status = 'published'
               AND (theme_slug = ? OR theme_slug IS NULL)
             ORDER BY created_at DESC
             LIMIT 20"
        );
        $stmt->execute([$activeTheme]);
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Build RSS XML
        header('Content-Type: application/rss+xml; charset=utf-8');
        header('Cache-Control: public, max-age=3600');

        $lastBuild = !empty($articles) ? date('r', strtotime($articles[0]['created_at'])) : date('r');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title><?= $this->xmlEscape($siteName) ?></title>
        <link><?= $this->xmlEscape($siteUrl) ?></link>
        <description><?= $this->xmlEscape($siteDesc) ?></description>
        <language>en</language>
        <lastBuildDate><?= $lastBuild ?></lastBuildDate>
        <generator>Jessie CMS</generator>
        <atom:link href="<?= $this->xmlEscape($siteUrl . '/feed') ?>" rel="self" type="application/rss+xml" />
        <?php foreach ($articles as $a): ?>
        <item>
            <title><?= $this->xmlEscape($a['title']) ?></title>
            <link><?= $this->xmlEscape($siteUrl . '/article/' . $a['slug']) ?></link>
            <guid isPermaLink="true"><?= $this->xmlEscape($siteUrl . '/article/' . $a['slug']) ?></guid>
            <pubDate><?= date('r', strtotime($a['created_at'])) ?></pubDate>
            <description><?= $this->xmlEscape($this->stripToExcerpt($a['excerpt'] ?: $a['content'])) ?></description>
        </item>
        <?php endforeach; ?>
    </channel>
</rss>
        <?php
    }

    private function xmlEscape(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function stripToExcerpt(string $text, int $maxLen = 300): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', trim($text));
        if (mb_strlen($text) > $maxLen) {
            $text = mb_substr($text, 0, $maxLen) . '...';
        }
        return $text;
    }
}
