/**
 * Theme Builder 3.0 - Design Settings Module
 * Handles renderDesignSettings and all sub-render functions
 * Part of TB 3.0 modularization
 */

// Typography mappings for each module type
TB.typographyMappings = {
    heading: [['title', 'Heading']],
    text: [['body', 'Body Text']],
    button: [['button', 'Button']],
    quote: [['quote', 'Quote'], ['author', 'Author']],
    cta: [['title', 'Title'], ['subtitle', 'Subtitle'], ['button', 'Button']],
    blurb: [['title', 'Title'], ['text', 'Description']],
    hero: [['title', 'Title'], ['subtitle', 'Subtitle'], ['button', 'Buttons']],
    testimonial: [['quote', 'Quote'], ['author', 'Author'], ['role', 'Role/Title']],
    pricing: [['title', 'Plan Title'], ['price', 'Price'], ['features', 'Features'], ['button', 'Button']],
    team: [['name', 'Name'], ['role', 'Role'], ['bio', 'Bio']],
    counter: [['number', 'Number'], ['title', 'Title']],
    countdown: [['title', 'Title'], ['number', 'Numbers'], ['label', 'Labels']],
    list: [['item', 'List Items']],
    menu: [['link', 'Menu Links']],
    sidebar: [['title', 'Sidebar Title'], ['widget_title', 'Widget Titles'], ['widget_content', 'Widget Content']],
    slider: [['title', 'Slide Title'], ['text', 'Slide Text'], ['button', 'Slide Button']],
    post_slider: [['title', 'Post Title'], ['meta', 'Meta'], ['excerpt', 'Excerpt'], ['button', 'Read More']],
    post_title: [['title', 'Title'], ['meta', 'Meta']],
    post_content: [['body', 'Body Text'], ['heading', 'Headings'], ['blockquote', 'Blockquotes'], ['code', 'Code Blocks']],
    posts_navigation: [['label', 'Navigation Label'], ['title', 'Post Title']],
    video_slider: [['title', 'Video Title'], ['description', 'Description']],
    toggle: [['title', 'Toggle Title'], ['content', 'Toggle Content']],
    bar_counters: [['label', 'Labels'], ['percent', 'Percentages']],
    circle_counter: [['number', 'Number'], ['title', 'Title']],
    search: [['input', 'Input Text'], ['button', 'Button']],
    comments: [['title', 'Section Title'], ['author', 'Author Name'], ['date', 'Date'], ['text', 'Comment Text'], ['form_title', 'Form Title'], ['button', 'Submit Button']]
};

// Text-based modules that support typography
TB.textModules = ['text', 'heading', 'button', 'quote', 'cta', 'pricing', 'blurb', 'hero', 'testimonial', 'team', 'countdown', 'counter', 'list', 'menu', 'slider', 'toggle', 'bar_counters', 'video_slider', 'post_slider', 'post_title', 'circle_counter', 'search', 'comments'];

// Inner Elements schema - defines editable elements per module type
TB.elementSchemas = {
    // Interactive modules
    toggle: {
        header: {
            label: 'Header',
            states: ['normal', 'hover', 'active'],
            properties: ['background', 'color', 'font_size', 'font_weight', 'padding', 'border_radius']
        },
        content: {
            label: 'Content',
            states: ['normal'],
            properties: ['background', 'color', 'font_size', 'padding', 'line_height']
        },
        icon: {
            label: 'Icon',
            states: ['normal', 'active'],
            properties: ['color', 'font_size']
        },
        item: {
            label: 'Item Container',
            states: ['normal'],
            properties: ['margin_bottom', 'border', 'border_radius']
        }
    },
    accordion: {
        header: {
            label: 'Header',
            states: ['normal', 'hover', 'active'],
            properties: ['background', 'color', 'font_size', 'font_weight', 'padding', 'border_radius']
        },
        content: {
            label: 'Content',
            states: ['normal'],
            properties: ['background', 'color', 'font_size', 'padding']
        },
        icon: {
            label: 'Icon',
            states: ['normal', 'active'],
            properties: ['color', 'font_size']
        }
    },
    tabs: {
        nav: {
            label: 'Navigation',
            states: ['normal'],
            properties: ['background', 'border_bottom', 'padding']
        },
        tab_button: {
            label: 'Tab Button',
            states: ['normal', 'hover', 'active'],
            properties: ['background', 'color', 'font_size', 'font_weight', 'padding', 'border_radius']
        },
        content: {
            label: 'Content Panel',
            states: ['normal'],
            properties: ['background', 'color', 'padding']
        }
    },
    // Button module
    button: {
        button: {
            label: 'Button',
            states: ['normal', 'hover', 'active'],
            properties: ['background', 'color', 'font_size', 'font_weight', 'padding', 'border', 'border_radius', 'box_shadow']
        }
    },
    // Typography modules
    text: {
        paragraph: {
            label: 'Paragraph',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_family', 'line_height', 'letter_spacing']
        },
        link: {
            label: 'Link',
            states: ['normal', 'hover'],
            properties: ['color', 'text_decoration']
        }
    },
    heading: {
        heading: {
            label: 'Heading',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_family', 'font_weight', 'line_height', 'letter_spacing', 'text_transform']
        },
        underline: {
            label: 'Underline',
            states: ['normal'],
            properties: ['background', 'width', 'height', 'margin_top']
        }
    },
    // Image modules
    image: {
        image: {
            label: 'Image',
            states: ['normal', 'hover'],
            properties: ['border_radius', 'border', 'box_shadow', 'opacity', 'transform']
        },
        caption: {
            label: 'Caption',
            states: ['normal'],
            properties: ['color', 'font_size', 'background', 'padding']
        },
        overlay: {
            label: 'Overlay',
            states: ['normal', 'hover'],
            properties: ['background', 'opacity']
        }
    },
    gallery: {
        image: {
            label: 'Image',
            states: ['normal', 'hover'],
            properties: ['border_radius', 'border', 'box_shadow']
        },
        caption: {
            label: 'Caption',
            states: ['normal'],
            properties: ['color', 'font_size', 'background', 'padding']
        },
        overlay: {
            label: 'Overlay',
            states: ['normal', 'hover'],
            properties: ['background', 'opacity']
        },
        grid: {
            label: 'Grid',
            states: ['normal'],
            properties: ['gap']
        }
    },
    // List module
    list: {
        item: {
            label: 'List Item',
            states: ['normal'],
            properties: ['color', 'font_size', 'padding', 'line_height']
        },
        bullet: {
            label: 'Bullet/Number',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        icon: {
            label: 'Icon',
            states: ['normal'],
            properties: ['color', 'font_size']
        }
    },
    // Quote module
    quote: {
        quote: {
            label: 'Quote Text',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_style', 'font_family', 'line_height', 'padding']
        },
        author: {
            label: 'Author',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        icon: {
            label: 'Quote Icon',
            states: ['normal'],
            properties: ['color', 'font_size', 'opacity']
        },
        border: {
            label: 'Border',
            states: ['normal'],
            properties: ['background', 'width']
        }
    },
    // Hero module
    hero: {
        container: {
            label: 'Container',
            states: ['normal'],
            properties: ['background', 'padding', 'border_radius', 'box_shadow']
        },
        overlay: {
            label: 'Overlay',
            states: ['normal'],
            properties: ['background', 'opacity']
        },
        content_box: {
            label: 'Content Box',
            states: ['normal'],
            properties: ['background', 'padding', 'border_radius']
        },
        title: {
            label: 'Title',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'font_weight', 'text_shadow', 'letter_spacing']
        },
        subtitle: {
            label: 'Subtitle',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'font_weight']
        },
        description: {
            label: 'Description',
            states: ['normal'],
            properties: ['color', 'font_size', 'line_height']
        },
        primary_button: {
            label: 'Primary Button',
            states: ['normal', 'hover', 'active'],
            properties: ['background', 'color', 'font_size', 'padding', 'border_radius', 'border', 'box_shadow']
        },
        secondary_button: {
            label: 'Secondary Button',
            states: ['normal', 'hover', 'active'],
            properties: ['background', 'color', 'font_size', 'padding', 'border_radius', 'border', 'box_shadow']
        }
    },
    // CTA module
    cta: {
        title: {
            label: 'Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        subtitle: {
            label: 'Subtitle',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        button: {
            label: 'Button',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'font_size', 'padding', 'border_radius', 'border']
        }
    },
    // Blurb module
    blurb: {
        icon: {
            label: 'Icon',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'background', 'padding', 'border_radius']
        },
        title: {
            label: 'Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        text: {
            label: 'Description',
            states: ['normal'],
            properties: ['color', 'font_size', 'line_height']
        }
    },
    // Testimonial module
    testimonial: {
        quote: {
            label: 'Quote',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_style', 'line_height']
        },
        author: {
            label: 'Author',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        role: {
            label: 'Role',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        image: {
            label: 'Author Image',
            states: ['normal'],
            properties: ['border_radius', 'border', 'width', 'height']
        },
        icon: {
            label: 'Quote Icon',
            states: ['normal'],
            properties: ['color', 'font_size', 'opacity']
        }
    },
    // Team module
    team: {
        image: {
            label: 'Photo',
            states: ['normal', 'hover'],
            properties: ['border_radius', 'border', 'box_shadow']
        },
        name: {
            label: 'Name',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        role: {
            label: 'Role',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        bio: {
            label: 'Bio',
            states: ['normal'],
            properties: ['color', 'font_size', 'line_height']
        },
        social: {
            label: 'Social Icons',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'background', 'padding', 'border_radius']
        }
    },
    // Pricing module
    pricing: {
        header: {
            label: 'Header',
            states: ['normal'],
            properties: ['background', 'color', 'padding']
        },
        title: {
            label: 'Plan Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        price: {
            label: 'Price',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        period: {
            label: 'Period',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        features: {
            label: 'Features',
            states: ['normal'],
            properties: ['color', 'font_size', 'padding']
        },
        feature_icon: {
            label: 'Feature Icon',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        button: {
            label: 'Button',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'font_size', 'padding', 'border_radius']
        },
        badge: {
            label: 'Badge',
            states: ['normal'],
            properties: ['background', 'color', 'font_size', 'padding', 'border_radius']
        }
    },
    // Counter modules
    counter: {
        number: {
            label: 'Number',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight', 'font_family']
        },
        title: {
            label: 'Title',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        icon: {
            label: 'Icon',
            states: ['normal'],
            properties: ['color', 'font_size']
        }
    },
    circle_counter: {
        circle: {
            label: 'Circle',
            states: ['normal'],
            properties: ['stroke', 'stroke_width', 'fill']
        },
        progress: {
            label: 'Progress',
            states: ['normal'],
            properties: ['stroke', 'stroke_width']
        },
        number: {
            label: 'Number',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        title: {
            label: 'Title',
            states: ['normal'],
            properties: ['color', 'font_size']
        }
    },
    bar_counters: {
        bar_container: {
            label: 'Bar Container',
            states: ['normal'],
            properties: ['background', 'height', 'border_radius']
        },
        bar_fill: {
            label: 'Bar Fill',
            states: ['normal'],
            properties: ['background', 'border_radius']
        },
        label: {
            label: 'Label',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        percent: {
            label: 'Percentage',
            states: ['normal'],
            properties: ['color', 'font_size']
        }
    },
    // Countdown module
    countdown: {
        container: {
            label: 'Number Container',
            states: ['normal'],
            properties: ['background', 'padding', 'border_radius', 'border']
        },
        number: {
            label: 'Number',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight', 'font_family']
        },
        label: {
            label: 'Label',
            states: ['normal'],
            properties: ['color', 'font_size', 'text_transform']
        },
        separator: {
            label: 'Separator',
            states: ['normal'],
            properties: ['color', 'font_size']
        }
    },
    // Form module
    form: {
        label: {
            label: 'Field Label',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight', 'margin_bottom']
        },
        input: {
            label: 'Input Field',
            states: ['normal', 'focus'],
            properties: ['background', 'color', 'font_size', 'padding', 'border', 'border_radius']
        },
        textarea: {
            label: 'Textarea',
            states: ['normal', 'focus'],
            properties: ['background', 'color', 'font_size', 'padding', 'border', 'border_radius', 'min_height']
        },
        submit: {
            label: 'Submit Button',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'font_size', 'font_weight', 'padding', 'border', 'border_radius']
        },
        error: {
            label: 'Error Message',
            states: ['normal'],
            properties: ['color', 'font_size', 'background', 'padding']
        },
        success: {
            label: 'Success Message',
            states: ['normal'],
            properties: ['color', 'font_size', 'background', 'padding']
        }
    },
    // Contact form module
    contact_form: {
        label: {
            label: 'Field Label',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        input: {
            label: 'Input Field',
            states: ['normal', 'focus'],
            properties: ['background', 'color', 'font_size', 'padding', 'border', 'border_radius']
        },
        submit: {
            label: 'Submit Button',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'font_size', 'padding', 'border_radius']
        }
    },
    // Search module
    search: {
        input: {
            label: 'Search Input',
            states: ['normal', 'focus'],
            properties: ['background', 'color', 'font_size', 'padding', 'border', 'border_radius']
        },
        button: {
            label: 'Search Button',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'font_size', 'padding', 'border_radius']
        },
        icon: {
            label: 'Search Icon',
            states: ['normal'],
            properties: ['color', 'font_size']
        }
    },
    // Social module
    social: {
        icon: {
            label: 'Social Icon',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'background', 'padding', 'border_radius', 'border']
        },
        container: {
            label: 'Container',
            states: ['normal'],
            properties: ['gap']
        }
    },
    // Icon module
    icon: {
        icon: {
            label: 'Icon',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'background', 'padding', 'border_radius', 'border']
        }
    },
    // Divider module
    divider: {
        line: {
            label: 'Divider Line',
            states: ['normal'],
            properties: ['background', 'height', 'width', 'border_radius']
        },
        icon: {
            label: 'Center Icon',
            states: ['normal'],
            properties: ['color', 'font_size', 'background', 'padding', 'border_radius']
        }
    },
    // Spacer module
    spacer: {
        spacer: {
            label: 'Spacer',
            states: ['normal'],
            properties: ['height', 'background']
        }
    },
    // Map module
    map: {
        container: {
            label: 'Map Container',
            states: ['normal'],
            properties: ['border', 'border_radius', 'box_shadow']
        },
        overlay: {
            label: 'Overlay',
            states: ['normal'],
            properties: ['background', 'opacity']
        }
    },
    // Video module
    video: {
        container: {
            label: 'Video Container',
            states: ['normal'],
            properties: ['border', 'border_radius', 'box_shadow']
        },
        play_button: {
            label: 'Play Button',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'font_size', 'padding', 'border_radius']
        },
        overlay: {
            label: 'Overlay',
            states: ['normal'],
            properties: ['background', 'opacity']
        }
    },
    // Audio module
    audio: {
        container: {
            label: 'Player Container',
            states: ['normal'],
            properties: ['background', 'padding', 'border', 'border_radius']
        },
        play_button: {
            label: 'Play Button',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'font_size']
        },
        progress: {
            label: 'Progress Bar',
            states: ['normal'],
            properties: ['background', 'height', 'border_radius']
        },
        progress_fill: {
            label: 'Progress Fill',
            states: ['normal'],
            properties: ['background']
        }
    },
    // Slider modules
    slider: {
        slide: {
            label: 'Slide',
            states: ['normal'],
            properties: ['background', 'padding']
        },
        title: {
            label: 'Slide Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        text: {
            label: 'Slide Text',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        button: {
            label: 'Slide Button',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'padding', 'border_radius']
        },
        nav: {
            label: 'Navigation Arrows',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'font_size', 'padding', 'border_radius']
        },
        dots: {
            label: 'Pagination Dots',
            states: ['normal', 'active'],
            properties: ['background', 'width', 'height', 'border_radius']
        }
    },
    image_slider: {
        image: {
            label: 'Slide Image',
            states: ['normal'],
            properties: ['border_radius']
        },
        nav: {
            label: 'Navigation Arrows',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'font_size', 'padding', 'border_radius']
        },
        dots: {
            label: 'Pagination Dots',
            states: ['normal', 'active'],
            properties: ['background', 'width', 'height', 'border_radius']
        },
        caption: {
            label: 'Caption',
            states: ['normal'],
            properties: ['color', 'font_size', 'background', 'padding']
        }
    },
    post_slider: {
        card: {
            label: 'Post Card',
            states: ['normal', 'hover'],
            properties: ['background', 'border', 'border_radius', 'box_shadow']
        },
        image: {
            label: 'Post Image',
            states: ['normal', 'hover'],
            properties: ['border_radius']
        },
        title: {
            label: 'Post Title',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'font_weight']
        },
        meta: {
            label: 'Meta',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        excerpt: {
            label: 'Excerpt',
            states: ['normal'],
            properties: ['color', 'font_size', 'line_height']
        },
        nav: {
            label: 'Navigation',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'font_size']
        }
    },
    video_slider: {
        thumbnail: {
            label: 'Video Thumbnail',
            states: ['normal', 'hover'],
            properties: ['border_radius', 'border']
        },
        play_icon: {
            label: 'Play Icon',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'background', 'padding', 'border_radius']
        },
        title: {
            label: 'Video Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        nav: {
            label: 'Navigation',
            states: ['normal', 'hover'],
            properties: ['background', 'color']
        }
    },
    // Menu module
    menu: {
        item: {
            label: 'Menu Item',
            states: ['normal', 'hover', 'active'],
            properties: ['color', 'font_size', 'font_weight', 'padding', 'background']
        },
        submenu: {
            label: 'Submenu',
            states: ['normal'],
            properties: ['background', 'border', 'border_radius', 'box_shadow', 'padding']
        },
        submenu_item: {
            label: 'Submenu Item',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'padding', 'background']
        },
        icon: {
            label: 'Menu Icon',
            states: ['normal'],
            properties: ['color', 'font_size']
        }
    },
    // Sidebar module
    sidebar: {
        container: {
            label: 'Sidebar Container',
            states: ['normal'],
            properties: ['background', 'padding', 'border', 'border_radius']
        },
        widget: {
            label: 'Widget',
            states: ['normal'],
            properties: ['background', 'padding', 'margin_bottom', 'border', 'border_radius']
        },
        widget_title: {
            label: 'Widget Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight', 'padding_bottom', 'border_bottom', 'margin_bottom']
        },
        widget_content: {
            label: 'Widget Content',
            states: ['normal'],
            properties: ['color', 'font_size', 'line_height']
        }
    },
    // Blog/Post modules
    blog: {
        card: {
            label: 'Post Card',
            states: ['normal', 'hover'],
            properties: ['background', 'border', 'border_radius', 'box_shadow']
        },
        image: {
            label: 'Featured Image',
            states: ['normal', 'hover'],
            properties: ['border_radius']
        },
        title: {
            label: 'Post Title',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'font_weight']
        },
        meta: {
            label: 'Meta',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        excerpt: {
            label: 'Excerpt',
            states: ['normal'],
            properties: ['color', 'font_size', 'line_height']
        },
        button: {
            label: 'Read More',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'font_weight']
        },
        category: {
            label: 'Category',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'background', 'padding', 'border_radius']
        }
    },
    post_title: {
        title: {
            label: 'Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight', 'line_height']
        },
        meta: {
            label: 'Meta',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        category: {
            label: 'Category',
            states: ['normal', 'hover'],
            properties: ['color', 'background', 'padding', 'border_radius']
        }
    },
    post_content: {
        body: {
            label: 'Body Text',
            states: ['normal'],
            properties: ['color', 'font_size', 'line_height']
        },
        heading: {
            label: 'Headings',
            states: ['normal'],
            properties: ['color', 'font_weight']
        },
        link: {
            label: 'Links',
            states: ['normal', 'hover'],
            properties: ['color', 'text_decoration']
        },
        blockquote: {
            label: 'Blockquote',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_style', 'border_left', 'padding', 'background']
        },
        code: {
            label: 'Code Block',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_family', 'background', 'padding', 'border_radius']
        },
        image: {
            label: 'Content Images',
            states: ['normal'],
            properties: ['border_radius', 'box_shadow']
        }
    },
    posts_navigation: {
        container: {
            label: 'Container',
            states: ['normal'],
            properties: ['padding', 'border_top', 'margin_top']
        },
        link: {
            label: 'Navigation Link',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size']
        },
        label: {
            label: 'Label',
            states: ['normal'],
            properties: ['color', 'font_size', 'text_transform']
        },
        title: {
            label: 'Post Title',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'font_weight']
        }
    },
    // Comments module
    comments: {
        container: {
            label: 'Comments Container',
            states: ['normal'],
            properties: ['background', 'padding', 'border', 'border_radius']
        },
        title: {
            label: 'Section Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        comment: {
            label: 'Comment Box',
            states: ['normal'],
            properties: ['background', 'padding', 'border', 'border_radius', 'margin_bottom']
        },
        author: {
            label: 'Author Name',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        date: {
            label: 'Date',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        text: {
            label: 'Comment Text',
            states: ['normal'],
            properties: ['color', 'font_size', 'line_height']
        },
        avatar: {
            label: 'Avatar',
            states: ['normal'],
            properties: ['border_radius', 'border', 'width', 'height']
        },
        reply_link: {
            label: 'Reply Link',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size']
        },
        form_title: {
            label: 'Form Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        input: {
            label: 'Form Input',
            states: ['normal', 'focus'],
            properties: ['background', 'border', 'border_radius', 'padding']
        },
        submit: {
            label: 'Submit Button',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'padding', 'border_radius']
        }
    },
    // Breadcrumbs module
    breadcrumbs: {
        container: {
            label: 'Container',
            states: ['normal'],
            properties: ['background', 'padding', 'border_radius']
        },
        link: {
            label: 'Link',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size']
        },
        current: {
            label: 'Current Page',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        separator: {
            label: 'Separator',
            states: ['normal'],
            properties: ['color', 'font_size', 'padding']
        }
    },
    // Pagination module
    pagination: {
        container: {
            label: 'Container',
            states: ['normal'],
            properties: ['gap']
        },
        link: {
            label: 'Page Link',
            states: ['normal', 'hover', 'active'],
            properties: ['background', 'color', 'font_size', 'padding', 'border', 'border_radius']
        },
        current: {
            label: 'Current Page',
            states: ['normal'],
            properties: ['background', 'color', 'font_size', 'padding', 'border', 'border_radius']
        },
        dots: {
            label: 'Ellipsis',
            states: ['normal'],
            properties: ['color', 'font_size']
        }
    },
    // Table module
    table: {
        table: {
            label: 'Table',
            states: ['normal'],
            properties: ['border', 'border_radius', 'box_shadow']
        },
        header: {
            label: 'Header Row',
            states: ['normal'],
            properties: ['background', 'color', 'font_size', 'font_weight', 'padding']
        },
        row: {
            label: 'Body Row',
            states: ['normal', 'hover'],
            properties: ['background', 'border_bottom']
        },
        cell: {
            label: 'Cell',
            states: ['normal'],
            properties: ['color', 'font_size', 'padding']
        },
        stripe: {
            label: 'Stripe Row',
            states: ['normal'],
            properties: ['background']
        }
    },
    // Code module
    code: {
        container: {
            label: 'Code Container',
            states: ['normal'],
            properties: ['background', 'padding', 'border', 'border_radius', 'box_shadow']
        },
        code: {
            label: 'Code Text',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_family', 'line_height']
        },
        header: {
            label: 'Header Bar',
            states: ['normal'],
            properties: ['background', 'color', 'font_size', 'padding']
        },
        copy_button: {
            label: 'Copy Button',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'font_size', 'padding', 'border_radius']
        },
        line_numbers: {
            label: 'Line Numbers',
            states: ['normal'],
            properties: ['color', 'background', 'padding']
        }
    },
    // Alert/Notice module
    alert: {
        container: {
            label: 'Alert Box',
            states: ['normal'],
            properties: ['background', 'color', 'padding', 'border', 'border_radius', 'border_left']
        },
        icon: {
            label: 'Icon',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        title: {
            label: 'Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        text: {
            label: 'Text',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        close: {
            label: 'Close Button',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size']
        }
    },
    // Login/Signup modules
    login: {
        container: {
            label: 'Form Container',
            states: ['normal'],
            properties: ['background', 'padding', 'border', 'border_radius', 'box_shadow']
        },
        title: {
            label: 'Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        label: {
            label: 'Field Label',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        input: {
            label: 'Input Field',
            states: ['normal', 'focus'],
            properties: ['background', 'color', 'border', 'border_radius', 'padding']
        },
        button: {
            label: 'Submit Button',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'font_size', 'padding', 'border_radius']
        },
        link: {
            label: 'Links',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size']
        }
    },
    signup: {
        container: {
            label: 'Form Container',
            states: ['normal'],
            properties: ['background', 'padding', 'border', 'border_radius', 'box_shadow']
        },
        title: {
            label: 'Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        label: {
            label: 'Field Label',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        input: {
            label: 'Input Field',
            states: ['normal', 'focus'],
            properties: ['background', 'color', 'border', 'border_radius', 'padding']
        },
        button: {
            label: 'Submit Button',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'font_size', 'padding', 'border_radius']
        },
        link: {
            label: 'Links',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size']
        }
    },
    // Embed module
    embed: {
        container: {
            label: 'Embed Container',
            states: ['normal'],
            properties: ['border', 'border_radius', 'box_shadow', 'padding']
        }
    },
    // HTML module
    html: {
        container: {
            label: 'HTML Container',
            states: ['normal'],
            properties: ['background', 'padding', 'border', 'border_radius']
        }
    },
    // Fullwidth modules
    fullwidth_header: {
        container: {
            label: 'Header Container',
            states: ['normal'],
            properties: ['background', 'padding', 'border_bottom']
        },
        logo: {
            label: 'Logo',
            states: ['normal'],
            properties: ['max_width', 'max_height']
        },
        nav_item: {
            label: 'Navigation Item',
            states: ['normal', 'hover', 'active'],
            properties: ['color', 'font_size', 'font_weight', 'padding']
        },
        button: {
            label: 'CTA Button',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'padding', 'border_radius']
        }
    },
    fullwidth_menu: {
        container: {
            label: 'Menu Container',
            states: ['normal'],
            properties: ['background', 'padding']
        },
        item: {
            label: 'Menu Item',
            states: ['normal', 'hover', 'active'],
            properties: ['color', 'font_size', 'font_weight', 'padding']
        }
    },
    fullwidth_slider: {
        slide: {
            label: 'Slide',
            states: ['normal'],
            properties: ['background']
        },
        title: {
            label: 'Slide Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight', 'text_shadow']
        },
        text: {
            label: 'Slide Text',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        button: {
            label: 'Slide Button',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'padding', 'border_radius']
        },
        nav: {
            label: 'Navigation',
            states: ['normal', 'hover'],
            properties: ['background', 'color']
        },
        dots: {
            label: 'Pagination Dots',
            states: ['normal', 'active'],
            properties: ['background', 'width', 'height']
        }
    },
    fullwidth_portfolio: {
        item: {
            label: 'Portfolio Item',
            states: ['normal', 'hover'],
            properties: ['border_radius', 'box_shadow']
        },
        image: {
            label: 'Image',
            states: ['normal', 'hover'],
            properties: ['opacity', 'transform']
        },
        overlay: {
            label: 'Overlay',
            states: ['normal', 'hover'],
            properties: ['background', 'opacity']
        },
        title: {
            label: 'Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        category: {
            label: 'Category',
            states: ['normal'],
            properties: ['color', 'font_size']
        }
    },
    // Portfolio module
    portfolio: {
        item: {
            label: 'Portfolio Item',
            states: ['normal', 'hover'],
            properties: ['border_radius', 'box_shadow']
        },
        image: {
            label: 'Image',
            states: ['normal', 'hover'],
            properties: ['opacity', 'transform']
        },
        overlay: {
            label: 'Overlay',
            states: ['normal', 'hover'],
            properties: ['background', 'opacity']
        },
        title: {
            label: 'Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        category: {
            label: 'Category',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        filter: {
            label: 'Filter Button',
            states: ['normal', 'hover', 'active'],
            properties: ['background', 'color', 'padding', 'border_radius']
        }
    },
    // Shop modules
    shop: {
        card: {
            label: 'Product Card',
            states: ['normal', 'hover'],
            properties: ['background', 'border', 'border_radius', 'box_shadow']
        },
        image: {
            label: 'Product Image',
            states: ['normal', 'hover'],
            properties: ['border_radius']
        },
        title: {
            label: 'Product Title',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'font_weight']
        },
        price: {
            label: 'Price',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        sale_price: {
            label: 'Sale Price',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        badge: {
            label: 'Sale Badge',
            states: ['normal'],
            properties: ['background', 'color', 'font_size', 'padding', 'border_radius']
        },
        button: {
            label: 'Add to Cart',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'padding', 'border_radius']
        }
    },
    woo_products: {
        card: {
            label: 'Product Card',
            states: ['normal', 'hover'],
            properties: ['background', 'border', 'border_radius', 'box_shadow']
        },
        image: {
            label: 'Product Image',
            states: ['normal', 'hover'],
            properties: ['border_radius']
        },
        title: {
            label: 'Product Title',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size', 'font_weight']
        },
        price: {
            label: 'Price',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        button: {
            label: 'Add to Cart',
            states: ['normal', 'hover'],
            properties: ['background', 'color', 'padding', 'border_radius']
        }
    },
    // Person/Profile module
    person: {
        image: {
            label: 'Photo',
            states: ['normal', 'hover'],
            properties: ['border_radius', 'border', 'box_shadow']
        },
        name: {
            label: 'Name',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        },
        role: {
            label: 'Role/Title',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        bio: {
            label: 'Bio',
            states: ['normal'],
            properties: ['color', 'font_size', 'line_height']
        },
        social: {
            label: 'Social Icons',
            states: ['normal', 'hover'],
            properties: ['color', 'font_size']
        }
    },
    // Number counter module
    number_counter: {
        number: {
            label: 'Number',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight', 'font_family']
        },
        title: {
            label: 'Title',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        prefix: {
            label: 'Prefix',
            states: ['normal'],
            properties: ['color', 'font_size']
        },
        suffix: {
            label: 'Suffix',
            states: ['normal'],
            properties: ['color', 'font_size']
        }
    },
    // Filterable portfolio module
    filterable_portfolio: {
        filter: {
            label: 'Filter Button',
            states: ['normal', 'hover', 'active'],
            properties: ['background', 'color', 'font_size', 'padding', 'border', 'border_radius']
        },
        item: {
            label: 'Portfolio Item',
            states: ['normal', 'hover'],
            properties: ['border_radius', 'box_shadow']
        },
        overlay: {
            label: 'Overlay',
            states: ['normal', 'hover'],
            properties: ['background', 'opacity']
        },
        title: {
            label: 'Item Title',
            states: ['normal'],
            properties: ['color', 'font_size', 'font_weight']
        }
    }
};

// Modules that support inner element styling
TB.elementModules = [
    'toggle', 'accordion', 'tabs', 'button', 'text', 'heading', 'image', 'gallery',
    'list', 'quote', 'hero', 'cta', 'blurb', 'testimonial', 'team', 'pricing',
    'counter', 'circle_counter', 'bar_counters', 'countdown', 'form', 'contact_form',
    'search', 'social', 'icon', 'divider', 'spacer', 'map', 'video', 'audio',
    'slider', 'image_slider', 'post_slider', 'video_slider', 'menu', 'sidebar',
    'blog', 'post_title', 'post_content', 'posts_navigation', 'comments', 'breadcrumbs',
    'pagination', 'table', 'code', 'alert', 'login', 'signup', 'embed', 'html',
    'fullwidth_header', 'fullwidth_menu', 'fullwidth_slider', 'fullwidth_portfolio',
    'portfolio', 'shop', 'woo_products', 'person', 'number_counter', 'filterable_portfolio'
];

// Main renderDesignSettings function
TB.renderDesignSettings = function(mod, sIdx, rIdx, cIdx, mIdx) {
    const settings = mod.settings || {};
    const type = mod.type || 'text';
    const hasTypography = this.textModules.includes(type);

    let html = '';

    // Typography Section
    if (hasTypography) {
        html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:16px;margin-bottom:16px"><div class="tb-setting-label" style="font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-accent)">Typography</div></div>';
        
        // Get typography mappings for this module type
        const mappings = this.typographyMappings[type] || [['default', 'Text']];
        mappings.forEach(mapping => {
            html += this.renderTypographySettings(sIdx, rIdx, cIdx, mIdx, mapping[0], mapping[1]);
        });
    }

    // Gallery-specific design settings
    if (type === 'gallery') {
        html += this.renderGalleryDesignSettings(mod, sIdx, rIdx, cIdx, mIdx);
    }

    // Module-specific design settings
    if (type === 'post_content') {
        html += this.renderPostContentDesignSettings(mod, sIdx, rIdx, cIdx, mIdx, hasTypography);
    }
    if (type === 'comments') {
        html += this.renderCommentsDesignSettings(mod, sIdx, rIdx, cIdx, mIdx, hasTypography);
    }

    // Element Styles Section (for toggle, accordion, tabs, button)
    if (this.elementModules.includes(type)) {
        html += this.renderElementStylesSettings(mod, sIdx, rIdx, cIdx, mIdx);
    }

    // Spacing Section
    html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:16px;margin-bottom:16px;margin-top:' + (hasTypography ? '16px' : '0') + '"><div class="tb-setting-label" style="font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-accent)">📐 Spacing</div></div>';
    html += this.renderCombinedSpacingBox(sIdx, rIdx, cIdx, mIdx);

    // Background Section
    html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:16px;margin-bottom:16px;margin-top:16px"><div class="tb-setting-label" style="font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-accent)">Background</div></div>';
    html += this.renderColorPicker('Background Color', settings.backgroundColor, 'TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'backgroundColor\',VALUE)', '#ffffff');

    // Layout Section
    html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:16px;margin-bottom:16px;margin-top:16px"><div class="tb-setting-label" style="font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-accent)">Layout</div></div>';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Text Alignment</div><select class="tb-setting-input" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'textAlign\',this.value)"><option value="">Default</option><option value="left"' + (settings.textAlign === 'left' ? ' selected' : '') + '>Left</option><option value="center"' + (settings.textAlign === 'center' ? ' selected' : '') + '>Center</option><option value="right"' + (settings.textAlign === 'right' ? ' selected' : '') + '>Right</option><option value="justify"' + (settings.textAlign === 'justify' ? ' selected' : '') + '>Justify</option></select></div>';

    // Border Section
    html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:16px;margin-bottom:16px;margin-top:16px"><div class="tb-setting-label" style="font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-accent)">🔲 Border</div></div>';
    html += this.renderBorderSettings(sIdx, rIdx, cIdx, mIdx);

    // Box Shadow Section
    html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:16px;margin-bottom:16px;margin-top:16px"><div class="tb-setting-label" style="font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-accent)">🌫️ Box Shadow</div></div>';
    html += this.renderBoxShadowSettings(sIdx, rIdx, cIdx, mIdx);

    // Hover Effects Section
    html += this.renderHoverEffectsSettings(sIdx, rIdx, cIdx, mIdx);

    // Transform Section
    html += this.renderTransformSettings(sIdx, rIdx, cIdx, mIdx);

    // Filter Section
    html += this.renderFilterSettings(sIdx, rIdx, cIdx, mIdx);

    // Position Section
    html += this.renderPositionSettings(sIdx, rIdx, cIdx, mIdx);

    // Animation Section
    html += this.renderAnimationSettings(sIdx, rIdx, cIdx, mIdx);

    return html;
};

// Typography Settings
TB.renderTypographySettings = function(sIdx, rIdx, cIdx, mIdx, element, label) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    const settings = mod?.design || {};
    const typoKey = 'typography_' + element;
    const typography = settings[typoKey] || {};

    // Get responsive values
    const getTypoValue = (prop) => {
        if (this.currentDevice === 'desktop') return typography[prop] || '';
        const deviceKey = prop + '_' + this.currentDevice;
        return (typography[deviceKey] !== undefined && typography[deviceKey] !== '') ? typography[deviceKey] : (typography[prop] || '');
    };
    const hasResponsive = (prop) => {
        return (typography[prop + '_tablet'] !== undefined && typography[prop + '_tablet'] !== '') ||
               (typography[prop + '_mobile'] !== undefined && typography[prop + '_mobile'] !== '');
    };
    const deviceIcon = this.getDeviceIcon(this.currentDevice);
    const deviceClass = this.currentDevice;

    let html = '<div class="tb-typography-section" data-element="' + element + '">';
    html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:8px;margin-bottom:12px;cursor:pointer" onclick="TB.toggleTypographySection(this)">';
    html += '<div style="display:flex;justify-content:space-between;align-items:center">';
    html += '<div class="tb-setting-label" style="font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-text-muted);margin:0">' + this.escapeHtml(label) + ' Typography</div>';
    html += '<span class="tb-typography-toggle" style="font-size:10px;color:var(--tb-text-muted)">▼</span>';
    html += '</div></div>';
    html += '<div class="tb-typography-content">';

    // Font Family
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Font Family</div><select class="tb-setting-input" onchange="TB.updateTypographyElement(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'font_family\',this.value)">';
    const fonts = [
        ['', 'Default'], ['system-ui, -apple-system, sans-serif', 'System UI'], ['Arial, Helvetica, sans-serif', 'Arial'],
        ['Georgia, serif', 'Georgia'], ['Times New Roman, Times, serif', 'Times New Roman'], ['Verdana, Geneva, sans-serif', 'Verdana'],
        ['Inter, sans-serif', 'Inter'], ['Roboto, sans-serif', 'Roboto'], ['Open Sans, sans-serif', 'Open Sans'],
        ['Lato, sans-serif', 'Lato'], ['Montserrat, sans-serif', 'Montserrat'], ['Poppins, sans-serif', 'Poppins'],
        ['Playfair Display, serif', 'Playfair Display'], ['Merriweather, serif', 'Merriweather']
    ];
    fonts.forEach(f => { html += '<option value="' + f[0] + '"' + (typography.font_family === f[0] ? ' selected' : '') + '>' + f[1] + '</option>'; });
    html += '</select></div>';

    // Font Size (Responsive)
    const fontSize = getTypoValue('font_size');
    html += '<div class="tb-setting-group"><div class="tb-setting-label"><span class="tb-device-icon ' + deviceClass + '">' + deviceIcon + '</span> Font Size';
    if (hasResponsive('font_size')) html += '<span class="tb-responsive-badge has-responsive">R</span>';
    html += '</div><div style="display:flex;gap:8px">';
    html += '<input type="number" class="tb-setting-input" style="flex:1" value="' + (parseInt(fontSize) || '') + '" placeholder="16" min="8" max="200" onchange="TB.updateResponsiveTypographyWithUnit(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'font_size\',this.value,this.nextElementSibling.value)">';
    const sizeUnit = fontSize ? fontSize.replace(/[0-9.]/g, '') || 'px' : 'px';
    html += '<select class="tb-setting-input" style="width:70px" onchange="TB.updateResponsiveTypographyWithUnit(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'font_size\',this.previousElementSibling.value,this.value)">';
    html += '<option value="px"' + (sizeUnit === 'px' ? ' selected' : '') + '>px</option><option value="em"' + (sizeUnit === 'em' ? ' selected' : '') + '>em</option><option value="rem"' + (sizeUnit === 'rem' ? ' selected' : '') + '>rem</option><option value="%"' + (sizeUnit === '%' ? ' selected' : '') + '>%</option>';
    html += '</select></div></div>';

    // Font Weight
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Font Weight</div><select class="tb-setting-input" onchange="TB.updateTypographyElement(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'font_weight\',this.value)">';
    const weights = [['', 'Default'], ['400', '400 - Normal'], ['500', '500 - Medium'], ['600', '600 - Semi Bold'], ['700', '700 - Bold'], ['800', '800 - Extra Bold']];
    weights.forEach(w => { html += '<option value="' + w[0] + '"' + (typography.font_weight === w[0] ? ' selected' : '') + '>' + w[1] + '</option>'; });
    html += '</select></div>';

    // Line Height (Responsive)
    const lineHeight = getTypoValue('line_height');
    const lhValue = lineHeight ? parseFloat(lineHeight) : '';
    const lhUnit = lineHeight ? lineHeight.replace(/[0-9.]/g, '') || '' : '';
    html += '<div class="tb-setting-group"><div class="tb-setting-label"><span class="tb-device-icon ' + deviceClass + '">' + deviceIcon + '</span> Line Height';
    if (hasResponsive('line_height')) html += '<span class="tb-responsive-badge has-responsive">R</span>';
    html += '</div><div style="display:flex;gap:8px">';
    html += '<input type="number" class="tb-setting-input" style="flex:1" value="' + lhValue + '" placeholder="1.6" min="0.5" max="5" step="0.1" onchange="TB.updateResponsiveTypographyWithUnit(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'line_height\',this.value,this.nextElementSibling.value)">';
    html += '<select class="tb-setting-input" style="width:70px" onchange="TB.updateResponsiveTypographyWithUnit(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'line_height\',this.previousElementSibling.value,this.value)">';
    html += '<option value=""' + (lhUnit === '' ? ' selected' : '') + '>-</option><option value="px"' + (lhUnit === 'px' ? ' selected' : '') + '>px</option><option value="em"' + (lhUnit === 'em' ? ' selected' : '') + '>em</option>';
    html += '</select></div></div>';

    // Letter Spacing
    const lsValue = typography.letter_spacing ? parseFloat(typography.letter_spacing) : '';
    const lsUnit = typography.letter_spacing ? typography.letter_spacing.replace(/[0-9.-]/g, '') || 'px' : 'px';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Letter Spacing</div><div style="display:flex;gap:8px">';
    html += '<input type="number" class="tb-setting-input" style="flex:1" value="' + lsValue + '" placeholder="0" step="0.5" min="-5" max="20" onchange="TB.updateTypographyElementWithUnit(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'letter_spacing\',this.value,this.nextElementSibling.value)">';
    html += '<select class="tb-setting-input" style="width:70px" onchange="TB.updateTypographyElementWithUnit(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'letter_spacing\',this.previousElementSibling.value,this.value)">';
    html += '<option value="px"' + (lsUnit === 'px' ? ' selected' : '') + '>px</option><option value="em"' + (lsUnit === 'em' ? ' selected' : '') + '>em</option>';
    html += '</select></div></div>';

    // Text Transform
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Text Transform</div><select class="tb-setting-input" onchange="TB.updateTypographyElement(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'text_transform\',this.value)">';
    html += '<option value=""' + (!typography.text_transform ? ' selected' : '') + '>None</option>';
    html += '<option value="uppercase"' + (typography.text_transform === 'uppercase' ? ' selected' : '') + '>UPPERCASE</option>';
    html += '<option value="lowercase"' + (typography.text_transform === 'lowercase' ? ' selected' : '') + '>lowercase</option>';
    html += '<option value="capitalize"' + (typography.text_transform === 'capitalize' ? ' selected' : '') + '>Capitalize</option>';
    html += '</select></div>';

    // Text Color
    html += this.renderColorPicker('Text Color', typography.color, 'TB.updateTypographyElement(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'color\',VALUE)', '#333333');

    html += '</div></div>';
    return html;
};

// Toggle typography section
TB.toggleTypographySection = function(headerEl) {
    const section = headerEl.closest('.tb-typography-section');
    const content = section.querySelector('.tb-typography-content');
    const toggle = section.querySelector('.tb-typography-toggle');
    if (content.style.display === 'none') {
        content.style.display = 'block';
        toggle.textContent = '▼';
    } else {
        content.style.display = 'none';
        toggle.textContent = '▶';
    }
};

// ═══════════════════════════════════════════════════════════
// SPACING SETTINGS (Combined Margin + Padding Box)
// ═══════════════════════════════════════════════════════════
TB.renderCombinedSpacingBox = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    const settings = mod?.design || {};
    const deviceIcon = this.getDeviceIcon(this.currentDevice);
    const deviceClass = this.currentDevice;

    // Get responsive spacing values
    const mTop = this.getResponsiveSpacingValue(settings, 'margin', 'top');
    const mRight = this.getResponsiveSpacingValue(settings, 'margin', 'right');
    const mBottom = this.getResponsiveSpacingValue(settings, 'margin', 'bottom');
    const mLeft = this.getResponsiveSpacingValue(settings, 'margin', 'left');
    const marginLinked = this.isResponsiveSpacingLinked(settings, 'margin');

    const pTop = this.getResponsiveSpacingValue(settings, 'padding', 'top');
    const pRight = this.getResponsiveSpacingValue(settings, 'padding', 'right');
    const pBottom = this.getResponsiveSpacingValue(settings, 'padding', 'bottom');
    const pLeft = this.getResponsiveSpacingValue(settings, 'padding', 'left');
    const paddingLinked = this.isResponsiveSpacingLinked(settings, 'padding');

    let html = '<div class="tb-spacing-box">';
    html += '<div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;font-size:11px;color:var(--tb-text-muted)">';
    html += '<span class="tb-device-icon ' + deviceClass + '">' + deviceIcon + '</span>';
    html += '<span>Editing for ' + this.currentDevice.charAt(0).toUpperCase() + this.currentDevice.slice(1) + '</span>';
    html += '</div>';

    // Outer margin box
    html += '<div class="tb-spacing-box-outer">';
    html += '<span class="tb-spacing-label tb-spacing-label-margin">MARGIN</span>';
    html += '<div class="tb-spacing-input-wrap tb-spacing-margin-top"><input type="number" value="' + mTop + '" min="0" max="500" placeholder="0" onchange="TB.handleResponsiveSpacingChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'margin\',\'top\',this.value)"></div>';
    html += '<div class="tb-spacing-input-wrap tb-spacing-margin-right"><input type="number" value="' + mRight + '" min="0" max="500" placeholder="0" onchange="TB.handleResponsiveSpacingChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'margin\',\'right\',this.value)"></div>';
    html += '<div class="tb-spacing-input-wrap tb-spacing-margin-bottom"><input type="number" value="' + mBottom + '" min="0" max="500" placeholder="0" onchange="TB.handleResponsiveSpacingChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'margin\',\'bottom\',this.value)"></div>';
    html += '<div class="tb-spacing-input-wrap tb-spacing-margin-left"><input type="number" value="' + mLeft + '" min="0" max="500" placeholder="0" onchange="TB.handleResponsiveSpacingChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'margin\',\'left\',this.value)"></div>';
    html += '<button type="button" class="tb-spacing-link-btn tb-spacing-link-margin' + (marginLinked ? ' linked' : '') + '" onclick="TB.toggleResponsiveSpacingLink(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'margin\')">' + (marginLinked ? '🔗' : '⛓️') + '</button>';

    // Inner padding box
    html += '<div class="tb-spacing-box-inner">';
    html += '<span class="tb-spacing-label tb-spacing-label-padding">PADDING</span>';
    html += '<div class="tb-spacing-input-wrap tb-spacing-padding-top"><input type="number" value="' + pTop + '" min="0" max="500" placeholder="0" onchange="TB.handleResponsiveSpacingChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'padding\',\'top\',this.value)"></div>';
    html += '<div class="tb-spacing-input-wrap tb-spacing-padding-right"><input type="number" value="' + pRight + '" min="0" max="500" placeholder="0" onchange="TB.handleResponsiveSpacingChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'padding\',\'right\',this.value)"></div>';
    html += '<div class="tb-spacing-input-wrap tb-spacing-padding-bottom"><input type="number" value="' + pBottom + '" min="0" max="500" placeholder="0" onchange="TB.handleResponsiveSpacingChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'padding\',\'bottom\',this.value)"></div>';
    html += '<div class="tb-spacing-input-wrap tb-spacing-padding-left"><input type="number" value="' + pLeft + '" min="0" max="500" placeholder="0" onchange="TB.handleResponsiveSpacingChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'padding\',\'left\',this.value)"></div>';
    html += '<button type="button" class="tb-spacing-link-btn tb-spacing-link-padding' + (paddingLinked ? ' linked' : '') + '" onclick="TB.toggleResponsiveSpacingLink(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'padding\')">' + (paddingLinked ? '🔗' : '⛓️') + '</button>';
    html += '<div class="tb-spacing-box-content">Content</div>';
    html += '</div></div></div>';

    return html;
};

// ═══════════════════════════════════════════════════════════
// BORDER SETTINGS
// ═══════════════════════════════════════════════════════════
TB.renderBorderSettings = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    const settings = mod?.design || {};

    const bwTop = parseInt(settings.border_width_top) || 0;
    const bwRight = parseInt(settings.border_width_right) || 0;
    const bwBottom = parseInt(settings.border_width_bottom) || 0;
    const bwLeft = parseInt(settings.border_width_left) || 0;
    const borderWidthLinked = settings.border_width_linked || false;
    const borderStyle = settings.border_style || 'none';
    const borderColor = settings.border_color || '#e2e8f0';
    const brTL = parseInt(settings.border_radius_tl) || 0;
    const brTR = parseInt(settings.border_radius_tr) || 0;
    const brBR = parseInt(settings.border_radius_br) || 0;
    const brBL = parseInt(settings.border_radius_bl) || 0;
    const borderRadiusLinked = settings.border_radius_linked || false;

    let html = '';

    // Border Width
    html += '<div class="tb-border-section"><div class="tb-border-section-header" onclick="TB.toggleBorderSection(this)"><div class="tb-border-section-title"><span style="color:#7c7fea">◼</span> Border Width</div><span class="tb-border-section-toggle">▼</span></div>';
    html += '<div class="tb-border-section-body">' + this.renderBorderWidthBox(sIdx, rIdx, cIdx, mIdx, bwTop, bwRight, bwBottom, bwLeft, borderWidthLinked) + '</div></div>';

    // Border Style
    html += '<div class="tb-border-section"><div class="tb-border-section-header" onclick="TB.toggleBorderSection(this)"><div class="tb-border-section-title"><span style="color:#89b4fa">◼</span> Border Style</div><span class="tb-border-section-toggle">▼</span></div>';
    html += '<div class="tb-border-section-body"><div class="tb-border-style-row"><label>Style</label><select onchange="TB.updateBorder(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'border_style\',this.value)">';
    ['none', 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge'].forEach(s => {
        html += '<option value="' + s + '"' + (borderStyle === s ? ' selected' : '') + '>' + s.charAt(0).toUpperCase() + s.slice(1) + '</option>';
    });
    html += '</select></div></div></div>';

    // Border Color
    html += '<div class="tb-border-section"><div class="tb-border-section-header" onclick="TB.toggleBorderSection(this)"><div class="tb-border-section-title"><span style="color:#a6e3a1">◼</span> Border Color</div><span class="tb-border-section-toggle">▼</span></div>';
    html += '<div class="tb-border-section-body"><div class="tb-border-color-row"><label>Color</label>';
    html += '<input type="color" value="' + borderColor + '" onchange="TB.updateBorder(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'border_color\',this.value)">';
    html += '<input type="text" value="' + borderColor + '" onchange="TB.updateBorder(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'border_color\',this.value)">';
    html += '</div></div></div>';

    // Border Radius
    html += '<div class="tb-border-section"><div class="tb-border-section-header" onclick="TB.toggleBorderSection(this)"><div class="tb-border-section-title"><span style="color:#f5c2e7">◼</span> Border Radius</div><span class="tb-border-section-toggle">▼</span></div>';
    html += '<div class="tb-border-section-body">' + this.renderBorderRadiusBox(sIdx, rIdx, cIdx, mIdx, brTL, brTR, brBR, brBL, borderRadiusLinked) + '</div></div>';

    return html;
};

TB.renderBorderWidthBox = function(sIdx, rIdx, cIdx, mIdx, top, right, bottom, left, linked) {
    let html = '<div class="tb-border-box"><div class="tb-border-box-outer"><span class="tb-border-label">WIDTH</span><div class="tb-border-box-inner">';
    html += '<div class="tb-border-input-wrap tb-border-width-top"><input type="number" value="' + top + '" min="0" max="50" onchange="TB.handleBorderWidthChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'top\',this.value)"></div>';
    html += '<div class="tb-border-input-wrap tb-border-width-right"><input type="number" value="' + right + '" min="0" max="50" onchange="TB.handleBorderWidthChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'right\',this.value)"></div>';
    html += '<div class="tb-border-input-wrap tb-border-width-bottom"><input type="number" value="' + bottom + '" min="0" max="50" onchange="TB.handleBorderWidthChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'bottom\',this.value)"></div>';
    html += '<div class="tb-border-input-wrap tb-border-width-left"><input type="number" value="' + left + '" min="0" max="50" onchange="TB.handleBorderWidthChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'left\',this.value)"></div>';
    html += '<button type="button" class="tb-border-link-btn' + (linked ? ' linked' : '') + '" onclick="TB.toggleBorderWidthLink(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')">' + (linked ? '🔗' : '⛓️') + '</button>';
    html += '</div></div></div>';
    return html;
};

TB.renderBorderRadiusBox = function(sIdx, rIdx, cIdx, mIdx, tl, tr, br, bl, linked) {
    let html = '<div class="tb-border-radius-box"><span class="tb-border-label">RADIUS</span>';
    html += '<div class="tb-border-radius-input-wrap tb-border-radius-tl"><input type="number" value="' + tl + '" min="0" max="500" onchange="TB.handleBorderRadiusChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'tl\',this.value)"></div>';
    html += '<div class="tb-border-radius-input-wrap tb-border-radius-tr"><input type="number" value="' + tr + '" min="0" max="500" onchange="TB.handleBorderRadiusChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'tr\',this.value)"></div>';
    html += '<div class="tb-border-radius-input-wrap tb-border-radius-br"><input type="number" value="' + br + '" min="0" max="500" onchange="TB.handleBorderRadiusChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'br\',this.value)"></div>';
    html += '<div class="tb-border-radius-input-wrap tb-border-radius-bl"><input type="number" value="' + bl + '" min="0" max="500" onchange="TB.handleBorderRadiusChange(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'bl\',this.value)"></div>';
    html += '<button type="button" class="tb-border-link-btn' + (linked ? ' linked' : '') + '" onclick="TB.toggleBorderRadiusLink(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')">' + (linked ? '🔗' : '⛓️') + '</button>';
    html += '</div>';
    return html;
};

TB.toggleBorderSection = function(el) {
    const section = el.closest('.tb-border-section');
    if (section) section.classList.toggle('collapsed');
};

// ═══════════════════════════════════════════════════════════
// BOX SHADOW SETTINGS
// ═══════════════════════════════════════════════════════════
TB.renderBoxShadowSettings = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    const settings = mod?.design || {};

    const enabled = settings.box_shadow_enabled || false;
    const h = parseInt(settings.box_shadow_horizontal) || 0;
    const v = parseInt(settings.box_shadow_vertical) || 4;
    const blur = parseInt(settings.box_shadow_blur) || 10;
    const spread = parseInt(settings.box_shadow_spread) || 0;
    const color = settings.box_shadow_color || 'rgba(0,0,0,0.1)';
    const inset = settings.box_shadow_inset || false;

    let html = '<div class="tb-shadow-section">';
    
    // Enable toggle
    html += '<div class="tb-shadow-row"><label>Enable Shadow</label>';
    html += '<label class="tb-toggle-switch"><input type="checkbox" ' + (enabled ? 'checked' : '') + ' onchange="TB.updateBoxShadow(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'box_shadow_enabled\',this.checked)"><span class="tb-toggle-slider"></span></label>';
    html += '</div>';

    if (enabled) {
        // Horizontal
        html += '<div class="tb-shadow-row"><label>Horizontal: <span>' + h + 'px</span></label>';
        html += '<input type="range" min="-50" max="50" value="' + h + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'px\'" onchange="TB.updateBoxShadow(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'box_shadow_horizontal\',this.value)">';
        html += '</div>';

        // Vertical
        html += '<div class="tb-shadow-row"><label>Vertical: <span>' + v + 'px</span></label>';
        html += '<input type="range" min="-50" max="50" value="' + v + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'px\'" onchange="TB.updateBoxShadow(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'box_shadow_vertical\',this.value)">';
        html += '</div>';

        // Blur
        html += '<div class="tb-shadow-row"><label>Blur: <span>' + blur + 'px</span></label>';
        html += '<input type="range" min="0" max="100" value="' + blur + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'px\'" onchange="TB.updateBoxShadow(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'box_shadow_blur\',this.value)">';
        html += '</div>';

        // Spread
        html += '<div class="tb-shadow-row"><label>Spread: <span>' + spread + 'px</span></label>';
        html += '<input type="range" min="-50" max="50" value="' + spread + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'px\'" onchange="TB.updateBoxShadow(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'box_shadow_spread\',this.value)">';
        html += '</div>';

        // Color
        html += '<div class="tb-shadow-row"><label>Color</label>';
        html += '<input type="color" value="' + this.rgbaToHex(color) + '" onchange="TB.updateBoxShadowColor(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',this.value)">';
        html += '</div>';

        // Inset
        html += '<div class="tb-shadow-row"><label>Inset</label>';
        html += '<label class="tb-toggle-switch"><input type="checkbox" ' + (inset ? 'checked' : '') + ' onchange="TB.updateBoxShadow(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'box_shadow_inset\',this.checked)"><span class="tb-toggle-slider"></span></label>';
        html += '</div>';
    }

    html += '</div>';
    return html;
};

TB.updateBoxShadow = function(sIdx, rIdx, cIdx, mIdx, prop, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    mod.design[prop] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateBoxShadowColor = function(sIdx, rIdx, cIdx, mIdx, hexColor) {
    const rgba = this.hexToRgba(hexColor, 0.1);
    this.updateBoxShadow(sIdx, rIdx, cIdx, mIdx, 'box_shadow_color', rgba);
};

// ═══════════════════════════════════════════════════════════
// HOVER EFFECTS SETTINGS
// ═══════════════════════════════════════════════════════════
TB.renderHoverEffectsSettings = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    const settings = mod?.design || {};

    const enabled = settings.hover_enabled || false;
    const duration = settings.hover_transition_duration || '0.3';
    const easing = settings.hover_transition_easing || 'ease';

    let html = '<div class="tb-hover-section">';
    html += '<div class="tb-hover-section-header" onclick="TB.toggleHoverSection(this)">';
    html += '<div class="tb-hover-section-title">👆 Hover Effects</div>';
    html += '<span class="tb-hover-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-hover-section-body">';

    // Enable toggle
    html += '<div class="tb-hover-row"><label>Enable Hover</label>';
    html += '<label class="tb-toggle-switch"><input type="checkbox" ' + (enabled ? 'checked' : '') + ' onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'hover_enabled\',this.checked)"><span class="tb-toggle-slider"></span></label>';
    html += '</div>';

    if (enabled) {
        // Transition Duration
        html += '<div class="tb-hover-row"><label>Duration: <span>' + duration + 's</span></label>';
        html += '<input type="range" min="0" max="2" step="0.1" value="' + duration + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'s\'" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'hover_transition_duration\',this.value)">';
        html += '</div>';

        // Easing
        html += '<div class="tb-hover-row"><label>Easing</label>';
        html += '<select onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'hover_transition_easing\',this.value)">';
        ['ease', 'ease-in', 'ease-out', 'ease-in-out', 'linear'].forEach(e => {
            html += '<option value="' + e + '"' + (easing === e ? ' selected' : '') + '>' + e + '</option>';
        });
        html += '</select></div>';

        // Color Changes
        html += '<div class="tb-hover-subsection"><div class="tb-hover-subsection-title">🎨 Colors</div>';
        html += this.renderColorPicker('Text Color', settings.text_color_hover, 'TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'text_color_hover\',VALUE)', '');
        html += this.renderColorPicker('Background', settings.background_color_hover, 'TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'background_color_hover\',VALUE)', '');
        html += this.renderColorPicker('Border Color', settings.border_color_hover, 'TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'border_color_hover\',VALUE)', '');
        html += '</div>';

        // Transform
        html += '<div class="tb-hover-subsection"><div class="tb-hover-subsection-title">🔄 Transform</div>';
        const scaleX = settings.transform_scale_x_hover || '100';
        const scaleY = settings.transform_scale_y_hover || '100';
        const translateY = settings.transform_translate_y_hover || '0';

        html += '<div class="tb-hover-row"><label>Scale X: <span>' + scaleX + '%</span></label>';
        html += '<input type="range" min="50" max="150" value="' + scaleX + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'%\'" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_scale_x_hover\',this.value)">';
        html += '</div>';

        html += '<div class="tb-hover-row"><label>Scale Y: <span>' + scaleY + '%</span></label>';
        html += '<input type="range" min="50" max="150" value="' + scaleY + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'%\'" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_scale_y_hover\',this.value)">';
        html += '</div>';

        html += '<div class="tb-hover-row"><label>Move Y: <span>' + translateY + 'px</span></label>';
        html += '<input type="range" min="-50" max="50" value="' + translateY + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'px\'" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_translate_y_hover\',this.value)">';
        html += '</div>';

        // Opacity
        const opacity = settings.opacity_hover || '1';
        html += '<div class="tb-hover-row"><label>Opacity: <span>' + opacity + '</span></label>';
        html += '<input type="range" min="0" max="1" step="0.1" value="' + opacity + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'opacity_hover\',this.value)">';
        html += '</div>';
        html += '</div>';

        // Hover Shadow
        html += '<div class="tb-hover-subsection"><div class="tb-hover-subsection-title">🌫️ Hover Shadow</div>';
        const shadowEnabled = settings.box_shadow_hover_enabled || false;
        html += '<div class="tb-hover-row"><label>Enable</label>';
        html += '<label class="tb-toggle-switch"><input type="checkbox" ' + (shadowEnabled ? 'checked' : '') + ' onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'box_shadow_hover_enabled\',this.checked)"><span class="tb-toggle-slider"></span></label>';
        html += '</div>';

        if (shadowEnabled) {
            const shV = parseInt(settings.box_shadow_hover_vertical) || 8;
            const shBlur = parseInt(settings.box_shadow_hover_blur) || 20;
            html += '<div class="tb-hover-row"><label>Vertical: <span>' + shV + 'px</span></label>';
            html += '<input type="range" min="0" max="50" value="' + shV + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'px\'" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'box_shadow_hover_vertical\',this.value)">';
            html += '</div>';
            html += '<div class="tb-hover-row"><label>Blur: <span>' + shBlur + 'px</span></label>';
            html += '<input type="range" min="0" max="100" value="' + shBlur + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'px\'" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'box_shadow_hover_blur\',this.value)">';
            html += '</div>';
        }
        html += '</div>';
    }

    html += '</div></div>'; // close body and section
    return html;
};

TB.toggleHoverSection = function(el) {
    el.closest('.tb-hover-section')?.classList.toggle('collapsed');
};

// ═══════════════════════════════════════════════════════════
// HOVER EFFECTS EXPANDED (for Hover Mode view)
// ═══════════════════════════════════════════════════════════
TB.renderHoverEffectsExpanded = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    const settings = mod?.design || {};

    const enabled = settings.hover_enabled || false;
    const duration = settings.hover_transition_duration || '0.3';
    const easing = settings.hover_transition_easing || 'ease';

    let html = '<div class="tb-hover-expanded">';

    // Enable toggle (always shown)
    html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:16px;margin-bottom:16px">';
    html += '<div style="display:flex;justify-content:space-between;align-items:center">';
    html += '<div class="tb-setting-label" style="font-weight:600">Enable Hover Effects</div>';
    html += '<label class="tb-toggle-switch"><input type="checkbox" ' + (enabled ? 'checked' : '') + ' onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'hover_enabled\',this.checked)"><span class="tb-toggle-slider"></span></label>';
    html += '</div></div>';

    if (!enabled) {
        html += '<div style="text-align:center;padding:40px 20px;color:var(--tb-text-muted)">';
        html += '<div style="font-size:32px;margin-bottom:12px">👆</div>';
        html += '<div>Enable hover effects to customize how this module responds to mouse interaction.</div>';
        html += '</div>';
        html += '</div>';
        return html;
    }

    // Transition Settings
    html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:16px;margin-bottom:16px">';
    html += '<div class="tb-setting-label" style="font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-accent);margin-bottom:12px">⚡ Transition</div>';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Duration: <strong>' + duration + 's</strong></div>';
    html += '<input type="range" class="tb-setting-input" min="0" max="2" step="0.1" value="' + duration + '" style="width:100%" oninput="this.previousElementSibling.querySelector(\'strong\').textContent=this.value+\'s\'" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'hover_transition_duration\',this.value)"></div>';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Easing</div>';
    html += '<select class="tb-setting-input" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'hover_transition_easing\',this.value)">';
    ['ease', 'ease-in', 'ease-out', 'ease-in-out', 'linear'].forEach(e => {
        html += '<option value="' + e + '"' + (easing === e ? ' selected' : '') + '>' + e + '</option>';
    });
    html += '</select></div></div>';

    // Colors Section
    html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:16px;margin-bottom:16px">';
    html += '<div class="tb-setting-label" style="font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-accent);margin-bottom:12px">🎨 Colors on Hover</div>';
    html += this.renderColorPicker('Text Color', settings.text_color_hover, 'TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'text_color_hover\',VALUE)', '');
    html += this.renderColorPicker('Background', settings.background_color_hover, 'TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'background_color_hover\',VALUE)', '');
    html += this.renderColorPicker('Border Color', settings.border_color_hover, 'TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'border_color_hover\',VALUE)', '');
    html += '</div>';

    // Transform Section
    html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:16px;margin-bottom:16px">';
    html += '<div class="tb-setting-label" style="font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-accent);margin-bottom:12px">🔄 Transform on Hover</div>';
    const scaleX = settings.transform_scale_x_hover || '100';
    const scaleY = settings.transform_scale_y_hover || '100';
    const translateY = settings.transform_translate_y_hover || '0';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Scale X: <strong>' + scaleX + '%</strong></div>';
    html += '<input type="range" class="tb-setting-input" min="50" max="150" value="' + scaleX + '" style="width:100%" oninput="this.previousElementSibling.querySelector(\'strong\').textContent=this.value+\'%\'" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_scale_x_hover\',this.value)"></div>';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Scale Y: <strong>' + scaleY + '%</strong></div>';
    html += '<input type="range" class="tb-setting-input" min="50" max="150" value="' + scaleY + '" style="width:100%" oninput="this.previousElementSibling.querySelector(\'strong\').textContent=this.value+\'%\'" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_scale_y_hover\',this.value)"></div>';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Move Y: <strong>' + translateY + 'px</strong></div>';
    html += '<input type="range" class="tb-setting-input" min="-50" max="50" value="' + translateY + '" style="width:100%" oninput="this.previousElementSibling.querySelector(\'strong\').textContent=this.value+\'px\'" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_translate_y_hover\',this.value)"></div>';
    html += '</div>';

    // Opacity Section
    html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:16px;margin-bottom:16px">';
    html += '<div class="tb-setting-label" style="font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-accent);margin-bottom:12px">👁️ Opacity on Hover</div>';
    const opacity = settings.opacity_hover || '1';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Opacity: <strong>' + opacity + '</strong></div>';
    html += '<input type="range" class="tb-setting-input" min="0" max="1" step="0.1" value="' + opacity + '" style="width:100%" oninput="this.previousElementSibling.querySelector(\'strong\').textContent=this.value" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'opacity_hover\',this.value)"></div>';
    html += '</div>';

    // Shadow Section
    html += '<div class="tb-setting-group">';
    html += '<div class="tb-setting-label" style="font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-accent);margin-bottom:12px">🌫️ Shadow on Hover</div>';
    const shadowEnabled = settings.box_shadow_hover_enabled || false;
    html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">';
    html += '<span>Enable Shadow</span>';
    html += '<label class="tb-toggle-switch"><input type="checkbox" ' + (shadowEnabled ? 'checked' : '') + ' onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'box_shadow_hover_enabled\',this.checked)"><span class="tb-toggle-slider"></span></label>';
    html += '</div>';
    if (shadowEnabled) {
        const shV = parseInt(settings.box_shadow_hover_vertical) || 8;
        const shBlur = parseInt(settings.box_shadow_hover_blur) || 20;
        const shColor = settings.box_shadow_hover_color || 'rgba(0,0,0,0.2)';
        html += '<div class="tb-setting-group"><div class="tb-setting-label">Vertical: <strong>' + shV + 'px</strong></div>';
        html += '<input type="range" class="tb-setting-input" min="0" max="50" value="' + shV + '" style="width:100%" oninput="this.previousElementSibling.querySelector(\'strong\').textContent=this.value+\'px\'" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'box_shadow_hover_vertical\',this.value)"></div>';
        html += '<div class="tb-setting-group"><div class="tb-setting-label">Blur: <strong>' + shBlur + 'px</strong></div>';
        html += '<input type="range" class="tb-setting-input" min="0" max="100" value="' + shBlur + '" style="width:100%" oninput="this.previousElementSibling.querySelector(\'strong\').textContent=this.value+\'px\'" onchange="TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'box_shadow_hover_blur\',this.value)"></div>';
        html += this.renderColorPicker('Shadow Color', shColor, 'TB.updateHoverSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'box_shadow_hover_color\',VALUE)', 'rgba(0,0,0,0.2)');
    }
    html += '</div>';

    html += '</div>';
    return html;
};

TB.updateHoverSetting = function(sIdx, rIdx, cIdx, mIdx, prop, value) {
    console.log('🔥 updateHoverSetting called:', {sIdx, rIdx, cIdx, mIdx, prop, value});
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) {
        console.error('❌ Module not found!');
        return;
    }
    // FIX: Ensure design is an object, not an array
    if (!mod.design || Array.isArray(mod.design)) {
        mod.design = {};
    }
    mod.design[prop] = value;
    console.log('✅ Design updated:', mod.design);
    this.saveToHistory();
    this.renderCanvas();
    this.updateHoverStylesheet();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// TRANSFORM SETTINGS
// ═══════════════════════════════════════════════════════════
TB.renderTransformSettings = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    const settings = mod?.design || {};

    const scaleX = settings.transform_scale_x || '100';
    const scaleY = settings.transform_scale_y || '100';
    const rotateZ = settings.transform_rotate_z || '0';
    const translateX = settings.transform_translate_x || '0';
    const translateY = settings.transform_translate_y || '0';
    const skewX = settings.transform_skew_x || '0';
    const skewY = settings.transform_skew_y || '0';
    const origin = settings.transform_origin || 'center center';

    let html = '<div class="tb-transform-section">';
    html += '<div class="tb-transform-section-header" onclick="TB.toggleTransformSection(this)">';
    html += '<div class="tb-transform-section-title">🔄 Transform</div>';
    html += '<span class="tb-transform-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-transform-section-body">';

    // Scale
    html += '<div class="tb-transform-subsection"><div class="tb-transform-subsection-title">📐 Scale</div>';
    html += '<div class="tb-transform-control-row"><label>Scale X</label>';
    html += '<input type="range" min="0" max="200" value="' + scaleX + '" oninput="TB.updateTransformSlider(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_scale_x\',this.value)">';
    html += '<input type="number" min="0" max="200" value="' + scaleX + '" onchange="TB.updateTransformSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_scale_x\',this.value)"><span class="tb-unit-label">%</span></div>';
    html += '<div class="tb-transform-control-row"><label>Scale Y</label>';
    html += '<input type="range" min="0" max="200" value="' + scaleY + '" oninput="TB.updateTransformSlider(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_scale_y\',this.value)">';
    html += '<input type="number" min="0" max="200" value="' + scaleY + '" onchange="TB.updateTransformSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_scale_y\',this.value)"><span class="tb-unit-label">%</span></div>';
    html += '</div>';

    // Rotate
    html += '<div class="tb-transform-subsection"><div class="tb-transform-subsection-title">🔃 Rotate</div>';
    html += '<div class="tb-transform-control-row"><label>Rotate Z</label>';
    html += '<input type="range" min="-180" max="180" value="' + rotateZ + '" oninput="TB.updateTransformSlider(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_rotate_z\',this.value)">';
    html += '<input type="number" min="-180" max="180" value="' + rotateZ + '" onchange="TB.updateTransformSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_rotate_z\',this.value)"><span class="tb-unit-label">°</span></div>';
    html += '</div>';

    // Translate
    html += '<div class="tb-transform-subsection"><div class="tb-transform-subsection-title">↔️ Translate</div>';
    html += '<div class="tb-transform-control-row"><label>Move X</label>';
    html += '<input type="range" min="-200" max="200" value="' + translateX + '" oninput="TB.updateTransformSlider(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_translate_x\',this.value)">';
    html += '<input type="number" min="-200" max="200" value="' + translateX + '" onchange="TB.updateTransformSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_translate_x\',this.value)"><span class="tb-unit-label">px</span></div>';
    html += '<div class="tb-transform-control-row"><label>Move Y</label>';
    html += '<input type="range" min="-200" max="200" value="' + translateY + '" oninput="TB.updateTransformSlider(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_translate_y\',this.value)">';
    html += '<input type="number" min="-200" max="200" value="' + translateY + '" onchange="TB.updateTransformSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_translate_y\',this.value)"><span class="tb-unit-label">px</span></div>';
    html += '</div>';

    // Skew
    html += '<div class="tb-transform-subsection"><div class="tb-transform-subsection-title">◇ Skew</div>';
    html += '<div class="tb-transform-control-row"><label>Skew X</label>';
    html += '<input type="range" min="-60" max="60" value="' + skewX + '" oninput="TB.updateTransformSlider(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_skew_x\',this.value)">';
    html += '<input type="number" min="-60" max="60" value="' + skewX + '" onchange="TB.updateTransformSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_skew_x\',this.value)"><span class="tb-unit-label">°</span></div>';
    html += '<div class="tb-transform-control-row"><label>Skew Y</label>';
    html += '<input type="range" min="-60" max="60" value="' + skewY + '" oninput="TB.updateTransformSlider(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_skew_y\',this.value)">';
    html += '<input type="number" min="-60" max="60" value="' + skewY + '" onchange="TB.updateTransformSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transform_skew_y\',this.value)"><span class="tb-unit-label">°</span></div>';
    html += '</div>';

    // Origin
    html += '<div class="tb-transform-subsection"><div class="tb-transform-subsection-title">⊕ Origin</div>';
    html += '<div class="tb-transform-origin-value">' + origin + '</div>';
    html += '<div class="tb-transform-origin-grid">';
    const points = [['left','top'],['center','top'],['right','top'],['left','center'],['center','center'],['right','center'],['left','bottom'],['center','bottom'],['right','bottom']];
    points.forEach(p => {
        const val = p[0] + ' ' + p[1];
        html += '<div class="tb-transform-origin-point' + (origin === val ? ' active' : '') + '" onclick="TB.setTransformOrigin(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + p[0] + '\',\'' + p[1] + '\')"></div>';
    });
    html += '</div></div>';

    // Reset
    html += '<button type="button" class="tb-transform-reset-btn" onclick="TB.resetTransforms(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')"><span>↺</span> Reset</button>';

    html += '</div></div>';
    return html;
};

TB.toggleTransformSection = function(el) { el.closest('.tb-transform-section')?.classList.toggle('collapsed'); };

TB.updateTransformSetting = function(sIdx, rIdx, cIdx, mIdx, prop, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    mod.design[prop] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateTransformSlider = function(sIdx, rIdx, cIdx, mIdx, prop, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    mod.design[prop] = value;
    clearTimeout(this.transformUpdateTimer);
    this.transformUpdateTimer = setTimeout(() => { this.saveToHistory(); this.renderCanvas(); }, 100);
};

TB.setTransformOrigin = function(sIdx, rIdx, cIdx, mIdx, x, y) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    mod.design.transform_origin = x + ' ' + y;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.resetTransforms = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    ['transform_scale_x','transform_scale_y','transform_rotate_z','transform_translate_x','transform_translate_y','transform_skew_x','transform_skew_y'].forEach(p => delete mod.design[p]);
    mod.design.transform_origin = 'center center';
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// FILTER SETTINGS
// ═══════════════════════════════════════════════════════════
TB.renderFilterSettings = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    const settings = mod?.design || {};

    const blur = settings.filter_blur || '0';
    const brightness = settings.filter_brightness || '100';
    const contrast = settings.filter_contrast || '100';
    const saturation = settings.filter_saturation || '100';
    const grayscale = settings.filter_grayscale || '0';
    const opacity = settings.filter_opacity || '100';

    let html = '<div class="tb-filter-section">';
    html += '<div class="tb-filter-section-header" onclick="TB.toggleFilterSection(this)">';
    html += '<div class="tb-filter-section-title">🎨 Filters</div>';
    html += '<span class="tb-filter-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-filter-section-body">';

    // Blur
    html += '<div class="tb-filter-control-row"><label>Blur: <span>' + blur + 'px</span></label>';
    html += '<input type="range" min="0" max="20" value="' + blur + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'px\'" onchange="TB.updateFilter(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'filter_blur\',this.value)"></div>';

    // Brightness
    html += '<div class="tb-filter-control-row"><label>Brightness: <span>' + brightness + '%</span></label>';
    html += '<input type="range" min="0" max="200" value="' + brightness + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'%\'" onchange="TB.updateFilter(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'filter_brightness\',this.value)"></div>';

    // Contrast
    html += '<div class="tb-filter-control-row"><label>Contrast: <span>' + contrast + '%</span></label>';
    html += '<input type="range" min="0" max="200" value="' + contrast + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'%\'" onchange="TB.updateFilter(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'filter_contrast\',this.value)"></div>';

    // Saturation
    html += '<div class="tb-filter-control-row"><label>Saturation: <span>' + saturation + '%</span></label>';
    html += '<input type="range" min="0" max="200" value="' + saturation + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'%\'" onchange="TB.updateFilter(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'filter_saturation\',this.value)"></div>';

    // Grayscale
    html += '<div class="tb-filter-control-row"><label>Grayscale: <span>' + grayscale + '%</span></label>';
    html += '<input type="range" min="0" max="100" value="' + grayscale + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'%\'" onchange="TB.updateFilter(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'filter_grayscale\',this.value)"></div>';

    // Opacity
    html += '<div class="tb-filter-control-row"><label>Opacity: <span>' + opacity + '%</span></label>';
    html += '<input type="range" min="0" max="100" value="' + opacity + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'%\'" onchange="TB.updateFilter(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'filter_opacity\',this.value)"></div>';

    // Reset
    html += '<button type="button" class="tb-filter-reset-btn" onclick="TB.resetFilters(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')"><span>↺</span> Reset</button>';

    html += '</div></div>';
    return html;
};

TB.toggleFilterSection = function(el) { el.closest('.tb-filter-section')?.classList.toggle('collapsed'); };

TB.updateFilter = function(sIdx, rIdx, cIdx, mIdx, prop, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    mod.design[prop] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.resetFilters = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    ['filter_blur','filter_brightness','filter_contrast','filter_saturation','filter_grayscale','filter_opacity'].forEach(p => delete mod.design[p]);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// POSITION SETTINGS
// ═══════════════════════════════════════════════════════════
TB.renderPositionSettings = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    const settings = mod?.design || {};

    const positionType = settings.position || 'static';
    const zIndex = settings.z_index || 'auto';
    const showOffsets = ['relative', 'absolute', 'fixed', 'sticky'].includes(positionType);

    let html = '<div class="tb-position-section">';
    html += '<div class="tb-position-section-header" onclick="TB.togglePositionSection(this)">';
    html += '<div class="tb-position-section-title">📍 Position</div>';
    html += '<span class="tb-position-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-position-section-body">';

    // Position Type
    html += '<div class="tb-position-type-row"><label>Position Type</label>';
    html += '<select onchange="TB.updatePosition(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'position\',this.value)">';
    html += '<option value="static"' + (positionType === 'static' ? ' selected' : '') + '>Static</option>';
    html += '<option value="relative"' + (positionType === 'relative' ? ' selected' : '') + '>Relative</option>';
    html += '<option value="absolute"' + (positionType === 'absolute' ? ' selected' : '') + '>Absolute</option>';
    html += '<option value="fixed"' + (positionType === 'fixed' ? ' selected' : '') + '>Fixed</option>';
    html += '<option value="sticky"' + (positionType === 'sticky' ? ' selected' : '') + '>Sticky</option>';
    html += '</select></div>';

    // Position Offsets
    if (showOffsets) {
        html += '<div class="tb-position-offset-box"><div class="tb-position-offset-title">↔️ Offsets</div>';
        ['top', 'right', 'bottom', 'left'].forEach(side => {
            const val = settings['position_' + side] || '';
            html += '<div class="tb-position-offset-input ' + side + '"><span>' + side.charAt(0).toUpperCase() + side.slice(1) + '</span>';
            html += '<input type="text" value="' + val + '" placeholder="auto" onchange="TB.updatePosition(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'position_' + side + '\',this.value)">';
            html += '</div>';
        });
        html += '<div class="tb-position-offset-center">Module</div></div>';
    }

    // Z-Index
    html += '<div class="tb-zindex-section"><div class="tb-zindex-title">📊 Z-Index</div>';
    html += '<input type="text" value="' + zIndex + '" placeholder="auto" onchange="TB.updatePosition(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'z_index\',this.value)">';
    html += '<div class="tb-zindex-presets">';
    ['-1', '0', '1', '10', '100', 'auto'].forEach(z => {
        html += '<button type="button" class="tb-zindex-preset' + (zIndex === z ? ' active' : '') + '" onclick="TB.updatePosition(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'z_index\',\'' + z + '\')">' + z + '</button>';
    });
    html += '</div></div>';

    html += '</div></div>';
    return html;
};

TB.togglePositionSection = function(el) { el.closest('.tb-position-section')?.classList.toggle('collapsed'); };

TB.updatePosition = function(sIdx, rIdx, cIdx, mIdx, prop, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    mod.design[prop] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// ANIMATION SETTINGS
// ═══════════════════════════════════════════════════════════
TB.renderAnimationSettings = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    const settings = mod?.design || {};

    const enabled = settings.animation_enabled || false;
    const animType = settings.animation_type || 'fadeIn';
    const duration = settings.animation_duration || '0.6';
    const delay = settings.animation_delay || '0';
    const easing = settings.animation_easing || 'ease-out';
    const scrollEnabled = settings.scroll_trigger_enabled || false;

    let html = '<div class="tb-animation-section">';
    html += '<div class="tb-animation-section-header" onclick="TB.toggleAnimationSection(this)">';
    html += '<div class="tb-animation-section-title">✨ Animation</div>';
    html += '<span class="tb-animation-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-animation-section-body">';

    // Enable
    html += '<div class="tb-animation-control-row"><label>Enable Animation</label>';
    html += '<label class="tb-toggle-switch"><input type="checkbox" ' + (enabled ? 'checked' : '') + ' onchange="TB.updateAnimation(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'animation_enabled\',this.checked)"><span class="tb-toggle-slider"></span></label>';
    html += '</div>';

    if (enabled) {
        // Type
        html += '<div class="tb-animation-control-row"><label>Animation Type</label>';
        html += '<select onchange="TB.updateAnimation(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'animation_type\',this.value)">';
        const types = ['fadeIn', 'fadeInUp', 'fadeInDown', 'fadeInLeft', 'fadeInRight', 'zoomIn', 'zoomOut', 'slideInUp', 'slideInDown', 'slideInLeft', 'slideInRight', 'bounceIn', 'flipInX', 'flipInY'];
        types.forEach(t => { html += '<option value="' + t + '"' + (animType === t ? ' selected' : '') + '>' + t + '</option>'; });
        html += '</select></div>';

        // Duration
        html += '<div class="tb-animation-control-row"><label>Duration: <span>' + duration + 's</span></label>';
        html += '<input type="range" min="0.1" max="3" step="0.1" value="' + duration + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'s\'" onchange="TB.updateAnimation(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'animation_duration\',this.value)">';
        html += '</div>';

        // Delay
        html += '<div class="tb-animation-control-row"><label>Delay: <span>' + delay + 's</span></label>';
        html += '<input type="range" min="0" max="2" step="0.1" value="' + delay + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'s\'" onchange="TB.updateAnimation(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'animation_delay\',this.value)">';
        html += '</div>';

        // Easing
        html += '<div class="tb-animation-control-row"><label>Easing</label>';
        html += '<select onchange="TB.updateAnimation(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'animation_easing\',this.value)">';
        ['ease', 'ease-in', 'ease-out', 'ease-in-out', 'linear'].forEach(e => {
            html += '<option value="' + e + '"' + (easing === e ? ' selected' : '') + '>' + e + '</option>';
        });
        html += '</select></div>';

        // Preview button
        html += '<button type="button" class="tb-animation-preview-btn" onclick="TB.previewAnimation(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ')">▶ Preview</button>';

        // Scroll Trigger
        html += '<div class="tb-animation-subsection"><div class="tb-animation-subsection-title">📜 Scroll Trigger</div>';
        html += '<div class="tb-animation-control-row"><label>Trigger on Scroll</label>';
        html += '<label class="tb-toggle-switch"><input type="checkbox" ' + (scrollEnabled ? 'checked' : '') + ' onchange="TB.updateAnimation(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'scroll_trigger_enabled\',this.checked)"><span class="tb-toggle-slider"></span></label>';
        html += '</div>';

        if (scrollEnabled) {
            const triggerPoint = settings.scroll_trigger_point || '80';
            const animateOnce = settings.scroll_animate_once !== false;
            html += '<div class="tb-animation-control-row"><label>Trigger Point: <span>' + triggerPoint + '%</span></label>';
            html += '<input type="range" min="0" max="100" step="5" value="' + triggerPoint + '" oninput="this.previousElementSibling.querySelector(\'span\').textContent=this.value+\'%\'" onchange="TB.updateAnimation(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'scroll_trigger_point\',this.value)">';
            html += '</div>';
            html += '<div class="tb-animation-control-row"><label>Animate Once</label>';
            html += '<label class="tb-toggle-switch"><input type="checkbox" ' + (animateOnce ? 'checked' : '') + ' onchange="TB.updateAnimation(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'scroll_animate_once\',this.checked)"><span class="tb-toggle-slider"></span></label>';
            html += '</div>';
        }
        html += '</div>';
    }

    html += '</div></div>';
    return html;
};

TB.toggleAnimationSection = function(el) { el.closest('.tb-animation-section')?.classList.toggle('collapsed'); };

TB.updateAnimation = function(sIdx, rIdx, cIdx, mIdx, prop, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    mod.design[prop] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.renderSettings();
};

TB.previewAnimation = function(sIdx, rIdx, cIdx, mIdx) {
    const moduleEl = document.querySelector('[data-module-coords="' + sIdx + '-' + rIdx + '-' + cIdx + '-' + mIdx + '"]');
    if (!moduleEl) return;
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod?.settings?.animation_enabled) return;
    const s = mod.settings;
    moduleEl.style.animation = 'none';
    moduleEl.offsetHeight;
    moduleEl.style.animation = 'tb-' + (s.animation_type || 'fadeIn') + ' ' + (s.animation_duration || '0.6') + 's ' + (s.animation_easing || 'ease-out') + ' ' + (s.animation_delay || '0') + 's both';
    setTimeout(() => { moduleEl.style.animation = ''; }, (parseFloat(s.animation_duration || '0.6') + parseFloat(s.animation_delay || '0')) * 1000 + 100);
};

// Helper functions that may be called from renderDesignSettings
TB.rgbaToHex = function(rgba) {
    if (!rgba || rgba.startsWith('#')) return rgba || '#000000';
    const match = rgba.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
    if (!match) return '#000000';
    return '#' + [match[1], match[2], match[3]].map(x => parseInt(x).toString(16).padStart(2, '0')).join('');
};

TB.hexToRgba = function(hex, alpha) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
};

// ═══════════════════════════════════════════════════════════
// FIX: Update textModules to include ALL typography modules
// ═══════════════════════════════════════════════════════════
TB.textModules = ['text', 'heading', 'button', 'quote', 'cta', 'pricing', 'blurb', 'hero', 'testimonial', 'team', 'countdown', 'counter', 'list', 'menu', 'sidebar', 'slider', 'toggle', 'bar_counters', 'video_slider', 'post_slider', 'post_title', 'post_content', 'posts_navigation', 'circle_counter', 'search', 'comments'];

// ═══════════════════════════════════════════════════════════
// GALLERY DESIGN SETTINGS (Full Implementation)
// ═══════════════════════════════════════════════════════════
TB.renderGalleryDesignSettings = function(mod, sIdx, rIdx, cIdx, mIdx) {
    const gDesign = mod.design || {};
    
    // Helper functions
    const mkToggle = (prop, val) => {
        const isOn = val !== false;
        return '<div class="tb-toggle ' + (isOn ? 'on' : '') + '" onclick="this.classList.toggle(\'on\');TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + prop + '\',this.classList.contains(\'on\'))"><div class="tb-toggle-knob"></div></div>';
    };
    const mkColor = (prop, val, placeholder) => {
        const v = val || placeholder;
        const hex = v.startsWith('#') ? v : '#888888';
        return '<div class="tb-color-wrap"><input type="color" class="tb-color-swatch" value="' + hex + '" onchange="this.nextElementSibling.value=this.value;TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + prop + '\',this.value)"><input type="text" class="tb-setting-input tb-color-text" value="' + v + '" onchange="TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + prop + '\',this.value)"></div>';
    };
    const mkBtnGroup = (prop, options, current) => {
        let h = '<div class="tb-btn-group">';
        options.forEach(o => {
            const val = Array.isArray(o) ? o[0] : o;
            const label = Array.isArray(o) ? o[1] : o;
            h += '<button class="tb-btn-opt ' + (current === val ? 'active' : '') + '" onclick="this.parentElement.querySelectorAll(\'.tb-btn-opt\').forEach(b=>b.classList.remove(\'active\'));this.classList.add(\'active\');TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + prop + '\',\'' + val + '\')">' + label + '</button>';
        });
        return h + '</div>';
    };

    let html = '<div class="tb-gallery-panel">';

    // LAYOUT SECTION
    html += '<div class="tb-section"><div class="tb-section-header" onclick="this.parentElement.classList.toggle(\'collapsed\')"><div class="tb-section-title"><span class="tb-section-icon">📐</span> Layout</div><span class="tb-section-chevron">▼</span></div><div class="tb-section-body">';
    html += '<div class="tb-row"><span class="tb-row-label">Type</span><div class="tb-row-control">' + mkBtnGroup('layout', ['grid', 'masonry', 'justified'], gDesign.layout || 'grid') + '</div></div>';
    html += '<div class="tb-row" style="flex-direction:column"><span class="tb-row-label">Gap</span><div class="tb-responsive-row">';
    html += '<div class="tb-resp-item"><span class="tb-resp-label">🖥 Desktop</span><input type="text" class="tb-setting-input tb-mini-input" value="' + (gDesign.gap || '16px') + '" onchange="TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'gap\',this.value)"></div>';
    html += '<div class="tb-resp-item"><span class="tb-resp-label">📱 Tablet</span><input type="text" class="tb-setting-input tb-mini-input" value="' + (gDesign.gap_tablet || '12px') + '" onchange="TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'gap_tablet\',this.value)"></div>';
    html += '<div class="tb-resp-item"><span class="tb-resp-label">📱 Mobile</span><input type="text" class="tb-setting-input tb-mini-input" value="' + (gDesign.gap_mobile || '8px') + '" onchange="TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'gap_mobile\',this.value)"></div>';
    html += '</div></div></div></div>';

    // IMAGE STYLE SECTION
    html += '<div class="tb-section"><div class="tb-section-header" onclick="this.parentElement.classList.toggle(\'collapsed\')"><div class="tb-section-title"><span class="tb-section-icon">🎨</span> Image Style</div><span class="tb-section-chevron">▼</span></div><div class="tb-section-body">';
    html += '<div class="tb-row"><span class="tb-row-label">Aspect Ratio</span><div class="tb-row-control"><select class="tb-mini-select" onchange="TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'aspect_ratio\',this.value)">';
    ['auto', '1:1', '4:3', '16:9', '3:2', '2:3'].forEach(opt => { html += '<option value="' + opt + '"' + ((gDesign.aspect_ratio || 'auto') === opt ? ' selected' : '') + '>' + opt + '</option>'; });
    html += '</select></div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Object Fit</span><div class="tb-row-control">' + mkBtnGroup('object_fit', ['cover', 'contain'], gDesign.object_fit || 'cover') + '</div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Border Radius</span><div class="tb-row-control"><input type="text" class="tb-setting-input tb-mini-input" value="' + (gDesign.border_radius || '8px') + '" onchange="TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'border_radius\',this.value)"></div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Shadow</span><div class="tb-row-control">' + mkBtnGroup('image_shadow', [['none','None'],['small','S'],['medium','M'],['large','L']], gDesign.image_shadow || 'none') + '</div></div>';
    html += '</div></div>';

    // HOVER EFFECTS SECTION
    html += '<div class="tb-section"><div class="tb-section-header" onclick="this.parentElement.classList.toggle(\'collapsed\')"><div class="tb-section-title"><span class="tb-section-icon">✨</span> Hover Effects</div><span class="tb-section-chevron">▼</span></div><div class="tb-section-body">';
    html += '<div class="tb-row"><span class="tb-row-label">Effect</span><div class="tb-row-control"><select class="tb-mini-select" onchange="TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'hover_effect\',this.value)">';
    [['none','None'],['zoom','Zoom'],['lift','Lift'],['overlay','Overlay'],['grayscale','Grayscale'],['blur','Blur']].forEach(o => { html += '<option value="' + o[0] + '"' + ((gDesign.hover_effect || 'zoom') === o[0] ? ' selected' : '') + '>' + o[1] + '</option>'; });
    html += '</select></div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Zoom Scale</span><div class="tb-row-control"><input type="text" class="tb-setting-input tb-mini-input" value="' + (gDesign.hover_zoom_scale || '1.05') + '" onchange="TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'hover_zoom_scale\',this.value)"></div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Overlay</span><div class="tb-row-control">' + mkColor('hover_overlay_color', gDesign.hover_overlay_color, 'rgba(0,0,0,0.4)') + '</div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Icon</span><div class="tb-row-control">' + mkBtnGroup('hover_icon', [['none','—'],['search','🔍'],['expand','⤢'],['plus','+'],['eye','👁']], gDesign.hover_icon || 'search') + '</div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Icon Color</span><div class="tb-row-control">' + mkColor('hover_icon_color', gDesign.hover_icon_color, '#ffffff') + '</div></div>';
    html += '</div></div>';

    // CAPTIONS SECTION
    html += '<div class="tb-section"><div class="tb-section-header" onclick="this.parentElement.classList.toggle(\'collapsed\')"><div class="tb-section-title"><span class="tb-section-icon">💬</span> Captions</div><span class="tb-section-chevron">▼</span></div><div class="tb-section-body">';
    html += '<div class="tb-row"><span class="tb-row-label">Display</span><div class="tb-row-control">' + mkBtnGroup('show_captions', [['never','Never'],['hover','Hover'],['always','Always']], gDesign.show_captions || 'hover') + '</div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Position</span><div class="tb-row-control"><select class="tb-mini-select" onchange="TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'caption_position\',this.value)">';
    [['bottom','Below'],['overlay-bottom','Over Bottom'],['overlay-center','Over Center']].forEach(o => { html += '<option value="' + o[0] + '"' + ((gDesign.caption_position || 'bottom') === o[0] ? ' selected' : '') + '>' + o[1] + '</option>'; });
    html += '</select></div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Background</span><div class="tb-row-control">' + mkColor('caption_bg', gDesign.caption_bg, 'rgba(0,0,0,0.7)') + '</div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Text Color</span><div class="tb-row-control">' + mkColor('caption_color', gDesign.caption_color, '#ffffff') + '</div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Font Size</span><div class="tb-row-control"><input type="text" class="tb-setting-input tb-mini-input" value="' + (gDesign.caption_font_size || '14px') + '" onchange="TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'caption_font_size\',this.value)"></div></div>';
    html += '</div></div>';

    // ANIMATION SECTION
    html += '<div class="tb-section"><div class="tb-section-header" onclick="this.parentElement.classList.toggle(\'collapsed\')"><div class="tb-section-title"><span class="tb-section-icon">🎬</span> Animation</div><span class="tb-section-chevron">▼</span></div><div class="tb-section-body">';
    html += '<div class="tb-row"><span class="tb-row-label">Load Effect</span><div class="tb-row-control">' + mkBtnGroup('load_animation', [['none','None'],['fade','Fade'],['slide-up','Slide'],['zoom-in','Zoom']], gDesign.load_animation || 'fade') + '</div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Duration</span><div class="tb-row-control"><input type="text" class="tb-setting-input tb-mini-input" value="' + (gDesign.animation_duration || '0.4s') + '" onchange="TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'animation_duration\',this.value)"></div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Stagger</span><div class="tb-row-control">' + mkToggle('animation_stagger', gDesign.animation_stagger) + '</div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Stagger Delay</span><div class="tb-row-control"><input type="text" class="tb-setting-input tb-mini-input" value="' + (gDesign.stagger_delay || '0.1s') + '" onchange="TB.updateModuleDesign(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'stagger_delay\',this.value)"></div></div>';
    html += '</div></div>';

    // LIGHTBOX SECTION
    html += '<div class="tb-section"><div class="tb-section-header" onclick="this.parentElement.classList.toggle(\'collapsed\')"><div class="tb-section-title"><span class="tb-section-icon">🔍</span> Lightbox</div><span class="tb-section-chevron">▼</span></div><div class="tb-section-body">';
    html += '<div class="tb-row"><span class="tb-row-label">Enable</span><div class="tb-row-control">' + mkToggle('lightbox', gDesign.lightbox) + '</div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Background</span><div class="tb-row-control">' + mkColor('lightbox_bg', gDesign.lightbox_bg, 'rgba(0,0,0,0.95)') + '</div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Counter</span><div class="tb-row-control">' + mkToggle('lightbox_counter', gDesign.lightbox_counter) + '</div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Arrows</span><div class="tb-row-control">' + mkToggle('lightbox_arrows', gDesign.lightbox_arrows) + '</div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Captions</span><div class="tb-row-control">' + mkToggle('lightbox_captions', gDesign.lightbox_captions) + '</div></div>';
    html += '<div class="tb-row"><span class="tb-row-label">Keyboard Nav</span><div class="tb-row-control">' + mkToggle('lightbox_keyboard', gDesign.lightbox_keyboard) + '</div></div>';
    html += '</div></div>';

    html += '</div>';
    return html;
};

// Helper for gallery design updates
TB.updateModuleDesign = function(sIdx, rIdx, cIdx, mIdx, prop, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design) mod.design = {};
    mod.design[prop] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// POST CONTENT DESIGN SETTINGS (Full Implementation)
// ═══════════════════════════════════════════════════════════
TB.renderPostContentDesignSettings = function(mod, sIdx, rIdx, cIdx, mIdx, hasTypography) {
    const content = mod.content || {};
    let html = '';

    html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:16px;margin-bottom:16px;margin-top:' + (hasTypography ? '16px' : '0') + '"><div class="tb-setting-label" style="font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-accent)">📄 Content Styling</div></div>';

    // Max Width
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Max Width</div>';
    html += '<input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.max_width || '800px') + '" placeholder="800px" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'max_width\',this.value)">';
    html += '<div style="font-size:11px;color:var(--tb-text-muted);margin-top:4px">Controls content width for readability</div></div>';

    // Text Color
    html += this.renderColorPicker('Text Color', content.text_color, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'text_color\',VALUE)', '#1e293b');

    // Font Size
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Font Size</div>';
    html += '<input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.font_size || '18px') + '" placeholder="18px" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'font_size\',this.value)"></div>';

    // Line Height
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Line Height</div>';
    html += '<input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.line_height || '1.8') + '" placeholder="1.8" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'line_height\',this.value)"></div>';

    // Paragraph Spacing
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Paragraph Spacing</div>';
    html += '<input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.paragraph_spacing || '1.5em') + '" placeholder="1.5em" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'paragraph_spacing\',this.value)"></div>';

    // Heading Color
    html += this.renderColorPicker('Heading Color (h2-h6)', content.heading_color, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'heading_color\',VALUE)', '#0f172a');

    // Link Colors Section
    html += '<div class="tb-setting-group" style="border-top:1px solid var(--tb-border);padding-top:12px;margin-top:12px"><div class="tb-setting-label" style="font-weight:600">Link Colors</div></div>';
    html += this.renderColorPicker('Link Color', content.link_color, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'link_color\',VALUE)', '#0073e6');
    html += this.renderColorPicker('Link Hover Color', content.link_hover_color, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'link_hover_color\',VALUE)', '#0056b3');

    // Images Section
    html += '<div class="tb-setting-group" style="border-top:1px solid var(--tb-border);padding-top:12px;margin-top:12px"><div class="tb-setting-label" style="font-weight:600">Images</div></div>';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Image Border Radius</div>';
    html += '<input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.image_border_radius || '8px') + '" placeholder="8px" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'image_border_radius\',this.value)"></div>';

    // Blockquote Section
    html += '<div class="tb-setting-group" style="border-top:1px solid var(--tb-border);padding-top:12px;margin-top:12px"><div class="tb-setting-label" style="font-weight:600">Blockquotes</div></div>';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Blockquote Style</div>';
    html += '<select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'blockquote_style\',this.value)">';
    html += '<option value="border"' + (content.blockquote_style === 'border' || !content.blockquote_style ? ' selected' : '') + '>Border (left line)</option>';
    html += '<option value="background"' + (content.blockquote_style === 'background' ? ' selected' : '') + '>Background</option>';
    html += '<option value="italic"' + (content.blockquote_style === 'italic' ? ' selected' : '') + '>Italic (minimal)</option>';
    html += '</select></div>';
    html += this.renderColorPicker('Blockquote Accent Color', content.blockquote_border_color, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'blockquote_border_color\',VALUE)', '#0073e6');

    // Code Section
    html += '<div class="tb-setting-group" style="border-top:1px solid var(--tb-border);padding-top:12px;margin-top:12px"><div class="tb-setting-label" style="font-weight:600">Code Blocks</div></div>';
    html += this.renderColorPicker('Code Background', content.code_background, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'code_background\',VALUE)', '#f1f5f9');
    html += this.renderColorPicker('Code Text Color', content.code_text_color, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'code_text_color\',VALUE)', '#e11d48');

    // Lists Section
    html += '<div class="tb-setting-group" style="border-top:1px solid var(--tb-border);padding-top:12px;margin-top:12px"><div class="tb-setting-label" style="font-weight:600">Lists</div></div>';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">List Marker Style</div>';
    html += '<select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'list_style\',this.value)">';
    html += '<option value="disc"' + (content.list_style === 'disc' || !content.list_style ? ' selected' : '') + '>Disc (•)</option>';
    html += '<option value="circle"' + (content.list_style === 'circle' ? ' selected' : '') + '>Circle (○)</option>';
    html += '<option value="square"' + (content.list_style === 'square' ? ' selected' : '') + '>Square (▪)</option>';
    html += '<option value="check"' + (content.list_style === 'check' ? ' selected' : '') + '>Checkmark (✓)</option>';
    html += '<option value="arrow"' + (content.list_style === 'arrow' ? ' selected' : '') + '>Arrow (→)</option>';
    html += '</select></div>';

    // Dropcap Section
    html += '<div class="tb-setting-group" style="border-top:1px solid var(--tb-border);padding-top:12px;margin-top:12px"><div class="tb-setting-label" style="font-weight:600">Dropcap</div></div>';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Enable Dropcap</div>';
    html += '<label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input type="checkbox" ' + (content.dropcap ? 'checked' : '') + ' onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'dropcap\',this.checked)"><span>Large decorative first letter</span></label></div>';
    if (content.dropcap) {
        html += this.renderColorPicker('Dropcap Color', content.dropcap_color, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'dropcap_color\',VALUE)', '#0073e6');
    }

    return html;
};

// ═══════════════════════════════════════════════════════════
// COMMENTS DESIGN SETTINGS (Full Implementation)
// ═══════════════════════════════════════════════════════════
TB.renderCommentsDesignSettings = function(mod, sIdx, rIdx, cIdx, mIdx, hasTypography) {
    const content = mod.content || {};
    let html = '';

    html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:16px;margin-bottom:16px;margin-top:' + (hasTypography ? '16px' : '0') + '"><div class="tb-setting-label" style="font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-accent)">💬 Comments Styling</div></div>';

    // Style Preset
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Style</div>';
    html += '<select class="tb-setting-input" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'style\',this.value)">';
    html += '<option value="default"' + (content.style === 'default' || !content.style ? ' selected' : '') + '>Default</option>';
    html += '<option value="card"' + (content.style === 'card' ? ' selected' : '') + '>Card</option>';
    html += '<option value="minimal"' + (content.style === 'minimal' ? ' selected' : '') + '>Minimal</option>';
    html += '<option value="threaded"' + (content.style === 'threaded' ? ' selected' : '') + '>Threaded</option>';
    html += '</select></div>';

    // Comment Colors Section
    html += '<div class="tb-setting-group" style="border-top:1px solid var(--tb-border);padding-top:12px;margin-top:12px"><div class="tb-setting-label" style="font-weight:600">Comment Colors</div></div>';
    html += this.renderColorPicker('Comment Background', content.comment_background, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'comment_background\',VALUE)', '#f8fafc');
    html += this.renderColorPicker('Comment Border Color', content.comment_border_color, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'comment_border_color\',VALUE)', '#e2e8f0');
    html += this.renderColorPicker('Author Name Color', content.author_color, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'author_color\',VALUE)', '#1e293b');
    html += this.renderColorPicker('Date Color', content.date_color, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'date_color\',VALUE)', '#64748b');
    html += this.renderColorPicker('Comment Text Color', content.text_color, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'text_color\',VALUE)', '#374151');

    // Avatar Section
    html += '<div class="tb-setting-group" style="border-top:1px solid var(--tb-border);padding-top:12px;margin-top:12px"><div class="tb-setting-label" style="font-weight:600">Avatar</div></div>';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Avatar Border Radius</div>';
    html += '<input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.avatar_radius || '50%') + '" placeholder="50%" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'avatar_radius\',this.value)"></div>';

    // Form Section
    html += '<div class="tb-setting-group" style="border-top:1px solid var(--tb-border);padding-top:12px;margin-top:12px"><div class="tb-setting-label" style="font-weight:600">Comment Form</div></div>';
    html += this.renderColorPicker('Form Background', content.form_background, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'form_background\',VALUE)', '#f8fafc');
    html += this.renderColorPicker('Input Background', content.input_background, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'input_background\',VALUE)', '#ffffff');
    html += this.renderColorPicker('Input Border Color', content.input_border_color, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'input_border_color\',VALUE)', '#e2e8f0');
    html += this.renderColorPicker('Button Background', content.button_background, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'button_background\',VALUE)', '#0073e6');
    html += this.renderColorPicker('Button Text Color', content.button_text_color, 'TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'button_text_color\',VALUE)', '#ffffff');

    // Spacing Section
    html += '<div class="tb-setting-group" style="border-top:1px solid var(--tb-border);padding-top:12px;margin-top:12px"><div class="tb-setting-label" style="font-weight:600">Spacing</div></div>';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Reply Indent</div>';
    html += '<input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.reply_indent || '40px') + '" placeholder="40px" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'reply_indent\',this.value)"></div>';
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Gap Between Comments</div>';
    html += '<input type="text" class="tb-setting-input" value="' + this.escapeHtml(content.gap || '20px') + '" placeholder="20px" onchange="TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'gap\',this.value)"></div>';

    return html;
};

// ═══════════════════════════════════════════════════════════
// INNER ELEMENT STYLING
// ═══════════════════════════════════════════════════════════

/**
 * Render Element Styles section for interactive modules
 */
TB.renderElementStylesSettings = function(mod, sIdx, rIdx, cIdx, mIdx) {
    const type = mod.type || '';
    const schema = this.elementSchemas[type];
    if (!schema) return '';

    const design = mod.design || {};
    const elements = design.elements || {};

    let html = '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:16px;margin-bottom:16px;margin-top:16px">';
    html += '<div class="tb-setting-label" style="font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-accent)">Element Styles</div>';
    html += '</div>';

    // Render each element
    for (const [elementKey, elementDef] of Object.entries(schema)) {
        // Skip if elementDef is invalid or missing required properties
        if (!elementDef || typeof elementDef !== 'object') continue;
        
        const states = elementDef.states || ['normal'];
        const properties = elementDef.properties || [];
        const label = elementDef.label || elementKey;
        
        const elementStyles = elements[elementKey] || {};

        html += '<div class="tb-element-section" data-element="' + elementKey + '">';
        html += '<div class="tb-setting-group" style="border-bottom:1px solid var(--tb-border);padding-bottom:8px;margin-bottom:12px;cursor:pointer" onclick="TB.toggleElementSection(this)">';
        html += '<div style="display:flex;justify-content:space-between;align-items:center">';
        html += '<div class="tb-setting-label" style="font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-text-muted);margin:0">' + this.escapeHtml(label) + '</div>';
        html += '<span class="tb-element-toggle" style="font-size:10px;color:var(--tb-text-muted)">▼</span>';
        html += '</div></div>';

        html += '<div class="tb-element-content">';

        // State tabs if multiple states
        if (states.length > 1) {
            html += '<div class="tb-state-tabs" style="display:flex;gap:4px;margin-bottom:12px">';
            states.forEach((state, idx) => {
                // Check if this state has saved data - show indicator
                const hasData = elementStyles[state] && Object.keys(elementStyles[state]).length > 0;
                const isActive = idx === 0 ? ' active' : '';
                const stateLabel = state.charAt(0).toUpperCase() + state.slice(1);
                const dataIndicator = hasData && idx !== 0 ? ' ●' : '';
                html += '<button type="button" class="tb-state-tab' + isActive + '" data-state="' + state + '" data-has-data="' + hasData + '" onclick="TB.switchElementState(this,\'' + elementKey + '\')" style="padding:6px 12px;border:1px solid var(--tb-border);background:' + (idx === 0 ? 'var(--tb-accent)' : 'transparent') + ';color:' + (idx === 0 ? '#fff' : 'var(--tb-text)') + ';border-radius:4px;font-size:11px;cursor:pointer">' + stateLabel + dataIndicator + '</button>';
            });
            html += '</div>';
        }

        // State content panels
        states.forEach((state, idx) => {
            const stateStyles = elementStyles[state] || {};
            const displayStyle = idx === 0 ? '' : 'display:none;';

            html += '<div class="tb-state-panel" data-state="' + state + '" style="' + displayStyle + '">';

            // Render property inputs based on available properties
            properties.forEach(prop => {
                html += this.renderElementPropertyInput(sIdx, rIdx, cIdx, mIdx, elementKey, state, prop, stateStyles[prop] || '');
            });

            html += '</div>';
        });

        html += '</div></div>';
    }

    return html;
};

/**
 * Render a single property input for element styling
 */
TB.renderElementPropertyInput = function(sIdx, rIdx, cIdx, mIdx, elementKey, state, prop, value) {
    const label = this.formatPropertyLabel(prop);
    const callback = 'TB.updateElementStyle(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + elementKey + '\',\'' + state + '\',\'' + prop + '\',VALUE)';

    let html = '<div class="tb-setting-group">';

    // Handle different property types
    if (prop === 'background' || prop === 'color' || prop === 'border_color') {
        // Color picker
        html += this.renderColorPicker(label, value, callback, prop === 'background' ? '#f5f5f5' : '#333333');
    } else if (prop === 'font_size' || prop === 'padding' || prop === 'margin_bottom' || prop === 'border_radius' || prop === 'line_height') {
        // Size input with unit
        const numValue = value ? parseFloat(value) : '';
        const unit = value ? value.replace(/[0-9.-]/g, '') || 'px' : 'px';
        html += '<div class="tb-setting-label">' + label + '</div>';
        html += '<div style="display:flex;gap:8px">';
        html += '<input type="number" class="tb-setting-input" style="flex:1" value="' + numValue + '" placeholder="' + this.getPropertyPlaceholder(prop) + '" onchange="TB.updateElementStyle(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + elementKey + '\',\'' + state + '\',\'' + prop + '\',this.value+this.nextElementSibling.value)">';
        html += '<select class="tb-setting-input" style="width:70px" onchange="TB.updateElementStyle(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + elementKey + '\',\'' + state + '\',\'' + prop + '\',this.previousElementSibling.value+this.value)">';
        html += '<option value="px"' + (unit === 'px' ? ' selected' : '') + '>px</option>';
        html += '<option value="em"' + (unit === 'em' ? ' selected' : '') + '>em</option>';
        html += '<option value="rem"' + (unit === 'rem' ? ' selected' : '') + '>rem</option>';
        html += '<option value="%"' + (unit === '%' ? ' selected' : '') + '>%</option>';
        html += '</select></div>';
    } else if (prop === 'font_weight') {
        // Font weight select
        html += '<div class="tb-setting-label">' + label + '</div>';
        html += '<select class="tb-setting-input" onchange="TB.updateElementStyle(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + elementKey + '\',\'' + state + '\',\'' + prop + '\',this.value)">';
        html += '<option value=""' + (!value ? ' selected' : '') + '>Default</option>';
        html += '<option value="400"' + (value === '400' ? ' selected' : '') + '>400 - Normal</option>';
        html += '<option value="500"' + (value === '500' ? ' selected' : '') + '>500 - Medium</option>';
        html += '<option value="600"' + (value === '600' ? ' selected' : '') + '>600 - Semi Bold</option>';
        html += '<option value="700"' + (value === '700' ? ' selected' : '') + '>700 - Bold</option>';
        html += '</select>';
    } else if (prop === 'border') {
        // Border shorthand
        html += '<div class="tb-setting-label">' + label + '</div>';
        html += '<input type="text" class="tb-setting-input" value="' + this.escapeHtml(value) + '" placeholder="1px solid #ddd" onchange="TB.updateElementStyle(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + elementKey + '\',\'' + state + '\',\'' + prop + '\',this.value)">';
    } else if (prop === 'box_shadow') {
        // Box shadow
        html += '<div class="tb-setting-label">' + label + '</div>';
        html += '<input type="text" class="tb-setting-input" value="' + this.escapeHtml(value) + '" placeholder="0 2px 4px rgba(0,0,0,0.1)" onchange="TB.updateElementStyle(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + elementKey + '\',\'' + state + '\',\'' + prop + '\',this.value)">';
    } else {
        // Generic text input
        html += '<div class="tb-setting-label">' + label + '</div>';
        html += '<input type="text" class="tb-setting-input" value="' + this.escapeHtml(value) + '" onchange="TB.updateElementStyle(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + elementKey + '\',\'' + state + '\',\'' + prop + '\',this.value)">';
    }

    html += '</div>';
    return html;
};

/**
 * Format property name to human-readable label
 */
TB.formatPropertyLabel = function(prop) {
    return prop.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};

/**
 * Get placeholder value for property
 */
TB.getPropertyPlaceholder = function(prop) {
    const placeholders = {
        font_size: '16',
        padding: '15',
        margin_bottom: '10',
        border_radius: '4',
        line_height: '1.6'
    };
    return placeholders[prop] || '';
};

/**
 * Update element style in module design
 */
TB.updateElementStyle = function(sIdx, rIdx, cIdx, mIdx, elementKey, state, prop, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;

    // Ensure design.elements structure exists
    if (!mod.design) mod.design = {};
    if (!mod.design.elements) mod.design.elements = {};
    if (!mod.design.elements[elementKey]) mod.design.elements[elementKey] = {};
    if (!mod.design.elements[elementKey][state]) mod.design.elements[elementKey][state] = {};

    // Set or remove value
    if (value === '' || value === null || value === undefined) {
        delete mod.design.elements[elementKey][state][prop];
        // Clean up empty objects
        if (Object.keys(mod.design.elements[elementKey][state]).length === 0) {
            delete mod.design.elements[elementKey][state];
        }
        if (Object.keys(mod.design.elements[elementKey]).length === 0) {
            delete mod.design.elements[elementKey];
        }
        if (Object.keys(mod.design.elements).length === 0) {
            delete mod.design.elements;
        }
    } else {
        mod.design.elements[elementKey][state][prop] = value;
    }

    this.markDirty();
    this.refreshPreview();
};

/**
 * Toggle element section visibility
 */
TB.toggleElementSection = function(header) {
    const section = header.closest('.tb-element-section');
    const content = section.querySelector('.tb-element-content');
    const toggle = header.querySelector('.tb-element-toggle');

    if (content.style.display === 'none') {
        content.style.display = 'block';
        toggle.textContent = '▼';
    } else {
        content.style.display = 'none';
        toggle.textContent = '▶';
    }
};

/**
 * Switch between element states (normal/hover/active)
 */
TB.switchElementState = function(btn, elementKey) {
    const section = btn.closest('.tb-element-section');
    const state = btn.dataset.state;

    // Update tab styles
    section.querySelectorAll('.tb-state-tab').forEach(tab => {
        if (tab.dataset.state === state) {
            tab.classList.add('active');
            tab.style.background = 'var(--tb-accent)';
            tab.style.color = '#fff';
        } else {
            tab.classList.remove('active');
            tab.style.background = 'transparent';
            tab.style.color = 'var(--tb-text)';
        }
    });

    // Show/hide panels
    section.querySelectorAll('.tb-state-panel').forEach(panel => {
        panel.style.display = panel.dataset.state === state ? 'block' : 'none';
    });
};

// ═══════════════════════════════════════════════════════════
// END OF TB-MODULES-DESIGN.JS
// ═══════════════════════════════════════════════════════════
console.log('TB 3.0: tb-modules-design.js loaded');
