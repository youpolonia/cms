(function() {
    "use strict";

    /* Signal to parent that preview is ready */
    window.parent.postMessage({ type: "theme-studio-ready" }, "*");

    /* CSS variable mappings — must match generate_studio_css_overrides() */
    var brandMap = {
        "primary_color": "--primary",
        "secondary_color": "--secondary",
        "accent_color": "--accent",
        "dark_color": "--surface",
        "bg_color": "--background",
        "text_color": "--text"
    };

    var legacyAliases = {
        "--primary": "--color-primary",
        "--secondary": "--color-secondary",
        "--accent": "--color-accent",
        "--surface": "--color-surface",
        "--background": "--color-background",
        "--text": "--color-text"
    };

    var buttonsMap = {
        "border_radius": "--btn-radius",
        "padding_x": "--btn-padding-x",
        "padding_y": "--btn-padding-y",
        "font_weight": "--btn-font-weight"
    };

    var layoutMap = {
        "container_width": "--container-width",
        "section_spacing": "--section-spacing",
        "border_radius": "--border-radius"
    };

    var effectsMap = {
        "hover_scale": "--hover-scale",
        "transition_speed": "--transition-speed"
    };

    /* Get or create the override <style> element */
    var styleEl = document.getElementById("ts-live-overrides");
    if (!styleEl) {
        styleEl = document.createElement("style");
        styleEl.id = "ts-live-overrides";
        document.head.appendChild(styleEl);
    }

    function setVar(name, value) {
        document.documentElement.style.setProperty(name, value);
    }

    function setText(selectors, text) {
        document.querySelectorAll(selectors).forEach(function(el) { el.textContent = text; });
    }

    function setHref(selectors, url) {
        document.querySelectorAll(selectors).forEach(function(el) {
            if (el.tagName === "A") el.href = url;
        });
    }

    function applyValues(vals) {
        if (!vals || typeof vals !== "object") return;

        var customCss = "";

        /* Brand colors */
        if (vals.brand) {
            Object.keys(brandMap).forEach(function(key) {
                if (vals.brand[key]) {
                    var cssVar = brandMap[key];
                    setVar(cssVar, vals.brand[key]);
                    if (legacyAliases[cssVar]) setVar(legacyAliases[cssVar], vals.brand[key]);
                }
            });
            if (vals.brand.heading_font) setVar("--font-heading", "'" + vals.brand.heading_font + "', sans-serif");
            if (vals.brand.body_font) setVar("--font-family", "'" + vals.brand.body_font + "', sans-serif");

            /* Logo — handle both img and text-only logos */
            if (vals.brand.logo !== undefined) {
                document.querySelectorAll(".header-logo, .footer-logo, .site-logo, .navbar-brand").forEach(function(container) {
                    var img = container.querySelector("img");
                    var textEl = container.querySelector(".logo-text, span");
                    if (vals.brand.logo) {
                        if (img) { img.src = vals.brand.logo; img.style.display = ""; }
                        else {
                            img = document.createElement("img");
                            img.src = vals.brand.logo;
                            img.alt = (vals.brand.site_name || "Logo");
                            img.style.maxHeight = "40px";
                            container.insertBefore(img, container.firstChild);
                        }
                        if (textEl) textEl.style.display = "none";
                    } else {
                        if (img) img.style.display = "none";
                        if (textEl) textEl.style.display = "";
                    }
                });
            }
            /* Site name */
            if (vals.brand.site_name !== undefined) {
                setText(".site-name, .site-title, .navbar-brand span, .logo-text, .brand-name", vals.brand.site_name);
                document.querySelectorAll("title").forEach(function(el) {
                    el.textContent = vals.brand.site_name;
                });
            }
            /* Tagline */
            if (vals.brand.tagline !== undefined) {
                setText(".site-tagline, .tagline, .site-description", vals.brand.tagline);
            }
        }

        /* Typography */
        if (vals.typography) {
            if (vals.typography.heading_font) setVar("--font-heading", "'" + vals.typography.heading_font + "', sans-serif");
            if (vals.typography.body_font) setVar("--font-family", "'" + vals.typography.body_font + "', sans-serif");
            if (vals.typography.base_font_size) setVar("--font-size-base", vals.typography.base_font_size);
            if (vals.typography.line_height) setVar("--line-height", vals.typography.line_height);
            if (vals.typography.heading_weight) setVar("--font-weight-heading", vals.typography.heading_weight);
        }

        /* Buttons */
        if (vals.buttons) {
            Object.keys(buttonsMap).forEach(function(key) {
                if (vals.buttons[key]) setVar(buttonsMap[key], vals.buttons[key]);
            });
            if (vals.buttons.uppercase !== undefined) {
                setVar("--btn-text-transform", vals.buttons.uppercase ? "uppercase" : "none");
            }
        }

        /* Layout */
        if (vals.layout) {
            Object.keys(layoutMap).forEach(function(key) {
                if (vals.layout[key]) setVar(layoutMap[key], vals.layout[key]);
            });
        }

        /* Effects */
        if (vals.effects) {
            Object.keys(effectsMap).forEach(function(key) {
                if (vals.effects[key]) setVar(effectsMap[key], vals.effects[key]);
            });
            if (vals.effects.shadow_strength !== undefined && vals.effects.shadow_strength !== "") {
                var opacity = (parseFloat(vals.effects.shadow_strength) / 100).toFixed(2);
                setVar("--shadow", "0 1px 3px rgba(0,0,0," + opacity + ")");
                setVar("--shadow-lg", "0 10px 40px rgba(0,0,0," + opacity + ")");
            }
        }

        /* Header fields */
        if (vals.header) {
            var ctaSel = ".header-cta, .cta-button, nav .btn-primary, header .btn, a.cta, .nav-cta";
            if (vals.header.cta_text !== undefined) {
                setText(ctaSel, vals.header.cta_text);
            }
            if (vals.header.cta_link !== undefined) {
                setHref(ctaSel, vals.header.cta_link);
            }
            if (vals.header.show_cta !== undefined) {
                var show = vals.header.show_cta === true || vals.header.show_cta === "1" || vals.header.show_cta === 1;
                document.querySelectorAll(ctaSel).forEach(function(el) {
                    el.style.display = show ? "" : "none";
                });
            }
        }

        /* Hero fields */
        if (vals.hero) {
            if (vals.hero.headline !== undefined) {
                setText(".hero h1, .hero-title, .hero-headline, section.hero h1, .hero-content h1", vals.hero.headline);
            }
            if (vals.hero.subtitle !== undefined) {
                setText(".hero p, .hero-subtitle, .hero-description, section.hero p, .hero-content p", vals.hero.subtitle);
            }
            if (vals.hero.btn_text !== undefined) {
                setText(".hero .btn, .hero-actions .btn, .hero-content .btn, .hero-cta, .hero .btn-primary", vals.hero.btn_text);
            }
            if (vals.hero.btn_link !== undefined) {
                setHref(".hero .btn, .hero-actions a.btn, .hero-content a.btn, a.hero-cta, .hero a.btn-primary", vals.hero.btn_link);
            }
            if (vals.hero.bg_image !== undefined) {
                /* .hero-bg is a common pattern: absolute div with gradient background.
                   We must override the full 'background' shorthand, not just background-image,
                   otherwise the gradient layers take precedence. */
                var heroBg = document.querySelector(".hero-bg");
                if (heroBg) {
                    if (vals.hero.bg_image) {
                        heroBg.style.background = "url(" + vals.hero.bg_image + ") center/cover no-repeat";
                    } else {
                        heroBg.style.background = "";
                    }
                } else {
                    /* Fallback: apply directly to hero section */
                    document.querySelectorAll(".hero, section.hero, .hero-section").forEach(function(el) {
                        if (vals.hero.bg_image) {
                            el.style.backgroundImage = "url(" + vals.hero.bg_image + ")";
                            el.style.backgroundSize = "cover";
                            el.style.backgroundPosition = "center";
                        } else {
                            el.style.backgroundImage = "";
                        }
                    });
                }
            }
        }

        /* Footer fields */
        if (vals.footer) {
            if (vals.footer.copyright !== undefined) {
                setText(".footer-bottom p, .copyright, footer .copy", vals.footer.copyright);
            }
            if (vals.footer.description !== undefined) {
                setText(".footer-description, .footer-about p, .footer-text, footer .about-text", vals.footer.description);
            }
        }

        /* Custom CSS */
        if (vals.custom_css && vals.custom_css.css_code !== undefined) {
            customCss = vals.custom_css.css_code;
        }
        styleEl.textContent = customCss;
    }

    /* Listen for messages from Theme Studio parent */
    window.addEventListener("message", function(e) {
        if (e.data && e.data.type === "theme-studio-update") {
            applyValues(e.data.values);
        }
    });
})();
