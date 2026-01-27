<?php
/**
 * Central Page Header Component
 * 
 * Usage in modules:
 * $pageHeader = [
 *     'icon' => '🔗',
 *     'title' => 'Internal Linking',
 *     'description' => 'Analyze your link structure',
 *     'back_url' => '/admin/ai-seo-dashboard',  // optional
 *     'back_text' => 'SEO Dashboard',           // optional
 *     'actions' => [                            // optional
 *         ['type' => 'link', 'url' => '/admin/reports', 'text' => '📊 Reports', 'class' => 'secondary'],
 *         ['type' => 'button', 'text' => '🔄 Analyze', 'class' => 'primary', 'form_action' => '?action=analyze'],
 *     ]
 * ];
 * require_once CMS_ROOT . '/admin/includes/page_header.php';
 */

if (!isset($pageHeader) || !is_array($pageHeader)) {
    return;
}

$ph_icon = $pageHeader['icon'] ?? '📄';
$ph_title = $pageHeader['title'] ?? 'Untitled';
$ph_desc = $pageHeader['description'] ?? '';
$ph_back_url = $pageHeader['back_url'] ?? '';
$ph_back_text = $pageHeader['back_text'] ?? 'Back';
$ph_actions = $pageHeader['actions'] ?? [];
$ph_gradient = $pageHeader['gradient'] ?? 'var(--accent), var(--purple)';
?>
<style>
.page-header{background:linear-gradient(135deg,var(--bg-secondary,#1e1e2e) 0%,var(--bg-tertiary,#313244) 100%);border-bottom:1px solid var(--border-color,#313244);padding:24px 32px;margin-bottom:24px}
.page-header-inner{max-width:1400px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap}
.page-title-section{display:flex;align-items:center;gap:16px}
.page-icon{width:56px;height:56px;background:linear-gradient(135deg,<?= htmlspecialchars($ph_gradient) ?>);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:28px;box-shadow:0 8px 32px rgba(137,180,250,.25);flex-shrink:0}
.page-title h1{font-size:24px;font-weight:700;margin:0 0 4px 0;background:linear-gradient(135deg,var(--text-primary,#cdd6f4),var(--accent-color,#89b4fa));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.page-title p{font-size:14px;color:var(--text-muted,#6c7086);margin:0}
.page-actions{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
.page-actions .btn{display:inline-flex;align-items:center;gap:6px;padding:10px 16px;font-size:13px;font-weight:500;border:none;border-radius:10px;cursor:pointer;text-decoration:none;transition:all .15s}
.page-actions .btn-primary{background:var(--accent-color,#89b4fa);color:#000}
.page-actions .btn-primary:hover{filter:brightness(1.1);transform:translateY(-1px)}
.page-actions .btn-secondary{background:var(--bg-elevated,#313244);color:var(--text-primary,#cdd6f4);border:1px solid var(--border-color,#313244)}
.page-actions .btn-secondary:hover{background:var(--bg-hover,#45475a);border-color:var(--accent-color,#89b4fa)}
.page-actions .btn-success{background:var(--success-color,#a6e3a1);color:#000}
.page-actions .btn-warning{background:var(--warning-color,#f9e2af);color:#000}
.page-actions .btn-danger{background:var(--danger-color,#f38ba8);color:#000}
@media(max-width:768px){
.page-header{padding:16px 20px}
.page-header-inner{flex-direction:column;align-items:flex-start;gap:16px}
.page-icon{width:48px;height:48px;font-size:24px}
.page-title h1{font-size:20px}
.page-actions{width:100%;justify-content:flex-start}
}
</style>

<div class="page-header">
    <div class="page-header-inner">
        <div class="page-title-section">
            <div class="page-icon"><?= $ph_icon ?></div>
            <div class="page-title">
                <h1><?= htmlspecialchars($ph_title) ?></h1>
                <?php if ($ph_desc): ?>
                <p><?= htmlspecialchars($ph_desc) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($ph_back_url || !empty($ph_actions)): ?>
        <div class="page-actions">
            <?php if ($ph_back_url): ?>
            <a href="<?= htmlspecialchars($ph_back_url) ?>" class="btn btn-secondary">← <?= htmlspecialchars($ph_back_text) ?></a>
            <?php endif; ?>
            <?php foreach ($ph_actions as $action): ?>
                <?php 
                $btn_class = 'btn btn-' . ($action['class'] ?? 'secondary');
                $btn_text = $action['text'] ?? 'Action';
                ?>
                <?php if (($action['type'] ?? '') === 'button' && isset($action['form_action'])): ?>
                <form method="POST" action="<?= htmlspecialchars($action['form_action']) ?>" style="display:inline;margin:0">
                    <?php if (function_exists('csrf_field')) csrf_field(); ?>
                    <button type="submit" class="<?= $btn_class ?>"><?= $btn_text ?></button>
                </form>
                <?php elseif (($action['type'] ?? '') === 'link' && isset($action['url'])): ?>
                <a href="<?= htmlspecialchars($action['url']) ?>" class="<?= $btn_class ?>"><?= $btn_text ?></a>
                <?php elseif (($action['type'] ?? '') === 'html' && isset($action['html'])): ?>
                <?= $action['html'] ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
