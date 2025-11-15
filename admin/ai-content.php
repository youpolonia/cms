<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
csrf_boot();
require_once __DIR__ . '/includes/admin_layout.php';
admin_render_page_start('AI Content Creator');

echo "<h1>AI Content Creator</h1>";
echo "<p>Local tools for drafting content. No external APIs are used.</p>";

$source = isset($_GET['source']) ? (string)$_GET['source'] : '';
$action = isset($_GET['action']) ? (string)$_GET['action'] : '';

echo '<form method="GET" action="/admin/ai-content.php" style="margin:12px 0">';
echo '<textarea name="source" rows="12" style="width:100%;max-width:100%">' . htmlspecialchars($source, ENT_QUOTES, 'UTF-8') . '</textarea>';
echo '<div style="display:flex;gap:8px;flex-wrap:wrap;margin:8px 0">';
foreach (['outline'=>'Outline','summary'=>'Summary','paraphrase'=>'Paraphrase','titles'=>'Titles (5)','slug'=>'Slug'] as $k=>$label) {
  $btn = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
  echo '<button type="submit" name="action" value="'.$k.'">'.$btn.'</button>';
}
echo '<span style="color:#666;margin-left:auto">Chars: '.strlen($source).'</span>';
echo '</div>';
echo '</form>';

function ac_sentences(string $txt): array {
  $txt = trim(preg_replace('/\\s+/u', ' ', $txt));
  if ($txt === '') return [];
  $parts = preg_split('/(?<=[.!?])\\s+/u', $txt);
  $out = [];
  foreach ($parts as $s) {
    $s = trim($s);
    if ($s !== '') $out[] = $s;
  }
  return $out;
}
function ac_outline(string $txt): array {
  $sents = ac_sentences($txt);
  if (!$sents) return [];
  $outline = [];
  $max = min(8, count($sents));
  for ($i=0; $i<$max; $i++) {
    $h = trim($sents[$i], " \t\n\r\0\x0B-–—");
    $outline[] = $h;
  }
  return $outline;
}
function ac_summary(string $txt): string {
  $sents = ac_sentences($txt);
  if (!$sents) return '';
  $keep = [];
  $limit = max(1, (int)ceil(min(5, count($sents)) / 2));
  for ($i=0; $i<$limit; $i++) $keep[] = $sents[$i];
  return implode(' ', $keep);
}
function ac_paraphrase(string $txt): string {
  $map = [
    '/\\bvery\\b/i' => 'highly',
    '/\\breally\\b/i' => 'truly',
    '/\\bimportant\\b/i' => 'key',
    '/\\bproblem\\b/i' => 'issue',
    '/\\brozwiązanie\\b/i' => 'rozwiązanie (proponowane)',
    '/\\bważny\\b/i' => 'kluczowy',
  ];
  $out = $txt;
  foreach ($map as $re=>$rep) $out = preg_replace($re, $rep, $out);
  return $out;
}
function ac_titles(string $txt): array {
  $txt = trim(preg_replace('/\\s+/u', ' ', $txt));
  if ($txt === '') return [];
  $base = mb_substr($txt, 0, 80, 'UTF-8');
  $core = rtrim($base, " \t-–—:;,.");
  $suffixes = ['Guide','Overview','Checklist','Best Practices','How-To'];
  $out = [];
  foreach ($suffixes as $s) {
    $t = $core;
    if (mb_strlen($t, 'UTF-8') > 60) $t = mb_substr($t, 0, 60, 'UTF-8');
    $out[] = $t . ' — ' . $s;
  }
  return $out;
}
function ac_slug(string $txt): string {
  $t = mb_strtolower($txt, 'UTF-8');
  $t = preg_replace('/[^\\pL\\pN]+/u', '-', $t);
  $t = trim($t, '-');
  $t = preg_replace('/-+/', '-', $t);
  return $t;
}

if ($source !== '' && $action !== '') {
  echo '<hr>';
  if ($action === 'outline') {
    $ol = ac_outline($source);
    echo '<h2>Outline</h2>';
    if (!$ol) echo '<p>No outline available.</p>';
    else { echo '<ol>'; foreach ($ol as $li) echo '<li>'.htmlspecialchars($li, ENT_QUOTES, 'UTF-8').'</li>'; echo '</ol>'; }
  } elseif ($action === 'summary') {
    $sum = ac_summary($source);
    echo '<h2>Summary</h2><pre>'.htmlspecialchars($sum, ENT_QUOTES, 'UTF-8').'</pre>';
  } elseif ($action === 'paraphrase') {
    $pp = ac_paraphrase($source);
    echo '<h2>Paraphrase</h2><pre>'.htmlspecialchars($pp, ENT_QUOTES, 'UTF-8').'</pre>';
  } elseif ($action === 'titles') {
    $ts = ac_titles($source);
    echo '<h2>Suggested Titles</h2>';
    if (!$ts) echo '<p>No titles generated.</p>';
    else { echo '<ul>'; foreach ($ts as $t) echo '<li>'.htmlspecialchars($t, ENT_QUOTES, 'UTF-8').'</li>'; echo '</ul>'; }
  } elseif ($action === 'slug') {
    echo '<h2>Slug</h2><pre>'.htmlspecialchars(ac_slug($source), ENT_QUOTES, 'UTF-8').'</pre>';
  }
}

admin_render_page_end();
