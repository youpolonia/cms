<?php
/**
 * Unified Topbar Navigation - Two Row Layout (Professional Design)
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
/* CRITICAL: Prevent horizontal overflow at root level */
html,body{max-width:100vw!important;overflow-x:hidden!important}

/* Two-row topbar layout */
.legacy-topbar{background:linear-gradient(180deg,#1a1a2e 0%,#16162a 100%);border-bottom:1px solid rgba(255,255,255,0.06);position:sticky;top:0;z-index:9999;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;width:100%;max-width:100vw;box-sizing:border-box;backdrop-filter:blur(20px)}

/* Row 1: Logo + User */
.legacy-topbar-row1{width:100%;max-width:100%;padding:0 20px;height:52px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.04);box-sizing:border-box}
.legacy-topbar .logo{display:flex;align-items:center;gap:10px;font-size:17px;font-weight:700;text-decoration:none;color:#e2e8f0;flex-shrink:0;letter-spacing:-0.3px}
.legacy-topbar .logo-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(139,92,246,0.15),rgba(99,102,241,0.15));border:1px solid rgba(139,92,246,0.2)}
.legacy-topbar .logo-icon img{width:28px;height:28px}
.legacy-topbar .logo span:last-child{background:linear-gradient(135deg,#e0e7ff 0%,#c7d2fe 50%,#a5b4fc 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

/* Row 1 Right side */
.legacy-topbar .topbar-right{display:flex;align-items:center;gap:10px;flex-shrink:0}
.legacy-topbar .icon-btn{width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);border-radius:10px;cursor:pointer;font-size:15px;transition:all .2s cubic-bezier(0.4,0,0.2,1);text-decoration:none;color:#94a3b8}
.legacy-topbar .icon-btn:hover{border-color:rgba(139,92,246,0.4);background:rgba(139,92,246,0.1);color:#e2e8f0;transform:translateY(-1px)}
.legacy-topbar .user-menu{display:flex;align-items:center;gap:8px;padding:5px 12px 5px 5px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);border-radius:24px;cursor:pointer;transition:all .2s cubic-bezier(0.4,0,0.2,1)}
.legacy-topbar .user-menu:hover{background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.1)}
.legacy-topbar .user-avatar{width:28px;height:28px;background:linear-gradient(135deg,#8b5cf6 0%,#6366f1 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#fff;flex-shrink:0;box-shadow:0 2px 8px rgba(139,92,246,0.3)}
.legacy-topbar .user-name{font-size:13px;font-weight:500;color:#e2e8f0}

/* Row 2: Navigation */
.legacy-topbar-row2{width:100%;max-width:100%;padding:0 20px;height:44px;display:flex;align-items:center;gap:4px;overflow:visible;box-sizing:border-box;flex-wrap:wrap}

/* Nav items */
.legacy-topbar .nav-link{display:inline-flex;align-items:center;gap:5px;padding:0 12px;font-size:13px;font-weight:500;color:#94a3b8;border-radius:8px;transition:all .2s cubic-bezier(0.4,0,0.2,1);cursor:pointer;background:none;border:none;text-decoration:none;height:34px;white-space:nowrap;flex-shrink:0;letter-spacing:-0.1px}
.legacy-topbar .nav-link:hover{background:rgba(255,255,255,0.06);color:#e2e8f0}
.legacy-topbar .nav-link.active{background:linear-gradient(135deg,rgba(139,92,246,0.15),rgba(99,102,241,0.1));color:#c4b5fd;border:1px solid rgba(139,92,246,0.2)}
.legacy-topbar .nav-badge{padding:3px 7px;font-size:9px;font-weight:700;border-radius:6px;background:linear-gradient(135deg,#8b5cf6,#6366f1);color:#fff;margin-left:4px;text-transform:uppercase;letter-spacing:0.5px;box-shadow:0 2px 6px rgba(139,92,246,0.3)}
.legacy-topbar .nav-link .arrow{font-size:10px;opacity:0.6;margin-left:2px;transition:transform .2s}
.legacy-topbar .nav-dropdown:hover .nav-link .arrow{transform:rotate(180deg)}

/* Dropdowns - Professional glassmorphism design */
.legacy-topbar .nav-dropdown{position:relative;display:inline-flex;align-items:center;flex-shrink:0}

.legacy-topbar .nav-dropdown-menu{
    position:absolute;
    top:calc(100% + 8px);
    left:50%;
    transform:translateX(-50%) translateY(10px);
    background:linear-gradient(180deg,rgba(30,30,46,0.98) 0%,rgba(24,24,37,0.98) 100%);
    backdrop-filter:blur(20px);
    border:1px solid rgba(255,255,255,0.08);
    border-radius:16px;
    box-shadow:
        0 4px 6px -1px rgba(0,0,0,0.2),
        0 10px 20px -5px rgba(0,0,0,0.3),
        0 25px 50px -12px rgba(0,0,0,0.4),
        inset 0 1px 0 rgba(255,255,255,0.05);
    min-width:220px;
    max-width:280px;
    max-height:75vh;
    overflow-y:auto;
    overflow-x:hidden;
    padding:8px;
    opacity:0;
    visibility:hidden;
    transition:all .25s cubic-bezier(0.4,0,0.2,1);
    z-index:10000
}

.legacy-topbar .nav-dropdown-menu::-webkit-scrollbar{width:6px}
.legacy-topbar .nav-dropdown-menu::-webkit-scrollbar-track{background:transparent}
.legacy-topbar .nav-dropdown-menu::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.1);border-radius:3px}
.legacy-topbar .nav-dropdown-menu::-webkit-scrollbar-thumb:hover{background:rgba(255,255,255,0.2)}

.legacy-topbar .nav-dropdown:hover .nav-dropdown-menu{
    opacity:1;
    visibility:visible;
    transform:translateX(-50%) translateY(0)
}

/* Dropdown header/title */
.legacy-topbar .nav-dropdown-menu::before{
    content:'';
    position:absolute;
    top:-6px;
    left:50%;
    transform:translateX(-50%);
    width:12px;
    height:12px;
    background:linear-gradient(135deg,rgba(30,30,46,0.98),rgba(30,30,46,0.98));
    border-left:1px solid rgba(255,255,255,0.08);
    border-top:1px solid rgba(255,255,255,0.08);
    transform:translateX(-50%) rotate(45deg);
    border-radius:3px 0 0 0
}

/* Menu items */
.legacy-topbar .nav-dropdown-menu a{
    display:flex;
    align-items:center;
    gap:12px;
    padding:10px 14px;
    font-size:13px;
    font-weight:500;
    color:#a1a1aa;
    border-radius:10px;
    text-decoration:none;
    transition:all .15s cubic-bezier(0.4,0,0.2,1);
    white-space:nowrap;
    position:relative;
    overflow:hidden
}

.legacy-topbar .nav-dropdown-menu a .menu-icon{
    width:32px;
    height:32px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:rgba(255,255,255,0.04);
    border-radius:8px;
    font-size:15px;
    flex-shrink:0;
    transition:all .15s
}

.legacy-topbar .nav-dropdown-menu a .menu-text{
    flex:1;
    display:flex;
    flex-direction:column;
    gap:2px
}

.legacy-topbar .nav-dropdown-menu a .menu-label{
    font-weight:500;
    color:#e2e8f0;
    font-size:13px
}

.legacy-topbar .nav-dropdown-menu a .menu-desc{
    font-size:11px;
    color:#64748b;
    font-weight:400
}

.legacy-topbar .nav-dropdown-menu a:hover{
    background:linear-gradient(135deg,rgba(139,92,246,0.12),rgba(99,102,241,0.08));
    color:#e2e8f0
}

.legacy-topbar .nav-dropdown-menu a:hover .menu-icon{
    background:linear-gradient(135deg,rgba(139,92,246,0.2),rgba(99,102,241,0.15));
    transform:scale(1.05)
}

.legacy-topbar .nav-dropdown-menu a:hover .menu-label{
    color:#fff
}

/* Divider */
.legacy-topbar .nav-dropdown-menu .menu-divider{
    height:1px;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,0.06),transparent);
    margin:6px 8px
}

/* Section label */
.legacy-topbar .nav-dropdown-menu .menu-section{
    padding:8px 14px 4px;
    font-size:10px;
    font-weight:600;
    color:#64748b;
    text-transform:uppercase;
    letter-spacing:1px
}

/* Special items styling */
.legacy-topbar .nav-dropdown-menu a.menu-highlight{
    background:linear-gradient(135deg,rgba(139,92,246,0.1),rgba(99,102,241,0.05));
    border:1px solid rgba(139,92,246,0.15)
}

.legacy-topbar .nav-dropdown-menu a.menu-highlight .menu-icon{
    background:linear-gradient(135deg,#8b5cf6,#6366f1);
    color:#fff
}

/* User dropdown specific */
.legacy-topbar .nav-dropdown-menu[style*="right:0"] {
    left:auto;
    right:0;
    transform:translateY(10px)
}
.legacy-topbar .nav-dropdown:hover .nav-dropdown-menu[style*="right:0"] {
    transform:translateY(0)
}
.legacy-topbar .nav-dropdown-menu[style*="right:0"]::before{
    left:auto;
    right:20px;
    transform:rotate(45deg)
}

/* Responsive */
@media(max-width:768px){
    .legacy-topbar-row1,.legacy-topbar-row2{padding:0 12px}
    .legacy-topbar .nav-link{padding:0 8px;font-size:12px}
    .legacy-topbar .user-name{display:none}
    .legacy-topbar .nav-dropdown-menu{min-width:200px}
}
</style>

<nav class="legacy-topbar">
    <!-- Row 1: Logo + User -->
    <div class="legacy-topbar-row1">
        <a href="/admin" class="logo">
            <span class="logo-icon"><img src="/assets/images/jessie-logo.svg" alt="Jessie" width="28" height="28"></span>
            <span>Jessie</span>
        </a>

        <div class="topbar-right">
            <a href="/admin/clear-cache" class="icon-btn" title="Clear Cache">🧹</a>
            <button class="icon-btn" id="theme-toggle-legacy" title="Toggle Theme">🌙</button>

            <div class="nav-dropdown">
                <div class="user-menu">
                    <span class="user-avatar"><?= strtoupper(substr($adminUsername, 0, 1)) ?></span>
                    <span class="user-name"><?= htmlspecialchars($adminUsername) ?></span>
                </div>
                <div class="nav-dropdown-menu" style="right:0;left:auto">
                    <?php if (!empty($menu['user']['items'])): ?>
                        <?php foreach ($menu['user']['items'] as $userItem): ?>
                            <a href="<?= htmlspecialchars($userItem['url']) ?>">
                                <span class="menu-icon"><?= preg_match('/^(\p{So}|\p{Cs})/u', $userItem['label'], $m) ? $m[0] : '•' ?></span>
                                <span class="menu-text">
                                    <span class="menu-label"><?= trim(preg_replace('/^(\p{So}|\p{Cs})\s*/u', '', $userItem['label'])) ?></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <a href="/admin/profile">
                            <span class="menu-icon">👤</span>
                            <span class="menu-text"><span class="menu-label">Profile</span></span>
                        </a>
                        <a href="/admin/settings">
                            <span class="menu-icon">⚙️</span>
                            <span class="menu-text"><span class="menu-label">Settings</span></span>
                        </a>
                        <div class="menu-divider"></div>
                        <a href="/admin/logout">
                            <span class="menu-icon">🚪</span>
                            <span class="menu-text"><span class="menu-label">Logout</span></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Navigation -->
    <div class="legacy-topbar-row2">
        <?php foreach ($menu as $key => $item): ?>
            <?php if ($key === 'user') continue; ?>

            <?php if (($item['type'] ?? 'link') === 'link'): ?>
                <a href="<?= htmlspecialchars($item['url']) ?>" class="nav-link <?= unified_is_active($item['url']) ?>"><?= $item['label'] ?></a>
            <?php else: ?>
                <div class="nav-dropdown">
                    <span class="nav-link <?= unified_dropdown_active($item['items'] ?? []) ?>">
                        <?= $item['label'] ?>
                        <span class="arrow">▾</span>
                        <?php if (!empty($item['badge'])): ?><span class="nav-badge"><?= $item['badge'] ?></span><?php endif; ?>
                    </span>
                    <div class="nav-dropdown-menu">
                        <?php
                        $items = $item['items'] ?? [];
                        $count = count($items);
                        foreach ($items as $idx => $subItem):
                            // Extract emoji from label
                            $emoji = '•';
                            $label = $subItem['label'];
                            if (preg_match('/^(\p{So}|\p{Cs}+)\s*/u', $label, $matches)) {
                                $emoji = $matches[1];
                                $label = trim(substr($subItem['label'], strlen($matches[0])));
                            }

                            // Check if this is a "special" item (AI, v5, new, etc)
                            $isHighlight = stripos($label, 'v5') !== false || stripos($label, 'new') !== false;
                        ?>
                            <a href="<?= htmlspecialchars($subItem['url']) ?>"<?= $isHighlight ? ' class="menu-highlight"' : '' ?>>
                                <span class="menu-icon"><?= $emoji ?></span>
                                <span class="menu-text">
                                    <span class="menu-label"><?= htmlspecialchars($label) ?></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</nav>

<script>
(function(){
    // Theme toggle
    var btn = document.getElementById('theme-toggle-legacy');
    if (btn) {
        btn.addEventListener('click', function() {
            var current = document.documentElement.getAttribute('data-theme') || 'dark';
            var next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('cms-theme', next);
            this.textContent = next === 'dark' ? '🌙' : '☀️';
        });
        var saved = localStorage.getItem('cms-theme') || 'dark';
        btn.textContent = saved === 'dark' ? '🌙' : '☀️';
    }
})();
</script>
