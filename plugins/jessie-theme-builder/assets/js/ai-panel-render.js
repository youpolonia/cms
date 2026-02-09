/**
 * JTB AI Panel - Module Renderer
 * Complete rendering system for all 80 modules
 *
 * This file contains renderPreview() and renderModuleHTML() functions
 * with full support for dynamic data from API
 */

// ========================================
// Preview Rendering
// ========================================

/**
 * Render complete layout preview
 * Uses actual data from generated layout - NO HARDCODED CONTENT
 */
function renderPreview(layout) {
    if (!layout?.sections?.length) {
        return '<div style="padding:60px 20px;text-align:center;color:#6b7280;font-family:Inter,sans-serif;">No content generated</div>';
    }

    // CSS Styles for preview
    const styles = `
        <style>
            .jtb-preview { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; color: #1f2937; transform-origin: top left; }
            .jtb-preview * { box-sizing: border-box; margin: 0; padding: 0; }
            .jtb-preview-section { position: relative; overflow: hidden; clear: both; }
            .jtb-preview-container { max-width: 100%; margin: 0 auto; padding: 0 20px; }
            .jtb-preview-row { display: flex; flex-wrap: nowrap; margin: 0 -10px 20px; gap: 0; }
            .jtb-preview-row:last-child { margin-bottom: 0; }
            .jtb-preview-col { padding: 0 10px; min-height: 1px; flex-shrink: 0; }

            /* Column widths */
            .jtb-col-12 { width: 100%; }
            .jtb-col-6 { width: 50%; }
            .jtb-col-4 { width: 33.333%; }
            .jtb-col-3 { width: 25%; }
            .jtb-col-2 { width: 16.666%; }

            /* Card base */
            .jtb-card {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
                padding: 32px 24px;
                height: 100%;
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .jtb-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
            }

            /* Typography */
            .jtb-preview h1 { font-size: 48px; font-weight: 800; line-height: 1.1; margin-bottom: 24px; }
            .jtb-preview h2 { font-size: 36px; font-weight: 700; line-height: 1.2; margin-bottom: 20px; }
            .jtb-preview h3 { font-size: 28px; font-weight: 700; line-height: 1.3; margin-bottom: 16px; }
            .jtb-preview h4 { font-size: 20px; font-weight: 600; line-height: 1.4; margin-bottom: 12px; }
            .jtb-preview p { margin-bottom: 16px; line-height: 1.7; }

            /* Buttons */
            .jtb-btn {
                display: inline-block;
                padding: 14px 28px;
                font-size: 16px;
                font-weight: 600;
                text-decoration: none;
                border-radius: 8px;
                transition: all 0.2s;
                cursor: pointer;
                border: none;
            }
            .jtb-btn-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; }
            .jtb-btn-secondary { background: #f3f4f6; color: #1f2937; }
            .jtb-btn-white { background: #fff; color: #3b82f6; }

            /* Avatar */
            .jtb-avatar {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                overflow: hidden;
                border: 3px solid #e5e7eb;
            }
            .jtb-avatar img { width: 100%; height: 100%; object-fit: cover; object-position: center top; }

            /* Icon box */
            .jtb-icon-box {
                width: 64px;
                height: 64px;
                border-radius: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 28px;
            }

            /* Form elements */
            .jtb-input {
                width: 100%;
                padding: 14px 18px;
                font-size: 16px;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                margin-bottom: 16px;
                font-family: inherit;
            }
            .jtb-textarea { min-height: 120px; resize: vertical; }

            /* Grid layouts */
            .jtb-grid { display: grid; gap: 24px; }
            .jtb-grid-2 { grid-template-columns: repeat(2, 1fr); }
            .jtb-grid-3 { grid-template-columns: repeat(3, 1fr); }
            .jtb-grid-4 { grid-template-columns: repeat(4, 1fr); }
        </style>
    `;

    let html = styles + '<div class="jtb-preview">';

    // Render each section
    layout.sections.forEach((section, sIndex) => {
        const sAttrs = section.attrs || {};
        const bg = sAttrs.background_color || (sIndex % 2 === 0 ? '#ffffff' : '#f9fafb');
        const pTop = sAttrs.padding?.top || 60;
        const pBottom = sAttrs.padding?.bottom || 60;

        html += `<div class="jtb-preview-section" style="background:${bg};padding:${pTop}px 0 ${pBottom}px;">`;
        html += '<div class="jtb-preview-container">';

        /**
         * Convert JTB column width format (e.g., '1_2', '1_3', '2_3') to CSS percentage
         */
        function getColumnWidthPercent(width) {
            const widthMap = {
                '1_1': '100%', '1_2': '50%', '1_3': '33.333%', '2_3': '66.666%',
                '1_4': '25%', '3_4': '75%', '1_5': '20%', '2_5': '40%', '3_5': '60%',
                '4_5': '80%', '1_6': '16.666%', '5_6': '83.333%'
            };
            return widthMap[width] || '100%';
        }

        // Render rows
        const rows = section.children || [];
        rows.forEach(row => {
            const cols = row.children || [];

            html += '<div class="jtb-preview-row">';

            cols.forEach(col => {
                // Get column width from attrs - JTB format is '1_2', '1_3', etc.
                const colWidth = col.attrs?.width || '1_1';
                const widthPercent = getColumnWidthPercent(colWidth);

                html += `<div class="jtb-preview-col" style="width:${widthPercent};">`;

                // Render modules
                const modules = col.children || [];
                modules.forEach(mod => {
                    html += renderModuleHTML(mod);
                });

                html += '</div>';
            });

            html += '</div>';
        });

        html += '</div></div>';
    });

    html += '</div>';
    return html;
}

// ========================================
// Module Rendering - ALL 80 MODULES
// ========================================

/**
 * Icon mapping for common icons
 */
const ICON_MAP = {
    'users': '👥', 'award': '🏆', 'headphones': '🎧', 'star': '⭐',
    'check': '✓', 'shield': '🛡️', 'zap': '⚡', 'heart': '❤️',
    'globe': '🌐', 'clock': '⏰', 'mail': '✉️', 'phone': '📞',
    'map-pin': '📍', 'calendar': '📅', 'settings': '⚙️', 'lock': '🔒',
    'eye': '👁️', 'home': '🏠', 'user': '👤', 'search': '🔍',
    'play': '▶️', 'pause': '⏸️', 'download': '⬇️', 'upload': '⬆️',
    'trash': '🗑️', 'edit': '✏️', 'plus': '➕', 'minus': '➖',
    'arrow-right': '→', 'arrow-left': '←', 'arrow-up': '↑', 'arrow-down': '↓'
};

/**
 * Render single module as HTML
 * @param {Object} module - Module data with type and attrs
 * @returns {string} HTML string
 */
function renderModuleHTML(module) {
    const type = module.type || 'unknown';
    const attrs = module.attrs || {};
    const children = module.children || [];

    // Debug logging
    // console.log removed

    switch (type) {
        // ========================================
        // STRUCTURE MODULES
        // ========================================

        case 'section':
        case 'row':
        case 'column':
            // These are handled by renderPreview, but just in case
            let structHtml = `<div class="jtb-${type}">`;
            children.forEach(child => { structHtml += renderModuleHTML(child); });
            structHtml += '</div>';
            return structHtml;

        // ========================================
        // CONTENT MODULES
        // ========================================

        case 'heading':
            const hLevel = attrs.level || 'h2';
            const hSize = attrs.font_size || (hLevel === 'h1' ? 48 : hLevel === 'h2' ? 36 : hLevel === 'h3' ? 28 : 20);
            const hColor = attrs.text_color || '#111827';
            const hAlign = attrs.text_align || 'left';
            const hWeight = attrs.font_weight || '700';
            return `<${hLevel} style="font-size:${hSize}px;color:${hColor};text-align:${hAlign};font-weight:${hWeight};line-height:1.2;margin-bottom:20px;">${attrs.text || ''}</${hLevel}>`;

        case 'text':
            const tContent = attrs.content || '';
            const tColor = attrs.text_color || '#4b5563';
            const tSize = attrs.font_size || 16;
            return `<div style="color:${tColor};font-size:${tSize}px;line-height:1.7;margin-bottom:20px;">${tContent}</div>`;

        case 'image':
            const imgSrc = attrs.src || attrs.image_url || '';
            const imgAlt = attrs.alt || '';
            const imgRadius = attrs.border_radius?.top_left || 12;
            if (imgSrc) {
                return `<div style="margin-bottom:20px;"><img src="${imgSrc}" alt="${imgAlt}" style="max-width:100%;height:auto;border-radius:${imgRadius}px;display:block;" /></div>`;
            }
            return `<div style="background:#f3f4f6;height:250px;border-radius:${imgRadius}px;display:flex;align-items:center;justify-content:center;color:#9ca3af;margin-bottom:20px;">Image</div>`;

        case 'button':
            const btnBg = attrs.background_color || '#3b82f6';
            const btnColor = attrs.text_color || '#ffffff';
            const btnText = attrs.text || 'Button';
            const btnAlign = attrs.align || 'left';
            const btnRadius = attrs.border_radius?.top_left || 8;
            return `<div style="margin:20px 0;text-align:${btnAlign};">
                <a href="${attrs.link_url || '#'}" class="jtb-btn" style="background:${btnBg};color:${btnColor};border-radius:${btnRadius}px;">${btnText}</a>
            </div>`;

        case 'blurb':
            const blurbIcon = attrs.font_icon || 'star';
            const blurbIconColor = attrs.icon_color || '#3b82f6';
            const blurbTitle = attrs.title || '';
            const blurbContent = (attrs.content || '').replace(/<[^>]*>/g, '');
            const blurbAlign = attrs.text_orientation || 'center';
            return `
                <div class="jtb-card" style="text-align:${blurbAlign};">
                    <div class="jtb-icon-box" style="background:linear-gradient(135deg,${blurbIconColor}15,${blurbIconColor}25);margin:${blurbAlign === 'center' ? '0 auto 20px' : '0 0 20px'};">
                        ${ICON_MAP[blurbIcon] || '⭐'}
                    </div>
                    <h4 style="color:#111827;font-size:20px;font-weight:700;margin-bottom:12px;">${blurbTitle}</h4>
                    <p style="color:#6b7280;font-size:15px;line-height:1.7;margin:0;">${blurbContent}</p>
                </div>`;

        case 'icon':
            const iconName = attrs.font_icon || 'star';
            const iconSize = attrs.icon_size || 48;
            const iconColor = attrs.icon_color || '#3b82f6';
            return `<div style="text-align:center;font-size:${iconSize}px;color:${iconColor};margin:20px 0;">${ICON_MAP[iconName] || '⭐'}</div>`;

        case 'divider':
            const divWeight = attrs.divider_weight || '1px';
            const divStyle = attrs.divider_style || 'solid';
            const divColor = attrs.divider_color || '#e5e7eb';
            return `<hr style="border:none;border-top:${divWeight} ${divStyle} ${divColor};margin:30px 0;" />`;

        case 'code':
            const codeContent = attrs.content || '// Code here';
            return `<pre style="background:#1e293b;color:#e2e8f0;padding:20px;border-radius:8px;overflow-x:auto;font-family:monospace;font-size:14px;margin:20px 0;"><code>${codeContent}</code></pre>`;

        case 'cta':
            const ctaBg = attrs.promo_color || attrs.background_color || '#3b82f6';
            const ctaTitle = attrs.title || '';
            const ctaContent = (attrs.content || '').replace(/<[^>]*>/g, '');
            const ctaBtn = attrs.button_text || 'Get Started';
            return `
                <div style="text-align:center;padding:60px 40px;background:${ctaBg};border-radius:16px;">
                    ${ctaTitle ? `<h3 style="color:#fff;font-size:32px;font-weight:700;margin-bottom:16px;">${ctaTitle}</h3>` : ''}
                    ${ctaContent ? `<p style="color:rgba(255,255,255,0.9);margin-bottom:24px;max-width:600px;margin-left:auto;margin-right:auto;line-height:1.6;">${ctaContent}</p>` : ''}
                    <a href="${attrs.link_url || '#'}" class="jtb-btn jtb-btn-white">${ctaBtn}</a>
                </div>`;

        case 'testimonial':
            const testPortrait = attrs.portrait_url || '';
            const testContent = (attrs.content || '').replace(/<[^>]*>/g, '');
            const testAuthor = attrs.author || '';
            const testJob = attrs.job_title || '';
            const testCompany = attrs.company || '';
            return `
                <div class="jtb-card" style="text-align:center;">
                    ${testPortrait ? `
                        <div class="jtb-avatar" style="margin:0 auto 16px;">
                            <img src="${testPortrait}" alt="${testAuthor}" onerror="this.parentElement.style.background='#e5e7eb';this.style.display='none'" />
                        </div>
                    ` : '<div style="width:80px;height:80px;border-radius:50%;background:#e5e7eb;margin:0 auto 16px;"></div>'}
                    <div style="font-size:28px;color:#3b82f6;margin-bottom:12px;font-family:Georgia,serif;">"</div>
                    <p style="color:#374151;font-style:italic;line-height:1.8;margin-bottom:20px;font-size:15px;">${testContent || 'Great experience!'}</p>
                    ${testAuthor ? `<div style="font-weight:700;color:#111827;font-size:16px;">${testAuthor}</div>` : ''}
                    ${(testJob || testCompany) ? `<div style="color:#6b7280;font-size:13px;">${testJob}${testJob && testCompany ? ', ' : ''}${testCompany}</div>` : ''}
                </div>`;

        case 'team_member':
            const tmImage = attrs.image || attrs.photo_url || '';
            const tmName = attrs.name || '';
            const tmPosition = attrs.position || '';
            const tmBio = (attrs.content || attrs.bio || '').replace(/<[^>]*>/g, '');
            return `
                <div class="jtb-card" style="text-align:center;">
                    ${tmImage ? `
                        <div style="width:120px;height:120px;border-radius:50%;overflow:hidden;margin:0 auto 16px;border:4px solid #f3f4f6;">
                            <img src="${tmImage}" alt="${tmName}" style="width:100%;height:100%;object-fit:cover;object-position:center top;" />
                        </div>
                    ` : '<div style="width:120px;height:120px;border-radius:50%;background:#e5e7eb;margin:0 auto 16px;"></div>'}
                    ${tmName ? `<h4 style="color:#111827;font-size:20px;font-weight:700;margin-bottom:4px;">${tmName}</h4>` : ''}
                    ${tmPosition ? `<div style="color:#3b82f6;font-size:14px;font-weight:500;margin-bottom:12px;">${tmPosition}</div>` : ''}
                    ${tmBio ? `<p style="color:#6b7280;font-size:14px;line-height:1.7;margin:0;">${tmBio}</p>` : ''}
                </div>`;

        case 'pricing_table':
            const ptTitle = attrs.title || 'Plan';
            const ptPrice = attrs.price || '$0';
            const ptPeriod = attrs.period || '/month';
            const ptFeatures = (attrs.features || '').split('\n').filter(f => f.trim());
            const ptBtn = attrs.button_text || 'Get Started';
            const ptFeatured = attrs.featured;
            const ptShadow = ptFeatured ? '0 25px 50px -12px rgba(59,130,246,0.25)' : '';
            const ptBorder = ptFeatured ? '2px solid #3b82f6' : '1px solid #e5e7eb';
            return `
                <div class="jtb-card" style="text-align:center;${ptFeatured ? `border:${ptBorder};box-shadow:${ptShadow};transform:scale(1.02);` : `border:${ptBorder};`}">
                    ${ptFeatured ? '<div style="background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;padding:6px 16px;border-radius:20px;font-size:11px;font-weight:700;display:inline-block;margin-bottom:16px;text-transform:uppercase;">POPULAR</div>' : ''}
                    <h4 style="color:#111827;font-size:24px;font-weight:700;margin-bottom:8px;">${ptTitle}</h4>
                    <div style="margin-bottom:24px;">
                        <span style="font-size:48px;font-weight:800;color:#111827;">${ptPrice}</span>
                        <span style="color:#6b7280;font-size:16px;">${ptPeriod}</span>
                    </div>
                    <ul style="list-style:none;margin-bottom:28px;text-align:left;">
                        ${ptFeatures.map(f => `<li style="padding:10px 0;border-bottom:1px solid #f3f4f6;color:#4b5563;font-size:15px;"><span style="color:#10B981;margin-right:8px;">✓</span>${f}</li>`).join('')}
                    </ul>
                    <a href="${attrs.link_url || '#'}" class="jtb-btn ${ptFeatured ? 'jtb-btn-primary' : 'jtb-btn-secondary'}" style="display:block;text-align:center;">${ptBtn}</a>
                </div>`;

        case 'number_counter':
        case 'circle_counter':
        case 'bar_counter':
            const ncNumber = attrs.number || attrs.percent || '100';
            const ncSuffix = attrs.suffix || (type === 'circle_counter' || type === 'bar_counter' ? '%' : '+');
            const ncPrefix = attrs.prefix || '';
            const ncTitle = attrs.title || '';
            const ncColor = attrs.bar_color || attrs.circle_color || '#3b82f6';

            if (type === 'circle_counter') {
                return `
                    <div style="text-align:center;padding:30px;">
                        <div style="width:120px;height:120px;border-radius:50%;border:8px solid #e5e7eb;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;position:relative;">
                            <div style="position:absolute;inset:0;border-radius:50%;border:8px solid transparent;border-top-color:${ncColor};"></div>
                            <span style="font-size:32px;font-weight:700;color:${ncColor};">${ncNumber}${ncSuffix}</span>
                        </div>
                        ${ncTitle ? `<div style="color:#6b7280;font-size:16px;">${ncTitle}</div>` : ''}
                    </div>`;
            }
            if (type === 'bar_counter') {
                return `
                    <div style="padding:20px 0;">
                        ${ncTitle ? `<div style="display:flex;justify-content:space-between;margin-bottom:8px;"><span style="color:#111827;font-weight:600;">${ncTitle}</span><span style="color:${ncColor};font-weight:700;">${ncNumber}${ncSuffix}</span></div>` : ''}
                        <div style="height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;">
                            <div style="width:${ncNumber}%;height:100%;background:${ncColor};border-radius:4px;"></div>
                        </div>
                    </div>`;
            }
            // number_counter
            return `
                <div style="text-align:center;padding:30px;">
                    <div style="font-size:48px;font-weight:700;color:${ncColor};">${ncPrefix}${ncNumber}${ncSuffix}</div>
                    ${ncTitle ? `<div style="color:#6b7280;font-size:16px;margin-top:8px;">${ncTitle}</div>` : ''}
                </div>`;

        case 'countdown':
            return `
                <div style="display:flex;justify-content:center;gap:20px;padding:30px;">
                    ${['Days', 'Hours', 'Mins', 'Secs'].map((label, i) => `
                        <div style="text-align:center;">
                            <div style="font-size:48px;font-weight:700;color:#111827;">${[30, 12, 45, 30][i]}</div>
                            <div style="color:#6b7280;font-size:14px;">${label}</div>
                        </div>
                    `).join('')}
                </div>`;

        case 'social_follow':
        case 'social_icons':
            const socialNetworks = attrs.networks || ['facebook', 'twitter', 'instagram', 'linkedin'];
            const socialColors = {facebook: '#1877F2', twitter: '#1DA1F2', instagram: '#E4405F', linkedin: '#0077B5', youtube: '#FF0000'};
            const socialIcons = {facebook: 'f', twitter: '𝕏', instagram: '📷', linkedin: 'in', youtube: '▶'};
            return `
                <div style="display:flex;gap:12px;justify-content:${attrs.align || 'center'};margin:20px 0;">
                    ${(Array.isArray(socialNetworks) ? socialNetworks : ['facebook', 'twitter', 'instagram', 'linkedin']).map(net => `
                        <a href="#" style="width:40px;height:40px;background:${socialColors[net] || '#6b7280'};border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-weight:bold;">${socialIcons[net] || net[0]}</a>
                    `).join('')}
                </div>`;

        case 'comments':
            return `
                <div style="padding:20px;background:#f9fafb;border-radius:12px;">
                    <h4 style="color:#111827;font-size:20px;margin-bottom:20px;">Comments</h4>
                    <div style="color:#6b7280;">Comments section placeholder</div>
                </div>`;

        case 'sidebar':
            return `
                <div style="padding:20px;background:#f9fafb;border-radius:12px;">
                    <h4 style="color:#111827;font-size:18px;margin-bottom:16px;">Sidebar</h4>
                    <div style="color:#6b7280;">Sidebar content placeholder</div>
                </div>`;

        case 'post_navigation':
            return `
                <div style="display:flex;justify-content:space-between;padding:20px 0;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;">
                    <a href="#" style="color:#3b82f6;text-decoration:none;">← Previous Post</a>
                    <a href="#" style="color:#3b82f6;text-decoration:none;">Next Post →</a>
                </div>`;

        case 'shop':
            return `
                <div class="jtb-grid jtb-grid-4" style="margin:20px 0;">
                    ${[1,2,3,4].map(i => `
                        <div class="jtb-card" style="padding:0;overflow:hidden;">
                            <div style="height:200px;background:#f3f4f6;"></div>
                            <div style="padding:16px;">
                                <h4 style="font-size:16px;color:#111827;margin-bottom:8px;">Product ${i}</h4>
                                <div style="color:#3b82f6;font-weight:700;">$${(19.99 * i).toFixed(2)}</div>
                            </div>
                        </div>
                    `).join('')}
                </div>`;

        // ========================================
        // INTERACTIVE MODULES
        // ========================================

        case 'accordion':
            let accHtml = '<div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;margin:20px 0;">';
            if (children.length > 0) {
                children.forEach((item, idx) => {
                    const itemAttrs = item.attrs || {};
                    const itemTitle = itemAttrs.title || `Item ${idx + 1}`;
                    const itemContent = (itemAttrs.content || '').replace(/<[^>]*>/g, '');
                    accHtml += `
                        <div style="${idx > 0 ? 'border-top:1px solid #e5e7eb;' : ''}">
                            <div style="padding:16px 20px;background:#f9fafb;font-weight:600;color:#111827;cursor:pointer;">${itemTitle}</div>
                            <div style="padding:16px 20px;color:#4b5563;line-height:1.6;${idx > 0 ? 'display:none;' : ''}">${itemContent}</div>
                        </div>`;
                });
            } else {
                accHtml += '<div style="padding:20px;color:#6b7280;">Accordion placeholder</div>';
            }
            accHtml += '</div>';
            return accHtml;

        case 'accordion_item':
            const aiTitle = attrs.title || 'Accordion Item';
            const aiContent = (attrs.content || '').replace(/<[^>]*>/g, '');
            return `
                <div style="border-bottom:1px solid #e5e7eb;">
                    <div style="padding:16px 20px;background:#f9fafb;font-weight:600;color:#111827;">${aiTitle}</div>
                    <div style="padding:16px 20px;color:#4b5563;line-height:1.6;">${aiContent}</div>
                </div>`;

        case 'tabs':
            let tabsHtml = '<div style="margin:20px 0;">';
            const tabTitles = children.map((tab, i) => tab.attrs?.title || `Tab ${i + 1}`);
            tabsHtml += `<div style="display:flex;border-bottom:2px solid #e5e7eb;margin-bottom:20px;">`;
            tabTitles.forEach((title, i) => {
                tabsHtml += `<div style="padding:12px 24px;cursor:pointer;${i === 0 ? 'border-bottom:2px solid #3b82f6;margin-bottom:-2px;color:#3b82f6;font-weight:600;' : 'color:#6b7280;'}">${title}</div>`;
            });
            tabsHtml += '</div>';
            if (children.length > 0) {
                const firstContent = (children[0].attrs?.content || '').replace(/<[^>]*>/g, '');
                tabsHtml += `<div style="color:#4b5563;line-height:1.6;">${firstContent}</div>`;
            }
            tabsHtml += '</div>';
            return tabsHtml;

        case 'tabs_item':
            return `<div style="color:#4b5563;line-height:1.6;">${(attrs.content || '').replace(/<[^>]*>/g, '')}</div>`;

        case 'toggle':
            const togTitle = attrs.title || 'Toggle';
            const togContent = (attrs.content || '').replace(/<[^>]*>/g, '');
            return `
                <div style="border:1px solid #e5e7eb;border-radius:8px;margin:10px 0;">
                    <div style="padding:16px 20px;display:flex;justify-content:space-between;align-items:center;cursor:pointer;">
                        <span style="font-weight:600;color:#111827;">${togTitle}</span>
                        <span style="color:#3b82f6;">+</span>
                    </div>
                    <div style="padding:0 20px 16px;color:#4b5563;line-height:1.6;">${togContent}</div>
                </div>`;

        // ========================================
        // MEDIA MODULES
        // ========================================

        case 'audio':
            return `
                <div style="background:#f9fafb;border-radius:12px;padding:20px;margin:20px 0;">
                    <div style="display:flex;align-items:center;gap:16px;">
                        <div style="width:60px;height:60px;background:#3b82f6;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:24px;">▶</div>
                        <div style="flex:1;">
                            <div style="height:4px;background:#e5e7eb;border-radius:2px;"><div style="width:30%;height:100%;background:#3b82f6;border-radius:2px;"></div></div>
                            <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:12px;color:#6b7280;"><span>1:23</span><span>4:56</span></div>
                        </div>
                    </div>
                </div>`;

        case 'video':
            const vidSrc = attrs.src || '';
            if (vidSrc && vidSrc.includes('youtube')) {
                const vidId = vidSrc.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/)?.[1] || '';
                return `<div style="position:relative;padding-bottom:56.25%;height:0;margin:20px 0;"><iframe src="https://www.youtube.com/embed/${vidId}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;border-radius:12px;"></iframe></div>`;
            }
            return `
                <div style="position:relative;background:#1e293b;border-radius:12px;padding-bottom:56.25%;margin:20px 0;">
                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                        <div style="width:80px;height:80px;background:rgba(255,255,255,0.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;cursor:pointer;">▶</div>
                    </div>
                </div>`;

        case 'gallery':
            const galImages = attrs.images || [];
            const galCols = attrs.columns || 3;
            const galGap = attrs.gap || 20;
            return `
                <div class="jtb-grid" style="grid-template-columns:repeat(${galCols}, 1fr);gap:${galGap}px;margin:20px 0;">
                    ${galImages.length > 0 ? galImages.map((img, i) => `
                        <div style="border-radius:8px;overflow:hidden;aspect-ratio:1;">
                            <img src="${img.src || img}" alt="${img.alt || ''}" style="width:100%;height:100%;object-fit:cover;" />
                        </div>
                    `).join('') : [1,2,3,4,5,6].map(i => `
                        <div style="background:#f3f4f6;border-radius:8px;aspect-ratio:1;display:flex;align-items:center;justify-content:center;color:#9ca3af;">Image ${i}</div>
                    `).join('')}
                </div>`;

        case 'slider':
        case 'fullwidth_slider':
            const sliderItems = children.length > 0 ? children : [{attrs: {heading: 'Slide 1', content: 'Slider content'}}];
            const firstSlide = sliderItems[0].attrs || {};
            return `
                <div style="position:relative;background:linear-gradient(135deg,#1e293b,#334155);border-radius:${type === 'fullwidth_slider' ? '0' : '16px'};padding:80px 40px;text-align:center;margin:20px 0;">
                    <h2 style="color:#fff;font-size:36px;margin-bottom:16px;">${firstSlide.heading || 'Slide Title'}</h2>
                    <p style="color:rgba(255,255,255,0.8);max-width:600px;margin:0 auto 24px;">${(firstSlide.content || '').replace(/<[^>]*>/g, '')}</p>
                    <div style="display:flex;justify-content:center;gap:8px;margin-top:30px;">
                        ${sliderItems.map((_, i) => `<div style="width:${i === 0 ? '24px' : '8px'};height:8px;background:${i === 0 ? '#fff' : 'rgba(255,255,255,0.4)'};border-radius:4px;"></div>`).join('')}
                    </div>
                </div>`;

        case 'slider_item':
        case 'fullwidth_slider_item':
            return ''; // Handled by parent slider

        case 'map':
        case 'fullwidth_map':
            const mapHeight = attrs.height || 400;
            const mapAddress = attrs.address || 'Map Location';
            return `
                <div style="height:${mapHeight}px;background:#e5e7eb;border-radius:${type === 'fullwidth_map' ? '0' : '12px'};display:flex;align-items:center;justify-content:center;margin:20px 0;">
                    <div style="text-align:center;color:#6b7280;">
                        <div style="font-size:48px;margin-bottom:8px;">📍</div>
                        <div>${mapAddress}</div>
                    </div>
                </div>`;

        case 'map_pin':
            return ''; // Handled by parent map

        // ========================================
        // FORM MODULES
        // ========================================

        case 'contact_form':
            const cfTitle = attrs.title || 'Contact Us';
            const cfDesc = attrs.description || '';
            const cfBtn = attrs.submit_text || 'Send Message';
            return `
                <div class="jtb-card">
                    ${cfTitle ? `<h4 style="color:#111827;font-size:24px;font-weight:600;margin-bottom:8px;">${cfTitle}</h4>` : ''}
                    ${cfDesc ? `<p style="color:#6b7280;margin-bottom:24px;">${cfDesc}</p>` : ''}
                    <input type="text" placeholder="Your Name" class="jtb-input" />
                    <input type="email" placeholder="Your Email" class="jtb-input" />
                    <textarea placeholder="Your Message" class="jtb-input jtb-textarea"></textarea>
                    <button class="jtb-btn jtb-btn-primary" style="width:100%;">${cfBtn}</button>
                </div>`;

        case 'contact_form_field':
            const fieldType = attrs.field_type || 'text';
            const fieldLabel = attrs.label || '';
            const fieldPlaceholder = attrs.placeholder || '';
            return `
                <div style="margin-bottom:16px;">
                    ${fieldLabel ? `<label style="display:block;margin-bottom:8px;font-weight:500;color:#374151;">${fieldLabel}</label>` : ''}
                    ${fieldType === 'textarea'
                        ? `<textarea placeholder="${fieldPlaceholder}" class="jtb-input jtb-textarea"></textarea>`
                        : `<input type="${fieldType}" placeholder="${fieldPlaceholder}" class="jtb-input" />`
                    }
                </div>`;

        case 'login':
            return `
                <div class="jtb-card" style="max-width:400px;margin:0 auto;">
                    <h4 style="color:#111827;font-size:24px;text-align:center;margin-bottom:24px;">Login</h4>
                    <input type="email" placeholder="Email" class="jtb-input" />
                    <input type="password" placeholder="Password" class="jtb-input" />
                    <button class="jtb-btn jtb-btn-primary" style="width:100%;">Sign In</button>
                    <p style="text-align:center;margin-top:16px;color:#6b7280;font-size:14px;">Don't have an account? <a href="#" style="color:#3b82f6;">Sign up</a></p>
                </div>`;

        case 'signup':
            return `
                <div class="jtb-card" style="max-width:400px;margin:0 auto;">
                    <h4 style="color:#111827;font-size:24px;text-align:center;margin-bottom:24px;">Create Account</h4>
                    <input type="text" placeholder="Full Name" class="jtb-input" />
                    <input type="email" placeholder="Email" class="jtb-input" />
                    <input type="password" placeholder="Password" class="jtb-input" />
                    <input type="password" placeholder="Confirm Password" class="jtb-input" />
                    <button class="jtb-btn jtb-btn-primary" style="width:100%;">Create Account</button>
                </div>`;

        case 'search':
        case 'search-form':
            return `
                <div style="display:flex;gap:12px;margin:20px 0;">
                    <input type="text" placeholder="${attrs.placeholder || 'Search...'}" class="jtb-input" style="margin:0;flex:1;" />
                    <button class="jtb-btn jtb-btn-primary">🔍</button>
                </div>`;

        // ========================================
        // BLOG MODULES
        // ========================================

        case 'blog':
            const blogCols = attrs.columns || 3;
            const blogPosts = attrs.posts_number || 3;
            return `
                <div class="jtb-grid" style="grid-template-columns:repeat(${blogCols}, 1fr);margin:20px 0;">
                    ${Array(blogPosts).fill(0).map((_, i) => `
                        <div class="jtb-card" style="padding:0;overflow:hidden;">
                            <div style="height:200px;background:linear-gradient(135deg,#f3f4f6,#e5e7eb);"></div>
                            <div style="padding:20px;">
                                <div style="color:#6b7280;font-size:14px;margin-bottom:8px;">Jan ${i + 1}, 2024</div>
                                <h4 style="color:#111827;font-size:18px;font-weight:600;margin-bottom:12px;">Blog Post Title ${i + 1}</h4>
                                <p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore.</p>
                            </div>
                        </div>
                    `).join('')}
                </div>`;

        case 'portfolio':
        case 'filterable_portfolio':
            const portCols = attrs.columns || 4;
            const portCount = attrs.posts_number || 8;
            return `
                <div class="jtb-grid" style="grid-template-columns:repeat(${portCols}, 1fr);margin:20px 0;">
                    ${Array(portCount).fill(0).map((_, i) => `
                        <div style="position:relative;aspect-ratio:1;background:linear-gradient(135deg,#f3f4f6,#e5e7eb);border-radius:8px;overflow:hidden;">
                            <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);opacity:0;transition:opacity 0.3s;display:flex;align-items:center;justify-content:center;">
                                <span style="color:#fff;font-weight:600;">Project ${i + 1}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>`;

        case 'post_slider':
        case 'fullwidth_post_slider':
            return `
                <div style="display:flex;gap:24px;overflow-x:auto;padding:20px 0;margin:0 -24px;padding-left:24px;padding-right:24px;">
                    ${[1,2,3,4].map(i => `
                        <div style="flex:0 0 300px;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                            <div style="height:180px;background:linear-gradient(135deg,#f3f4f6,#e5e7eb);"></div>
                            <div style="padding:16px;">
                                <div style="color:#6b7280;font-size:12px;margin-bottom:8px;">Jan ${i}, 2024</div>
                                <h4 style="color:#111827;font-size:16px;font-weight:600;">Featured Post ${i}</h4>
                            </div>
                        </div>
                    `).join('')}
                </div>`;

        // ========================================
        // FULLWIDTH MODULES
        // ========================================

        case 'fullwidth_header':
            const fhTitle = attrs.title || 'Welcome';
            const fhSubtitle = attrs.subtitle || '';
            const fhBtn = attrs.button_text || '';
            const fhBg = attrs.background_image || attrs.background_color || '#1e293b';
            const hasBgImage = attrs.background_image;
            return `
                <div style="background:${hasBgImage ? `url(${fhBg}) center/cover` : fhBg};padding:120px 40px;text-align:center;position:relative;">
                    ${hasBgImage ? '<div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);"></div>' : ''}
                    <div style="position:relative;z-index:1;max-width:800px;margin:0 auto;">
                        <h1 style="color:#fff;font-size:56px;font-weight:800;margin-bottom:24px;">${fhTitle}</h1>
                        ${fhSubtitle ? `<p style="color:rgba(255,255,255,0.9);font-size:20px;margin-bottom:32px;">${fhSubtitle}</p>` : ''}
                        ${fhBtn ? `<a href="${attrs.link_url || '#'}" class="jtb-btn jtb-btn-primary" style="font-size:18px;padding:18px 36px;">${fhBtn}</a>` : ''}
                    </div>
                </div>`;

        case 'fullwidth_image':
            const fiSrc = attrs.src || '';
            return fiSrc
                ? `<div style="margin:0;"><img src="${fiSrc}" alt="${attrs.alt || ''}" style="width:100%;height:auto;display:block;" /></div>`
                : `<div style="height:400px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#9ca3af;">Full Width Image</div>`;

        case 'fullwidth_menu':
            return `
                <nav style="background:#fff;padding:20px 40px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <div style="font-size:24px;font-weight:700;color:#111827;">Logo</div>
                    <div style="display:flex;gap:32px;">
                        ${['Home', 'About', 'Services', 'Contact'].map(item => `<a href="#" style="color:#4b5563;text-decoration:none;font-weight:500;">${item}</a>`).join('')}
                    </div>
                    <a href="#" class="jtb-btn jtb-btn-primary">Get Started</a>
                </nav>`;

        case 'fullwidth_portfolio':
            return renderModuleHTML({type: 'portfolio', attrs: {...attrs, columns: 4}});

        case 'fullwidth_code':
            return `<pre style="background:#1e293b;color:#e2e8f0;padding:40px;overflow-x:auto;font-family:monospace;font-size:14px;margin:0;"><code>${attrs.content || '// Full width code block'}</code></pre>`;

        case 'fullwidth_post_title':
            return `
                <div style="background:#1e293b;padding:80px 40px;text-align:center;">
                    <h1 style="color:#fff;font-size:48px;font-weight:800;margin-bottom:16px;">${attrs.title || 'Post Title'}</h1>
                    <div style="color:rgba(255,255,255,0.7);">By ${attrs.author || 'Author'} • ${attrs.date || 'January 1, 2024'}</div>
                </div>`;

        // ========================================
        // THEME MODULES
        // ========================================

        case 'featured-image':
            const fiImg = attrs.src || '';
            return fiImg
                ? `<div style="margin-bottom:30px;"><img src="${fiImg}" alt="" style="width:100%;height:auto;border-radius:12px;" /></div>`
                : `<div style="height:300px;background:#f3f4f6;border-radius:12px;margin-bottom:30px;display:flex;align-items:center;justify-content:center;color:#9ca3af;">Featured Image</div>`;

        case 'post-excerpt':
            return `<p style="color:#6b7280;font-size:18px;line-height:1.8;margin-bottom:24px;">${attrs.excerpt || 'Post excerpt goes here...'}</p>`;

        case 'post-meta':
            return `
                <div style="display:flex;gap:16px;color:#6b7280;font-size:14px;margin-bottom:20px;">
                    <span>📅 ${attrs.date || 'Jan 1, 2024'}</span>
                    <span>👤 ${attrs.author || 'Author'}</span>
                    <span>📁 ${attrs.category || 'Category'}</span>
                </div>`;

        case 'author-box':
            return `
                <div style="display:flex;gap:20px;padding:24px;background:#f9fafb;border-radius:12px;margin:30px 0;">
                    <div style="width:80px;height:80px;border-radius:50%;background:#e5e7eb;flex-shrink:0;"></div>
                    <div>
                        <h4 style="color:#111827;font-size:18px;font-weight:600;margin-bottom:8px;">${attrs.author || 'Author Name'}</h4>
                        <p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0;">${attrs.bio || 'Author bio goes here...'}</p>
                    </div>
                </div>`;

        case 'related-posts':
            return `
                <div style="margin:40px 0;">
                    <h3 style="color:#111827;font-size:24px;margin-bottom:24px;">Related Posts</h3>
                    <div class="jtb-grid jtb-grid-3">
                        ${[1,2,3].map(i => `
                            <div class="jtb-card" style="padding:0;overflow:hidden;">
                                <div style="height:150px;background:#f3f4f6;"></div>
                                <div style="padding:16px;">
                                    <h4 style="font-size:16px;color:#111827;">Related Post ${i}</h4>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>`;

        case 'archive-title':
            return `<h1 style="color:#111827;font-size:36px;font-weight:700;margin-bottom:24px;">${attrs.title || 'Archive'}</h1>`;

        case 'breadcrumbs':
            const crumbs = attrs.items || ['Home', 'Blog', 'Current'];
            return `
                <nav style="color:#6b7280;font-size:14px;margin-bottom:24px;">
                    ${crumbs.map((crumb, i) => `${i > 0 ? ' / ' : ''}<a href="#" style="color:${i === crumbs.length - 1 ? '#111827' : '#3b82f6'};text-decoration:none;">${crumb}</a>`).join('')}
                </nav>`;

        case 'archive-posts':
            return renderModuleHTML({type: 'blog', attrs: attrs});

        case 'menu':
            const menuItems = attrs.items || ['Home', 'About', 'Services', 'Contact'];
            return `
                <nav style="display:flex;gap:24px;">
                    ${menuItems.map(item => `<a href="#" style="color:#4b5563;text-decoration:none;font-weight:500;">${item}</a>`).join('')}
                </nav>`;

        case 'post-content':
            return `<div style="color:#374151;line-height:1.8;font-size:17px;">${attrs.content || '<p>Post content goes here...</p>'}</div>`;

        case 'post-title':
            return `<h1 style="color:#111827;font-size:42px;font-weight:800;line-height:1.2;margin-bottom:16px;">${attrs.title || 'Post Title'}</h1>`;

        case 'site-logo':
            const logoSrc = attrs.logo || '';
            return logoSrc
                ? `<img src="${logoSrc}" alt="${attrs.alt || 'Logo'}" style="max-height:${attrs.height || 50}px;" />`
                : `<div style="font-size:28px;font-weight:800;color:#111827;">${attrs.text || 'Logo'}</div>`;

        // ========================================
        // FOOTER MODULES
        // ========================================

        case 'footer-info':
            return `
                <div>
                    <div style="font-size:24px;font-weight:700;color:#111827;margin-bottom:16px;">Company</div>
                    <p style="color:#6b7280;line-height:1.7;">${attrs.description || 'Company description goes here.'}</p>
                </div>`;

        case 'footer-menu':
            const footerLinks = attrs.items || ['About', 'Services', 'Contact', 'Privacy'];
            return `
                <div>
                    <h4 style="color:#111827;font-size:16px;font-weight:600;margin-bottom:16px;">${attrs.title || 'Links'}</h4>
                    <ul style="list-style:none;">
                        ${footerLinks.map(link => `<li style="margin-bottom:8px;"><a href="#" style="color:#6b7280;text-decoration:none;">${link}</a></li>`).join('')}
                    </ul>
                </div>`;

        case 'copyright':
            return `<div style="text-align:center;padding:20px 0;border-top:1px solid #e5e7eb;color:#6b7280;font-size:14px;">${attrs.text || '© 2024 Company. All rights reserved.'}</div>`;

        case 'header-button':
            return `<a href="${attrs.url || '#'}" class="jtb-btn jtb-btn-primary">${attrs.text || 'Get Started'}</a>`;

        case 'cart-icon':
            return `<a href="#" style="position:relative;color:#111827;font-size:24px;">🛒<span style="position:absolute;top:-8px;right:-8px;background:#3b82f6;color:#fff;font-size:12px;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;">${attrs.count || 0}</span></a>`;

        // ========================================
        // VIDEO MODULES
        // ========================================

        case 'video_slider':
            return `
                <div style="display:flex;gap:24px;overflow-x:auto;padding:20px 0;">
                    ${[1,2,3].map(i => `
                        <div style="flex:0 0 350px;position:relative;aspect-ratio:16/9;background:#1e293b;border-radius:12px;">
                            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                                <div style="width:60px;height:60px;background:rgba(255,255,255,0.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;">▶</div>
                            </div>
                        </div>
                    `).join('')}
                </div>`;

        case 'video_slider_item':
            return ''; // Handled by parent

        // ========================================
        // DEFAULT FALLBACK
        // ========================================

        default:
            console.warn('Unknown module type:', type);
            return `<div style="padding:20px;background:#fef3c7;border:1px dashed #f59e0b;border-radius:8px;color:#92400e;text-align:center;margin:10px 0;">[${type}]</div>`;
    }
}

// Export for use in ai-panel.js
if (typeof window !== 'undefined') {
    window.JTB_AI_Render = {
        renderPreview,
        renderModuleHTML,
        ICON_MAP
    };
}
