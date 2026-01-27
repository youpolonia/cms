/**
 * Theme Builder 3.0 - Comprehensive Element Schemas
 * Defines inner elements for all 52 modules with full state support
 *
 * Each element can have these states:
 * - normal: Default state
 * - hover: Mouse hover state
 * - active: Active/pressed state
 * - focus: Focus state (for form elements)
 *
 * All elements now get FULL design options (not just limited properties)
 */

TB.elementSchemas = {

    // ═══════════════════════════════════════════════════════════════════════════
    // CONTENT MODULES
    // ═══════════════════════════════════════════════════════════════════════════

    text: {
        container: { label: 'Container', states: ['normal', 'hover'], properties: ['background', 'padding', 'border_radius', 'box_shadow'] },
        paragraph: { label: 'Paragraph', states: ['normal', 'hover'], properties: ['color', 'font_size', 'font_weight', 'line_height', 'letter_spacing', 'text_transform'] },
        link: { label: 'Link', states: ['normal', 'hover', 'active'], properties: ['color', 'font_weight', 'text_decoration'] },
        heading: { label: 'Heading', states: ['normal', 'hover'], properties: ['color', 'font_size', 'font_weight', 'letter_spacing', 'text_transform'] }
    },

    heading: {
        heading: { label: 'Heading', states: ['normal', 'hover'], properties: ['color', 'font_size', 'font_weight', 'letter_spacing', 'text_transform', 'text_shadow'] },
        subheading: { label: 'Subheading', states: ['normal', 'hover'], properties: ['color', 'font_size', 'font_weight', 'letter_spacing'] }
    },

    button: {
        button: { label: 'Button', states: ['normal', 'hover', 'active', 'focus'], properties: ['background', 'color', 'font_size', 'font_weight', 'padding', 'border_radius', 'border', 'box_shadow'] },
        icon: { label: 'Icon', states: ['normal', 'hover'], properties: ['color', 'font_size'] },
        text: { label: 'Button Text', states: ['normal'], properties: ['color', 'font_size', 'font_weight'] }
    },

    image: {
        container: { label: 'Container', states: ['normal', 'hover'], properties: ['border_radius', 'box_shadow', 'border'] },
        image: { label: 'Image', states: ['normal', 'hover'], properties: ['opacity', 'filter'] },
        caption: { label: 'Caption', states: ['normal', 'hover'], properties: ['color', 'font_size', 'font_weight', 'text_align', 'margin'] },
        overlay: { label: 'Overlay', states: ['normal', 'hover'], properties: ['background', 'opacity'] }
    },

    divider: {
        line: { label: 'Divider Line', states: ['normal'], properties: ['background', 'height', 'border_radius'] },
        icon: { label: 'Icon', states: ['normal'], properties: ['color', 'font_size'] }
    },

    spacer: {
        spacer: { label: 'Spacer', states: ['normal'] }
    },

    icon: {
        icon: { label: 'Icon', states: ['normal', 'hover'] },
        background: { label: 'Background', states: ['normal', 'hover'] }
    },

    // ═══════════════════════════════════════════════════════════════════════════
    // HERO & MARKETING MODULES
    // ═══════════════════════════════════════════════════════════════════════════

    hero: {
        container: { 
            label: 'Container', 
            states: ['normal'], 
            properties: [
                'background', 'background_color', 'background_image', 'background_gradient',
                'padding', 'padding_top', 'padding_right', 'padding_bottom', 'padding_left',
                'border_radius', 'border_radius_tl', 'border_radius_tr', 'border_radius_br', 'border_radius_bl',
                'box_shadow', 'box_shadow_enabled', 'box_shadow_h', 'box_shadow_v', 'box_shadow_blur', 'box_shadow_spread', 'box_shadow_color', 'box_shadow_inset'
            ]
        },
        overlay: {
            label: 'Overlay',
            states: ['normal'],
            sections: {
                background: { enabled: true }
            }
        },
        content: { 
            label: 'Content Box', 
            states: ['normal'], 
            properties: [
                'background', 'background_color',
                'padding', 'padding_top', 'padding_right', 'padding_bottom', 'padding_left',
                'border_radius', 'border_radius_tl', 'border_radius_tr', 'border_radius_br', 'border_radius_bl'
            ]
        },
        title: {
            label: 'Title',
            states: ['normal', 'hover'],
            sections: {
                typography: { enabled: true },
                spacing: { enabled: true }
            }
        },
        subtitle: {
            label: 'Subtitle',
            states: ['normal', 'hover'],
            sections: {
                typography: { enabled: true },
                spacing: { enabled: true }
            }
        },
        description: {
            label: 'Description',
            states: ['normal'],
            sections: {
                typography: { enabled: true },
                spacing: { enabled: true }
            }
        },
        button: {
            label: 'Primary Button',
            states: ['normal', 'hover', 'active'],
            sections: {
                typography: { enabled: true },
                spacing: { enabled: true },
                background: { enabled: true },
                border: { enabled: true },
                box_shadow: { enabled: true }
            }
        },
        button_secondary: {
            label: 'Secondary Button',
            states: ['normal', 'hover', 'active'],
            sections: {
                typography: { enabled: true },
                spacing: { enabled: true },
                background: { enabled: true },
                border: { enabled: true },
                box_shadow: { enabled: true }
            }
        }
    },

    cta: {
        container: { label: 'Container', states: ['normal', 'hover'] },
        title: { label: 'Title', states: ['normal'] },
        description: { label: 'Description', states: ['normal'] },
        button: { label: 'Button', states: ['normal', 'hover', 'active'] }
    },

    blurb: {
        container: { label: 'Container', states: ['normal', 'hover'] },
        icon: { label: 'Icon', states: ['normal', 'hover'] },
        title: { label: 'Title', states: ['normal', 'hover'] },
        description: { label: 'Description', states: ['normal'] },
        button: { label: 'Button', states: ['normal', 'hover', 'active'] }
    },

    testimonial: {
        container: { label: 'Container', states: ['normal', 'hover'] },
        quote: { label: 'Quote', states: ['normal'] },
        avatar: { label: 'Avatar', states: ['normal'] },
        name: { label: 'Name', states: ['normal'] },
        role: { label: 'Role/Title', states: ['normal'] },
        company: { label: 'Company', states: ['normal'] },
        rating: { label: 'Rating Stars', states: ['normal'] }
    },

    pricing: {
        container: { label: 'Container', states: ['normal', 'hover'] },
        header: { label: 'Header', states: ['normal'] },
        title: { label: 'Plan Title', states: ['normal'] },
        price: { label: 'Price', states: ['normal'] },
        currency: { label: 'Currency', states: ['normal'] },
        period: { label: 'Period', states: ['normal'] },
        description: { label: 'Description', states: ['normal'] },
        features: { label: 'Features List', states: ['normal'] },
        feature: { label: 'Feature Item', states: ['normal', 'hover'] },
        button: { label: 'Button', states: ['normal', 'hover', 'active'] },
        badge: { label: 'Badge', states: ['normal'] }
    },

    counter: {
        container: { label: 'Container', states: ['normal'] },
        number: { label: 'Number', states: ['normal'] },
        prefix: { label: 'Prefix', states: ['normal'] },
        suffix: { label: 'Suffix', states: ['normal'] },
        title: { label: 'Title', states: ['normal'] }
    },

    countdown: {
        container: { label: 'Container', states: ['normal'] },
        digit_box: { label: 'Digit Box', states: ['normal'] },
        digit: { label: 'Digit', states: ['normal'] },
        label: { label: 'Label', states: ['normal'] },
        separator: { label: 'Separator', states: ['normal'] }
    },

    // ═══════════════════════════════════════════════════════════════════════════
    // INTERACTIVE MODULES
    // ═══════════════════════════════════════════════════════════════════════════

    accordion: {
        container: { label: 'Container', states: ['normal'] },
        item: { label: 'Item', states: ['normal'] },
        header: { label: 'Header', states: ['normal', 'hover', 'active'] },
        title: { label: 'Title', states: ['normal', 'active'] },
        icon: { label: 'Toggle Icon', states: ['normal', 'active'] },
        content: { label: 'Content', states: ['normal'] }
    },

    toggle: {
        container: { label: 'Container', states: ['normal'] },
        header: { label: 'Header', states: ['normal', 'hover', 'active'] },
        title: { label: 'Title', states: ['normal', 'active'] },
        icon: { label: 'Toggle Icon', states: ['normal', 'active'] },
        content: { label: 'Content', states: ['normal'] }
    },

    tabs: {
        container: { label: 'Container', states: ['normal'] },
        nav: { label: 'Tab Navigation', states: ['normal'] },
        tab_button: { label: 'Tab Button', states: ['normal', 'hover', 'active'] },
        content: { label: 'Tab Content', states: ['normal'] },
        panel: { label: 'Tab Panel', states: ['normal'] }
    },

    // ═══════════════════════════════════════════════════════════════════════════
    // FORM MODULES
    // ═══════════════════════════════════════════════════════════════════════════

    form: {
        container: { label: 'Form Container', states: ['normal'] },
        field_wrapper: { label: 'Field Wrapper', states: ['normal'] },
        label: { label: 'Label', states: ['normal'] },
        input: { label: 'Input Field', states: ['normal', 'hover', 'focus'] },
        textarea: { label: 'Textarea', states: ['normal', 'hover', 'focus'] },
        select: { label: 'Select', states: ['normal', 'hover', 'focus'] },
        checkbox: { label: 'Checkbox', states: ['normal', 'hover', 'focus'] },
        radio: { label: 'Radio', states: ['normal', 'hover', 'focus'] },
        submit: { label: 'Submit Button', states: ['normal', 'hover', 'active'] },
        error: { label: 'Error Message', states: ['normal'] },
        success: { label: 'Success Message', states: ['normal'] }
    },

    search: {
        container: { label: 'Container', states: ['normal'] },
        input: { label: 'Search Input', states: ['normal', 'hover', 'focus'] },
        button: { label: 'Search Button', states: ['normal', 'hover', 'active'] },
        icon: { label: 'Search Icon', states: ['normal'] },
        results: { label: 'Results Dropdown', states: ['normal'] }
    },

    login: {
        container: { label: 'Container', states: ['normal'] },
        title: { label: 'Title', states: ['normal'] },
        field: { label: 'Input Field', states: ['normal', 'hover', 'focus'] },
        label: { label: 'Label', states: ['normal'] },
        button: { label: 'Login Button', states: ['normal', 'hover', 'active'] },
        link: { label: 'Link', states: ['normal', 'hover'] },
        error: { label: 'Error Message', states: ['normal'] }
    },

    signup: {
        container: { label: 'Container', states: ['normal'] },
        title: { label: 'Title', states: ['normal'] },
        field: { label: 'Input Field', states: ['normal', 'hover', 'focus'] },
        label: { label: 'Label', states: ['normal'] },
        button: { label: 'Signup Button', states: ['normal', 'hover', 'active'] },
        link: { label: 'Link', states: ['normal', 'hover'] },
        terms: { label: 'Terms Text', states: ['normal'] }
    },

    // ═══════════════════════════════════════════════════════════════════════════
    // MEDIA MODULES
    // ═══════════════════════════════════════════════════════════════════════════

    video: {
        container: { label: 'Container', states: ['normal', 'hover'] },
        video: { label: 'Video', states: ['normal'] },
        overlay: { label: 'Overlay', states: ['normal', 'hover'] },
        play_button: { label: 'Play Button', states: ['normal', 'hover'] },
        caption: { label: 'Caption', states: ['normal'] }
    },

    audio: {
        container: { label: 'Container', states: ['normal'] },
        player: { label: 'Player', states: ['normal'] },
        controls: { label: 'Controls', states: ['normal'] },
        title: { label: 'Title', states: ['normal'] }
    },

    gallery: {
        container: { label: 'Container', states: ['normal'] },
        item: { label: 'Gallery Item', states: ['normal', 'hover'] },
        image: { label: 'Image', states: ['normal', 'hover'] },
        overlay: { label: 'Overlay', states: ['normal', 'hover'] },
        caption: { label: 'Caption', states: ['normal'] },
        navigation: { label: 'Navigation', states: ['normal'] },
        pagination: { label: 'Pagination', states: ['normal'] }
    },

    slider: {
        container: { label: 'Container', states: ['normal'] },
        slide: { label: 'Slide', states: ['normal', 'active'] },
        image: { label: 'Image', states: ['normal'] },
        content: { label: 'Content', states: ['normal'] },
        title: { label: 'Title', states: ['normal'] },
        description: { label: 'Description', states: ['normal'] },
        nav_prev: { label: 'Previous Button', states: ['normal', 'hover'] },
        nav_next: { label: 'Next Button', states: ['normal', 'hover'] },
        pagination: { label: 'Pagination', states: ['normal'] },
        dot: { label: 'Pagination Dot', states: ['normal', 'hover', 'active'] }
    },

    video_slider: {
        container: { label: 'Container', states: ['normal'] },
        slide: { label: 'Slide', states: ['normal', 'active'] },
        video: { label: 'Video', states: ['normal'] },
        overlay: { label: 'Overlay', states: ['normal'] },
        play_button: { label: 'Play Button', states: ['normal', 'hover'] },
        navigation: { label: 'Navigation', states: ['normal'] }
    },

    map: {
        container: { label: 'Container', states: ['normal'] },
        map: { label: 'Map', states: ['normal'] },
        marker: { label: 'Marker', states: ['normal', 'hover'] },
        info_window: { label: 'Info Window', states: ['normal'] }
    },

    // ═══════════════════════════════════════════════════════════════════════════
    // NAVIGATION MODULES
    // ═══════════════════════════════════════════════════════════════════════════

    menu: {
        container: { label: 'Container', states: ['normal'] },
        nav: { label: 'Navigation', states: ['normal'] },
        menu_item: { label: 'Menu Item', states: ['normal', 'hover', 'active'] },
        link: { label: 'Link', states: ['normal', 'hover', 'active'] },
        submenu: { label: 'Submenu', states: ['normal'] },
        submenu_item: { label: 'Submenu Item', states: ['normal', 'hover'] },
        mobile_toggle: { label: 'Mobile Toggle', states: ['normal', 'hover'] }
    },

    logo: {
        container: { label: 'Container', states: ['normal', 'hover'] },
        image: { label: 'Logo Image', states: ['normal', 'hover'] },
        text: { label: 'Logo Text', states: ['normal', 'hover'] }
    },

    social: {
        container: { label: 'Container', states: ['normal'] },
        icon: { label: 'Social Icon', states: ['normal', 'hover'] },
        link: { label: 'Link', states: ['normal', 'hover'] }
    },

    sidebar: {
        container: { label: 'Container', states: ['normal'] },
        widget: { label: 'Widget', states: ['normal'] },
        widget_title: { label: 'Widget Title', states: ['normal'] },
        widget_content: { label: 'Widget Content', states: ['normal'] }
    },

    // ═══════════════════════════════════════════════════════════════════════════
    // CONTENT DISPLAY MODULES
    // ═══════════════════════════════════════════════════════════════════════════

    blog: {
        container: { label: 'Container', states: ['normal'] },
        post: { label: 'Post Card', states: ['normal', 'hover'] },
        image: { label: 'Featured Image', states: ['normal', 'hover'] },
        category: { label: 'Category', states: ['normal', 'hover'] },
        title: { label: 'Title', states: ['normal', 'hover'] },
        excerpt: { label: 'Excerpt', states: ['normal'] },
        meta: { label: 'Meta Info', states: ['normal'] },
        author: { label: 'Author', states: ['normal', 'hover'] },
        date: { label: 'Date', states: ['normal'] },
        read_more: { label: 'Read More', states: ['normal', 'hover'] },
        pagination: { label: 'Pagination', states: ['normal'] }
    },

    team: {
        container: { label: 'Container', states: ['normal'] },
        member: { label: 'Member Card', states: ['normal', 'hover'] },
        photo: { label: 'Photo', states: ['normal', 'hover'] },
        name: { label: 'Name', states: ['normal', 'hover'] },
        role: { label: 'Role', states: ['normal'] },
        bio: { label: 'Bio', states: ['normal'] },
        social: { label: 'Social Icons', states: ['normal'] },
        social_icon: { label: 'Social Icon', states: ['normal', 'hover'] }
    },

    portfolio: {
        container: { label: 'Container', states: ['normal'] },
        item: { label: 'Portfolio Item', states: ['normal', 'hover'] },
        image: { label: 'Image', states: ['normal', 'hover'] },
        overlay: { label: 'Overlay', states: ['normal', 'hover'] },
        title: { label: 'Title', states: ['normal', 'hover'] },
        category: { label: 'Category', states: ['normal'] },
        link: { label: 'Link', states: ['normal', 'hover'] },
        filter: { label: 'Filter', states: ['normal'] },
        filter_item: { label: 'Filter Item', states: ['normal', 'hover', 'active'] }
    },

    // ═══════════════════════════════════════════════════════════════════════════
    // ADVANCED MODULES
    // ═══════════════════════════════════════════════════════════════════════════

    bar_counters: {
        container: { label: 'Container', states: ['normal'] },
        bar_wrapper: { label: 'Bar Wrapper', states: ['normal'] },
        bar: { label: 'Progress Bar', states: ['normal'] },
        bar_fill: { label: 'Bar Fill', states: ['normal'] },
        label: { label: 'Label', states: ['normal'] },
        value: { label: 'Value', states: ['normal'] }
    },

    circle_counter: {
        container: { label: 'Container', states: ['normal'] },
        circle: { label: 'Circle', states: ['normal'] },
        progress: { label: 'Progress', states: ['normal'] },
        number: { label: 'Number', states: ['normal'] },
        title: { label: 'Title', states: ['normal'] }
    },

    quote: {
        container: { label: 'Container', states: ['normal', 'hover'] },
        text: { label: 'Quote Text', states: ['normal'] },
        mark: { label: 'Quote Mark', states: ['normal'] },
        author: { label: 'Author', states: ['normal'] },
        source: { label: 'Source', states: ['normal'] }
    },

    list: {
        container: { label: 'Container', states: ['normal'] },
        item: { label: 'List Item', states: ['normal', 'hover'] },
        icon: { label: 'Item Icon', states: ['normal'] },
        text: { label: 'Item Text', states: ['normal'] }
    },

    // ═══════════════════════════════════════════════════════════════════════════
    // DYNAMIC/POST MODULES
    // ═══════════════════════════════════════════════════════════════════════════

    post_title: {
        title: { label: 'Title', states: ['normal', 'hover'] }
    },

    post_content: {
        content: { label: 'Content', states: ['normal'] },
        paragraph: { label: 'Paragraph', states: ['normal'] },
        heading: { label: 'Heading', states: ['normal'] },
        link: { label: 'Link', states: ['normal', 'hover'] }
    },

    posts_navigation: {
        container: { label: 'Container', states: ['normal'] },
        prev: { label: 'Previous', states: ['normal', 'hover'] },
        next: { label: 'Next', states: ['normal', 'hover'] },
        label: { label: 'Label', states: ['normal'] },
        title: { label: 'Post Title', states: ['normal', 'hover'] }
    },

    comments: {
        container: { label: 'Container', states: ['normal'] },
        comment: { label: 'Comment', states: ['normal'] },
        avatar: { label: 'Avatar', states: ['normal'] },
        author: { label: 'Author', states: ['normal', 'hover'] },
        date: { label: 'Date', states: ['normal'] },
        content: { label: 'Content', states: ['normal'] },
        reply_link: { label: 'Reply Link', states: ['normal', 'hover'] },
        form: { label: 'Comment Form', states: ['normal'] }
    },

    post_slider: {
        container: { label: 'Container', states: ['normal'] },
        slide: { label: 'Slide', states: ['normal', 'active'] },
        image: { label: 'Image', states: ['normal', 'hover'] },
        category: { label: 'Category', states: ['normal', 'hover'] },
        title: { label: 'Title', states: ['normal', 'hover'] },
        excerpt: { label: 'Excerpt', states: ['normal'] },
        navigation: { label: 'Navigation', states: ['normal'] }
    },

    // ═══════════════════════════════════════════════════════════════════════════
    // FULLWIDTH MODULES
    // ═══════════════════════════════════════════════════════════════════════════

    fullwidth_code: {
        container: { label: 'Container', states: ['normal'] },
        code: { label: 'Code Block', states: ['normal'] }
    },

    fullwidth_image: {
        container: { label: 'Container', states: ['normal'] },
        image: { label: 'Image', states: ['normal', 'hover'] },
        overlay: { label: 'Overlay', states: ['normal'] },
        caption: { label: 'Caption', states: ['normal'] }
    },

    fullwidth_map: {
        container: { label: 'Container', states: ['normal'] },
        map: { label: 'Map', states: ['normal'] }
    },

    fullwidth_menu: {
        container: { label: 'Container', states: ['normal'] },
        menu_item: { label: 'Menu Item', states: ['normal', 'hover', 'active'] }
    },

    fullwidth_slider: {
        container: { label: 'Container', states: ['normal'] },
        slide: { label: 'Slide', states: ['normal', 'active'] },
        content: { label: 'Content', states: ['normal'] },
        navigation: { label: 'Navigation', states: ['normal'] }
    },

    fullwidth_header: {
        container: { label: 'Container', states: ['normal'] },
        logo: { label: 'Logo', states: ['normal', 'hover'] },
        menu: { label: 'Menu', states: ['normal'] },
        menu_item: { label: 'Menu Item', states: ['normal', 'hover', 'active'] }
    },

    fullwidth_portfolio: {
        container: { label: 'Container', states: ['normal'] },
        item: { label: 'Item', states: ['normal', 'hover'] },
        overlay: { label: 'Overlay', states: ['normal', 'hover'] }
    },

    fullwidth_post_slider: {
        container: { label: 'Container', states: ['normal'] },
        slide: { label: 'Slide', states: ['normal', 'active'] },
        title: { label: 'Title', states: ['normal', 'hover'] },
        excerpt: { label: 'Excerpt', states: ['normal'] }
    },

    // ═══════════════════════════════════════════════════════════════════════════
    // CODE MODULE
    // ═══════════════════════════════════════════════════════════════════════════

    code: {
        container: { label: 'Container', states: ['normal'] },
        code_block: { label: 'Code Block', states: ['normal'] },
        line_numbers: { label: 'Line Numbers', states: ['normal'] },
        copy_button: { label: 'Copy Button', states: ['normal', 'hover'] }
    },

    // ═══════════════════════════════════════════════════════════════════════════
    // ALERT/NOTICE MODULE
    // ═══════════════════════════════════════════════════════════════════════════

    alert: {
        container: { label: 'Container', states: ['normal'] },
        icon: { label: 'Icon', states: ['normal'] },
        title: { label: 'Title', states: ['normal'] },
        message: { label: 'Message', states: ['normal'] },
        close_button: { label: 'Close Button', states: ['normal', 'hover'] }
    },

    notice: {
        container: { label: 'Container', states: ['normal'] },
        icon: { label: 'Icon', states: ['normal'] },
        content: { label: 'Content', states: ['normal'] }
    }
};

// Log loaded schemas count
console.log('TB Element Schemas loaded - ' + Object.keys(TB.elementSchemas).length + ' modules defined');
