/**
 * Theme Builder 3.0 - Core Module
 * Extends TB object with core functionality
 */

// ═══════════════════════════════════════════════════════════
// DEFAULT CONTENT FOR MODULES
// ═══════════════════════════════════════════════════════════
TB.getDefaultContent = function(type) {
    const defaults = {
        text: { text: '' },
        heading: { text: 'Heading', tag: 'h2' },
        image: { src: '', alt: '' },
        button: { text: 'Click Here', url: '#', style: 'primary' },
        divider: { style: 'solid' },
        spacer: { height: '40px' },
        video: { url: '', autoplay: false, controls: true, loop: false },
        audio: { audio_url: '', title: '', artist: '', album: '', cover_image: '', style: 'default', show_cover: true, cover_size: '120px', background_color: '#1e1e2e', text_color: '#ffffff', accent_color: '#0073e6', autoplay: false, loop: false, show_download: false },
        code: { code: '', language: 'javascript' },
        html: { html: '' },
        gallery: { images: [], columns: 3, gap: '10px', lightbox: true },
        list: { items: ['Item 1', 'Item 2', 'Item 3'], type: 'unordered', icon: 'bullet' },
        quote: { quote: '', author: '', source: '' },
        accordion: { items: [{ title: 'Accordion Item 1', content: 'Content here...' }], allow_multiple: false },
        toggle: { items: [{ title: 'Toggle Item 1', content: 'Content here...' }], style: 'default', open_by_default: false },
        tabs: { tabs: [{ title: 'Tab 1', content: 'Content here...' }] },
        map: { address: '', lat: '', lng: '', zoom: 14 },
        icon: { icon: '⭐', size: '48px', color: '#6366f1' },
        social: { networks: [{ name: 'facebook', url: '#' }], style: 'icons', size: '24px' },
        contact_form: { fields: [{ type: 'text', label: 'Name', required: true }, { type: 'email', label: 'Email', required: true }, { type: 'textarea', label: 'Message', required: true }], submit_text: 'Send Message', recipient: '' },
        cta: { title: 'Call to Action', subtitle: '', button_text: 'Get Started', button_url: '#' },
        countdown: { target_date: '', style: 'default' },
        progress: { value: 50, max: 100, label: '', show_percentage: true },
        testimonial: { quote: '', author: '', role: '', avatar: '' },
        pricing: { title: 'Basic', price: '$9.99', period: '/month', features: ['Feature 1', 'Feature 2'], button_text: 'Choose Plan', button_url: '#', featured: false },
        team: { name: '', role: '', photo: '', bio: '', social: [] },
        slider: { slides: [{ image: '', title: '', description: '' }], autoplay: true, interval: 5000 },
        hero: { title: 'Hero Title', subtitle: '', bg_image: '', buttons: [] },
        features: { items: [{ icon: '⭐', title: 'Feature 1', description: '' }], columns: 3 },
        stats: { items: [{ value: '100+', label: 'Customers' }] },
        faq: { items: [{ question: 'Question?', answer: 'Answer.' }] },
        timeline: { items: [{ date: '2024', title: 'Event', description: '' }] },
        logo_grid: { logos: [], columns: 4 },
        before_after: { before_image: '', after_image: '', orientation: 'horizontal' },
        hotspots: { image: '', spots: [] },
        table: { headers: ['Column 1', 'Column 2'], rows: [['Cell 1', 'Cell 2']] },
        menu: { items: [{ label: 'Home', url: '#' }] },
        fullwidth_menu: { items: [{ label: 'Home', url: '#' }], style: 'default' },
        bar_counters: { bars: [{ label: 'Skill', percent: 75, color: '#6366f1' }] },
        video_slider: { videos: [{ url: '', title: '' }] },
        post_title: { tag: 'h1', link: false },
        post_content: {},
        post_excerpt: { length: 200 },
        featured_image: { size: 'large', link: false },
        post_meta: { show_date: true, show_author: true, show_categories: true },
        author_box: { show_avatar: true, show_bio: true, show_social: true },
        related_posts: { count: 3, columns: 3 },
        posts_navigation: { prev_text: 'Previous', next_text: 'Next' },
        comments: { title: 'Comments', show_form: true },
        breadcrumbs: { separator: '/' },
        search: { placeholder: 'Search...', button_text: 'Search' },
        sidebar: { widget_area: 'default' },
        signup: { title: 'Subscribe', button_text: 'Subscribe', placeholder: 'Enter email' }
    };
    return JSON.parse(JSON.stringify(defaults[type] || {}));
};

// ═══════════════════════════════════════════════════════════
// ICON HELPERS
// ═══════════════════════════════════════════════════════════
TB.renderIconFromFormat = function(iconValue) {
    if (!iconValue) return '⭐';
    if (iconValue.startsWith('fa:')) {
        const iconClass = iconValue.substring(3);
        return '<i class="' + iconClass + '"></i>';
    }
    if (iconValue.startsWith('lucide:')) {
        const iconName = iconValue.substring(7);
        return '<i data-lucide="' + iconName + '"></i>';
    }
    return iconValue;
};

// ═══════════════════════════════════════════════════════════
// ICON PICKER
// ═══════════════════════════════════════════════════════════
TB.iconPickerCallback = null;
TB.currentIconStyle = 'fontawesome';

TB.openIconPicker = function(callback) {
    this.iconPickerCallback = callback;
    const overlay = document.getElementById('tb-icon-picker-overlay');
    if (overlay) {
        overlay.classList.add('active');
        this.loadIcons('fontawesome');
    }
};

TB.closeIconPicker = function() {
    const overlay = document.getElementById('tb-icon-picker-overlay');
    if (overlay) overlay.classList.remove('active');
    this.iconPickerCallback = null;
};

TB.switchIconStyle = function(style) {
    this.currentIconStyle = style;
    document.querySelectorAll('.tb-icon-tab').forEach(tab => {
        tab.classList.toggle('active', tab.textContent.toLowerCase().includes(style));
    });
    this.loadIcons(style);
};

TB.loadIcons = function(style) {
    const grid = document.getElementById('iconGrid');
    if (!grid) return;
    
    let icons = [];
    if (style === 'fontawesome') {
        icons = this.fontawesomeIcons || [];
        if (icons.length === 0) {
            grid.innerHTML = '<div style="padding:20px;text-align:center;color:var(--tb-text-muted)">Loading icons...</div>';
            return;
        }
    } else if (style === 'emoji') {
        icons = ['😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇','🥰','😍','🤩','😘','😗','😚','😙','🥲','😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','😬','🤥','😌','😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🤧','🥵','🥶','🥴','😵','🤯','🤠','🥳','🥸','😎','🤓','🧐','😕','😟','🙁','☹️','😮','😯','😲','😳','🥺','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣','😞','😓','😩','😫','🥱','😤','😡','😠','🤬','😈','👿','💀','☠️','💩','🤡','👹','👺','👻','👽','👾','🤖','😺','😸','😹','😻','😼','😽','🙀','😿','😾','🙈','🙉','🙊','💋','💌','💘','💝','💖','💗','💓','💞','💕','💟','❣️','💔','❤️','🧡','💛','💚','💙','💜','🤎','🖤','🤍','💯','💢','💥','💫','💦','💨','🕳️','💣','💬','👁️‍🗨️','🗨️','🗯️','💭','💤','👋','🤚','🖐️','✋','🖖','👌','🤌','🤏','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','🖕','👇','☝️','👍','👎','✊','👊','🤛','🤜','👏','🙌','👐','🤲','🤝','🙏','✍️','💅','🤳','💪','🦾','🦿','🦵','🦶','👂','🦻','👃','🧠','🫀','🫁','🦷','🦴','👀','👁️','👅','👄','👶','🧒','👦','👧','🧑','👱','👨','🧔','👩','🧓','👴','👵','🙍','🙎','🙅','🙆','💁','🙋','🧏','🙇','🤦','🤷','👮','🕵️','💂','🥷','👷','🤴','👸','👳','👲','🧕','🤵','👰','🤰','🤱','👼','🎒','👓','🕶️','🥽','🥼','🦺','👔','👕','👖','🧣','🧤','🧥','🧦','👗','👘','🥻','🩱','🩲','🩳','👙','👚','👛','👜','👝','🛍️','🎀','👞','👟','🥾','🥿','👠','👡','🩰','👢','👑','👒','🎩','🎓','🧢','⛑️','📿','💄','💍','💎','🔇','🔈','🔉','🔊','📢','📣','📯','🔔','🔕','🎼','🎵','🎶','🎙️','🎚️','🎛️','🎤','🎧','📻','🎷','🎸','🎹','🎺','🎻','🪕','🥁','📱','📲','☎️','📞','📟','📠','🔋','🔌','💻','🖥️','🖨️','⌨️','🖱️','🖲️','💽','💾','💿','📀','🧮','🎥','🎞️','📽️','🎬','📺','📷','📸','📹','📼','🔍','🔎','🕯️','💡','🔦','🏮','🪔','📔','📕','📖','📗','📘','📙','📚','📓','📒','📃','📜','📄','📰','🗞️','📑','🔖','🏷️','💰','💴','💵','💶','💷','💸','💳','🧾','✉️','📧','📨','📩','📤','📥','📦','📫','📪','📬','📭','📮','🗳️','✏️','✒️','🖋️','🖊️','🖌️','🖍️','📝','💼','📁','📂','🗂️','📅','📆','🗒️','🗓️','📇','📈','📉','📊','📋','📌','📍','📎','🖇️','📏','📐','✂️','🗃️','🗄️','🗑️','🔒','🔓','🔏','🔐','🔑','🗝️','🔨','🪓','⛏️','⚒️','🛠️','🗡️','⚔️','🔫','🏹','🛡️','🔧','🔩','⚙️','🗜️','⚖️','🦯','🔗','⛓️','🧰','🧲','⚗️','🧪','🧫','🧬','🔬','🔭','📡','💉','🩸','💊','🩹','🩺','🚪','🛏️','🛋️','🪑','🚽','🚿','🛁','🪒','🧴','🧷','🧹','🧺','🧻','🧼','🧽','🧯','🛒','🚬','⚰️','⚱️','🗿','🏧','🚮','🚰','♿','🚹','🚺','🚻','🚼','🚾','🛂','🛃','🛄','🛅','⚠️','🚸','⛔','🚫','🚳','🚭','🚯','🚱','🚷','📵','🔞','☢️','☣️','⬆️','↗️','➡️','↘️','⬇️','↙️','⬅️','↖️','↕️','↔️','↩️','↪️','⤴️','⤵️','🔃','🔄','🔙','🔚','🔛','🔜','🔝','🛐','⚛️','🕉️','✡️','☸️','☯️','✝️','☦️','☪️','☮️','🕎','🔯','♈','♉','♊','♋','♌','♍','♎','♏','♐','♑','♒','♓','⛎','🔀','🔁','🔂','▶️','⏩','⏭️','⏯️','◀️','⏪','⏮️','🔼','⏫','🔽','⏬','⏸️','⏹️','⏺️','⏏️','🎦','🔅','🔆','📶','📳','📴','♀️','♂️','⚧️','✖️','➕','➖','➗','♾️','‼️','⁉️','❓','❔','❕','❗','〰️','💱','💲','⚕️','♻️','⚜️','🔱','📛','🔰','⭕','✅','☑️','✔️','❌','❎','➰','➿','〽️','✳️','✴️','❇️','©️','®️','™️','#️⃣','*️⃣','0️⃣','1️⃣','2️⃣','3️⃣','4️⃣','5️⃣','6️⃣','7️⃣','8️⃣','9️⃣','🔟','🔠','🔡','🔢','🔣','🔤','🅰️','🆎','🅱️','🆑','🆒','🆓','ℹ️','🆔','Ⓜ️','🆕','🆖','🅾️','🆗','🅿️','🆘','🆙','🆚','🈁','🈂️','🈷️','🈶','🈯','🉐','🈹','🈚','🈲','🉑','🈸','🈴','🈳','㊗️','㊙️','🈺','🈵','🔴','🟠','🟡','🟢','🔵','🟣','🟤','⚫','⚪','🟥','🟧','🟨','🟩','🟦','🟪','🟫','⬛','⬜','◼️','◻️','◾','◽','▪️','▫️','🔶','🔷','🔸','🔹','🔺','🔻','💠','🔘','🔳','🔲','🏁','🚩','🎌','🏴','🏳️','🏳️‍🌈','🏳️‍⚧️','🏴‍☠️','🇦🇨'];
    } else if (style === 'lucide') {
        icons = ['activity','airplay','alert-circle','alert-octagon','alert-triangle','align-center','align-justify','align-left','align-right','anchor','aperture','archive','arrow-down','arrow-left','arrow-right','arrow-up','at-sign','award','bar-chart','battery','bell','bluetooth','bold','book','bookmark','box','briefcase','calendar','camera','cast','check','chevron-down','chevron-left','chevron-right','chevron-up','circle','clipboard','clock','cloud','code','coffee','columns','command','compass','copy','corner-down-left','corner-down-right','corner-left-down','corner-left-up','corner-right-down','corner-right-up','corner-up-left','corner-up-right','cpu','credit-card','crop','crosshair','database','delete','disc','dollar-sign','download','droplet','edit','external-link','eye','facebook','fast-forward','feather','file','film','filter','flag','folder','gift','git-branch','git-commit','git-merge','git-pull-request','github','gitlab','globe','grid','hard-drive','hash','headphones','heart','help-circle','home','image','inbox','info','instagram','italic','layers','layout','life-buoy','link','linkedin','list','loader','lock','log-in','log-out','mail','map','map-pin','maximize','menu','message-circle','message-square','mic','minimize','minus','monitor','moon','more-horizontal','more-vertical','move','music','navigation','octagon','package','paperclip','pause','pen-tool','percent','phone','pie-chart','play','plus','pocket','power','printer','radio','refresh-ccw','refresh-cw','repeat','rewind','rotate-ccw','rotate-cw','rss','save','scissors','search','send','server','settings','share','shield','shopping-bag','shopping-cart','shuffle','sidebar','skip-back','skip-forward','slack','slash','sliders','smartphone','speaker','square','star','stop-circle','sun','sunrise','sunset','tablet','tag','target','terminal','thermometer','thumbs-down','thumbs-up','toggle-left','toggle-right','trash','trending-down','trending-up','triangle','truck','tv','twitter','type','umbrella','underline','unlock','upload','user','user-check','user-minus','user-plus','user-x','users','video','voicemail','volume','volume-1','volume-2','volume-x','watch','wifi','wind','x','x-circle','x-square','youtube','zap','zoom-in','zoom-out'];
    }
    
    this.renderIconGrid(icons, style);
};

TB.renderIconGrid = function(icons, style) {
    const grid = document.getElementById('iconGrid');
    if (!grid) return;
    
    let html = '';
    icons.forEach(icon => {
        let display, value;
        if (style === 'fontawesome') {
            display = '<i class="' + icon + '"></i>';
            value = 'fa:' + icon;
        } else if (style === 'lucide') {
            display = '<i data-lucide="' + icon + '"></i>';
            value = 'lucide:' + icon;
        } else {
            display = icon;
            value = icon;
        }
        html += '<div class="tb-icon-item" data-icon="' + value + '" onclick="TB.selectIcon(\'' + value.replace(/'/g, "\\'") + '\')">' + display + '</div>';
    });
    
    grid.innerHTML = html;
    
    if (style === 'lucide' && typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
};

TB.filterIcons = function(query) {
    const items = document.querySelectorAll('.tb-icon-item');
    const q = query.toLowerCase();
    items.forEach(item => {
        const icon = item.dataset.icon.toLowerCase();
        item.style.display = icon.includes(q) ? '' : 'none';
    });
};

TB.selectIcon = function(icon) {
    const preview = document.getElementById('icon-picker-preview');
    const display = document.getElementById('icon-preview-display');
    const name = document.getElementById('icon-preview-name');
    const code = document.getElementById('icon-preview-code');
    
    if (preview) preview.style.display = 'flex';
    if (display) display.innerHTML = this.renderIconFromFormat(icon);
    if (name) name.textContent = icon;
    if (code) code.textContent = icon;
    
    this.selectedIcon = icon;
};

TB.confirmIconSelection = function() {
    if (this.iconPickerCallback && this.selectedIcon) {
        this.iconPickerCallback(this.selectedIcon);
    }
    this.closeIconPicker();
};

// ═══════════════════════════════════════════════════════════
// ESCAPE HTML HELPER
// ═══════════════════════════════════════════════════════════
TB.escapeHtml = function(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

console.log('TB 3.0: tb-core.js loaded');
