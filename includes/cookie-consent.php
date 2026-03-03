<?php
/**
 * Cookie Consent Banner — GDPR compliant
 * Include this in theme layout files or via footer.
 * Respects user's choice via localStorage.
 * 
 * Usage in layout.php:
 *   <?php if (file_exists(CMS_ROOT . '/includes/cookie-consent.php')) require_once CMS_ROOT . '/includes/cookie-consent.php'; ?>
 */
if (defined('COOKIE_CONSENT_LOADED')) return;
define('COOKIE_CONSENT_LOADED', true);
?>
<div id="jcc-banner" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:9999;background:rgba(15,23,42,0.95);backdrop-filter:blur(8px);color:#e2e8f0;padding:16px 24px;font-family:system-ui,-apple-system,sans-serif;font-size:0.9rem;">
    <div style="max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <div style="flex:1;min-width:200px;">
            🍪 We use cookies to improve your experience. By continuing, you agree to our
            <a href="/page/cookie-policy" style="color:#818cf8;text-decoration:underline;">Cookie Policy</a> and
            <a href="/page/privacy-policy" style="color:#818cf8;text-decoration:underline;">Privacy Policy</a>.
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <button onclick="jccAccept('essential')" style="padding:8px 16px;background:transparent;color:#94a3b8;border:1px solid #475569;border-radius:6px;cursor:pointer;font-size:0.85rem;">Essential Only</button>
            <button onclick="jccAccept('all')" style="padding:8px 16px;background:#6366f1;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:0.85rem;">Accept All</button>
        </div>
    </div>
</div>
<script>
(function(){
    var b=document.getElementById('jcc-banner');
    if(!b)return;
    var consent=localStorage.getItem('jcc_consent');
    if(!consent){b.style.display='block';}
    window.jccAccept=function(level){
        localStorage.setItem('jcc_consent',level);
        localStorage.setItem('jcc_consent_date',new Date().toISOString());
        b.style.display='none';
        // Dispatch event for analytics to listen
        document.dispatchEvent(new CustomEvent('cookieConsent',{detail:{level:level}}));
    };
    // Expose consent check
    window.jccHasConsent=function(type){
        var c=localStorage.getItem('jcc_consent');
        if(type==='essential')return true;
        return c==='all';
    };
})();
</script>
