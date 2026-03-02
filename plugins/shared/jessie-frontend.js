/**
 * Jessie CMS — Shared Frontend Kit
 * Lightweight utilities for all plugins (no dependencies)
 * Include: <script src="/plugins/shared/jessie-frontend.js"></script>
 */
(function(window) {
    'use strict';

    var J = window.Jessie = window.Jessie || {};

    // ═══════════════════════════════════════
    //  TOAST NOTIFICATIONS
    // ═══════════════════════════════════════
    J.toast = function(message, type, duration) {
        type = type || 'info';
        duration = duration || 4000;
        var container = document.getElementById('j-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'j-toast-container';
            container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:8px;pointer-events:none';
            document.body.appendChild(container);
        }
        var icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
        var colors = { success: '#22c55e', error: '#ef4444', warning: '#f59e0b', info: '#6366f1' };
        var toast = document.createElement('div');
        toast.style.cssText = 'pointer-events:auto;background:#1e293b;color:#e2e8f0;border:1px solid #334155;border-left:4px solid ' + (colors[type] || colors.info) + ';border-radius:10px;padding:12px 16px;font-size:.85rem;display:flex;align-items:center;gap:10px;min-width:280px;max-width:420px;box-shadow:0 8px 24px rgba(0,0,0,.3);opacity:0;transform:translateX(40px);transition:all .3s ease';
        toast.innerHTML = '<span style="font-size:1.1rem">' + (icons[type] || icons.info) + '</span><span style="flex:1">' + message + '</span><span style="cursor:pointer;opacity:.5;font-size:1.1rem" onclick="this.parentElement.remove()">×</span>';
        container.appendChild(toast);
        requestAnimationFrame(function() { toast.style.opacity = '1'; toast.style.transform = 'translateX(0)'; });
        setTimeout(function() {
            toast.style.opacity = '0'; toast.style.transform = 'translateX(40px)';
            setTimeout(function() { toast.remove(); }, 300);
        }, duration);
    };

    // ═══════════════════════════════════════
    //  LIGHTBOX
    // ═══════════════════════════════════════
    J.lightbox = function(images, startIndex) {
        startIndex = startIndex || 0;
        var idx = startIndex;
        if (!Array.isArray(images)) images = [images];
        var overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:99998;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s';
        overlay.innerHTML = '<div style="position:relative;max-width:90vw;max-height:90vh;display:flex;align-items:center;justify-content:center"><img id="j-lb-img" src="" style="max-width:90vw;max-height:85vh;object-fit:contain;border-radius:8px;transition:opacity .2s"></div>'
            + '<button id="j-lb-close" style="position:absolute;top:16px;right:20px;background:none;border:none;color:#fff;font-size:2rem;cursor:pointer;opacity:.7;z-index:1">✕</button>'
            + (images.length > 1 ? '<button id="j-lb-prev" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.1);border:none;color:#fff;font-size:1.5rem;padding:12px 16px;border-radius:50%;cursor:pointer">←</button><button id="j-lb-next" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.1);border:none;color:#fff;font-size:1.5rem;padding:12px 16px;border-radius:50%;cursor:pointer">→</button>' : '')
            + '<div id="j-lb-counter" style="position:absolute;bottom:16px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,.6);font-size:.8rem"></div>';
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(function() { overlay.style.opacity = '1'; });
        var img = overlay.querySelector('#j-lb-img'), counter = overlay.querySelector('#j-lb-counter');
        function show(i) {
            idx = (i + images.length) % images.length;
            img.style.opacity = '0';
            setTimeout(function() { img.src = typeof images[idx] === 'string' ? images[idx] : images[idx].src; img.style.opacity = '1'; if (counter) counter.textContent = (idx + 1) + ' / ' + images.length; }, 150);
        }
        show(idx);
        function close() { overlay.style.opacity = '0'; setTimeout(function() { overlay.remove(); document.body.style.overflow = ''; }, 200); }
        overlay.querySelector('#j-lb-close').addEventListener('click', close);
        overlay.addEventListener('click', function(e) { if (e.target === overlay) close(); });
        if (images.length > 1) {
            overlay.querySelector('#j-lb-prev').addEventListener('click', function(e) { e.stopPropagation(); show(idx - 1); });
            overlay.querySelector('#j-lb-next').addEventListener('click', function(e) { e.stopPropagation(); show(idx + 1); });
        }
        document.addEventListener('keydown', function handler(e) {
            if (e.key === 'Escape') { close(); document.removeEventListener('keydown', handler); }
            if (e.key === 'ArrowLeft' && images.length > 1) show(idx - 1);
            if (e.key === 'ArrowRight' && images.length > 1) show(idx + 1);
        });
    };
    // Auto-init lightbox on [data-lightbox]
    document.addEventListener('DOMContentLoaded', function() {
        var groups = {};
        document.querySelectorAll('[data-lightbox]').forEach(function(el) {
            var g = el.dataset.lightbox || 'default';
            if (!groups[g]) groups[g] = [];
            groups[g].push(el.href || el.src || el.dataset.src);
            el.addEventListener('click', function(e) { e.preventDefault(); J.lightbox(groups[g], groups[g].indexOf(el.href || el.src || el.dataset.src)); });
        });
    });

    // ═══════════════════════════════════════
    //  AJAX FILTERS
    // ═══════════════════════════════════════
    J.ajaxFilter = function(opts) {
        var form = typeof opts.form === 'string' ? document.querySelector(opts.form) : opts.form;
        var target = typeof opts.target === 'string' ? document.querySelector(opts.target) : opts.target;
        var url = opts.url, onSuccess = opts.onSuccess || null, timer = null;
        if (!form || !target || !url) return;
        function doFilter() {
            var params = new URLSearchParams(new FormData(form)).toString();
            target.style.opacity = '0.5';
            fetch(url + (url.indexOf('?') > -1 ? '&' : '?') + params, { credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(data) { target.style.opacity = '1'; if (onSuccess) onSuccess(data, target); })
                .catch(function() { target.style.opacity = '1'; J.toast('Failed to load results', 'error'); });
        }
        form.querySelectorAll('input, select').forEach(function(el) {
            el.addEventListener('input', function() { clearTimeout(timer); timer = setTimeout(doFilter, opts.debounce || 300); });
            el.addEventListener('change', doFilter);
        });
        form.addEventListener('submit', function(e) { e.preventDefault(); doFilter(); });
        return { refresh: doFilter };
    };

    // ═══════════════════════════════════════
    //  TABS
    // ═══════════════════════════════════════
    J.tabs = function(container) {
        var el = typeof container === 'string' ? document.querySelector(container) : container;
        if (!el) return;
        var triggers = el.querySelectorAll('[data-tab]'), panels = el.querySelectorAll('[data-tab-panel]');
        function activate(name) {
            triggers.forEach(function(t) { t.classList.toggle('active', t.dataset.tab === name); });
            panels.forEach(function(p) { p.style.display = p.dataset.tabPanel === name ? '' : 'none'; });
        }
        triggers.forEach(function(t) { t.addEventListener('click', function(e) { e.preventDefault(); activate(this.dataset.tab); }); });
        if (triggers.length > 0) activate(triggers[0].dataset.tab);
    };

    // ═══════════════════════════════════════
    //  MODAL
    // ═══════════════════════════════════════
    J.modal = function(opts) {
        var overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:99997;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;transition:opacity .2s';
        overlay.innerHTML = '<div style="background:#1e293b;border:1px solid #334155;border-radius:16px;width:100%;max-width:' + (opts.size || '480px') + ';max-height:85vh;overflow-y:auto;transform:scale(.95);transition:transform .2s">'
            + (opts.title ? '<div style="padding:20px 24px 0;font-size:1.1rem;font-weight:700;color:#e2e8f0">' + opts.title + '</div>' : '')
            + '<div style="padding:20px 24px;color:#94a3b8;font-size:.9rem;line-height:1.6">' + (opts.content || '') + '</div>'
            + '<div style="padding:0 24px 20px;display:flex;gap:10px;justify-content:flex-end">'
            + '<button class="j-modal-cancel" style="padding:10px 20px;background:#0f172a;border:1px solid #334155;color:#e2e8f0;border-radius:8px;cursor:pointer;font-size:.85rem">' + (opts.cancelText || 'Cancel') + '</button>'
            + (opts.onConfirm ? '<button class="j-modal-ok" style="padding:10px 20px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:.85rem">' + (opts.confirmText || 'Confirm') + '</button>' : '')
            + '</div></div>';
        document.body.appendChild(overlay); document.body.style.overflow = 'hidden';
        requestAnimationFrame(function() { overlay.style.opacity = '1'; overlay.querySelector('div').style.transform = 'scale(1)'; });
        function close() { overlay.style.opacity = '0'; setTimeout(function() { overlay.remove(); document.body.style.overflow = ''; }, 200); }
        overlay.querySelector('.j-modal-cancel').addEventListener('click', close);
        overlay.addEventListener('click', function(e) { if (e.target === overlay) close(); });
        if (opts.onConfirm) overlay.querySelector('.j-modal-ok').addEventListener('click', function() { opts.onConfirm(); close(); });
        document.addEventListener('keydown', function h(e) { if (e.key === 'Escape') { close(); document.removeEventListener('keydown', h); } });
        return { close: close };
    };

    // ═══════════════════════════════════════
    //  COPY TO CLIPBOARD
    // ═══════════════════════════════════════
    J.copy = function(text, msg) {
        if (navigator.clipboard) { navigator.clipboard.writeText(text).then(function() { J.toast(msg || 'Copied!', 'success', 2000); }); }
        else { var ta = document.createElement('textarea'); ta.value = text; ta.style.cssText = 'position:fixed;left:-9999px'; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); ta.remove(); J.toast(msg || 'Copied!', 'success', 2000); }
    };
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-copy]').forEach(function(el) {
            el.style.cursor = 'pointer';
            el.addEventListener('click', function() { J.copy(this.dataset.copy || this.textContent); });
        });
    });

    // ═══════════════════════════════════════
    //  LOADING SPINNER
    // ═══════════════════════════════════════
    J.loading = function(target, show) {
        var el = typeof target === 'string' ? document.querySelector(target) : target;
        if (!el) return;
        var existing = el.querySelector('.j-spinner');
        if (show === false) { if (existing) existing.remove(); el.style.position = ''; return; }
        if (existing) return;
        el.style.position = 'relative';
        var s = document.createElement('div'); s.className = 'j-spinner';
        s.style.cssText = 'position:absolute;inset:0;background:rgba(15,23,42,.7);display:flex;align-items:center;justify-content:center;border-radius:inherit;z-index:10';
        s.innerHTML = '<div style="width:32px;height:32px;border:3px solid #334155;border-top-color:#6366f1;border-radius:50%;animation:j-spin 0.7s linear infinite"></div>';
        if (!document.querySelector('#j-spin-style')) { var st = document.createElement('style'); st.id = 'j-spin-style'; st.textContent = '@keyframes j-spin{to{transform:rotate(360deg)}}'; document.head.appendChild(st); }
        el.appendChild(s);
    };

    // ═══════════════════════════════════════
    //  FETCH HELPER & UTILITIES
    // ═══════════════════════════════════════
    J.api = function(url, opts) {
        opts = opts || {};
        var fetchOpts = { method: (opts.method || 'GET').toUpperCase(), credentials: 'same-origin', headers: {} };
        if (opts.data && fetchOpts.method !== 'GET') { fetchOpts.headers['Content-Type'] = 'application/json'; fetchOpts.body = JSON.stringify(opts.data); }
        return fetch(url, fetchOpts).then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); });
    };
    J.debounce = function(fn, ms) { var t; return function() { var ctx = this, args = arguments; clearTimeout(t); t = setTimeout(function() { fn.apply(ctx, args); }, ms || 300); }; };
    J.formatCurrency = function(n, sym) { return (sym || '$') + parseFloat(n || 0).toFixed(2); };
    J.formatDate = function(s) { return new Date(s).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }); };
    J.timeAgo = function(s) { var d = Math.floor((Date.now() - new Date(s).getTime()) / 1000); if (d < 60) return 'just now'; if (d < 3600) return Math.floor(d / 60) + 'm ago'; if (d < 86400) return Math.floor(d / 3600) + 'h ago'; if (d < 2592000) return Math.floor(d / 86400) + 'd ago'; return J.formatDate(s); };

    // ═══════════════════════════════════════
    //  FORM VALIDATION
    // ═══════════════════════════════════════
    J.validateForm = function(form) {
        var el = typeof form === 'string' ? document.querySelector(form) : form;
        if (!el) return true;
        var valid = true;
        el.querySelectorAll('.j-validation-error').forEach(function(e) { e.remove(); });
        el.querySelectorAll('[required]').forEach(function(input) {
            var val = input.value.trim(), ok = true;
            if (!val) ok = false;
            if (input.type === 'email' && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) ok = false;
            if (input.minLength > 0 && val.length < input.minLength) ok = false;
            input.style.borderColor = ok ? '' : '#ef4444';
            if (!ok) { valid = false; var err = document.createElement('div'); err.className = 'j-validation-error'; err.style.cssText = 'color:#ef4444;font-size:.78rem;margin-top:4px'; err.textContent = input.dataset.error || 'This field is required'; input.parentNode.appendChild(err); }
        });
        return valid;
    };

})(window);
