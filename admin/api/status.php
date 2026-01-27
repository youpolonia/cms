<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
// Start session before checking permissions
require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../../core/csrf.php';
csrf_boot('admin');
// RBAC: Require admin access
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();
header('Content-Type: application/json; charset=UTF-8');
require_once CMS_ROOT . '/core/seo.php';
require_once CMS_ROOT . '/core/settings_email.php';
require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/n8n_client.php';

$root = dirname(__DIR__, 2);
$paths = [
  'uploads' => $root . '/uploads',
  'themes' => $root . '/themes',
  'logs' => $root . '/logs',
  'n8n_workflows' => $root . '/n8n/workflows',
];
$modules = [
  '/admin/articles.php',
  '/admin/ai-content.php',
  '/admin/pages.php',
  '/admin/categories.php',
  '/admin/comments.php',
  '/admin/media.php',
  '/admin/galleries.php',
  '/admin/themes.php',
  '/admin/theme-builder.php',
  '/admin/migrations.php',
  '/admin/migration_manager.php',
  '/admin/scheduler.php',
  '/admin/email-queue.php',
  '/admin/backup.php',
  '/admin/maintenance.php',
  '/admin/extensions.php',
  '/admin/seo.php',
  '/admin/security.php',
  '/admin/automations.php',
  '/admin/logs.php',
  '/admin/users.php',
  '/admin/settings.php',
  '/admin/content.php',
];

$mods = [];
foreach ($modules as $p) { $mods[$p] = file_exists($_SERVER['DOCUMENT_ROOT'] . $p); }
$dirs = [];
foreach ($paths as $k=>$p) { $dirs[$k] = is_dir($p); }

$seoSettings = seo_get_settings();
$canonicalBase = trim((string)($seoSettings['canonical_base_url'] ?? ''));
$robotsIndex = (string)($seoSettings['robots_index'] ?? 'index');
$robotsFollow = (string)($seoSettings['robots_follow'] ?? 'follow');
$robotsIndex = ($robotsIndex === 'noindex') ? 'noindex' : 'index';
$robotsFollow = ($robotsFollow === 'nofollow') ? 'nofollow' : 'follow';
$canonicalConfigured = ($canonicalBase !== '');
$metaTitleSuffix = (string)($seoSettings['meta_title_suffix'] ?? '');
$metaDescriptionDefault = (string)($seoSettings['meta_description_default'] ?? '');
$isIndexable = ($robotsIndex === 'index' && $robotsFollow === 'follow');

$emailSettings = email_settings_get();
$fromName       = (string)($emailSettings['from_name'] ?? '');
$fromEmail      = (string)($emailSettings['from_email'] ?? '');
$replyToEmail   = (string)($emailSettings['reply_to_email'] ?? '');
$sendMode       = (string)($emailSettings['send_mode'] ?? 'smtp');
$sendModeNormalized = ($sendMode === 'phpmail') ? 'phpmail' : 'smtp';
$emailConfigured = ($fromEmail !== '');
$hasReplyTo      = ($replyToEmail !== '');

$hfConfig = ai_hf_config_load();
$hfConfigured = ai_hf_is_configured($hfConfig);

$n8nConfig = n8n_config_load();
$n8nConfigured = n8n_is_configured($n8nConfig);

echo json_encode([
  'ok' => true,
  'dev_mode' => (defined('DEV_MODE') && DEV_MODE === true),
  'php' => PHP_VERSION,
  'paths' => $dirs,
  'modules' => $mods,
  'seo' => [
    'canonical_base_url_configured' => $canonicalConfigured,
    'canonical_base_url' => $canonicalConfigured ? $canonicalBase : '',
    'robots_index' => $robotsIndex,
    'robots_follow' => $robotsFollow,
    'robots_meta' => $robotsIndex . ',' . $robotsFollow,
    'canonicalConfigured' => $canonicalConfigured,
    'is_indexable' => $isIndexable,
    'meta_title_suffix' => $metaTitleSuffix,
    'meta_description_default' => $metaDescriptionDefault,
  ],
  'email' => [
    'from_name'       => $fromName,
    'from_email'      => $fromEmail,
    'reply_to_email'  => $replyToEmail,
    'send_mode'       => $sendModeNormalized,
    'configured'      => $emailConfigured,
    'has_reply_to'    => $hasReplyTo,
  ],
  'hf' => [
    'configured' => (bool)$hfConfigured,
  ],
  'n8n' => [
    'configured' => (bool)$n8nConfigured,
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
