<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../core/csrf.php';
csrf_boot();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
$out = null;
// Storage path for drafts
$DRAFT_DIR = __DIR__ . '/../cms_storage/ai_drafts';
if (!is_dir($DRAFT_DIR)) { @mkdir($DRAFT_DIR, 0775, true); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = (string)($_POST['action'] ?? 'preview');
    $title  = mb_substr(trim((string)($_POST['title'] ?? '')), 0, 160);
    $prompt = trim((string)($_POST['prompt'] ?? ''));
    $model  = mb_substr(trim((string)($_POST['model'] ?? 'generic-ai')), 0, 40);
    if ($title === '' || $prompt === '') {
        $out = ['type' => 'error', 'msg' => 'Title and prompt are required'];
    } else {
        if ($action === 'save') {
            $slug = preg_replace('/[^a-z0-9\-]+/','-', strtolower(trim($title)));
            $slug = trim($slug, '-');
            if ($slug === '') { $slug = 'draft-'.bin2hex(random_bytes(3)); }
            $payload = [
                'title'=>$title,
                'model'=>$model,
                'prompt'=>$prompt,
                'created_at'=>gmdate('c')
            ];
            $ok = @file_put_contents($DRAFT_DIR . '/' . $slug . '.json', json_encode($payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES), LOCK_EX);
            if ($ok === false) {
                $out = ['type'=>'error','msg'=>'Failed to save draft'];
            } else {
                $out = ['type'=>'success','msg'=>'Draft saved','title'=>$title,'model'=>$model,'file'=>$slug.'.json'];
            }
        } else {
            $out = ['type' => 'info', 'msg' => 'DRY RUN: AI generate', 'title' => $title, 'model' => $model, 'preview' => mb_substr($prompt, 0, 400)];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>AI Content Creator</title><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body>
<main class="container">
    <h1>AI Content Creator</h1>
    <?php if ($out): ?>
        <div class="notice <?=htmlspecialchars($out['type'])?>"><strong><?=htmlspecialchars($out['msg'])?></strong></div>
        <?php if (!empty($out['title'])): ?><p><b>Title:</b> <?=htmlspecialchars($out['title'])?> (<?=htmlspecialchars($out['model'])?>)</p><?php endif; ?>
        <?php if (!empty($out['preview'])): ?><pre><?=htmlspecialchars($out['preview'])?></pre><?php endif; ?>
        <?php if (!empty($out['file'])): ?><p><b>Saved:</b> cms_storage/ai_drafts/<?=htmlspecialchars($out['file'])?></p><?php endif; ?>
    <?php endif; ?>
    <form method="post">
        <?php csrf_field(); ?>
        <div><label for="title">Title</label><input id="title" name="title" type="text" required></div>
        <div><label for="model">Model</label>
            <select id="model" name="model">
                <option value="generic-ai">Generic AI</option>
                <option value="seo-writer">SEO Writer</option>
            </select>
        </div>
        <div><label for="prompt">Prompt</label><textarea id="prompt" name="prompt" rows="8" required></textarea></div>
        <button type="submit" name="action" value="preview">Preview (Dry Run)</button>
        <button type="submit" name="action" value="save">Save Draft</button>
    </form>
<?php require_once __DIR__ . '/includes/footer.php';
