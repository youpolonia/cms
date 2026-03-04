<?php
/**
 * Unified Topbar Navigation v2.0 — Mega-Menu Support
 * Supports type=link, type=dropdown, type=mega (multi-column sections)
 * Single source of truth — reads admin_menu.php
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(dirname(__DIR__)));
}

require_once CMS_ROOT . '/core/session.php';

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
        if (!empty($item['url']) && strpos($path, $item['url']) === 0) return 'active';
    }
    return '';
}

function unified_mega_active(array $sections): string {
    global $currentPath;
    $path = $currentPath ?? '/';
    foreach ($sections as $section) {
        foreach ($section['items'] ?? [] as $item) {
            if (!empty($item['url']) && strpos($path, $item['url']) === 0) return 'active';
        }
    }
    return '';
}

$menu = require CMS_ROOT . '/admin/includes/admin_menu.php';
?>
<style>
/* ════════════════════════════════════════════
   TOPBAR v2.0 — Mega-Menu Navigation
   ════════════════════════════════════════════ */
html,body{max-width:100vw!important;overflow-x:hidden!important}

.legacy-topbar{background:linear-gradient(180deg,#1a1a2e 0%,#16162a 100%);border-bottom:1px solid rgba(255,255,255,0.06);position:sticky;top:0;z-index:9999;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;width:100%;max-width:100vw;box-sizing:border-box;backdrop-filter:blur(20px)}

/* Row 1: Logo + User */
.legacy-topbar-row1{width:100%;max-width:100%;padding:0 20px;height:52px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.04);box-sizing:border-box}
.legacy-topbar .logo{display:flex;align-items:center;gap:10px;font-size:17px;font-weight:700;text-decoration:none;color:#e2e8f0;flex-shrink:0;letter-spacing:-0.3px}
.legacy-topbar .logo-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(139,92,246,0.15),rgba(99,102,241,0.15));border:1px solid rgba(139,92,246,0.2)}
.legacy-topbar .logo-icon img{width:28px;height:28px}
.legacy-topbar .logo span:last-child{background:linear-gradient(135deg,#e0e7ff 0%,#c7d2fe 50%,#a5b4fc 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

/* Row 1 Right */
.legacy-topbar .topbar-right{display:flex;align-items:center;gap:10px;flex-shrink:0}
.legacy-topbar .icon-btn{width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);border-radius:10px;cursor:pointer;font-size:15px;transition:all .2s cubic-bezier(0.4,0,0.2,1);text-decoration:none;color:#94a3b8}
.legacy-topbar .icon-btn:hover{border-color:rgba(139,92,246,0.4);background:rgba(139,92,246,0.1);color:#e2e8f0;transform:translateY(-1px)}
.legacy-topbar .user-menu{display:flex;align-items:center;gap:8px;padding:5px 12px 5px 5px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);border-radius:24px;cursor:pointer;transition:all .2s cubic-bezier(0.4,0,0.2,1)}
.legacy-topbar .user-menu:hover{background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.1)}
.legacy-topbar .user-avatar{width:28px;height:28px;background:linear-gradient(135deg,#8b5cf6 0%,#6366f1 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#fff;flex-shrink:0;box-shadow:0 2px 8px rgba(139,92,246,0.3)}
.legacy-topbar .user-name{font-size:13px;font-weight:500;color:#e2e8f0}

/* Row 2: Navigation */
.legacy-topbar-row2{width:100%;max-width:100%;padding:0 20px;height:44px;display:flex;align-items:center;gap:4px;overflow:visible;box-sizing:border-box;flex-wrap:nowrap}

/* Nav items */
.legacy-topbar .nav-link{display:inline-flex;align-items:center;gap:5px;padding:0 12px;font-size:13px;font-weight:500;color:#94a3b8;border-radius:8px;transition:all .2s cubic-bezier(0.4,0,0.2,1);cursor:pointer;background:none;border:none;text-decoration:none;height:34px;white-space:nowrap;flex-shrink:0;letter-spacing:-0.1px}
.legacy-topbar .nav-link:hover{background:rgba(255,255,255,0.06);color:#e2e8f0}
.legacy-topbar .nav-link.active{background:linear-gradient(135deg,rgba(139,92,246,0.15),rgba(99,102,241,0.1));color:#c4b5fd;border:1px solid rgba(139,92,246,0.2)}
.legacy-topbar .nav-badge{padding:3px 7px;font-size:9px;font-weight:700;border-radius:6px;background:linear-gradient(135deg,#8b5cf6,#6366f1);color:#fff;margin-left:4px;text-transform:uppercase;letter-spacing:0.5px;box-shadow:0 2px 6px rgba(139,92,246,0.3)}
.legacy-topbar .nav-link .arrow{font-size:10px;opacity:0.6;margin-left:2px;transition:transform .2s}
.legacy-topbar .nav-dropdown:hover .nav-link .arrow{transform:rotate(180deg)}

/* ─── Dropdown container ─── */
.legacy-topbar .nav-dropdown{position:relative;display:inline-flex;align-items:center;flex-shrink:0}

/* ─── Regular dropdown ─── */
.legacy-topbar .nav-dropdown-menu{
    position:absolute;
    top:calc(100% + 8px);
    left:50%;
    transform:translateX(-50%) translateY(10px);
    background:linear-gradient(180deg,rgba(30,30,46,0.98) 0%,rgba(24,24,37,0.98) 100%);
    backdrop-filter:blur(20px);
    border:1px solid rgba(255,255,255,0.08);
    border-radius:16px;
    box-shadow:0 4px 6px -1px rgba(0,0,0,0.2),0 10px 20px -5px rgba(0,0,0,0.3),0 25px 50px -12px rgba(0,0,0,0.4),inset 0 1px 0 rgba(255,255,255,0.05);
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

.legacy-topbar .nav-dropdown:hover>.nav-dropdown-menu{
    opacity:1;visibility:visible;transform:translateX(-50%) translateY(0)
}

/* Arrow triangle */
.legacy-topbar .nav-dropdown-menu::before{
    content:'';position:absolute;top:-6px;left:50%;width:12px;height:12px;
    background:linear-gradient(135deg,rgba(30,30,46,0.98),rgba(30,30,46,0.98));
    border-left:1px solid rgba(255,255,255,0.08);border-top:1px solid rgba(255,255,255,0.08);
    transform:translateX(-50%) rotate(45deg);border-radius:3px 0 0 0
}

/* Menu item links */
.legacy-topbar .nav-dropdown-menu a{
    display:flex;align-items:center;gap:10px;padding:9px 14px;font-size:13px;font-weight:500;
    color:#a1a1aa;border-radius:10px;text-decoration:none;transition:all .15s cubic-bezier(0.4,0,0.2,1);
    white-space:nowrap;position:relative;overflow:hidden
}
.legacy-topbar .nav-dropdown-menu a .menu-icon{
    width:28px;height:28px;display:flex;align-items:center;justify-content:center;
    background:rgba(255,255,255,0.04);border-radius:7px;font-size:14px;flex-shrink:0;transition:all .15s
}
.legacy-topbar .nav-dropdown-menu a .menu-label{font-weight:500;color:#e2e8f0;font-size:13px}

.legacy-topbar .nav-dropdown-menu a:hover{
    background:linear-gradient(135deg,rgba(139,92,246,0.12),rgba(99,102,241,0.08));color:#e2e8f0
}
.legacy-topbar .nav-dropdown-menu a:hover .menu-icon{
    background:linear-gradient(135deg,rgba(139,92,246,0.2),rgba(99,102,241,0.15));transform:scale(1.05)
}
.legacy-topbar .nav-dropdown-menu a:hover .menu-label{color:#fff}

/* Divider & Section label */
.legacy-topbar .nav-dropdown-menu .menu-divider{height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.06),transparent);margin:6px 8px}
.legacy-topbar .nav-dropdown-menu .menu-section{padding:8px 14px 4px;font-size:10px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:1px}

/* ════════════════════════════════════════════
   MEGA-MENU — Multi-column sections
   ════════════════════════════════════════════ */
.legacy-topbar .mega-dropdown-menu{
    position:absolute;
    top:calc(100% + 8px);
    left:50%;
    transform:translateX(-50%) translateY(10px);
    background:linear-gradient(180deg,rgba(26,26,46,0.99) 0%,rgba(22,22,40,0.99) 100%);
    backdrop-filter:blur(24px);
    border:1px solid rgba(255,255,255,0.08);
    border-radius:16px;
    box-shadow:0 8px 16px rgba(0,0,0,0.25),0 20px 40px rgba(0,0,0,0.35),inset 0 1px 0 rgba(255,255,255,0.06);
    padding:16px;
    opacity:0;
    visibility:hidden;
    transition:all .25s cubic-bezier(0.4,0,0.2,1);
    z-index:10000;
    max-height:80vh;
    overflow-y:auto
}
.legacy-topbar .mega-dropdown-menu::-webkit-scrollbar{width:6px}
.legacy-topbar .mega-dropdown-menu::-webkit-scrollbar-track{background:transparent}
.legacy-topbar .mega-dropdown-menu::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.1);border-radius:3px}

.legacy-topbar .nav-dropdown:hover>.mega-dropdown-menu{
    opacity:1;visibility:visible;transform:translateX(-50%) translateY(0)
}
.legacy-topbar .mega-dropdown-menu::before{
    content:'';position:absolute;top:-6px;left:50%;width:12px;height:12px;
    background:rgba(26,26,46,0.99);
    border-left:1px solid rgba(255,255,255,0.08);border-top:1px solid rgba(255,255,255,0.08);
    transform:translateX(-50%) rotate(45deg);border-radius:3px 0 0 0
}

/* Column layout */
.mega-columns{display:grid;gap:12px}
.mega-columns.cols-1{grid-template-columns:1fr;min-width:240px}
.mega-columns.cols-2{grid-template-columns:repeat(2,1fr);min-width:440px}
.mega-columns.cols-3{grid-template-columns:repeat(3,1fr);min-width:640px}
.mega-columns.cols-4{grid-template-columns:repeat(4,1fr);min-width:780px}

/* Section within mega-menu */
.mega-section{min-width:0}
.mega-section-title{
    display:flex;align-items:center;gap:8px;
    padding:6px 10px;margin-bottom:4px;
    font-size:12px;font-weight:700;
    color:#c4b5fd;text-transform:uppercase;letter-spacing:0.8px;
    border-bottom:1px solid rgba(139,92,246,0.15);
    white-space:nowrap
}
.mega-section-title-emoji{font-size:14px}

/* Mega-menu links */
.mega-section a{
    display:flex;align-items:center;gap:8px;
    padding:7px 10px;font-size:13px;font-weight:500;
    color:#a1a1aa;border-radius:8px;text-decoration:none;
    transition:all .15s;white-space:nowrap
}
.mega-section a:hover{background:rgba(139,92,246,0.1);color:#e2e8f0}
.mega-section a.active-link{background:rgba(139,92,246,0.12);color:#c4b5fd}

/* ─── Overflow protection ─── */
.mega-dropdown-menu.mega-left{left:0;transform:translateX(0) translateY(10px)}
.legacy-topbar .nav-dropdown:hover>.mega-dropdown-menu.mega-left{transform:translateX(0) translateY(0)}
.mega-dropdown-menu.mega-left::before{left:30px;transform:rotate(45deg)}

.mega-dropdown-menu.mega-right{left:auto;right:0;transform:translateX(0) translateY(10px)}
.legacy-topbar .nav-dropdown:hover>.mega-dropdown-menu.mega-right{transform:translateX(0) translateY(0)}
.mega-dropdown-menu.mega-right::before{left:auto;right:30px;transform:rotate(45deg)}

/* User dropdown */
.legacy-topbar .nav-dropdown-menu.user-dd{left:auto;right:0;transform:translateY(10px)}
.legacy-topbar .nav-dropdown:hover>.nav-dropdown-menu.user-dd{transform:translateY(0)}
.legacy-topbar .nav-dropdown-menu.user-dd::before{left:auto;right:20px;transform:rotate(45deg)}

/* ─── Hamburger button ─── */
.topbar-hamburger{
    display:none;width:36px;height:36px;align-items:center;justify-content:center;
    background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);
    border-radius:10px;cursor:pointer;font-size:18px;color:#94a3b8;
    transition:all .2s;flex-shrink:0
}
.topbar-hamburger:hover{background:rgba(139,92,246,0.1);color:#e2e8f0;border-color:rgba(139,92,246,0.3)}

/* ─── Mobile overlay backdrop ─── */
.topbar-overlay{
    display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9998;
    opacity:0;transition:opacity .25s
}
.topbar-overlay.open{display:block;opacity:1}

/* ─── Responsive ─── */
@media(max-width:1200px){
    .mega-columns.cols-4{grid-template-columns:repeat(3,1fr);min-width:600px}
    .legacy-topbar .nav-link{padding:0 10px;font-size:12px}
}
@media(max-width:1024px){
    .topbar-hamburger{display:flex}

    .legacy-topbar-row2{
        display:none;position:fixed;top:0;right:0;width:300px;height:100vh;
        flex-direction:column;align-items:stretch;gap:0;padding:16px;
        background:linear-gradient(180deg,#1a1a2e 0%,#16162a 100%);
        border-left:1px solid rgba(255,255,255,0.08);
        box-shadow:-8px 0 30px rgba(0,0,0,0.4);
        z-index:9999;overflow-y:auto;overflow-x:hidden;
        transform:translateX(100%);transition:transform .3s cubic-bezier(0.4,0,0.2,1)
    }
    .legacy-topbar-row2.mobile-open{display:flex;transform:translateX(0)}

    .mobile-close{
        display:flex;align-self:flex-end;width:36px;height:36px;align-items:center;justify-content:center;
        background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08);
        border-radius:10px;cursor:pointer;font-size:18px;color:#94a3b8;margin-bottom:12px;flex-shrink:0
    }
    .mobile-close:hover{color:#e2e8f0;background:rgba(243,139,168,0.15)}

    .legacy-topbar .nav-link{
        width:100%;justify-content:space-between;height:40px;padding:0 12px;font-size:13px;border-radius:8px
    }
    .legacy-topbar .nav-dropdown{width:100%;flex-direction:column;align-items:stretch}

    .legacy-topbar .nav-dropdown-menu,
    .legacy-topbar .mega-dropdown-menu{
        position:static!important;transform:none!important;
        left:auto!important;right:auto!important;
        width:100%!important;min-width:0!important;max-width:100%!important;
        border-radius:8px;margin:4px 0 8px;padding:4px 0 4px 8px;
        box-shadow:none;border:1px solid rgba(255,255,255,0.06);
        display:none;opacity:1;visibility:visible;max-height:50vh;overflow-y:auto
    }
    .legacy-topbar .nav-dropdown-menu::before,
    .legacy-topbar .mega-dropdown-menu::before{display:none}

    .legacy-topbar .nav-dropdown.mobile-expanded>.nav-dropdown-menu,
    .legacy-topbar .nav-dropdown.mobile-expanded>.mega-dropdown-menu{display:block}

    .legacy-topbar .nav-dropdown:hover>.nav-dropdown-menu,
    .legacy-topbar .nav-dropdown:hover>.mega-dropdown-menu{opacity:0;visibility:hidden;display:none}
    .legacy-topbar .nav-dropdown.mobile-expanded:hover>.nav-dropdown-menu,
    .legacy-topbar .nav-dropdown.mobile-expanded:hover>.mega-dropdown-menu{opacity:1;visibility:visible;display:block}

    .mega-columns{grid-template-columns:1fr!important;min-width:0!important;gap:8px}
    .mega-section a{padding:6px 10px;font-size:12px}
    .legacy-topbar .user-name{display:none}
}
@media(max-width:480px){
    .legacy-topbar-row2{width:100%}
    .legacy-topbar-row1{padding:0 12px}
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
            <a href="/" target="_blank" class="icon-btn" title="View Site">🌐</a>
            <button class="icon-btn" id="theme-toggle-legacy" title="Toggle Theme">🌙</button>
            <button class="topbar-hamburger" id="topbar-hamburger" title="Menu">☰</button>

            <div class="nav-dropdown">
                <div class="user-menu">
                    <span class="user-avatar"><?= strtoupper(substr($adminUsername, 0, 1)) ?></span>
                    <span class="user-name"><?= htmlspecialchars($adminUsername) ?></span>
                </div>
                <div class="nav-dropdown-menu user-dd">
                    <?php if (!empty($menu['user']['items'])): ?>
                        <?php foreach ($menu['user']['items'] as $ui): ?>
                            <?php
                                $emoji = '•'; $label = $ui['label'];
                                if (preg_match('/^(\p{So}|\p{Cs}+)\s*/u', $label, $m)) { $emoji = $m[1]; $label = trim(substr($ui['label'], strlen($m[0]))); }
                            ?>
                            <a href="<?= htmlspecialchars($ui['url']) ?>">
                                <span class="menu-icon"><?= $emoji ?></span>
                                <span class="menu-label"><?= htmlspecialchars($label) ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="topbar-overlay" id="topbar-overlay"></div>
    <!-- Row 2: Navigation -->
    <div class="legacy-topbar-row2" id="topbar-nav">
        <button class="mobile-close" id="topbar-close" style="display:none">✕</button>
        <?php
        $navIndex = 0;
        $navTotal = count(array_filter($menu, fn($k) => $k !== 'user', ARRAY_FILTER_USE_KEY));
        foreach ($menu as $key => $item):
            if ($key === 'user') continue;
            $navIndex++;
            $type = $item['type'] ?? 'link';
        ?>

        <?php if ($type === 'link'): ?>
            <a href="<?= htmlspecialchars($item['url']) ?>" class="nav-link <?= unified_is_active($item['url']) ?>"><?= $item['label'] ?></a>

        <?php elseif ($type === 'dropdown'): ?>
            <div class="nav-dropdown">
                <span class="nav-link <?= unified_dropdown_active($item['items'] ?? []) ?>">
                    <?= $item['label'] ?>
                    <span class="arrow">▾</span>
                    <?php if (!empty($item['badge'])): ?><span class="nav-badge"><?= $item['badge'] ?></span><?php endif; ?>
                </span>
                <div class="nav-dropdown-menu">
                    <?php foreach ($item['items'] ?? [] as $sub):
                        $emoji = '•'; $label = $sub['label'];
                        if (preg_match('/^(\p{So}|\p{Cs}+)\s*/u', $label, $m)) { $emoji = $m[1]; $label = trim(substr($sub['label'], strlen($m[0]))); }
                    ?>
                    <a href="<?= htmlspecialchars($sub['url']) ?>">
                        <span class="menu-icon"><?= $emoji ?></span>
                        <span class="menu-label"><?= htmlspecialchars($label) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php elseif ($type === 'mega'): ?>
            <?php
                $cols = min($item['columns'] ?? 2, 4);
                $sections = $item['sections'] ?? [];
                if (empty($sections)) continue;
                // Decide alignment to prevent overflow
                $megaAlign = '';
                if ($navIndex <= 2) $megaAlign = 'mega-left';
                elseif ($navIndex >= $navTotal - 1) $megaAlign = 'mega-right';
            ?>
            <div class="nav-dropdown">
                <span class="nav-link <?= unified_mega_active($sections) ?>">
                    <?= $item['label'] ?>
                    <span class="arrow">▾</span>
                    <?php if (!empty($item['badge'])): ?><span class="nav-badge"><?= $item['badge'] ?></span><?php endif; ?>
                </span>
                <div class="mega-dropdown-menu <?= $megaAlign ?>">
                    <div class="mega-columns cols-<?= $cols ?>">
                        <?php foreach ($sections as $section): ?>
                        <div class="mega-section">
                            <?php
                                $sTitle = $section['title'] ?? '';
                                $sEmoji = '';
                                if (preg_match('/^(\p{So}|\p{Cs}+)\s*/u', $sTitle, $m)) {
                                    $sEmoji = $m[1]; $sTitle = trim(substr($section['title'], strlen($m[0])));
                                }
                            ?>
                            <div class="mega-section-title">
                                <?php if ($sEmoji): ?><span class="mega-section-title-emoji"><?= $sEmoji ?></span><?php endif; ?>
                                <?= htmlspecialchars($sTitle) ?>
                            </div>
                            <?php foreach ($section['items'] ?? [] as $si):
                                $isActive = unified_is_active($si['url']) ? ' active-link' : '';
                            ?>
                            <a href="<?= htmlspecialchars($si['url']) ?>" class="<?= $isActive ?>"><?= htmlspecialchars($si['label']) ?></a>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php endforeach; ?>
    </div>
</nav>

<script>
(function(){
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

    // Prevent mega-menus from going off-screen (desktop)
    document.querySelectorAll('.mega-dropdown-menu').forEach(function(el) {
        el.closest('.nav-dropdown').addEventListener('mouseenter', function() {
            if (window.innerWidth <= 1024) return;
            var rect = el.getBoundingClientRect();
            if (rect.right > window.innerWidth - 10) {
                el.classList.remove('mega-left');
                el.classList.add('mega-right');
            } else if (rect.left < 10) {
                el.classList.remove('mega-right');
                el.classList.add('mega-left');
            }
        });
    });

    // ─── Mobile menu ───
    var hamburger = document.getElementById('topbar-hamburger');
    var nav = document.getElementById('topbar-nav');
    var overlay = document.getElementById('topbar-overlay');
    var closeBtn = document.getElementById('topbar-close');

    function openMobileMenu() {
        nav.classList.add('mobile-open');
        overlay.classList.add('open');
        closeBtn.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeMobileMenu() {
        nav.classList.remove('mobile-open');
        overlay.classList.remove('open');
        closeBtn.style.display = 'none';
        document.body.style.overflow = '';
        // Close all expanded dropdowns
        document.querySelectorAll('.nav-dropdown.mobile-expanded').forEach(function(d) {
            d.classList.remove('mobile-expanded');
        });
    }

    if (hamburger) hamburger.addEventListener('click', openMobileMenu);
    if (closeBtn) closeBtn.addEventListener('click', closeMobileMenu);
    if (overlay) overlay.addEventListener('click', closeMobileMenu);

    // Mobile: click nav-link to toggle dropdown instead of hover
    document.querySelectorAll('.legacy-topbar .nav-dropdown > .nav-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (window.innerWidth > 1024) return;
            e.preventDefault();
            e.stopPropagation();
            var parent = this.closest('.nav-dropdown');
            var wasExpanded = parent.classList.contains('mobile-expanded');
            // Close other dropdowns
            document.querySelectorAll('.nav-dropdown.mobile-expanded').forEach(function(d) {
                d.classList.remove('mobile-expanded');
            });
            if (!wasExpanded) parent.classList.add('mobile-expanded');
        });
    });

    // Close mobile menu on link click (actual navigation)
    document.querySelectorAll('.legacy-topbar .nav-dropdown-menu a, .legacy-topbar .mega-dropdown-menu a').forEach(function(a) {
        a.addEventListener('click', function() {
            if (window.innerWidth <= 1024) closeMobileMenu();
        });
    });

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMobileMenu();
    });

    // Show/hide close button based on viewport
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            closeMobileMenu();
        }
    });
})();
</script>
