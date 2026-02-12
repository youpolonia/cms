/**
 * Theme Studio — Live Preview Bridge
 *
 * Listens for postMessage from Theme Studio sidebar and applies changes
 * to the preview iframe in real-time.
 *
 * DATA ATTRIBUTES (theme templates mark editable elements):
 *   data-ts="section.field"       — text content (or img src for <img>)
 *   data-ts-bg="section.field"    — background image
 *   data-ts-href="section.field"  — link href
 *
 * CSS VARIABLES (applied universally, no data attributes needed):
 *   Brand colors, typography, buttons, layout, effects
 */
(function() {
    "use strict";

    /* Signal to parent that preview is ready */
    window.parent.postMessage({ type: "theme-studio-ready" }, "*");

    /* ── CSS Variable Mappings ────────────────────────────── */

    var brandColorMap = {
        "primary_color": ["--primary", "--color-primary", "--blog-primary"],
        "secondary_color": ["--secondary", "--color-secondary", "--blog-primary-light"],
        "accent_color": ["--accent", "--color-accent", "--blog-accent"],
        "dark_color": ["--surface", "--color-surface", "--blog-surface"],
        "bg_color": ["--background", "--color-background", "--color-bg", "--blog-bg"],
        "text_color": ["--text", "--color-text", "--blog-text"]
    };

    var simpleVarMap = {
        "buttons.border_radius": "--btn-radius",
        "buttons.padding_x": "--btn-padding-x",
        "buttons.padding_y": "--btn-padding-y",
        "buttons.font_weight": "--btn-font-weight",
        "layout.container_width": "--container-width",
        "layout.section_spacing": "--section-spacing",
        "layout.border_radius": "--border-radius",
        "effects.hover_scale": "--hover-scale",
        "effects.transition_speed": "--transition-speed"
    };

    /* Override <style> for custom CSS */
    var styleEl = document.getElementById("ts-live-overrides");
    if (!styleEl) {
        styleEl = document.createElement("style");
        styleEl.id = "ts-live-overrides";
        document.head.appendChild(styleEl);
    }

    function setVar(name, value) {
        document.documentElement.style.setProperty(name, value);
    }

    /* ── Data-Attribute Driven Updates ────────────────────── */

    function applyField(fieldPath, value) {
        if (value === undefined) return;

        /* Text content / image src via data-ts */
        document.querySelectorAll('[data-ts="' + fieldPath + '"]').forEach(function(el) {
            if (el.tagName === "IMG") {
                el.src = value || "";
            } else if (el.tagName === "INPUT" || el.tagName === "TEXTAREA") {
                el.value = value || "";
            } else {
                /* Logo container special case: toggle img vs text */
                if (fieldPath === "brand.logo") {
                    applyLogo(el, value);
                } else {
                    el.textContent = value;
                }
            }
        });

        /* Background image via data-ts-bg */
        document.querySelectorAll('[data-ts-bg="' + fieldPath + '"]').forEach(function(el) {
            if (value) {
                el.style.background = "url(" + value + ") center/cover no-repeat";
            } else {
                el.style.background = "";
            }
        });

        /* Link href via data-ts-href */
        document.querySelectorAll('[data-ts-href="' + fieldPath + '"]').forEach(function(el) {
            if (el.tagName === "A") el.href = value || "#";
        });
    }

    function applyLogo(container, logoUrl) {
        var img = container.querySelector("img");
        var textEl = container.querySelector(".logo-text, span[data-ts]");
        if (!textEl) textEl = container.querySelector("span");

        if (logoUrl) {
            if (img) {
                img.src = logoUrl;
                img.style.display = "";
            } else {
                img = document.createElement("img");
                img.src = logoUrl;
                img.alt = "Logo";
                img.style.maxHeight = "40px";
                container.insertBefore(img, container.firstChild);
            }
            if (textEl) textEl.style.display = "none";
        } else {
            if (img) img.style.display = "none";
            if (textEl) textEl.style.display = "";
        }
    }

    /* ── CSS Variable Updates ─────────────────────────────── */

    function applyCssVariables(vals) {
        /* Brand colors — use !== undefined to allow clearing values */
        if (vals.brand) {
            Object.keys(brandColorMap).forEach(function(key) {
                if (vals.brand[key] !== undefined && vals.brand[key] !== null && vals.brand[key] !== "") {
                    brandColorMap[key].forEach(function(v) { setVar(v, vals.brand[key]); });
                }
            });
            if (vals.brand.heading_font) setVar("--font-heading", "'" + vals.brand.heading_font + "', sans-serif");
            if (vals.brand.body_font) setVar("--font-family", "'" + vals.brand.body_font + "', sans-serif");
        }

        /* Typography */
        if (vals.typography) {
            if (vals.typography.heading_font) setVar("--font-heading", "'" + vals.typography.heading_font + "', sans-serif");
            if (vals.typography.body_font) setVar("--font-family", "'" + vals.typography.body_font + "', sans-serif");
            if (vals.typography.base_font_size) setVar("--font-size-base", vals.typography.base_font_size);
            if (vals.typography.line_height) setVar("--line-height", vals.typography.line_height);
            if (vals.typography.heading_weight) setVar("--font-weight-heading", vals.typography.heading_weight);
        }

        /* Simple 1:1 variable mappings (buttons, layout, effects) */
        Object.keys(simpleVarMap).forEach(function(path) {
            var parts = path.split(".");
            if (vals[parts[0]] && vals[parts[0]][parts[1]]) {
                setVar(simpleVarMap[path], vals[parts[0]][parts[1]]);
            }
        });

        /* Buttons special: uppercase toggle */
        if (vals.buttons && vals.buttons.uppercase !== undefined) {
            setVar("--btn-text-transform", vals.buttons.uppercase ? "uppercase" : "none");
        }

        /* Effects special: shadow strength → computed shadow values */
        if (vals.effects && vals.effects.shadow_strength !== undefined && vals.effects.shadow_strength !== "") {
            var opacity = (parseFloat(vals.effects.shadow_strength) / 100).toFixed(2);
            setVar("--shadow", "0 1px 3px rgba(0,0,0," + opacity + ")");
            setVar("--shadow-lg", "0 10px 40px rgba(0,0,0," + opacity + ")");
        }

        /* Custom CSS injection */
        if (vals.custom_css && vals.custom_css.css_code !== undefined) {
            styleEl.textContent = vals.custom_css.css_code;
        }

        /* Gradient */
        if (vals.effects && vals.effects.gradient) {
            setVar("--gradient", vals.effects.gradient);
        }

        /* Box Shadow (custom) */
        if (vals.effects && vals.effects.box_shadow) {
            setVar("--shadow-custom", vals.effects.box_shadow);
        }

        /* Spacing / Box Model */
        if (vals.layout && vals.layout.section_padding) {
            try {
                var sp = typeof vals.layout.section_padding === "string" ? JSON.parse(vals.layout.section_padding) : vals.layout.section_padding;
                if (sp) {
                    setVar("--section-margin-top", (sp.mt || 0) + "px");
                    setVar("--section-margin-right", (sp.mr || 0) + "px");
                    setVar("--section-margin-bottom", (sp.mb || 0) + "px");
                    setVar("--section-margin-left", (sp.ml || 0) + "px");
                    setVar("--section-padding-top", (sp.pt || 20) + "px");
                    setVar("--section-padding-right", (sp.pr || 20) + "px");
                    setVar("--section-padding-bottom", (sp.pb || 20) + "px");
                    setVar("--section-padding-left", (sp.pl || 20) + "px");
                }
            } catch(e) { /* ignore parse errors */ }
        }

        /* Color mode overlay — independent from brand fields */
        var modeStyle = document.getElementById("ts-color-mode-css");
        if (!modeStyle) {
            modeStyle = document.createElement("style");
            modeStyle.id = "ts-color-mode-css";
            document.head.appendChild(modeStyle);
        }
        var cmode = (vals.brand && vals.brand.color_mode) ? vals.brand.color_mode : "default";
        var primary = (vals.brand && vals.brand.primary_color) || "var(--primary, #6366f1)";

        if (cmode === "dark") {
            modeStyle.textContent = [
                "/* Theme Studio — Dark Mode Overlay */",
                ":root { --background:#0f172a; --color-background:#0f172a; --color-bg:#0f172a; --blog-bg:#0f172a; --surface:#1e293b; --color-surface:#1e293b; --color-surface-elevated:#1e293b; --blog-surface:#1e293b; --text:#e2e8f0; --color-text:#e2e8f0; --color-text-heading:#f1f5f9; --color-text-muted:#94a3b8; --blog-text:#e2e8f0; --color-border:rgba(255,255,255,0.08); }",
                "html, body { background-color:#0f172a !important; color:#e2e8f0 !important; }",
                "section, .section { background-color:#0f172a !important; color:#e2e8f0 !important; }",
                "h1,h2,h3,h4,h5,h6 { color:#f1f5f9 !important; }",
                "p,span,li,label,blockquote,figcaption,small,em,strong,td,th,dt,dd,address { color:#e2e8f0 !important; }",
                "a:not(.btn):not([class*='btn']) { color:" + primary + " !important; }",
                ".card,[class*='card'],[class*='item'],[class*='box'],[class*='testimonial'],[class*='service'] { background-color:#1e293b !important; color:#e2e8f0 !important; border-color:rgba(255,255,255,0.08) !important; }",
                "header,nav,.header,.navbar,[class*='header'],[class*='nav']:not(section) { background-color:#1e293b !important; color:#e2e8f0 !important; }",
                "footer,.footer,[class*='footer'] { background-color:#1e293b !important; color:#cbd5e1 !important; }",
                "input,textarea,select { background-color:#1e293b !important; color:#e2e8f0 !important; border-color:rgba(255,255,255,0.12) !important; }",
                ".btn-outline,[class*='btn-outline'] { border-color:rgba(255,255,255,0.25) !important; color:#e2e8f0 !important; }",
                "hr { border-color:rgba(255,255,255,0.08) !important; }",
            ].join("\n");
        } else if (cmode === "light") {
            modeStyle.textContent = [
                "/* Theme Studio — Light Mode Overlay */",
                ":root { --background:#ffffff; --color-background:#ffffff; --color-bg:#ffffff; --blog-bg:#ffffff; --surface:#f8fafc; --color-surface:#f8fafc; --color-surface-elevated:#ffffff; --blog-surface:#f1f5f9; --text:#1e293b; --color-text:#1e293b; --color-text-heading:#0f172a; --color-text-muted:#64748b; --blog-text:#1e293b; --color-border:rgba(0,0,0,0.1); --color-bg-alt:#f8fafc; --color-surface-hover:#f1f5f9; --color-border-hover:rgba(0,0,0,0.15); }",
                "html, body { background-color:#ffffff !important; color:#1e293b !important; }",
                "section, .section { background-color:#ffffff !important; color:#1e293b !important; }",
                "h1,h2,h3,h4,h5,h6 { color:#0f172a !important; }",
                "p,span,li,label,blockquote,figcaption,small,em,strong,td,th,dt,dd,address { color:#1e293b !important; }",
                "a:not(.btn):not([class*='btn']) { color:" + primary + " !important; }",
                ".card,[class*='card'],[class*='item'],[class*='box'],[class*='testimonial'],[class*='service'] { background-color:#ffffff !important; color:#1e293b !important; border-color:rgba(0,0,0,0.1) !important; box-shadow:0 1px 3px rgba(0,0,0,0.08) !important; }",
                "header,nav,.header,.navbar,[class*='header'],[class*='nav']:not(section) { background-color:#ffffff !important; color:#1e293b !important; border-bottom-color:rgba(0,0,0,0.1) !important; }",
                "footer,.footer,[class*='footer'] { background-color:#f8fafc !important; color:#475569 !important; }",
                "input,textarea,select { background-color:#ffffff !important; color:#1e293b !important; border-color:rgba(0,0,0,0.15) !important; }",
                ".btn-outline,[class*='btn-outline'] { border-color:rgba(0,0,0,0.2) !important; color:#1e293b !important; }",
                "hr { border-color:rgba(0,0,0,0.1) !important; }",
            ].join("\n");
        } else {
            /* default = no overlay, theme's own colors */
            modeStyle.textContent = "";
        }
    }

    /* ── Toggle visibility ────────────────────────────────── */

    function applyToggles(vals) {
        if (vals.header && vals.header.show_cta !== undefined) {
            var show = vals.header.show_cta === true || vals.header.show_cta === "1" || vals.header.show_cta === 1;
            document.querySelectorAll('[data-ts="header.cta_text"]').forEach(function(el) {
                el.style.display = show ? "" : "none";
            });
        }

        /* Announcement bar */
        if (vals.announcement) {
            var bar = document.querySelector('.ts-announcement-bar');
            var enabled = vals.announcement.enabled === true || vals.announcement.enabled === "1" || vals.announcement.enabled === 1;
            var text = vals.announcement.text || '';
            if (enabled && text) {
                if (!bar) {
                    bar = document.createElement('div');
                    bar.className = 'ts-announcement-bar';
                    bar.style.cssText = 'text-align:center;padding:10px 20px;font-size:14px;font-weight:500;position:relative;z-index:9998';
                    document.body.insertBefore(bar, document.body.firstChild);
                }
                bar.style.background = vals.announcement.bg_color || '#6366f1';
                bar.style.color = vals.announcement.text_color || '#ffffff';
                var link = vals.announcement.link;
                bar.innerHTML = link ? '<a href="' + link + '" style="color:inherit;text-decoration:underline">' + text + '</a>' : text;
                bar.style.display = '';
            } else if (bar) {
                bar.style.display = 'none';
            }
        }

        /* Favicon live update */
        if (vals.brand && vals.brand.favicon) {
            var link = document.querySelector('link[rel="icon"]');
            if (!link) { link = document.createElement('link'); link.rel = 'icon'; document.head.appendChild(link); }
            link.href = vals.brand.favicon;
        }
    }

    /* ── Main: Apply All Values ───────────────────────────── */

    function applyValues(vals) {
        if (!vals || typeof vals !== "object") return;

        /* 1. CSS variables (universal, no data-ts needed) */
        applyCssVariables(vals);

        /* 2. Content fields via data-ts attributes */
        Object.keys(vals).forEach(function(section) {
            if (typeof vals[section] !== "object") return;
            Object.keys(vals[section]).forEach(function(field) {
                applyField(section + "." + field, vals[section][field]);
            });
        });

        /* 3. Toggle visibility */
        applyToggles(vals);
    }

    /* ── Listen for Messages from Theme Studio ────────────── */

    window.addEventListener("message", function(e) {
        if (e.data && e.data.type === "theme-studio-update") {
            applyValues(e.data.values);
        }
    });
})();
