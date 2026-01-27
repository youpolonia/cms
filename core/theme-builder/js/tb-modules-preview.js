/**
 * Theme Builder 3.0 - Module Previews
 * Full preview rendering for all 52 module types on canvas
 * Extends TB.renderModule from tb-core.js
 * @package ThemeBuilder
 * @version 3.0
 */

// Generate preview HTML for a module (content only, no wrapper)
// The wrapper is created by TB.renderModule in tb-render.js
TB.renderModulePreview = function(mod) {
    const type = mod.type || 'text';
    const content = mod.content || {};
    const settings = mod.settings || {};
    const design = mod.design || {};
    const icon = this.getModuleIcon(type);
    
    // Helper for typography styles
    const getTypo = (element) => {
        if (typeof this.getElementTypographyStyles === 'function') {
            return this.getElementTypographyStyles(settings, element);
        }
        return '';
    };
    
    let preview = '';
    
    switch (type) {
        case 'text':
            const textTypo = getTypo('body');
            preview = content.text 
                ? '<p style="color:#333;margin:0;' + textTypo + '">' + this.escapeHtml(content.text.substring(0, 100)) + (content.text.length > 100 ? '...' : '') + '</p>' 
                : '<p style="color:#94a3b8;font-style:italic;' + textTypo + '">Click to add text...</p>';
            break;
            
        case 'heading':
            const tag = content.tag || 'h2';
            const headingTypo = getTypo('title');
            preview = '<' + tag + ' style="margin:0;color:#333;' + headingTypo + '">' + this.escapeHtml(content.text || 'Heading') + '</' + tag + '>';
            break;
            
        case 'image':
            console.log('🖼️ Rendering image module, content:', content);
            console.log('  - content.src:', content.src);
            console.log('  - content.alt:', content.alt);
            if (content.src) {
                // Apply design styles (filters, transforms, etc) directly to img tag
                const imgStyles = (typeof this.getModuleStyles === 'function') ? this.getModuleStyles(design) : '';
                console.log('  - imgStyles:', imgStyles);
                preview = '<img src="' + this.escapeHtml(content.src) + '" alt="' + this.escapeHtml(content.alt || '') + '" style="max-width:100%;height:auto;border-radius:4px;' + imgStyles + '">';
            } else {
                preview = '<div style="background:#e2e8f0;height:80px;display:flex;align-items:center;justify-content:center;border-radius:4px;color:#64748b">🖼 Click to add image</div>';
            }
            break;
            
        case 'button':
            const btnStyle = content.style || 'primary';
            const btnTypo = getTypo('button');
            const btnBg = btnStyle === 'secondary' ? '#64748b' : (btnStyle === 'outline' ? 'transparent' : '#6366f1');
            const btnBorder = btnStyle === 'outline' ? '2px solid #6366f1' : 'none';
            const btnColor = btnStyle === 'outline' ? '#6366f1' : '#fff';
            preview = '<button style="background:' + btnBg + ';color:' + btnColor + ';border:' + btnBorder + ';padding:10px 20px;border-radius:6px;font-weight:500;' + btnTypo + '">' + this.escapeHtml(content.text || 'Button') + '</button>';
            break;
            
        case 'divider':
            const divStyle = content.style || 'solid';
            const divColor = content.color || '#e2e8f0';
            const divWidth = content.width || '100%';
            preview = '<hr style="border:none;border-top:2px ' + divStyle + ' ' + divColor + ';margin:8px 0;width:' + divWidth + '">';
            break;
            
        case 'spacer':
            const height = content.height || '40px';
            preview = '<div style="height:' + height + ';background:repeating-linear-gradient(45deg,#f8fafc,#f8fafc 10px,#f1f5f9 10px,#f1f5f9 20px);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:11px">' + height + '</div>';
            break;
            
        case 'video':
            if (content.url) {
                const thumbUrl = this.getVideoThumbnail ? this.getVideoThumbnail(content.url) : '';
                preview = thumbUrl 
                    ? '<div style="position:relative;padding-bottom:56.25%;background:url(' + thumbUrl + ') center/cover;border-radius:4px"><div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center"><div style="width:48px;height:48px;background:rgba(0,0,0,0.7);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px">▶</div></div></div>'
                    : '<div style="background:#1e293b;height:120px;display:flex;align-items:center;justify-content:center;border-radius:4px;color:#fff">🎬 Video</div>';
            } else {
                preview = '<div style="background:#1e293b;height:80px;display:flex;align-items:center;justify-content:center;border-radius:4px;color:#94a3b8">🎬 Click to add video</div>';
            }
            break;
            
        case 'audio':
            const audioUrl = content.audio_url || '';
            const audioTitle = content.title || 'Untitled Track';
            const audioArtist = content.artist || 'Unknown Artist';
            const audioBg = content.background_color || '#1e1e2e';
            const audioAccent = content.accent_color || '#0073e6';
            if (!audioUrl) {
                preview = '<div style="background:' + audioBg + ';padding:24px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#94a3b8;flex-direction:column;gap:8px"><span style="font-size:32px">🎵</span><span style="font-size:12px">Click to add audio</span></div>';
            } else {
                preview = '<div style="background:' + audioBg + ';padding:16px;border-radius:8px;color:#fff"><div style="display:flex;align-items:center;gap:12px"><button style="width:40px;height:40px;border-radius:50%;background:' + audioAccent + ';border:none;color:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center">▶</button><div style="flex:1"><div style="font-size:14px;font-weight:500">' + this.escapeHtml(audioTitle) + '</div><div style="font-size:12px;opacity:0.7">' + this.escapeHtml(audioArtist) + '</div></div><span style="font-size:12px;opacity:0.6">0:00 / 3:45</span></div><div style="margin-top:12px;height:4px;background:rgba(255,255,255,0.2);border-radius:2px"><div style="width:30%;height:100%;background:' + audioAccent + ';border-radius:2px"></div></div></div>';
            }
            break;
            
        case 'code':
            preview = content.code 
                ? '<pre style="background:#1e293b;color:#a5f3fc;padding:12px;border-radius:4px;font-size:11px;overflow:hidden;max-height:80px;margin:0">' + this.escapeHtml(content.code.substring(0, 150)) + '</pre>' 
                : '<div style="background:#1e293b;padding:12px;border-radius:4px;color:#94a3b8;font-family:monospace">💻 Code block</div>';
            break;
            
        case 'html':
            preview = '<div style="background:#fef3c7;padding:12px;border-radius:4px;color:#92400e;font-size:12px">🔧 Custom HTML block</div>';
            break;
            
        case 'quote':
            const quoteTypo = getTypo('quote');
            // Support both 'quote' (Content panel) and 'text' (legacy)
            const quoteText = content.quote || content.text || '';
            if (quoteText) {
                preview = '<blockquote style="border-left:4px solid #6366f1;padding-left:16px;margin:0;color:#475569;font-style:italic;' + quoteTypo + '">"' + this.escapeHtml(quoteText.substring(0, 80)) + (quoteText.length > 80 ? '...' : '') + '"</blockquote>';
                if (content.author) preview += '<div style="padding-left:16px;margin-top:8px;font-size:13px;color:#64748b">— ' + this.escapeHtml(content.author) + '</div>';
            } else {
                preview = '<blockquote style="border-left:4px solid #e2e8f0;padding-left:16px;margin:0;color:#94a3b8;font-style:italic">💬 Add quote text...</blockquote>';
            }
            break;
            
        case 'list':
            console.log('🔍 List preview - content.items:', content.items);
            const listItems = content.items || ['Item 1', 'Item 2'];
            const listTag = content.type === 'ordered' ? 'ol' : 'ul';
            preview = '<' + listTag + ' style="margin:0;padding-left:20px;color:#333">';
            listItems.slice(0, 5).forEach(item => {
                // Support both string items (Content panel) and object items (legacy)
                const itemText = typeof item === 'string' ? item : (item?.text || 'Item');
                preview += '<li>' + this.escapeHtml(itemText) + '</li>';
            });
            if (listItems.length > 5) preview += '<li style="color:#94a3b8">... +' + (listItems.length - 5) + ' more</li>';
            preview += '</' + listTag + '>';
            break;
            
        case 'icon':
            const iconName = content.icon || 'fa-star';
            const iconSize = content.size || '48px';
            const iconColor = content.color || '#cba6f7';
            const iconHtml = typeof this.renderIconFromFormat === 'function' ? this.renderIconFromFormat(iconName) : '⭐';
            preview = '<div style="text-align:center;font-size:' + iconSize + ';color:' + iconColor + '">' + iconHtml + '</div>';
            break;
            
        case 'accordion':
            const accItems = content.items || [{ title: 'Section 1', content: 'Content...' }];
            preview = '<div style="border:1px solid #e2e8f0;border-radius:6px;overflow:hidden">';
            accItems.slice(0, 3).forEach((item, i) => {
                preview += '<div style="border-bottom:1px solid #e2e8f0;padding:12px;display:flex;justify-content:space-between;align-items:center;background:' + (i === 0 ? '#f8fafc' : '#fff') + '"><span style="font-weight:500;color:#333">' + this.escapeHtml(item.title) + '</span><span>' + (i === 0 ? '▼' : '▶') + '</span></div>';
                if (i === 0) preview += '<div style="padding:12px;color:#666;font-size:13px">' + this.escapeHtml((item.content || '').substring(0, 50)) + '...</div>';
            });
            preview += '</div>';
            break;
            
        case 'tabs':
            const tabItems = content.tabs || [{ title: 'Tab 1', content: 'Content...' }];
            preview = '<div><div style="display:flex;border-bottom:2px solid #e2e8f0">';
            tabItems.slice(0, 4).forEach((tab, i) => {
                preview += '<div style="padding:10px 16px;cursor:pointer;border-bottom:2px solid ' + (i === 0 ? '#6366f1' : 'transparent') + ';margin-bottom:-2px;color:' + (i === 0 ? '#6366f1' : '#64748b') + ';font-weight:500">' + this.escapeHtml(tab.title) + '</div>';
            });
            preview += '</div><div style="padding:16px;color:#666">' + this.escapeHtml((tabItems[0]?.content || '').substring(0, 60)) + '...</div></div>';
            break;
            
        case 'toggle':
            // Support both 'items[]' array (legacy) and single 'title'/'content' fields (Content panel)
            let toggleItems;
            if (content.items && content.items.length > 0) {
                toggleItems = content.items;
            } else {
                // Single toggle from Content panel
                toggleItems = [{ 
                    title: content.title || 'Toggle Title', 
                    content: content.content || 'Toggle content...'
                }];
            }
            const toggleOpen = content.open_by_default === true;
            preview = '<div style="display:flex;flex-direction:column;gap:8px">';
            toggleItems.slice(0, 3).forEach((item, i) => {
                const isOpen = (i === 0 && toggleOpen) || (i === 0 && toggleItems.length === 1);
                preview += '<div style="border:1px solid #e2e8f0;border-radius:6px;overflow:hidden">';
                preview += '<div style="padding:12px;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;cursor:pointer"><span style="font-weight:500;color:#333">' + this.escapeHtml(item.title || 'Toggle') + '</span><span style="color:#6366f1">' + (isOpen ? '−' : '+') + '</span></div>';
                if (isOpen) preview += '<div style="padding:12px;color:#666;font-size:13px;border-top:1px solid #e2e8f0">' + this.escapeHtml((item.content || '').substring(0, 100)) + (item.content && item.content.length > 100 ? '...' : '') + '</div>';
                preview += '</div>';
            });
            preview += '</div>';
            break;
            
        case 'gallery':
            const images = content.images || [];
            const galCols = content.columns || 3;
            if (images.length === 0) {
                preview = '<div style="background:#e2e8f0;height:100px;display:flex;align-items:center;justify-content:center;border-radius:4px;color:#64748b">🖼 Add images to gallery</div>';
            } else {
                preview = '<div style="display:grid;grid-template-columns:repeat(' + Math.min(galCols, 3) + ',1fr);gap:8px">';
                images.slice(0, 6).forEach(img => {
                    preview += '<div style="aspect-ratio:1;border-radius:4px;overflow:hidden"><img src="' + this.escapeHtml(img.src || img) + '" style="width:100%;height:100%;object-fit:cover"></div>';
                });
                preview += '</div>';
                if (images.length > 6) preview += '<div style="text-align:center;margin-top:8px;color:#64748b;font-size:11px">+' + (images.length - 6) + ' more</div>';
            }
            break;
            
        case 'map':
            const address = content.address || 'Enter address...';
            preview = '<div style="background:linear-gradient(135deg,#e0f2fe,#bae6fd);height:150px;border-radius:8px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px"><span style="font-size:32px">🗺️</span><span style="font-size:12px;color:#0369a1">' + this.escapeHtml(address.substring(0, 30)) + '</span></div>';
            break;
            
        case 'form':
            const fields = content.fields || [{ type: 'text', label: 'Name' }];
            const submitText = content.submit_text || 'Submit';
            preview = '<div style="display:flex;flex-direction:column;gap:12px">';
            fields.slice(0, 3).forEach(field => {
                preview += '<div><label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px">' + this.escapeHtml(field.label) + '</label>';
                if (field.type === 'textarea') {
                    preview += '<textarea style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:4px;resize:none" rows="2"></textarea>';
                } else {
                    preview += '<input type="' + field.type + '" style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:4px" placeholder="' + this.escapeHtml(field.placeholder || '') + '">';
                }
                preview += '</div>';
            });
            preview += '<button style="background:#6366f1;color:#fff;border:none;padding:10px 20px;border-radius:6px;font-weight:500;cursor:pointer">' + this.escapeHtml(submitText) + '</button></div>';
            break;
            
        case 'social':
            const networks = content.networks || [{ network: 'facebook', url: '#' }];
            const socialSize = content.icon_size || '32px';
            const socialColors = { facebook: '#1877f2', twitter: '#1da1f2', instagram: '#e4405f', linkedin: '#0a66c2', youtube: '#ff0000', tiktok: '#000', pinterest: '#e60023' };
            preview = '<div style="display:flex;gap:12px;flex-wrap:wrap">';
            networks.forEach(net => {
                const color = socialColors[net.network] || '#64748b';
                preview += '<div style="width:' + socialSize + ';height:' + socialSize + ';background:' + color + ';border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:calc(' + socialSize + ' * 0.5)">' + (net.network || 'S').charAt(0).toUpperCase() + '</div>';
            });
            preview += '</div>';
            break;
            
        case 'social_follow':
            const sfNetworks = content.networks || [{ platform: 'facebook', url: '#' }, { platform: 'twitter', url: '#' }];
            const sfStyle = content.style || 'icons';
            const sfIconSize = content.icon_size || '24px';
            const sfShowLabels = content.show_labels || false;
            const sfBrandColors = { 
                facebook: '#1877f2', twitter: '#1da1f2', instagram: '#e4405f', 
                linkedin: '#0a66c2', youtube: '#ff0000', tiktok: '#000', 
                pinterest: '#e60023', github: '#333', discord: '#5865f2' 
            };
            preview = '<div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">';
            sfNetworks.forEach(net => {
                const brandColor = sfBrandColors[net.platform] || '#64748b';
                const platformLabel = net.platform.charAt(0).toUpperCase() + net.platform.slice(1);
                if (sfStyle === 'buttons') {
                    preview += '<button style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:' + brandColor + ';color:#fff;border:none;border-radius:6px;font-size:13px;font-weight:500"><span style="font-size:' + sfIconSize + '">' + platformLabel.charAt(0) + '</span>' + (sfShowLabels ? '<span>Follow</span>' : '') + '</button>';
                } else if (sfStyle === 'icons_text') {
                    preview += '<div style="display:flex;align-items:center;gap:6px;color:' + brandColor + ';font-size:14px"><div style="width:' + sfIconSize + ';height:' + sfIconSize + ';background:' + brandColor + ';border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px">' + platformLabel.charAt(0) + '</div><span>' + platformLabel + '</span></div>';
                } else {
                    // icons only
                    preview += '<div style="width:' + sfIconSize + ';height:' + sfIconSize + ';background:' + brandColor + ';border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:calc(' + sfIconSize + ' * 0.5);cursor:pointer" title="Follow on ' + platformLabel + '">' + platformLabel.charAt(0) + '</div>';
                }
            });
            preview += '</div>';
            break;
            
        case 'testimonial':
            const testQuote = content.quote || content.text || 'Great service!';
            const testAuthor = content.author || 'John Doe';
            const testRole = content.role || 'CEO';
            // Support both 'avatar' (Content panel) and 'image' (legacy)
            const testImage = content.avatar || content.image || '';
            preview = '<div style="background:#f8fafc;padding:20px;border-radius:8px;text-align:center">';
            if (testImage) {
                preview += '<img src="' + this.escapeHtml(testImage) + '" style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin-bottom:12px">';
            } else {
                preview += '<div style="width:60px;height:60px;background:#e2e8f0;border-radius:50%;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:24px">👤</div>';
            }
            preview += '<p style="color:#475569;font-style:italic;margin:0 0 12px">"' + this.escapeHtml(testQuote.substring(0, 80)) + (testQuote.length > 80 ? '...' : '') + '"</p>';
            preview += '<div style="font-weight:600;color:#333">' + this.escapeHtml(testAuthor) + '</div>';
            preview += '<div style="font-size:12px;color:#64748b">' + this.escapeHtml(testRole) + '</div></div>';
            break;
            
        case 'pricing':
            const priceTitle = content.title || 'Basic';
            const priceAmount = content.price || '$9';
            const pricePeriod = content.period || '/mo';
            const priceFeatures = content.features || ['Feature 1'];
            const priceFeatured = content.featured || false;
            const priceBg = priceFeatured ? '#6366f1' : '#fff';
            const priceColor = priceFeatured ? '#fff' : '#333';
            preview = '<div style="background:' + priceBg + ';padding:24px;border-radius:12px;text-align:center;border:' + (priceFeatured ? 'none' : '1px solid #e2e8f0') + '">';
            preview += '<div style="font-size:18px;font-weight:600;color:' + priceColor + ';margin-bottom:8px">' + this.escapeHtml(priceTitle) + '</div>';
            preview += '<div style="font-size:36px;font-weight:700;color:' + priceColor + '">' + this.escapeHtml(priceAmount) + '<span style="font-size:14px;font-weight:400">' + this.escapeHtml(pricePeriod) + '</span></div>';
            preview += '<div style="margin:16px 0;border-top:1px solid ' + (priceFeatured ? 'rgba(255,255,255,0.2)' : '#e2e8f0') + ';padding-top:16px">';
            priceFeatures.slice(0, 4).forEach(f => { preview += '<div style="color:' + (priceFeatured ? 'rgba(255,255,255,0.9)' : '#64748b') + ';font-size:13px;margin:8px 0">✓ ' + this.escapeHtml(f) + '</div>'; });
            preview += '</div><button style="width:100%;padding:10px;background:' + (priceFeatured ? '#fff' : '#6366f1') + ';color:' + (priceFeatured ? '#6366f1' : '#fff') + ';border:none;border-radius:6px;font-weight:500">Choose Plan</button></div>';
            break;
            
        case 'blurb':
            const blurbIcon = content.icon || 'star';
            const blurbTitle = content.title || 'Feature';
            const blurbText = content.text || 'Description...';
            const blurbLayout = content.layout || 'top';
            const blurbIconSize = content.icon_size || '48px';
            const blurbIconColor = content.icon_color || '#6366f1';
            const blurbIconHtml = typeof this.renderIconFromFormat === 'function' ? this.renderIconFromFormat(blurbIcon) : '⭐';
            const flexDir = blurbLayout === 'left' ? 'row' : (blurbLayout === 'right' ? 'row-reverse' : 'column');
            preview = '<div style="display:flex;flex-direction:' + flexDir + ';align-items:' + (blurbLayout === 'top' ? 'center' : 'flex-start') + ';gap:16px;text-align:' + (blurbLayout === 'top' ? 'center' : 'left') + '">';
            preview += '<div style="font-size:' + blurbIconSize + ';color:' + blurbIconColor + ';flex-shrink:0">' + blurbIconHtml + '</div>';
            preview += '<div><h4 style="margin:0 0 8px;font-size:18px;color:#333">' + this.escapeHtml(blurbTitle) + '</h4>';
            preview += '<p style="margin:0;font-size:14px;color:#666">' + this.escapeHtml(blurbText.substring(0, 80)) + '</p></div></div>';
            break;
            
        case 'team':
            const teamName = content.name || 'John Doe';
            const teamRole = content.role || 'Developer';
            // Support both 'photo' (Content panel) and 'image' (legacy)
            const teamImage = content.photo || content.image || '';
            const teamBio = content.bio || '';
            preview = '<div style="text-align:center">';
            if (teamImage) {
                preview += '<img src="' + this.escapeHtml(teamImage) + '" style="width:120px;height:120px;border-radius:50%;object-fit:cover;margin-bottom:12px">';
            } else {
                preview += '<div style="width:120px;height:120px;background:#e2e8f0;border-radius:50%;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:48px">👤</div>';
            }
            preview += '<div style="font-size:18px;font-weight:600;color:#333">' + this.escapeHtml(teamName) + '</div>';
            preview += '<div style="font-size:14px;color:#6366f1">' + this.escapeHtml(teamRole) + '</div>';
            if (teamBio) preview += '<p style="margin:12px 0 0;font-size:13px;color:#666">' + this.escapeHtml(teamBio.substring(0, 60)) + (teamBio.length > 60 ? '...' : '') + '</p>';
            preview += '</div>';
            break;
            
        case 'cta':
            const ctaTitle = content.title || 'Ready to start?';
            // Support both 'subtitle' (Content panel) and 'text' (legacy)
            const ctaText = content.subtitle || content.text || 'Get started today';
            const ctaBtnText = content.button_text || 'Get Started';
            const ctaBg = content.background_color || '#6366f1';
            preview = '<div style="background:' + ctaBg + ';padding:32px;border-radius:12px;text-align:center">';
            preview += '<h3 style="margin:0 0 8px;font-size:24px;color:#fff">' + this.escapeHtml(ctaTitle) + '</h3>';
            preview += '<p style="margin:0 0 20px;color:rgba(255,255,255,0.9)">' + this.escapeHtml(ctaText) + '</p>';
            preview += '<button style="background:#fff;color:' + ctaBg + ';border:none;padding:12px 24px;border-radius:6px;font-weight:600;cursor:pointer">' + this.escapeHtml(ctaBtnText) + '</button></div>';
            break;
            
        case 'hero':
            const heroTitle = content.title || 'Welcome to Our Site';
            const heroSubtitle = content.subtitle || 'We build amazing digital experiences';
            const heroBgType = content.background_type || 'color';
            const heroBgColor = content.background_color || '#1e1e2e';
            // Support both bg_image (Content panel) and background_image (legacy)
            const heroBgImage = content.bg_image || content.background_image || '';
            const heroGradientStart = content.gradient_start || '#667eea';
            const heroGradientEnd = content.gradient_end || '#764ba2';
            // Support both overlay_opacity (float) and overlay_color (rgba string)
            const heroOverlayOpacity = content.overlay_opacity !== undefined ? content.overlay_opacity : 0.5;
            const heroOverlay = content.overlay_color || 'rgba(0,0,0,' + heroOverlayOpacity + ')';
            const heroTextColor = content.text_color || '#ffffff';
            const heroAlign = content.alignment || 'center';
            const heroPrimaryText = content.button_text || content.primary_button_text || 'Get Started';
            const heroSecondaryText = content.secondary_button_text || 'Learn More';
            const heroShowSecondary = content.show_secondary === true;
            const heroTitleTypo = getTypo('title');
            const heroSubtitleTypo = getTypo('subtitle');
            const heroButtonTypo = getTypo('button');
            let heroBgStyle = '';
            // If bg_image exists, use it regardless of background_type
            if (heroBgImage) {
                heroBgStyle = 'background:linear-gradient(' + heroOverlay + ',' + heroOverlay + '),url(' + this.escapeHtml(heroBgImage) + ') center/cover no-repeat';
            } else if (heroBgType === 'gradient') {
                heroBgStyle = 'background:linear-gradient(135deg,' + heroGradientStart + ',' + heroGradientEnd + ')';
            } else {
                heroBgStyle = 'background:' + heroBgColor;
            }
            preview = '<div style="' + heroBgStyle + ';min-height:200px;padding:40px 20px;display:flex;flex-direction:column;justify-content:center;align-items:' + heroAlign + ';text-align:' + heroAlign + ';border-radius:8px">';
            preview += '<h1 style="margin:0 0 12px 0;font-size:28px;font-weight:700;color:' + heroTextColor + ';' + heroTitleTypo + '">' + this.escapeHtml(heroTitle) + '</h1>';
            preview += '<p style="margin:0 0 20px 0;font-size:16px;color:' + heroTextColor + ';opacity:0.9;' + heroSubtitleTypo + '">' + this.escapeHtml(heroSubtitle) + '</p>';
            preview += '<div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:' + heroAlign + '">';
            preview += '<button style="padding:10px 20px;background:#0073e6;color:#fff;border:none;border-radius:4px;font-size:14px;cursor:pointer;' + heroButtonTypo + '">' + this.escapeHtml(heroPrimaryText) + '</button>';
            if (heroShowSecondary) {
                preview += '<button style="padding:10px 20px;background:transparent;color:' + heroTextColor + ';border:2px solid ' + heroTextColor + ';border-radius:4px;font-size:14px;cursor:pointer;' + heroButtonTypo + '">' + this.escapeHtml(heroSecondaryText) + '</button>';
            }
            preview += '</div></div>';
            break;
            
        case 'slider':
            const slides = content.slides || [{ title: 'Slide 1', image: '' }];
            const slide = slides[0] || {};
            const slideBg = slide.image ? 'url(' + this.escapeHtml(slide.image) + ') center/cover' : 'linear-gradient(135deg,#667eea,#764ba2)';
            preview = '<div style="position:relative;background:' + slideBg + ';min-height:180px;border-radius:8px;overflow:hidden">';
            preview += '<div style="position:absolute;inset:0;background:rgba(0,0,0,0.3);display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;padding:20px">';
            preview += '<h3 style="margin:0 0 8px;font-size:22px;color:#fff">' + this.escapeHtml(slide.title || 'Slide Title') + '</h3>';
            if (slide.text) preview += '<p style="margin:0;font-size:14px;color:#fff;opacity:0.9">' + this.escapeHtml(slide.text) + '</p>';
            preview += '</div>';
            if (slides.length > 1) {
                preview += '<div style="position:absolute;left:8px;top:50%;transform:translateY(-50%);width:28px;height:28px;background:rgba(255,255,255,0.9);border-radius:50%;display:flex;align-items:center;justify-content:center">❮</div>';
                preview += '<div style="position:absolute;right:8px;top:50%;transform:translateY(-50%);width:28px;height:28px;background:rgba(255,255,255,0.9);border-radius:50%;display:flex;align-items:center;justify-content:center">❯</div>';
            }
            preview += '<div style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,0.6);color:#fff;padding:2px 8px;border-radius:4px;font-size:11px">' + slides.length + ' slides</div></div>';
            break;
            
        case 'counter':
            const counterNum = content.number || 100;
            const counterSuffix = content.suffix || '+';
            const counterTitle = content.title || 'Clients';
            const counterColor = content.color || '#6366f1';
            preview = '<div style="text-align:center;padding:20px">';
            preview += '<div style="font-size:48px;font-weight:700;color:' + counterColor + '">' + counterNum + counterSuffix + '</div>';
            preview += '<div style="font-size:16px;color:#64748b;margin-top:8px">' + this.escapeHtml(counterTitle) + '</div></div>';
            break;
            
        case 'countdown':
            const cdTitle = content.title || 'Coming Soon';
            preview = '<div style="text-align:center;padding:20px">';
            preview += '<div style="font-size:18px;color:#333;margin-bottom:16px">' + this.escapeHtml(cdTitle) + '</div>';
            preview += '<div style="display:flex;justify-content:center;gap:16px">';
            ['Days', 'Hours', 'Mins', 'Secs'].forEach((label, i) => {
                const val = [12, 8, 45, 30][i];
                preview += '<div style="text-align:center"><div style="font-size:32px;font-weight:700;color:#6366f1">' + val + '</div><div style="font-size:11px;color:#64748b">' + label + '</div></div>';
            });
            preview += '</div></div>';
            break;
            
        case 'progress':
            const progValue = content.value || 75;
            const progLabel = content.label || 'Progress';
            const progColor = content.color || '#6366f1';
            preview = '<div style="padding:8px 0">';
            preview += '<div style="display:flex;justify-content:space-between;margin-bottom:8px"><span style="font-size:14px;color:#333">' + this.escapeHtml(progLabel) + '</span><span style="font-size:14px;color:#64748b">' + progValue + '%</span></div>';
            preview += '<div style="height:12px;background:#e2e8f0;border-radius:6px;overflow:hidden"><div style="width:' + progValue + '%;height:100%;background:' + progColor + ';border-radius:6px"></div></div></div>';
            break;
            
        case 'bar_counters':
            const bars = content.bars || [{ label: 'Skill', value: 80 }];
            preview = '<div style="display:flex;flex-direction:column;gap:16px">';
            bars.slice(0, 4).forEach(bar => {
                preview += '<div><div style="display:flex;justify-content:space-between;margin-bottom:6px"><span style="font-size:13px;color:#333">' + this.escapeHtml(bar.label) + '</span><span style="font-size:13px;color:#64748b">' + (bar.value || 0) + '%</span></div>';
                preview += '<div style="height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden"><div style="width:' + (bar.value || 0) + '%;height:100%;background:#6366f1;border-radius:4px"></div></div></div>';
            });
            preview += '</div>';
            break;
            
        case 'circle_counter':
            const ccValue = content.value || 75;
            const ccTitle = content.title || 'Complete';
            const ccColor = content.color || '#6366f1';
            const ccSize = 120;
            const ccStroke = 8;
            const ccRadius = (ccSize - ccStroke) / 2;
            const ccCircumference = 2 * Math.PI * ccRadius;
            const ccOffset = ccCircumference - (ccValue / 100) * ccCircumference;
            preview = '<div style="text-align:center"><svg width="' + ccSize + '" height="' + ccSize + '" style="transform:rotate(-90deg)"><circle cx="' + ccSize/2 + '" cy="' + ccSize/2 + '" r="' + ccRadius + '" fill="none" stroke="#e2e8f0" stroke-width="' + ccStroke + '"/><circle cx="' + ccSize/2 + '" cy="' + ccSize/2 + '" r="' + ccRadius + '" fill="none" stroke="' + ccColor + '" stroke-width="' + ccStroke + '" stroke-dasharray="' + ccCircumference + '" stroke-dashoffset="' + ccOffset + '" stroke-linecap="round"/></svg>';
            preview += '<div style="margin-top:-' + (ccSize/2 + 20) + 'px;font-size:24px;font-weight:700;color:' + ccColor + '">' + ccValue + '%</div>';
            preview += '<div style="margin-top:' + (ccSize/2 - 20) + 'px;font-size:14px;color:#64748b">' + this.escapeHtml(ccTitle) + '</div></div>';
            break;
            
        case 'menu':
            const menuItems = content.items || [{ label: 'Home', url: '/' }, { label: 'About', url: '/about' }];
            const menuOrientation = content.orientation || 'horizontal';
            const menuBg = content.background_color || 'transparent';
            const menuColor = content.text_color || '#333';
            const menuFlexDir = menuOrientation === 'vertical' ? 'column' : 'row';
            preview = '<nav style="display:flex;flex-direction:' + menuFlexDir + ';gap:' + (menuOrientation === 'vertical' ? '8px' : '24px') + ';background:' + menuBg + ';padding:12px">';
            menuItems.slice(0, 6).forEach((item, i) => {
                preview += '<a style="color:' + menuColor + ';text-decoration:none;font-weight:' + (i === 0 ? '600' : '400') + ';font-size:14px">' + this.escapeHtml(item.label) + '</a>';
            });
            preview += '</nav>';
            break;
            
        case 'logo':
            const logoImage = content.image || '';
            const logoAlt = content.image_alt || 'Logo';
            const logoMaxHeight = content.max_height || '60px';
            if (logoImage) {
                preview = '<img src="' + this.escapeHtml(logoImage) + '" alt="' + this.escapeHtml(logoAlt) + '" style="max-height:' + logoMaxHeight + ';width:auto">';
            } else {
                preview = '<div style="display:flex;align-items:center;gap:8px"><div style="width:40px;height:40px;background:#6366f1;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700">L</div><span style="font-size:20px;font-weight:700;color:#333">Logo</span></div>';
            }
            break;
            
        case 'newsletter':
            const nlTitle = content.title || 'Subscribe';
            const nlPlaceholder = content.placeholder || 'Enter email';
            const nlBtnText = content.button_text || 'Subscribe';
            preview = '<div style="background:#f8fafc;padding:24px;border-radius:8px;text-align:center">';
            preview += '<div style="font-size:18px;font-weight:600;color:#333;margin-bottom:16px">' + this.escapeHtml(nlTitle) + '</div>';
            preview += '<div style="display:flex;gap:8px;max-width:400px;margin:0 auto">';
            preview += '<input type="email" placeholder="' + this.escapeHtml(nlPlaceholder) + '" style="flex:1;padding:10px 12px;border:1px solid #e2e8f0;border-radius:6px">';
            preview += '<button style="background:#6366f1;color:#fff;border:none;padding:10px 20px;border-radius:6px;font-weight:500">' + this.escapeHtml(nlBtnText) + '</button></div></div>';
            break;
            
        case 'login':
            const showRegister = content.show_register !== false;
            preview = '<div style="max-width:320px;margin:0 auto;padding:24px;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1)">';
            preview += '<div style="text-align:center;margin-bottom:20px;font-size:20px;font-weight:600">Login</div>';
            preview += '<div style="margin-bottom:12px"><input placeholder="Email" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px"></div>';
            preview += '<div style="margin-bottom:16px"><input type="password" placeholder="Password" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px"></div>';
            preview += '<button style="width:100%;background:#6366f1;color:#fff;border:none;padding:12px;border-radius:6px;font-weight:500">Sign In</button>';
            if (showRegister) preview += '<div style="text-align:center;margin-top:12px;font-size:13px;color:#64748b">Don\'t have an account? <a style="color:#6366f1">Register</a></div>';
            preview += '</div>';
            break;
            
        case 'portfolio':
            const portItems = content.items || [{ title: 'Project 1', category: 'Design' }];
            const portCols = content.columns || 3;
            preview = '<div style="display:grid;grid-template-columns:repeat(' + Math.min(portCols, 3) + ',1fr);gap:16px">';
            portItems.slice(0, 6).forEach(item => {
                preview += '<div style="position:relative;aspect-ratio:4/3;background:' + (item.image ? 'url(' + this.escapeHtml(item.image) + ') center/cover' : 'linear-gradient(135deg,#667eea,#764ba2)') + ';border-radius:8px;overflow:hidden">';
                preview += '<div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);display:flex;flex-direction:column;justify-content:flex-end;padding:12px">';
                preview += '<div style="color:#fff;font-weight:600">' + this.escapeHtml(item.title) + '</div>';
                preview += '<div style="color:rgba(255,255,255,0.8);font-size:12px">' + this.escapeHtml(item.category || '') + '</div></div></div>';
            });
            preview += '</div>';
            break;
            
        case 'blog':
            const blogCols = content.columns || 3;
            const samplePosts = [{ title: 'Blog Post Title', date: 'Dec 10' }, { title: 'Another Post', date: 'Dec 8' }, { title: 'Third Article', date: 'Dec 5' }];
            preview = '<div style="display:grid;grid-template-columns:repeat(' + Math.min(blogCols, 3) + ',1fr);gap:16px">';
            samplePosts.slice(0, 3).forEach(post => {
                preview += '<div style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.1)">';
                preview += '<div style="height:100px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:#fff;font-size:24px">📷</div>';
                preview += '<div style="padding:12px"><div style="font-weight:600;color:#333;margin-bottom:4px">' + this.escapeHtml(post.title) + '</div>';
                preview += '<div style="font-size:12px;color:#64748b">' + post.date + '</div></div></div>';
            });
            preview += '</div>';
            break;
            
        case 'post_slider':
        case 'fullwidth_post_slider':
            const psCount = content.posts_count || 5;
            preview = '<div style="position:relative;background:linear-gradient(135deg,#1e293b,#334155);min-height:180px;border-radius:8px;overflow:hidden">';
            preview += '<div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;padding:24px">';
            preview += '<div style="font-size:10px;color:#6366f1;text-transform:uppercase;margin-bottom:8px">Category</div>';
            preview += '<div style="font-size:20px;font-weight:700;color:#fff;margin-bottom:8px">Sample Blog Post Title</div>';
            preview += '<div style="font-size:13px;color:rgba(255,255,255,0.7)">Brief excerpt from the post content...</div></div>';
            preview += '<div style="position:absolute;left:8px;top:50%;transform:translateY(-50%);width:28px;height:28px;background:rgba(255,255,255,0.9);border-radius:50%;display:flex;align-items:center;justify-content:center">❮</div>';
            preview += '<div style="position:absolute;right:8px;top:50%;transform:translateY(-50%);width:28px;height:28px;background:rgba(255,255,255,0.9);border-radius:50%;display:flex;align-items:center;justify-content:center">❯</div>';
            preview += '<div style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,0.6);color:#fff;padding:2px 8px;border-radius:4px;font-size:11px">📰 ' + psCount + ' posts</div></div>';
            break;
            
        case 'fullwidth_slider':
            const fwSlides = content.slides || [{ title: 'Slide 1' }];
            const fwSlide = fwSlides[0] || {};
            const fwBg = fwSlide.image ? 'url(' + this.escapeHtml(fwSlide.image) + ') center/cover' : 'linear-gradient(135deg,#667eea,#764ba2)';
            preview = '<div style="position:relative;background:' + fwBg + ';min-height:250px;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center">';
            preview += '<div style="position:absolute;inset:0;background:rgba(0,0,0,0.4)"></div>';
            preview += '<div style="position:relative;z-index:1;padding:20px">';
            preview += '<h2 style="margin:0 0 12px;font-size:32px;color:#fff">' + this.escapeHtml(fwSlide.title || 'Fullwidth Slide') + '</h2>';
            if (fwSlide.text) preview += '<p style="margin:0 0 20px;color:rgba(255,255,255,0.9);font-size:16px">' + this.escapeHtml(fwSlide.text) + '</p>';
            if (fwSlide.button_text) preview += '<button style="background:#fff;color:#333;border:none;padding:12px 24px;border-radius:6px;font-weight:500">' + this.escapeHtml(fwSlide.button_text) + '</button>';
            preview += '</div>';
            if (fwSlides.length > 1) {
                preview += '<div style="position:absolute;left:16px;top:50%;transform:translateY(-50%);width:40px;height:40px;background:rgba(255,255,255,0.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px">❮</div>';
                preview += '<div style="position:absolute;right:16px;top:50%;transform:translateY(-50%);width:40px;height:40px;background:rgba(255,255,255,0.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px">❯</div>';
            }
            preview += '<div style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,0.6);color:#fff;padding:4px 10px;border-radius:4px;font-size:12px">' + fwSlides.length + ' slides</div></div>';
            break;
            
        case 'fullwidth_menu':
            const fwMenuItems = content.items || [{ label: 'Home' }, { label: 'About' }, { label: 'Services' }, { label: 'Contact' }];
            const fwMenuBg = content.background_color || '#1e1e2e';
            preview = '<nav style="background:' + fwMenuBg + ';padding:16px 24px;display:flex;justify-content:space-between;align-items:center">';
            preview += '<div style="font-size:20px;font-weight:700;color:#fff">Logo</div>';
            preview += '<div style="display:flex;gap:32px">';
            fwMenuItems.slice(0, 6).forEach((item, i) => {
                preview += '<a style="color:' + (i === 0 ? '#6366f1' : '#fff') + ';text-decoration:none;font-size:14px">' + this.escapeHtml(item.label) + '</a>';
            });
            preview += '</div></nav>';
            break;
            
        case 'fullwidth_portfolio':
            const fwPortItems = content.items || [{ title: 'Project 1', category: 'Design' }];
            preview = '<div style="display:grid;grid-template-columns:repeat(4,1fr)">';
            fwPortItems.slice(0, 4).forEach(item => {
                preview += '<div style="position:relative;aspect-ratio:1;background:' + (item.image ? 'url(' + this.escapeHtml(item.image) + ') center/cover' : 'linear-gradient(135deg,#667eea,#764ba2)') + '">';
                preview += '<div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);opacity:0.8;display:flex;flex-direction:column;justify-content:center;align-items:center;color:#fff">';
                preview += '<div style="font-weight:600">' + this.escapeHtml(item.title) + '</div>';
                preview += '<div style="font-size:12px;opacity:0.8">' + this.escapeHtml(item.category || '') + '</div></div></div>';
            });
            preview += '</div>';
            break;
            
        case 'fullwidth_image':
            const fwImg = content.src || content.image || '';
            if (fwImg) {
                preview = '<img src="' + this.escapeHtml(fwImg) + '" style="width:100%;height:auto;display:block">';
            } else {
                preview = '<div style="background:linear-gradient(135deg,#e2e8f0,#cbd5e1);height:200px;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:48px">🌅</div>';
            }
            break;
            
        case 'fullwidth_map':
            const fwMapAddress = content.address || 'Enter address...';
            preview = '<div style="background:linear-gradient(135deg,#e0f2fe,#bae6fd);height:300px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px">';
            preview += '<span style="font-size:64px">🗺️</span>';
            preview += '<span style="font-size:14px;color:#0369a1">' + this.escapeHtml(fwMapAddress.substring(0, 50)) + '</span></div>';
            break;
            
        case 'fullwidth_code':
            const fwCode = content.code || '// Your code here';
            preview = '<pre style="background:#1e293b;color:#a5f3fc;padding:20px;margin:0;font-size:13px;overflow-x:auto">' + this.escapeHtml(fwCode.substring(0, 200)) + '</pre>';
            break;
            
        case 'fullwidth_header':
            const fwHdrTitle = content.title || 'Page Title';
            const fwHdrSubtitle = content.subtitle || '';
            const fwHdrBg = content.background_color || '#1e1e2e';
            preview = '<div style="background:' + fwHdrBg + ';padding:60px 24px;text-align:center">';
            preview += '<h1 style="margin:0;font-size:36px;color:#fff">' + this.escapeHtml(fwHdrTitle) + '</h1>';
            if (fwHdrSubtitle) preview += '<p style="margin:12px 0 0;font-size:18px;color:rgba(255,255,255,0.8)">' + this.escapeHtml(fwHdrSubtitle) + '</p>';
            preview += '</div>';
            break;
            
        case 'header':
            const hdrLogo = content.logo || '';
            const hdrTitle = content.title || 'Site Name';
            preview = '<header style="background:#fff;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 4px rgba(0,0,0,0.1)">';
            if (hdrLogo) {
                preview += '<img src="' + this.escapeHtml(hdrLogo) + '" style="height:40px">';
            } else {
                preview += '<div style="font-size:20px;font-weight:700;color:#333">' + this.escapeHtml(hdrTitle) + '</div>';
            }
            preview += '<nav style="display:flex;gap:24px"><a style="color:#333;text-decoration:none">Home</a><a style="color:#64748b;text-decoration:none">About</a><a style="color:#64748b;text-decoration:none">Contact</a></nav></header>';
            break;
            
        case 'footer':
            preview = '<footer style="background:#1e293b;padding:32px 24px;color:#fff">';
            preview += '<div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:24px">';
            preview += '<div><div style="font-weight:600;margin-bottom:12px">Company</div><div style="font-size:13px;color:#94a3b8">About Us<br>Services<br>Contact</div></div>';
            preview += '<div><div style="font-weight:600;margin-bottom:12px">Legal</div><div style="font-size:13px;color:#94a3b8">Privacy<br>Terms<br>Cookies</div></div>';
            preview += '<div><div style="font-weight:600;margin-bottom:12px">Connect</div><div style="display:flex;gap:12px;font-size:18px">📘 🐦 📸</div></div>';
            preview += '</div><div style="margin-top:24px;padding-top:16px;border-top:1px solid #334155;font-size:12px;color:#64748b">© 2025 Company. All rights reserved.</div></footer>';
            break;
            
        case 'navigation':
        case 'sidebar':
            preview = '<div style="background:#f8fafc;padding:16px;border-radius:8px">';
            preview += '<div style="font-weight:600;color:#333;margin-bottom:12px">' + (type === 'sidebar' ? 'Sidebar' : 'Navigation') + '</div>';
            preview += '<nav style="display:flex;flex-direction:column;gap:8px">';
            ['Home', 'About', 'Services', 'Contact'].forEach((item, i) => {
                preview += '<a style="color:' + (i === 0 ? '#6366f1' : '#64748b') + ';text-decoration:none;font-size:14px;padding:8px 12px;background:' + (i === 0 ? '#e0e7ff' : 'transparent') + ';border-radius:4px">' + item + '</a>';
            });
            preview += '</nav></div>';
            break;
            
        case 'search':
            preview = '<div style="display:flex;gap:8px">';
            preview += '<input type="search" placeholder="Search..." style="flex:1;padding:10px 12px;border:1px solid #e2e8f0;border-radius:6px">';
            preview += '<button style="background:#6366f1;color:#fff;border:none;padding:10px 16px;border-radius:6px">🔍</button></div>';
            break;
            
        case 'carousel':
            const carItems = content.items || [{ image: '' }, { image: '' }, { image: '' }];
            preview = '<div style="display:flex;gap:12px;overflow:hidden">';
            carItems.slice(0, 4).forEach((item, i) => {
                const opacity = i === 0 ? '1' : (i === 1 ? '0.7' : '0.4');
                preview += '<div style="flex:0 0 200px;height:120px;background:' + (item.image ? 'url(' + this.escapeHtml(item.image) + ') center/cover' : 'linear-gradient(135deg,#667eea,#764ba2)') + ';border-radius:8px;opacity:' + opacity + '"></div>';
            });
            preview += '</div>';
            break;
            
        case 'video_slider':
            const vsVideos = content.videos || [{ title: 'Video 1' }];
            preview = '<div style="position:relative;background:#1e293b;padding-bottom:56.25%;border-radius:8px;overflow:hidden">';
            preview += '<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center">';
            preview += '<div style="width:64px;height:64px;background:rgba(255,255,255,0.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;cursor:pointer">▶</div>';
            preview += '</div>';
            preview += '<div style="position:absolute;bottom:12px;left:12px;color:#fff;font-weight:500">' + this.escapeHtml(vsVideos[0]?.title || 'Video') + '</div>';
            preview += '<div style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,0.6);color:#fff;padding:2px 8px;border-radius:4px;font-size:11px">🎬 ' + vsVideos.length + '</div></div>';
            break;
            
        case 'post_title':
            preview = '<h1 style="margin:0;font-size:32px;color:#333">Post Title</h1>';
            break;
            
        case 'post_content':
            preview = '<div style="color:#475569;line-height:1.7"><p style="margin:0 0 12px">This is sample post content. The actual content will be displayed here when viewing a blog post...</p><p style="margin:0">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p></div>';
            break;
            
        case 'posts_navigation':
            preview = '<div style="display:flex;justify-content:space-between;padding:16px 0;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0">';
            preview += '<a style="color:#6366f1;text-decoration:none">← Previous Post</a>';
            preview += '<a style="color:#6366f1;text-decoration:none">Next Post →</a></div>';
            break;
            
        case 'comments':
            preview = '<div style="padding:16px 0"><div style="font-size:18px;font-weight:600;color:#333;margin-bottom:16px">💬 Comments (3)</div>';
            preview += '<div style="display:flex;gap:12px;margin-bottom:16px"><div style="width:40px;height:40px;background:#e2e8f0;border-radius:50%;display:flex;align-items:center;justify-content:center">👤</div><div style="flex:1;background:#f8fafc;padding:12px;border-radius:8px"><div style="font-weight:500;color:#333">John Doe</div><div style="font-size:13px;color:#64748b;margin-top:4px">Great article! Thanks for sharing.</div></div></div></div>';
            break;
            
        case 'signup':
            preview = '<div style="background:#f8fafc;padding:24px;border-radius:8px;text-align:center">';
            preview += '<div style="font-size:20px;font-weight:600;color:#333;margin-bottom:8px">Sign Up</div>';
            preview += '<p style="color:#64748b;margin:0 0 16px">Create your account</p>';
            preview += '<div style="display:flex;flex-direction:column;gap:12px;max-width:300px;margin:0 auto">';
            preview += '<input placeholder="Name" style="padding:10px;border:1px solid #e2e8f0;border-radius:6px">';
            preview += '<input placeholder="Email" style="padding:10px;border:1px solid #e2e8f0;border-radius:6px">';
            preview += '<button style="background:#6366f1;color:#fff;border:none;padding:12px;border-radius:6px;font-weight:500">Sign Up</button></div></div>';
            break;
            
        default:
            preview = '<div style="color:#94a3b8;padding:16px;text-align:center">' + icon + ' [' + type + ']</div>';
    }

    // NOTE: This function now returns ONLY the preview content, not the full wrapper.
    // The wrapper (including onclick handlers with proper indices) is created by
    // TB.renderModule in tb-render.js which calls this function.
    return preview;
};

// Helper: Get video thumbnail from URL
TB.getVideoThumbnail = function(url) {
    if (!url) return '';
    const ytMatch = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    if (ytMatch) return 'https://img.youtube.com/vi/' + ytMatch[1] + '/maxresdefault.jpg';
    const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
    if (vimeoMatch) return '';
    return '';
};

// Helper: Render icon from various formats
TB.renderIconFromFormat = function(iconStr) {
    if (!iconStr) return '⭐';
    if (/\p{Emoji}/u.test(iconStr)) return iconStr;
    if (iconStr.startsWith('fa:') || iconStr.startsWith('fa-')) {
        const iconName = iconStr.replace('fa:', '').replace('fa-', '');
        return '<i class="fas fa-' + iconName + '"></i>';
    }
    if (iconStr.startsWith('material:')) {
        const iconName = iconStr.replace('material:', '');
        return '<span class="material-icons">' + iconName + '</span>';
    }
    const emojiMap = { star: '⭐', heart: '❤️', check: '✓', arrow: '→', home: '🏠', user: '👤', mail: '✉️', phone: '📞', settings: '⚙️', search: '🔍' };
    return emojiMap[iconStr] || '⭐';
};

console.log('TB Modules Preview loaded');
