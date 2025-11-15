<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
csrf_boot();
require_once __DIR__ . '/includes/admin_layout.php';
admin_render_page_start('AI SEO Assistant');

echo '<h1>AI SEO Assistant</h1>';
echo '<p>Paste content below to analyze keywords and draft meta tags. Local-only heuristic (no external API).</p>';

$content = isset($_GET['content']) ? (string)$_GET['content'] : '';

echo '<form method="GET" action="/admin/ai-seo.php" style="display:block;margin:12px 0">';
echo '<textarea name="content" rows="10" style="width:100%;max-width:100%;">' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</textarea>';
echo '<div style="margin:8px 0">';
echo '<button type="submit">Analyze</button>';
echo ' <span style="color:#666">Characters: ' . strlen($content) . ', Words: ' . (preg_match_all("/\\pL+/u", $content, $m) ? count($m[0]) : 0) . '</span>';
echo '</div>';
echo '</form>';

function ai_seo_tokenize(string $txt): array {
    $txt = mb_strtolower($txt, 'UTF-8');
    preg_match_all('/\\pL{2,}/u', $txt, $m);
    $tokens = $m[0] ?? [];
    // basic EN+PL stoplist
    $stop = [
        'the','and','for','with','that','this','from','are','was','were','have','has','had','you','your','but','not','all','any','can','into','more','most','other','some','such','no','nor','too','very','of','in','on','at','as','to','a','an','is','it','we','our','by','or',
        'i','be','do','does','did','so','if','than','then','they','them','their','there',
        'jak','oraz','ale','lub','jest','są','być','było','była','który','która','które','których','którym','oraz','dla','przez','z','bez','o','w','na','do','po','nad','pod','u','od',
        'się','jego','jej','ich','ten','ta','to','te','tam','tu','nie','tak','co','czy','gdy','kiedy','aby'
    ];
    $stop = array_flip($stop);
    $out = [];
    foreach ($tokens as $t) {
        if (isset($stop[$t])) { continue; }
        if (mb_strlen($t, 'UTF-8') < 3) { continue; }
        $out[] = $t;
    }
    return $out;
}

function ai_seo_top_keywords(string $txt, int $limit = 10): array {
    $toks = ai_seo_tokenize($txt);
    $freq = [];
    foreach ($toks as $t) { $freq[$t] = ($freq[$t] ?? 0) + 1; }
    arsort($freq, SORT_NUMERIC);
    return array_slice($freq, 0, $limit, true);
}

function ai_seo_title_suggest(string $txt, array $kw): string {
    $base = trim(preg_replace('/\\s+/', ' ', $txt));
    if ($base === '') { return ''; }
    $title = mb_substr($base, 0, 60, 'UTF-8');
    // prepend top keyword if missing and space exists
    $top = key($kw);
    if ($top && mb_stripos($title, $top, 0, 'UTF-8') === false) {
        $candidate = ucfirst($top) . ': ' . $title;
        if (mb_strlen($candidate, 'UTF-8') <= 60) { $title = $candidate; }
    }
    return rtrim($title, " \t\n\r\0\x0B-–—:;,.");
}

function ai_seo_description_suggest(string $txt, array $kw): string {
    $clean = trim(preg_replace('/\\s+/', ' ', $txt));
    if ($clean === '') { return ''; }
    $desc = mb_substr($clean, 0, 160, 'UTF-8');
    // try to end at word boundary
    if (mb_strlen($clean, 'UTF-8') > 160) {
        $pos = mb_strrpos($desc, ' ', 0, 'UTF-8');
        if ($pos !== false && $pos > 120) { $desc = mb_substr($desc, 0, $pos, 'UTF-8') . '…'; }
    }
    return rtrim($desc);
}

if ($content !== '') {
    $keywords = ai_seo_top_keywords($content, 12);
    $title = ai_seo_title_suggest($content, $keywords);
    $desc  = ai_seo_description_suggest($content, $keywords);

    echo '<hr>';
    echo '<h2>Keyword Suggestions</h2>';
    if (empty($keywords)) {
        echo '<p>No strong keywords found. Add more specific nouns/verbs.</p>';
    } else {
        echo '<ol>';
        foreach ($keywords as $k => $n) {
            echo '<li>' . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . ' <span style="color:#666">(' . (int)$n . ')</span></li>';
        }
        echo '</ol>';
    }

    echo '<h2>Meta Helpers</h2>';
    $tlen = mb_strlen($title, 'UTF-8');
    $dlen = mb_strlen($desc, 'UTF-8');
    $tok = ($tlen >= 50 && $tlen <= 60);
    $dok = ($dlen >= 140 && $dlen <= 160);
    echo '<p><strong>Title</strong> (' . $tlen . ' chars ' . ($tok ? '✓' : '• 50–60 recommended') . ')</p>';
    echo '<pre>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</pre>';
    echo '<p><strong>Description</strong> (' . $dlen . ' chars ' . ($dok ? '✓' : '• 140–160 recommended') . ')</p>';
    echo '<pre>' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . '</pre>';
}

admin_render_page_end();
