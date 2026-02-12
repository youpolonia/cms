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
        "bg_color": ["--background", "--color-background", "--blog-bg"],
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
        /* Brand colors */
        if (vals.brand) {
            Object.keys(brandColorMap).forEach(function(key) {
                if (vals.brand[key]) {
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
    }

    /* ── Toggle visibility ────────────────────────────────── */

    function applyToggles(vals) {
        if (vals.header && vals.header.show_cta !== undefined) {
            var show = vals.header.show_cta === true || vals.header.show_cta === "1" || vals.header.show_cta === 1;
            document.querySelectorAll('[data-ts="header.cta_text"]').forEach(function(el) {
                el.style.display = show ? "" : "none";
            });
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
