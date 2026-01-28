<?php
/**
 * Unified Topbar Navigation
 * Single source of truth - uses admin_menu.php for menu configuration
 * Works for both legacy and MVC modules
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(dirname(__DIR__)));
}

require_once CMS_ROOT . '/admin/includes/admin_menu.php';

$adminUsername = $_SESSION['admin_username'] ?? $_SESSION['cms_admin_username'] ?? 'Admin';
$currentPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';

function unified_is_active(string $url): string {
    global $currentPath;
    $path = $currentPath ?? '/';
    if ($url === '/admin' && ($path === '/admin' || $path === '/admin/' || $path === '/admin/dashboard')) {
        return 'active';
    }
    return ($url !== '/admin' && strpos($path, $url) === 0) ? 'active' : '';
}

function unified_dropdown_active(array $items): string {
    global $currentPath;
    $path = $currentPath ?? '/';
    foreach ($items as $item) {
        if (strpos($path, $item['url']) === 0) {
            return 'active';
        }
    }
    return '';
}

// Get menu from centralized config
$menu = require CMS_ROOT . '/admin/includes/admin_menu.php';
?>
<style>
.legacy-topbar{background:#1e1e2e;border-bottom:1px solid #313244;position:sticky;top:0;z-index:9999;font-family:'Inter',-apple-system,sans-serif}
.legacy-topbar-inner{max-width:1600px;margin:0 auto;padding:0 24px;height:64px;display:flex;align-items:center;gap:24px}
.legacy-topbar .logo{display:flex;align-items:center;gap:10px;font-size:18px;font-weight:700;text-decoration:none}
.legacy-topbar .logo-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px}
.legacy-topbar .logo span:last-child{background:linear-gradient(135deg,#c4b5fd,#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.legacy-topbar .nav-main{display:flex;align-items:center;gap:4px;flex:1}
.legacy-topbar .nav-link{display:inline-flex;align-items:center;gap:5px;padding:0 12px;font-size:13px;font-weight:500;color:#a6adc8;border-radius:8px;transition:all .15s;cursor:pointer;background:none;border:none;text-decoration:none;height:36px;white-space:nowrap}
.legacy-topbar .nav-link:hover{background:#313244;color:#cdd6f4}
.legacy-topbar .nav-link.active{background:rgba(137,180,250,.15);color:#89b4fa}
.legacy-topbar .nav-badge{padding:2px 8px;font-size:10px;font-weight:600;border-radius:10px;background:#89b4fa;color:#1e1e2e;margin-left:4px}
.legacy-topbar .nav-dropdown{position:relative;display:inline-flex;align-items:center}
.legacy-topbar .nav-dropdown-menu{position:absolute;top:100%;left:0;background:#1e1e2e;border:1px solid #313244;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,.3);min-width:200px;max-height:70vh;overflow-y:auto;padding:8px;opacity:0;visibility:hidden;transform:translateY(8px);transition:all .15s;z-index:10000}
.legacy-topbar .nav-dropdown:hover .nav-dropdown-menu{opacity:1;visibility:visible;transform:translateY(4px)}
.legacy-topbar .nav-dropdown-menu a{display:flex;align-items:center;gap:10px;padding:10px 12px;font-size:14px;color:#a6adc8;border-radius:8px;text-decoration:none;transition:all .15s}
.legacy-topbar .nav-dropdown-menu a:hover{background:#313244;color:#cdd6f4}
.legacy-topbar .nav-right{display:flex;align-items:center;gap:12px}
.legacy-topbar .icon-btn{width:40px;height:40px;display:flex;align-items:center;justify-content:center;background:#313244;border:1px solid #313244;border-radius:8px;cursor:pointer;font-size:18px;transition:all .15s;text-decoration:none}
.legacy-topbar .icon-btn:hover{border-color:#89b4fa;background:#45475a}
.legacy-topbar .user-menu{display:flex;align-items:center;gap:10px;padding:6px 12px 6px 6px;background:#313244;border-radius:24px;cursor:pointer;transition:all .15s}
.legacy-topbar .user-menu:hover{background:#45475a}
.legacy-topbar .user-avatar{width:32px;height:32px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:#fff}
.legacy-topbar .user-name{font-size:14px;font-weight:500;color:#cdd6f4}
@media(max-width:1100px){.legacy-topbar .nav-main{display:none}}
</style>
<nav class="legacy-topbar">
    <div class="legacy-topbar-inner">
        <a href="/admin" class="logo">
            <span class="logo-icon"><img src="/assets/images/jessie-logo.svg" alt="Jessie" width="36" height="36"></span>
            <span>Jessie</span>
        </a>
        
        <div class="nav-main">
            <?php foreach ($menu as $key => $item): ?>
                <?php if ($key === 'user') continue; // User menu rendered separately ?>
                
                <?php if (($item['type'] ?? 'link') === 'link'): ?>
                    <a href="<?= htmlspecialchars($item['url']) ?>" class="nav-link <?= unified_is_active($item['url']) ?>"><?= $item['label'] ?></a>
                <?php else: ?>
                    <div class="nav-dropdown">
                        <span class="nav-link <?= unified_dropdown_active($item['items'] ?? []) ?>">
                            <?= $item['label'] ?> ▾
                            <?php if (!empty($item['badge'])): ?><span class="nav-badge"><?= $item['badge'] ?></span><?php endif; ?>
                        </span>
                        <div class="nav-dropdown-menu">
                            <?php foreach ($item['items'] ?? [] as $subItem): ?>
                                <a href="<?= htmlspecialchars($subItem['url']) ?>"><?= $subItem['label'] ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <div class="nav-right">
            <!-- Clear Cache Button -->
            <a href="/admin/clear-cache" class="icon-btn" title="Clear Cache">🧹</a>
            
            <!-- Theme Toggle -->
            <button class="icon-btn" id="theme-toggle-legacy" title="Toggle Theme">🌙</button>
            
            <!-- User Menu -->
            <div class="nav-dropdown">
                <div class="user-menu">
                    <span class="user-avatar"><?= strtoupper(substr($adminUsername, 0, 1)) ?></span>
                    <span class="user-name"><?= htmlspecialchars($adminUsername) ?></span>
                </div>
                <div class="nav-dropdown-menu" style="right:0;left:auto">
                    <?php if (!empty($menu['user']['items'])): ?>
                        <?php foreach ($menu['user']['items'] as $userItem): ?>
                            <a href="<?= htmlspecialchars($userItem['url']) ?>"><?= $userItem['label'] ?></a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <a href="/admin/profile">👤 Profile</a>
                        <a href="/admin/settings">⚙️ Settings</a>
                        <a href="/admin/logout">🚪 Logout</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</nav>
<script>
(function(){
    const btn = document.getElementById('theme-toggle-legacy');
    if (btn) {
        btn.addEventListener('click', function() {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('cms-theme', next);
            this.textContent = next === 'dark' ? '🌙' : '☀️';
        });
        const saved = localStorage.getItem('cms-theme') || 'dark';
        btn.textContent = saved === 'dark' ? '🌙' : '☀️';
    }
})();
</script>
