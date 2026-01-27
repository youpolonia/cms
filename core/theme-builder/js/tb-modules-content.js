/**
 * Theme Builder 3.0 - Module Content Settings (PART 1)
 * Modules 1-15: text, heading, image, button, divider, spacer, video, audio, code, html, quote, list, icon, map
 * @version 3.0
 */

TB.renderContentSettings = function(mod) {
    const content = mod.content || {};
    // Get indices from selectedElement OR modalState (for modal editor)
    let sIdx, rIdx, cIdx, mIdx;
    if (this.selectedElement && this.selectedElement.sIdx !== undefined) {
        ({ sIdx, rIdx, cIdx, mIdx } = this.selectedElement);
    } else if (this.modalState && this.modalState.sIdx !== undefined) {
        ({ sIdx, rIdx, cIdx, mIdx } = this.modalState);
    } else {
        return '<div class="tb-setting-group"><p style="color:#ef4444">Error: No module selected</p></div>';
    }
    let html = '';

    switch (mod.type) {
        case 'text':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Text Content</div><textarea class="tb-setting-input" rows="6" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'text\',this.value)">' + this.escapeHtml(content.text || '') + '</textarea><button type="button" class="tb-btn-ai" onclick="TB.handleAIGenerate(\'text\', \'text\', ' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ', this)">✨ AI</button></div>';
            break;
        case 'heading':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Heading Text</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.text || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'text\',this.value)"><button type="button" class="tb-btn-ai" onclick="TB.handleAIGenerate(\'heading\', \'text\', ' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ', this)">✨ AI</button></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Level</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'tag\',this.value)"><option value="h1"' + (content.tag === 'h1' ? ' selected' : '') + '>H1</option><option value="h2"' + (content.tag === 'h2' || !content.tag ? ' selected' : '') + '>H2</option><option value="h3"' + (content.tag === 'h3' ? ' selected' : '') + '>H3</option><option value="h4"' + (content.tag === 'h4' ? ' selected' : '') + '>H4</option></select></div>';
            break;
        case 'image':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Image URL</div><div style="display:flex;gap:8px"><input type="text" class="tb-setting-input" style="flex:1" value="' + this.escapeHtml(content.src || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'src\',this.value)"><button type="button" class="tb-btn-media" onclick="TB.openMediaGallery(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'src\',url))">📁</button></div></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Alt Text</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.alt || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'alt\',this.value)"></div>';
            break;
        case 'button':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Button Text</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.text || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'text\',this.value)"><button type="button" class="tb-btn-ai" onclick="TB.handleAIGenerate(\'button\', \'text\', ' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ', this)">✨ AI</button></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">URL</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.url || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'url\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Style</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'style\',this.value)"><option value="primary"' + (content.style === 'primary' || !content.style ? ' selected' : '') + '>Primary</option><option value="secondary"' + (content.style === 'secondary' ? ' selected' : '') + '>Secondary</option><option value="outline"' + (content.style === 'outline' ? ' selected' : '') + '>Outline</option></select></div>';
            break;
        case 'divider':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Style</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'style\',this.value)"><option value="solid"' + (content.style === 'solid' || !content.style ? ' selected' : '') + '>Solid</option><option value="dashed"' + (content.style === 'dashed' ? ' selected' : '') + '>Dashed</option><option value="dotted"' + (content.style === 'dotted' ? ' selected' : '') + '>Dotted</option></select></div>';
            break;
        case 'spacer':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Height</div><input type="text" class="tb-setting-input" value="' + (content.height || '40px') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'height\',this.value)"></div>';
            break;
        case 'video':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Video URL</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.url || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'url\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Autoplay</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'autoplay\',this.value===\'true\')"><option value="false"' + (!content.autoplay ? ' selected' : '') + '>No</option><option value="true"' + (content.autoplay ? ' selected' : '') + '>Yes</option></select></div>';
            break;
        case 'audio':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Audio URL</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.audio_url || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'audio_url\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Title</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.title || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'title\',this.value)"></div>';
            break;
        case 'code':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Language</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'language\',this.value)"><option value="javascript"' + (content.language === 'javascript' || !content.language ? ' selected' : '') + '>JavaScript</option><option value="php"' + (content.language === 'php' ? ' selected' : '') + '>PHP</option><option value="python"' + (content.language === 'python' ? ' selected' : '') + '>Python</option><option value="html"' + (content.language === 'html' ? ' selected' : '') + '>HTML</option><option value="css"' + (content.language === 'css' ? ' selected' : '') + '>CSS</option></select></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Code</div><textarea class="tb-setting-input" rows="8" style="font-family:monospace" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'code\',this.value)">' + this.escapeHtml(content.code || '') + '</textarea></div>';
            break;
        case 'html':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Custom HTML</div><textarea class="tb-setting-input" rows="8" style="font-family:monospace" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'html\',this.value)">' + this.escapeHtml(content.html || '') + '</textarea></div>';
            break;
        case 'quote':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Quote</div><textarea class="tb-setting-input" rows="4" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'quote\',this.value)">' + this.escapeHtml(content.quote || '') + '</textarea><button type="button" class="tb-btn-ai" onclick="TB.handleAIGenerate(\'quote\', \'quote\', ' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ', this)">✨ AI</button></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Author</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.author || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'author\',this.value)"></div>';
            break;
        case 'list':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Type</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'type\',this.value)"><option value="unordered"' + (content.type !== 'ordered' ? ' selected' : '') + '>Unordered</option><option value="ordered"' + (content.type === 'ordered' ? ' selected' : '') + '>Ordered</option></select></div>';
            html += '<div class="tb-setting-group" style="display:flex;justify-content:space-between"><div class="tb-setting-label">Items</div><button type="button" class="tb-btn tb-btn-sm" style="background:#10b981;color:#fff" onclick="TB.addListItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')">+ Add</button></div>';
            (content.items || []).forEach((item, i) => { const itemText = typeof item === 'string' ? item : (item.text || ''); html += '<div class="tb-setting-group" style="background:var(--tb-surface-2);padding:8px;border-radius:6px;margin-bottom:4px"><div style="display:flex;gap:4px"><input type="text" class="tb-setting-input" style="flex:1" value="' + this.escapeHtml(itemText) + '" onchange="TB.updateListItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',this.value)"><button type="button" class="tb-btn tb-btn-sm" style="background:#ef4444;color:#fff" onclick="TB.removeListItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ')">×</button></div></div>'; });
            break;
        case 'icon':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Icon</div><div style="display:flex;gap:8px;align-items:center">';
            if (content.icon) html += '<span style="font-size:24px">' + content.icon + '</span>';
            html += '<button type="button" class="tb-btn" onclick="TB.openIconPicker(icon => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'icon\',icon))">Choose</button></div></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Size</div><input type="text" class="tb-setting-input" value="' + (content.size || '48px') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'size\',this.value)"></div>';
            break;
        case 'map':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Address</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.address || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'address\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Zoom</div><input type="range" min="1" max="18" value="' + (content.zoom || 14) + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'zoom\',parseInt(this.value))"></div>';
            break;
        default:
            html += '<div class="tb-setting-group"><p style="color:var(--tb-text-muted)">Module: ' + mod.type + '</p></div>';
    }
    return html;
};

// PART 2: accordion, toggle, tabs, gallery, testimonial, cta, pricing, blurb
TB.renderContentSettingsPart2 = function(mod, sIdx, rIdx, cIdx, mIdx) {
    const content = mod.content || {};
    let html = '';
    switch (mod.type) {
        case 'accordion':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Style</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'style\',this.value)"><option value="default"' + (content.style === 'default' || !content.style ? ' selected' : '') + '>Default</option><option value="bordered"' + (content.style === 'bordered' ? ' selected' : '') + '>Bordered</option><option value="minimal"' + (content.style === 'minimal' ? ' selected' : '') + '>Minimal</option></select></div>';
            html += '<div class="tb-setting-group" style="display:flex;justify-content:space-between"><div class="tb-setting-label">Items</div><button type="button" class="tb-btn tb-btn-sm" style="background:#10b981;color:#fff" onclick="TB.addAccordionItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')">+ Add</button></div>';
            (content.items || []).forEach((item, i) => { html += '<div class="tb-setting-group" style="background:var(--tb-surface-2);padding:10px;border-radius:6px;margin-bottom:6px"><div style="display:flex;justify-content:space-between;margin-bottom:6px"><strong style="font-size:11px">Item ' + (i+1) + '</strong><button type="button" class="tb-btn tb-btn-sm" style="background:#ef4444;color:#fff" onclick="TB.removeAccordionItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ')">×</button></div><input type="text" class="tb-setting-input" style="margin-bottom:6px" placeholder="Title" value="' + this.escapeHtml(item.title || '') + '" onchange="TB.updateAccordionItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'title\',this.value)"><textarea class="tb-setting-input" rows="2" placeholder="Content" onchange="TB.updateAccordionItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'content\',this.value)">' + this.escapeHtml(item.content || '') + '</textarea></div>'; });
            break;
        case 'toggle':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Style</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'style\',this.value)"><option value="default"' + (content.style === 'default' || !content.style ? ' selected' : '') + '>Default</option><option value="bordered"' + (content.style === 'bordered' ? ' selected' : '') + '>Bordered</option><option value="minimal"' + (content.style === 'minimal' ? ' selected' : '') + '>Minimal</option></select></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Open First Item</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'open_by_default\',this.value===\'true\')"><option value="false"' + (!content.open_by_default ? ' selected' : '') + '>No</option><option value="true"' + (content.open_by_default ? ' selected' : '') + '>Yes</option></select></div>';
            html += '<div class="tb-setting-group" style="display:flex;justify-content:space-between"><div class="tb-setting-label">Toggle Items</div><button type="button" class="tb-btn tb-btn-sm" style="background:#10b981;color:#fff" onclick="TB.addToggleItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')">+ Add</button></div>';
            (content.items || []).forEach((item, i) => { html += '<div class="tb-setting-group" style="background:var(--tb-surface-2);padding:10px;border-radius:6px;margin-bottom:6px"><div style="display:flex;justify-content:space-between;margin-bottom:6px"><strong style="font-size:11px">Item ' + (i+1) + '</strong><button type="button" class="tb-btn tb-btn-sm" style="background:#ef4444;color:#fff" onclick="TB.removeToggleItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ')">×</button></div><input type="text" class="tb-setting-input" style="margin-bottom:6px" placeholder="Title" value="' + this.escapeHtml(item.title || '') + '" onchange="TB.updateToggleItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'title\',this.value)"><textarea class="tb-setting-input" rows="2" placeholder="Content" onchange="TB.updateToggleItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'content\',this.value)">' + this.escapeHtml(item.content || '') + '</textarea></div>'; });
            break;
        case 'tabs':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Style</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'style\',this.value)"><option value="default"' + (content.style === 'default' || !content.style ? ' selected' : '') + '>Default</option><option value="pills"' + (content.style === 'pills' ? ' selected' : '') + '>Pills</option><option value="underline"' + (content.style === 'underline' ? ' selected' : '') + '>Underline</option></select></div>';
            html += '<div class="tb-setting-group" style="display:flex;justify-content:space-between"><div class="tb-setting-label">Tabs</div><button type="button" class="tb-btn tb-btn-sm" style="background:#10b981;color:#fff" onclick="TB.addTab(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')">+ Add</button></div>';
            (content.tabs || []).forEach((tab, i) => { html += '<div class="tb-setting-group" style="background:var(--tb-surface-2);padding:10px;border-radius:6px;margin-bottom:6px"><div style="display:flex;justify-content:space-between;margin-bottom:6px"><strong style="font-size:11px">Tab ' + (i+1) + '</strong><button type="button" class="tb-btn tb-btn-sm" style="background:#ef4444;color:#fff" onclick="TB.removeTab(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ')">×</button></div><input type="text" class="tb-setting-input" style="margin-bottom:6px" placeholder="Title" value="' + this.escapeHtml(tab.title || '') + '" onchange="TB.updateTab(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'title\',this.value)"><textarea class="tb-setting-input" rows="2" placeholder="Content" onchange="TB.updateTab(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'content\',this.value)">' + this.escapeHtml(tab.content || '') + '</textarea></div>'; });
            break;
        case 'gallery':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Columns</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'columns\',parseInt(this.value))"><option value="2"' + (content.columns === 2 ? ' selected' : '') + '>2</option><option value="3"' + (content.columns === 3 || !content.columns ? ' selected' : '') + '>3</option><option value="4"' + (content.columns === 4 ? ' selected' : '') + '>4</option><option value="5"' + (content.columns === 5 ? ' selected' : '') + '>5</option></select></div>';
            html += '<div class="tb-setting-group" style="display:flex;justify-content:space-between"><div class="tb-setting-label">Images (' + (content.images || []).length + ')</div><button type="button" class="tb-btn tb-btn-sm" style="background:#10b981;color:#fff" onclick="TB.openMediaGallery(url => TB.addGalleryImage(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',url))">+ Add</button></div>';
            if ((content.images || []).length > 0) { html += '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:8px">'; (content.images || []).forEach((img, i) => { html += '<div style="position:relative"><img src="' + this.escapeHtml(img.src || img) + '" style="width:100%;height:50px;object-fit:cover;border-radius:4px"><button type="button" style="position:absolute;top:2px;right:2px;background:#ef4444;color:#fff;border:none;width:18px;height:18px;border-radius:50%;cursor:pointer;font-size:10px" onclick="TB.removeGalleryImage(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ')">×</button></div>'; }); html += '</div>'; }
            break;
        case 'testimonial':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Quote</div><textarea class="tb-setting-input" rows="4" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'quote\',this.value)">' + this.escapeHtml(content.quote || '') + '</textarea><button type="button" class="tb-btn-ai" onclick="TB.handleAIGenerate(\'testimonial\', \'quote\', ' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ', this)">✨ AI</button></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Author</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.author || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'author\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Role</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.role || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'role\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Avatar</div><div style="display:flex;gap:8px"><input type="text" class="tb-setting-input" style="flex:1" value="' + this.escapeHtml(content.avatar || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'avatar\',this.value)"><button type="button" class="tb-btn-media" onclick="TB.openMediaGallery(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'avatar\',url))">📁</button></div></div>';
            break;
        case 'cta':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Title</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.title || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'title\',this.value)"><button type="button" class="tb-btn-ai" onclick="TB.handleAIGenerate(\'cta\', \'title\', ' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ', this)">✨ AI</button></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Subtitle</div><textarea class="tb-setting-input" rows="2" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'subtitle\',this.value)">' + this.escapeHtml(content.subtitle || '') + '</textarea><button type="button" class="tb-btn-ai" onclick="TB.handleAIGenerate(\'cta\', \'subtitle\', ' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ', this)">✨ AI</button></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Button Text</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.button_text || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'button_text\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Button URL</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.button_url || '#') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'button_url\',this.value)"></div>';
            break;
        case 'pricing':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Plan Title</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.title || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'title\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Price</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.price || '') + '" placeholder="$99" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'price\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Period</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.period || '/month') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'period\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Features (one per line)</div><textarea class="tb-setting-input" rows="5" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'features\',this.value.split(\'\\n\').filter(f=>f.trim()))">' + (content.features || []).join('\n') + '</textarea></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Button Text</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.button_text || 'Get Started') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'button_text\',this.value)"></div>';
            break;
        case 'blurb':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Icon</div><div style="display:flex;gap:8px;align-items:center">';
            if (content.icon) html += '<span style="font-size:24px">' + content.icon + '</span>';
            html += '<button type="button" class="tb-btn" onclick="TB.openIconPicker(icon => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'icon\',icon))">Choose</button></div></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Title</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.title || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'title\',this.value)"><button type="button" class="tb-btn-ai" onclick="TB.handleAIGenerate(\'blurb\', \'title\', ' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ', this)">✨ AI</button></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Text</div><textarea class="tb-setting-input" rows="3" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'text\',this.value)">' + this.escapeHtml(content.text || '') + '</textarea></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Link URL</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.url || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'url\',this.value)"></div>';
            break;
    }
    return html;
};

// PART 3: hero, slider, team, countdown, counter, form
TB.renderContentSettingsPart3 = function(mod, sIdx, rIdx, cIdx, mIdx) {
    const content = mod.content || {};
    let html = '';
    switch (mod.type) {
        case 'hero':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Title</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.title || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'title\',this.value)"><button type="button" class="tb-btn-ai" onclick="TB.handleAIGenerate(\'hero\', \'title\', ' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ', this)">✨ AI</button></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Subtitle</div><textarea class="tb-setting-input" rows="2" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'subtitle\',this.value)">' + this.escapeHtml(content.subtitle || '') + '</textarea><button type="button" class="tb-btn-ai" onclick="TB.handleAIGenerate(\'hero\', \'subtitle\', ' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ', this)">✨ AI</button></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Button Text</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.button_text || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'button_text\',this.value)"><button type="button" class="tb-btn-ai" onclick="TB.handleAIGenerate(\'hero\', \'button\', ' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ', this)">✨ AI</button></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Button URL</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.button_url || '#') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'button_url\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Background Image</div><div style="display:flex;gap:8px"><input type="text" class="tb-setting-input" style="flex:1" value="' + this.escapeHtml(content.bg_image || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'bg_image\',this.value)"><button type="button" class="tb-btn-media" onclick="TB.openMediaGallery(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'bg_image\',url))">📁</button></div></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Background Color</div><input type="color" class="tb-setting-input" style="width:60px;height:36px;padding:2px" value="' + (content.background_color || '#1e1e2e') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'background_color\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Text Color</div><input type="color" class="tb-setting-input" style="width:60px;height:36px;padding:2px" value="' + (content.text_color || '#ffffff') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'text_color\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Overlay Opacity</div><div style="display:flex;gap:8px;align-items:center"><input type="range" class="tb-setting-input" style="flex:1" min="0" max="1" step="0.1" value="' + (content.overlay_opacity !== undefined ? content.overlay_opacity : 0.5) + '" oninput="this.nextElementSibling.textContent=this.value;TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'overlay_opacity\',parseFloat(this.value))"><span style="min-width:30px;text-align:right">' + (content.overlay_opacity !== undefined ? content.overlay_opacity : 0.5) + '</span></div></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Min Height</div><input type="text" class="tb-setting-input" value="' + (content.min_height || '500px') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'min_height\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Alignment</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'alignment\',this.value)"><option value="left"' + (content.alignment === 'left' ? ' selected' : '') + '>Left</option><option value="center"' + (content.alignment === 'center' || !content.alignment ? ' selected' : '') + '>Center</option><option value="right"' + (content.alignment === 'right' ? ' selected' : '') + '>Right</option></select></div>';
            break;
        case 'slider':
            html += '<div class="tb-setting-group" style="display:flex;justify-content:space-between"><div class="tb-setting-label">Slides (' + (content.slides || []).length + ')</div><button type="button" class="tb-btn tb-btn-sm" style="background:#10b981;color:#fff" onclick="TB.addSlide(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')">+ Add</button></div>';
            (content.slides || []).forEach((slide, i) => { html += '<div class="tb-setting-group" style="background:var(--tb-surface-2);padding:10px;border-radius:6px;margin-bottom:6px"><div style="display:flex;justify-content:space-between;margin-bottom:6px"><strong style="font-size:11px">Slide ' + (i+1) + '</strong><button type="button" class="tb-btn tb-btn-sm" style="background:#ef4444;color:#fff" onclick="TB.removeSlide(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ')">×</button></div><input type="text" class="tb-setting-input" style="margin-bottom:6px" placeholder="Title" value="' + this.escapeHtml(slide.title || '') + '" onchange="TB.updateSlide(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'title\',this.value)"><div style="display:flex;gap:4px"><input type="text" class="tb-setting-input" style="flex:1" placeholder="Image URL" value="' + this.escapeHtml(slide.image || '') + '" onchange="TB.updateSlide(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'image\',this.value)"><button type="button" class="tb-btn-media" onclick="TB.openMediaGallery(url => TB.updateSlide(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'image\',url))">📁</button></div></div>'; });
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Autoplay</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'autoplay\',this.value===\'true\')"><option value="true"' + (content.autoplay !== false ? ' selected' : '') + '>Yes</option><option value="false"' + (content.autoplay === false ? ' selected' : '') + '>No</option></select></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Show Arrows</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'show_arrows\',this.value===\'true\')"><option value="true"' + (content.show_arrows !== false ? ' selected' : '') + '>Yes</option><option value="false"' + (content.show_arrows === false ? ' selected' : '') + '>No</option></select></div>';
            break;
        case 'team':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Name</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.name || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'name\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Role</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.role || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'role\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Photo</div><div style="display:flex;gap:8px"><input type="text" class="tb-setting-input" style="flex:1" value="' + this.escapeHtml(content.photo || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'photo\',this.value)"><button type="button" class="tb-btn-media" onclick="TB.openMediaGallery(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'photo\',url))">📁</button></div></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Bio</div><textarea class="tb-setting-input" rows="3" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'bio\',this.value)">' + this.escapeHtml(content.bio || '') + '</textarea></div>';
            break;
        case 'countdown':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Target Date</div><input type="datetime-local" class="tb-setting-input" value="' + (content.target_date || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'target_date\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Title</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.title || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'title\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Style</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'style\',this.value)"><option value="default"' + (content.style === 'default' || !content.style ? ' selected' : '') + '>Default</option><option value="cards"' + (content.style === 'cards' ? ' selected' : '') + '>Cards</option><option value="minimal"' + (content.style === 'minimal' ? ' selected' : '') + '>Minimal</option></select></div>';
            break;
        case 'counter':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Number</div><input type="number" class="tb-setting-input" value="' + (content.number || 0) + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'number\',parseInt(this.value))"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Suffix</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.suffix || '') + '" placeholder="e.g., +, %" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'suffix\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Label</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.label || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'label\',this.value)"></div>';
            break;
        case 'form':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Form Title</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.title || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'title\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Submit Button</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.submit_text || 'Submit') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'submit_text\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Success Message</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.success_message || 'Thank you!') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'success_message\',this.value)"></div>';
            html += '<div class="tb-setting-group" style="display:flex;justify-content:space-between"><div class="tb-setting-label">Fields</div><button type="button" class="tb-btn tb-btn-sm" style="background:#10b981;color:#fff" onclick="TB.addFormField(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')">+ Add</button></div>';
            (content.fields || []).forEach((field, i) => { html += '<div class="tb-setting-group" style="background:var(--tb-surface-2);padding:8px;border-radius:6px;margin-bottom:4px"><div style="display:flex;gap:4px;margin-bottom:4px"><select class="tb-setting-input" style="width:80px" onchange="TB.updateFormField(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'type\',this.value)"><option value="text"' + (field.type === 'text' ? ' selected' : '') + '>Text</option><option value="email"' + (field.type === 'email' ? ' selected' : '') + '>Email</option><option value="textarea"' + (field.type === 'textarea' ? ' selected' : '') + '>Textarea</option></select><input type="text" class="tb-setting-input" style="flex:1" placeholder="Label" value="' + this.escapeHtml(field.label || '') + '" onchange="TB.updateFormField(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'label\',this.value)"><button type="button" class="tb-btn tb-btn-sm" style="background:#ef4444;color:#fff" onclick="TB.removeFormField(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ')">×</button></div></div>'; });
            break;
    }
    return html;
};

// PART 4: login, signup, menu, search, sidebar, social, bar_counters, circle_counter, posts_navigation, comments, portfolio, blog, post_slider, video_slider, fullwidth modules
TB.renderContentSettingsPart4 = function(mod, sIdx, rIdx, cIdx, mIdx) {
    const content = mod.content || {};
    let html = '';
    switch (mod.type) {
        case 'login':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Title</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.title || 'Login') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'title\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Button Text</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.button_text || 'Sign In') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'button_text\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Redirect URL</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.redirect_url || '/') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'redirect_url\',this.value)"></div>';
            break;
        case 'signup':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Title</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.title || 'Sign Up') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'title\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Button Text</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.button_text || 'Create Account') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'button_text\',this.value)"></div>';
            break;
        case 'menu':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Menu Style</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'style\',this.value)"><option value="horizontal"' + (content.style === 'horizontal' || !content.style ? ' selected' : '') + '>Horizontal</option><option value="vertical"' + (content.style === 'vertical' ? ' selected' : '') + '>Vertical</option></select></div>';
            html += '<div class="tb-setting-group" style="display:flex;justify-content:space-between"><div class="tb-setting-label">Items</div><button type="button" class="tb-btn tb-btn-sm" style="background:#10b981;color:#fff" onclick="TB.addMenuItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')">+ Add</button></div>';
            (content.items || []).forEach((item, i) => { html += '<div class="tb-setting-group" style="background:var(--tb-surface-2);padding:8px;border-radius:6px;margin-bottom:4px"><div style="display:flex;gap:4px"><input type="text" class="tb-setting-input" style="flex:1" placeholder="Label" value="' + this.escapeHtml(item.label || '') + '" onchange="TB.updateMenuItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'label\',this.value)"><input type="text" class="tb-setting-input" style="flex:1" placeholder="URL" value="' + this.escapeHtml(item.url || '') + '" onchange="TB.updateMenuItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'url\',this.value)"><button type="button" class="tb-btn tb-btn-sm" style="background:#ef4444;color:#fff" onclick="TB.removeMenuItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ')">×</button></div></div>'; });
            break;
        case 'search':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Placeholder</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.placeholder || 'Search...') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'placeholder\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Style</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'style\',this.value)"><option value="default"' + (content.style === 'default' || !content.style ? ' selected' : '') + '>Default</option><option value="minimal"' + (content.style === 'minimal' ? ' selected' : '') + '>Minimal</option><option value="expanded"' + (content.style === 'expanded' ? ' selected' : '') + '>Expanded</option></select></div>';
            break;
        case 'sidebar':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Sidebar ID</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.sidebar_id || 'default') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'sidebar_id\',this.value)"></div>';
            break;
        case 'social':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Style</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'style\',this.value)"><option value="icons"' + (content.style === 'icons' || !content.style ? ' selected' : '') + '>Icons Only</option><option value="icons_text"' + (content.style === 'icons_text' ? ' selected' : '') + '>Icons + Text</option></select></div>';
            html += '<div class="tb-setting-group" style="display:flex;justify-content:space-between"><div class="tb-setting-label">Networks</div><button type="button" class="tb-btn tb-btn-sm" style="background:#10b981;color:#fff" onclick="TB.addSocialNetwork(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')">+ Add</button></div>';
            (content.networks || []).forEach((net, i) => { html += '<div class="tb-setting-group" style="background:var(--tb-surface-2);padding:8px;border-radius:6px;margin-bottom:4px"><div style="display:flex;gap:4px"><select class="tb-setting-input" style="width:100px" onchange="TB.updateSocialNetwork(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'platform\',this.value)"><option value="facebook"' + (net.platform === 'facebook' ? ' selected' : '') + '>Facebook</option><option value="twitter"' + (net.platform === 'twitter' ? ' selected' : '') + '>Twitter</option><option value="instagram"' + (net.platform === 'instagram' ? ' selected' : '') + '>Instagram</option><option value="linkedin"' + (net.platform === 'linkedin' ? ' selected' : '') + '>LinkedIn</option><option value="youtube"' + (net.platform === 'youtube' ? ' selected' : '') + '>YouTube</option></select><input type="text" class="tb-setting-input" style="flex:1" placeholder="URL" value="' + this.escapeHtml(net.url || '') + '" onchange="TB.updateSocialNetwork(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'url\',this.value)"><button type="button" class="tb-btn tb-btn-sm" style="background:#ef4444;color:#fff" onclick="TB.removeSocialNetwork(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ')">×</button></div></div>'; });
            break;
        case 'bar_counters':
            html += '<div class="tb-setting-group" style="display:flex;justify-content:space-between"><div class="tb-setting-label">Bars</div><button type="button" class="tb-btn tb-btn-sm" style="background:#10b981;color:#fff" onclick="TB.addBar(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')">+ Add</button></div>';
            (content.bars || []).forEach((bar, i) => { html += '<div class="tb-setting-group" style="background:var(--tb-surface-2);padding:8px;border-radius:6px;margin-bottom:4px"><div style="display:flex;gap:4px;margin-bottom:4px"><input type="text" class="tb-setting-input" style="flex:1" placeholder="Label" value="' + this.escapeHtml(bar.label || '') + '" onchange="TB.updateBar(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'label\',this.value)"><input type="number" class="tb-setting-input" style="width:60px" min="0" max="100" value="' + (bar.percent || 0) + '" onchange="TB.updateBar(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'percent\',parseInt(this.value))"><span>%</span><button type="button" class="tb-btn tb-btn-sm" style="background:#ef4444;color:#fff" onclick="TB.removeBar(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ')">×</button></div></div>'; });
            break;
        case 'circle_counter':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Percentage</div><input type="number" class="tb-setting-input" min="0" max="100" value="' + (content.percent || 0) + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'percent\',parseInt(this.value))"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Label</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.label || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'label\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Size</div><input type="text" class="tb-setting-input" value="' + (content.size || '150px') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'size\',this.value)"></div>';
            break;
        case 'posts_navigation':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Prev Text</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.prev_text || '← Previous') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'prev_text\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Next Text</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.next_text || 'Next →') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'next_text\',this.value)"></div>';
            break;
        case 'comments':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Title</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.title || 'Comments') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'title\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Show Avatar</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'show_avatar\',this.value===\'true\')"><option value="true"' + (content.show_avatar !== false ? ' selected' : '') + '>Yes</option><option value="false"' + (content.show_avatar === false ? ' selected' : '') + '>No</option></select></div>';
            break;
        case 'portfolio':
        case 'blog':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Posts Per Page</div><input type="number" class="tb-setting-input" value="' + (content.posts_per_page || 6) + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'posts_per_page\',parseInt(this.value))"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Columns</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'columns\',parseInt(this.value))"><option value="2"' + (content.columns === 2 ? ' selected' : '') + '>2</option><option value="3"' + (content.columns === 3 || !content.columns ? ' selected' : '') + '>3</option><option value="4"' + (content.columns === 4 ? ' selected' : '') + '>4</option></select></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Show Excerpt</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'show_excerpt\',this.value===\'true\')"><option value="true"' + (content.show_excerpt !== false ? ' selected' : '') + '>Yes</option><option value="false"' + (content.show_excerpt === false ? ' selected' : '') + '>No</option></select></div>';
            break;
        case 'post_slider':
        case 'video_slider':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Posts Count</div><input type="number" class="tb-setting-input" value="' + (content.posts_count || 5) + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'posts_count\',parseInt(this.value))"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Autoplay</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'autoplay\',this.value===\'true\')"><option value="true"' + (content.autoplay !== false ? ' selected' : '') + '>Yes</option><option value="false"' + (content.autoplay === false ? ' selected' : '') + '>No</option></select></div>';
            break;
        case 'post_title':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Tag</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'tag\',this.value)"><option value="h1"' + (content.tag === 'h1' || !content.tag ? ' selected' : '') + '>H1</option><option value="h2"' + (content.tag === 'h2' ? ' selected' : '') + '>H2</option></select></div>';
            break;
        case 'post_content':
            html += '<div class="tb-setting-group"><p style="color:var(--tb-text-muted);font-size:12px">Displays the full post content automatically.</p></div>';
            break;
        case 'fullwidth_header':
        case 'fullwidth_menu':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Logo</div><div style="display:flex;gap:8px"><input type="text" class="tb-setting-input" style="flex:1" value="' + this.escapeHtml(content.logo || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'logo\',this.value)"><button type="button" class="tb-btn-media" onclick="TB.openMediaGallery(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'logo\',url))">📁</button></div></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Style</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'style\',this.value)"><option value="default"' + (content.style === 'default' || !content.style ? ' selected' : '') + '>Default</option><option value="centered"' + (content.style === 'centered' ? ' selected' : '') + '>Centered</option><option value="minimal"' + (content.style === 'minimal' ? ' selected' : '') + '>Minimal</option></select></div>';
            break;
        case 'fullwidth_image':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Image</div><div style="display:flex;gap:8px"><input type="text" class="tb-setting-input" style="flex:1" value="' + this.escapeHtml(content.src || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'src\',this.value)"><button type="button" class="tb-btn-media" onclick="TB.openMediaGallery(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'src\',url))">📁</button></div></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Alt Text</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.alt || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'alt\',this.value)"></div>';
            break;
        case 'fullwidth_map':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Address</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.address || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'address\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Height</div><input type="text" class="tb-setting-input" value="' + (content.height || '400px') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'height\',this.value)"></div>';
            break;
        case 'fullwidth_code':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Custom Code</div><textarea class="tb-setting-input" rows="8" style="font-family:monospace" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'code\',this.value)">' + this.escapeHtml(content.code || '') + '</textarea></div>';
            break;
        case 'fullwidth_slider':
        case 'fullwidth_post_slider':
        case 'fullwidth_portfolio':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Items Count</div><input type="number" class="tb-setting-input" value="' + (content.count || 5) + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'count\',parseInt(this.value))"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Autoplay</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'autoplay\',this.value===\'true\')"><option value="true"' + (content.autoplay !== false ? ' selected' : '') + '>Yes</option><option value="false"' + (content.autoplay === false ? ' selected' : '') + '>No</option></select></div>';
            break;
        case 'social_follow':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Style</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'style\',this.value)"><option value="icons"' + (content.style === 'icons' || !content.style ? ' selected' : '') + '>Icons Only</option><option value="icons_text"' + (content.style === 'icons_text' ? ' selected' : '') + '>Icons + Text</option><option value="buttons"' + (content.style === 'buttons' ? ' selected' : '') + '>Buttons</option></select></div>';
            html += '<div class="tb-setting-group" style="display:flex;justify-content:space-between"><div class="tb-setting-label">Networks</div><button type="button" class="tb-btn tb-btn-sm" style="background:#10b981;color:#fff" onclick="TB.addSocialFollowNetwork(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')">+ Add</button></div>';
            (content.networks || []).forEach((net, i) => { html += '<div class="tb-setting-group" style="background:var(--tb-surface-2);padding:8px;border-radius:6px;margin-bottom:4px"><div style="display:flex;gap:4px"><select class="tb-setting-input" style="width:100px" onchange="TB.updateSocialFollowNetwork(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'platform\',this.value)"><option value="facebook"' + (net.platform === 'facebook' ? ' selected' : '') + '>Facebook</option><option value="twitter"' + (net.platform === 'twitter' ? ' selected' : '') + '>Twitter/X</option><option value="instagram"' + (net.platform === 'instagram' ? ' selected' : '') + '>Instagram</option><option value="linkedin"' + (net.platform === 'linkedin' ? ' selected' : '') + '>LinkedIn</option><option value="youtube"' + (net.platform === 'youtube' ? ' selected' : '') + '>YouTube</option><option value="tiktok"' + (net.platform === 'tiktok' ? ' selected' : '') + '>TikTok</option><option value="pinterest"' + (net.platform === 'pinterest' ? ' selected' : '') + '>Pinterest</option></select><input type="text" class="tb-setting-input" style="flex:1" placeholder="URL" value="' + this.escapeHtml(net.url || '') + '" onchange="TB.updateSocialFollowNetwork(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\'url\',this.value)"><button type="button" class="tb-btn tb-btn-sm" style="background:#ef4444;color:#fff" onclick="TB.removeSocialFollowNetwork(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ')">×</button></div></div>'; });
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Show Labels</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'show_labels\',this.value===\'true\')"><option value="false"' + (!content.show_labels ? ' selected' : '') + '>No</option><option value="true"' + (content.show_labels ? ' selected' : '') + '>Yes</option></select></div>';
            break;
        case 'logo':
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Logo Image</div><div style="display:flex;gap:8px"><input type="text" class="tb-setting-input" style="flex:1" value="' + this.escapeHtml(content.image || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'image\',this.value)"><button type="button" class="tb-btn-media" onclick="TB.openMediaGallery(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'image\',url))">📁</button></div></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Alt Text</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.image_alt || '') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'image_alt\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Link URL</div><input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.link_url || '/') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'link_url\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Link Target</div><select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'link_target\',this.value)"><option value="_self"' + (content.link_target === '_self' || !content.link_target ? ' selected' : '') + '>Same Window</option><option value="_blank"' + (content.link_target === '_blank' ? ' selected' : '') + '>New Tab</option></select></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Max Height (Desktop)</div><input type="text" class="tb-setting-input" value="' + (content.max_height || '60px') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'max_height\',this.value)"></div>';
            html += '<div class="tb-setting-group"><div class="tb-setting-label">Max Height (Mobile)</div><input type="text" class="tb-setting-input" value="' + (content.mobile_max_height || '40px') + '" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'mobile_max_height\',this.value)"></div>';
            break;
    }
    return html;
};

// MERGE: Override main function to use all parts
(function() {
    const originalRender = TB.renderContentSettings;
    TB.renderContentSettings = function(mod) {
        // Get indices from selectedElement OR modalState (for modal editor)
        let sIdx, rIdx, cIdx, mIdx;
        if (this.selectedElement && this.selectedElement.sIdx !== undefined) {
            ({ sIdx, rIdx, cIdx, mIdx } = this.selectedElement);
        } else if (this.modalState && this.modalState.sIdx !== undefined) {
            ({ sIdx, rIdx, cIdx, mIdx } = this.modalState);
        } else {
            console.error('TB.renderContentSettings: No valid selectedElement or modalState found');
            return '<div class="tb-setting-group"><p style="color:#ef4444">Error: Module selection not found</p></div>';
        }

        // Part 1 modules
        const part1 = ['text','heading','image','button','divider','spacer','video','audio','code','html','quote','list','icon','map'];
        if (part1.includes(mod.type)) {
            return originalRender.call(this, mod);
        }

        // Part 2 modules
        const part2 = ['accordion','toggle','tabs','gallery','testimonial','cta','pricing','blurb'];
        if (part2.includes(mod.type)) {
            return this.renderContentSettingsPart2(mod, sIdx, rIdx, cIdx, mIdx);
        }

        // Part 3 modules
        const part3 = ['hero','slider','team','countdown','counter','form'];
        if (part3.includes(mod.type)) {
            return this.renderContentSettingsPart3(mod, sIdx, rIdx, cIdx, mIdx);
        }

        // Part 4 modules (all remaining)
        return this.renderContentSettingsPart4(mod, sIdx, rIdx, cIdx, mIdx);
    };
})();
