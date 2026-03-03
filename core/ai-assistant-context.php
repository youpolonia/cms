<?php
/**
 * AI Assistant — Contextual Help Map v3.0
 * Uses longest-prefix matching — more specific entries take priority.
 * Every admin sub-page has its own dedicated entry.
 */

function getAssistantContext(string $path): array {
    $path = strtok($path, '?');
    $path = rtrim($path, '/') ?: '/admin';

    $map = getContextMap();

    // 1. Exact match
    if (isset($map[$path])) return $map[$path];

    // 2. Longest prefix match — find the most specific entry that is a prefix of $path
    $bestMatch = null;
    $bestLen = 0;
    foreach ($map as $prefix => $ctx) {
        $pLen = strlen($prefix);
        if ($pLen > $bestLen && (
            $path === $prefix ||
            str_starts_with($path, $prefix . '/') ||
            str_starts_with($path, $prefix)
        )) {
            $bestMatch = $ctx;
            $bestLen = $pLen;
        }
    }
    if ($bestMatch) return $bestMatch;

    return getDefaultContext();
}

function getDefaultContext(): array {
    return [
        'title' => 'Admin Panel',
        'icon' => '🏠',
        'intro' => 'Welcome to Jessie AI-CMS admin panel. Use the navigation above to manage your site.',
        'steps' => [
            ['label' => 'Explore the top navigation', 'detail' => '8 main sections: Dashboard, Content, AI & SEO, Commerce, Modules, Marketing, Design, System'],
            ['label' => 'Configure basic settings', 'detail' => 'Go to System → Settings to set your site name, URL, timezone, and email'],
            ['label' => 'Set up AI providers', 'detail' => 'Go to AI & SEO → AI System → AI Settings to add API keys'],
        ],
        'tips' => [
            '💡 Use the top menu to navigate between sections',
            '💡 The AI & SEO menu has all AI-powered tools',
            '💡 Check System → Settings for site configuration',
        ],
    ];
}

function getContextMap(): array {
    return [

        // ═══════════════════════════════════════════
        // DASHBOARD
        // ═══════════════════════════════════════════

        '/admin' => [
            'title' => 'Dashboard',
            'icon' => '📊',
            'intro' => 'Your command center — site stats, recent activity, quick actions, and AI suggestions at a glance.',
            'steps' => [
                ['label' => 'Check the stat cards at the top', 'detail' => 'Shows total pages, articles, users, and today\'s page views — click any card to go to its section'],
                ['label' => 'Review recent activity', 'detail' => 'Latest published content, new comments, and form submissions appear in the activity feed'],
                ['label' => 'Use quick action buttons', 'detail' => 'Click "New Page", "New Article", or "New Product" to jump straight to creation'],
                ['label' => 'Check AI insights widget', 'detail' => 'The dashboard shows AI-powered suggestions for content gaps and SEO improvements'],
            ],
            'tips' => [
                '💡 Bookmark /admin — it\'s your daily starting point',
                '💡 The revenue chart updates in real-time if Shop module is active',
                '💡 Click any stat card number to go to its detailed management page',
            ],
        ],

        // ═══════════════════════════════════════════
        // CONTENT — PAGES
        // ═══════════════════════════════════════════

        '/admin/pages' => [
            'title' => 'Pages',
            'icon' => '📄',
            'intro' => 'Manage all static pages on your site — Home, About, Contact, Services, and any custom pages.',
            'steps' => [
                ['label' => 'Search or filter pages', 'detail' => 'Use the search bar to find pages by title; filter by status (Published / Draft)'],
                ['label' => 'Click a page title to edit', 'detail' => 'Opens the page editor with content, SEO fields, and featured image'],
                ['label' => 'Use the "Builder" button for advanced layouts', 'detail' => 'Switches to the drag-and-drop Page Builder with 79 modules'],
                ['label' => 'Click "+ New Page" to create', 'detail' => 'Takes you to /admin/pages/create with a blank page form'],
                ['label' => 'Bulk-delete drafts', 'detail' => 'Select multiple pages with checkboxes and choose "Delete selected"'],
            ],
            'tips' => [
                '💡 Published pages are live immediately — use Draft for work-in-progress',
                '💡 For landing pages, use the Page Builder (JTB) for rich layouts',
                '💡 Set a featured image on every page for better social sharing previews',
            ],
        ],

        '/admin/pages/create' => [
            'title' => 'New Page',
            'icon' => '📄',
            'intro' => 'Create a new static page — fill in the title, content, SEO fields, and publish.',
            'steps' => [
                ['label' => 'Enter a page title', 'detail' => 'The title appears in the browser tab, search results, and as the H1 heading — be descriptive'],
                ['label' => 'Write or paste your content', 'detail' => 'Use the rich text editor for formatting; or click "Switch to Builder" for drag-and-drop layout'],
                ['label' => 'Set the slug (URL)', 'detail' => 'Auto-generated from title — edit it to be short and keyword-rich, e.g. "about-us"'],
                ['label' => 'Fill in SEO meta title and description', 'detail' => 'Meta title: 50-60 chars | Meta description: 150-160 chars — these appear in Google results'],
                ['label' => 'Upload a featured image, then click Publish', 'detail' => 'Choose Published to go live, or Draft to save without publishing'],
            ],
            'tips' => [
                '💡 Use AI Content Creator to draft page content — then personalize it',
                '💡 The slug cannot be changed easily after publishing (breaks links) — set it right first time',
                '💡 Always fill in meta description — pages without one get auto-generated snippets in Google',
            ],
        ],

        // ═══════════════════════════════════════════
        // CONTENT — ARTICLES
        // ═══════════════════════════════════════════

        '/admin/articles' => [
            'title' => 'Articles',
            'icon' => '📰',
            'intro' => 'Manage your blog posts and news articles — search, filter by category or status, and quick-edit.',
            'steps' => [
                ['label' => 'Filter by category or status', 'detail' => 'Use the dropdowns to show only Published, Draft, or articles in a specific category'],
                ['label' => 'Click an article title to edit', 'detail' => 'Opens the full article editor with content, categories, tags, and SEO fields'],
                ['label' => 'Hover a row for quick actions', 'detail' => 'Edit, Preview, Duplicate, or Delete without opening the full editor'],
                ['label' => 'Click "+ New Article" to create', 'detail' => 'Takes you to /admin/articles/create'],
                ['label' => 'Use bulk actions', 'detail' => 'Select multiple articles to publish, unpublish, or delete in one click'],
            ],
            'tips' => [
                '💡 Aim for 1-2 articles per week for consistent SEO growth',
                '💡 Duplicate an existing article as a template for similar posts',
                '💡 Use Content Calendar to plan upcoming articles',
            ],
        ],

        '/admin/articles/create' => [
            'title' => 'New Article',
            'icon' => '📰',
            'intro' => 'Write a new blog post — enter title, content, categories, tags, and SEO metadata.',
            'steps' => [
                ['label' => 'Enter an article title', 'detail' => 'Make it compelling and keyword-rich — e.g. "10 Tips for Better SEO in 2025"'],
                ['label' => 'Write the content', 'detail' => 'Use the rich text editor — add H2/H3 headings, images, bullet lists, and internal links'],
                ['label' => 'Assign category and tags', 'detail' => 'Select one primary category; add 3-5 relevant tags for related post linking'],
                ['label' => 'Upload a featured image', 'detail' => 'Used in article cards, social sharing, and at the top of the article — min 1200×630px recommended'],
                ['label' => 'Fill SEO fields and set publish date', 'detail' => 'Write a unique meta title + description; set a future date to schedule auto-publishing'],
            ],
            'tips' => [
                '💡 Use AI Content Creator to generate a first draft — saves 80% of writing time',
                '💡 Aim for 800-1500 words for good SEO ranking potential',
                '💡 Add at least 2 internal links to related pages or articles',
            ],
        ],

        // ═══════════════════════════════════════════
        // CONTENT — CATEGORIES
        // ═══════════════════════════════════════════

        '/admin/categories' => [
            'title' => 'Categories',
            'icon' => '📁',
            'intro' => 'Manage content categories — organize articles and pages into hierarchical groups.',
            'steps' => [
                ['label' => 'View all categories in the list', 'detail' => 'See category name, slug, article count, and parent category'],
                ['label' => 'Click a category to edit', 'detail' => 'Change name, slug, description, parent, or featured image'],
                ['label' => 'Click "+ New Category" to create', 'detail' => 'Takes you to /admin/categories/create'],
                ['label' => 'Delete empty categories', 'detail' => 'Categories with 0 articles can be safely removed — they add clutter'],
            ],
            'tips' => [
                '💡 Keep categories broad — 5-10 max. Use tags for specifics',
                '💡 A good category structure improves navigation and SEO',
                '💡 Each category gets an archive page at /category/{slug}',
            ],
        ],

        '/admin/categories/create' => [
            'title' => 'New Category',
            'icon' => '📁',
            'intro' => 'Create a new content category to organize your articles and pages.',
            'steps' => [
                ['label' => 'Enter a category name', 'detail' => 'Use a clear, descriptive name — e.g. "Web Design Tips" or "Industry News"'],
                ['label' => 'Set the slug', 'detail' => 'Auto-generated from name — keep it short and lowercase, e.g. "web-design-tips"'],
                ['label' => 'Select a parent category (optional)', 'detail' => 'Create sub-categories: "Services" → "Web Design", "Branding", "SEO"'],
                ['label' => 'Add a description and image (optional)', 'detail' => 'Description shows on the category archive page; image used in category listings'],
            ],
            'tips' => [
                '💡 Plan your category structure before creating — it\'s hard to reorganize later',
                '💡 Max 2-3 levels deep — deeper hierarchies confuse users',
            ],
        ],

        // ═══════════════════════════════════════════
        // CONTENT — MEDIA
        // ═══════════════════════════════════════════

        '/admin/media' => [
            'title' => 'Media Library',
            'icon' => '🖼️',
            'intro' => 'Browse, search, and manage all uploaded images, documents, and files.',
            'steps' => [
                ['label' => 'Search files by name', 'detail' => 'Type in the search bar to find any file by filename or title'],
                ['label' => 'Filter by file type', 'detail' => 'Use the dropdown to show only Images, Documents, Videos, or Audio'],
                ['label' => 'Click any file to view details', 'detail' => 'See file info, edit the title and alt text, copy the URL, or see where it\'s used'],
                ['label' => 'Drag files directly to upload', 'detail' => 'Drop files anywhere on the page to upload; or click "Upload Files"'],
                ['label' => 'Select multiple files to delete', 'detail' => 'Hold Shift or Ctrl to select multiple, then click Delete'],
            ],
            'tips' => [
                '💡 Always add alt text to images — it\'s required for accessibility and SEO',
                '💡 Use AI Alt Generator to bulk-generate alt text for all images at once',
                '💡 Compress images before upload: TinyPNG or Squoosh.app are free tools',
            ],
        ],

        '/admin/media/upload' => [
            'title' => 'Upload Media',
            'icon' => '📤',
            'intro' => 'Upload new files to your Media Library — images, documents, videos, and more.',
            'steps' => [
                ['label' => 'Drag files onto the upload area', 'detail' => 'Drop multiple files at once — JPG, PNG, GIF, WebP, SVG, PDF, DOC, ZIP supported'],
                ['label' => 'Or click "Browse Files" to select', 'detail' => 'Opens your file picker — hold Ctrl/Cmd to select multiple'],
                ['label' => 'Wait for upload to complete', 'detail' => 'Progress bar shows upload status — do not close the tab during upload'],
                ['label' => 'Add alt text and titles after upload', 'detail' => 'Click each uploaded file to add metadata before using in content'],
            ],
            'tips' => [
                '💡 Max recommended image size: 2MB. Compress larger images before uploading',
                '💡 Use descriptive filenames before uploading — "about-team-photo.jpg" not "IMG_4392.jpg"',
                '💡 WebP format gives 30% smaller files than JPG at same quality',
            ],
        ],

        '/admin/media/stock-search' => [
            'title' => 'Stock Photo Search',
            'icon' => '🔍',
            'intro' => 'Search and import free stock photos from Unsplash and Pexels directly into your Media Library.',
            'steps' => [
                ['label' => 'Enter a search term', 'detail' => 'Type keywords — e.g. "modern office", "coffee shop", "nature landscape"'],
                ['label' => 'Browse results and select a photo', 'detail' => 'Click any image to see a larger preview'],
                ['label' => 'Click "Import to Library"', 'detail' => 'Downloads the image to your Media Library at full resolution'],
                ['label' => 'Add alt text after importing', 'detail' => 'Stock photos need descriptive alt text for SEO'],
            ],
            'tips' => [
                '💡 All Unsplash/Pexels photos are free for commercial use — no attribution required',
                '💡 Use specific search terms for better results — "smiling doctor" vs "doctor"',
                '💡 Imported photos appear instantly in your Media Library',
            ],
        ],

        // ═══════════════════════════════════════════
        // CONTENT — GALLERIES
        // ═══════════════════════════════════════════

        '/admin/galleries' => [
            'title' => 'Galleries',
            'icon' => '🎨',
            'intro' => 'Manage image galleries — view all galleries, edit image sets, and update display settings.',
            'steps' => [
                ['label' => 'Click a gallery to edit', 'detail' => 'See all images in the gallery, drag to reorder, or add/remove images'],
                ['label' => 'Click "+ New Gallery" to create', 'detail' => 'Takes you to /admin/galleries/create'],
                ['label' => 'Copy the shortcode', 'detail' => 'Use [gallery id="X"] shortcode to embed in any page or article content'],
                ['label' => 'Delete a gallery', 'detail' => 'Removes the gallery entry — uploaded images remain in Media Library'],
            ],
            'tips' => [
                '💡 Galleries auto-display as a responsive grid — no coding needed',
                '💡 Lightbox is enabled by default — visitors click to see full-size images',
            ],
        ],

        '/admin/galleries/create' => [
            'title' => 'New Gallery',
            'icon' => '🎨',
            'intro' => 'Create a new image gallery by naming it and selecting images from your Media Library.',
            'steps' => [
                ['label' => 'Enter a gallery name', 'detail' => 'Internal name for reference — e.g. "Portfolio 2025", "Product Photos"'],
                ['label' => 'Click "Add Images"', 'detail' => 'Opens the Media Library picker — select multiple images at once'],
                ['label' => 'Drag images to reorder', 'detail' => 'Arrange images in the order they should display'],
                ['label' => 'Choose layout style', 'detail' => 'Grid, Masonry, Slider, or Justified — select what fits your design'],
                ['label' => 'Save and copy the shortcode', 'detail' => 'Paste [gallery id="X"] into any page or article to display it'],
            ],
            'tips' => [
                '💡 Use consistent aspect ratios for a cleaner gallery look',
                '💡 Add alt text to every image in the Media Library before adding to gallery',
            ],
        ],

        // ═══════════════════════════════════════════
        // CONTENT — COMMENTS
        // ═══════════════════════════════════════════

        '/admin/comments' => [
            'title' => 'Comments',
            'icon' => '💬',
            'intro' => 'Moderate user comments on your articles and pages — approve, reply, or delete.',
            'steps' => [
                ['label' => 'Review "Pending" tab first', 'detail' => 'New comments await approval here — approve genuine ones, reject spam'],
                ['label' => 'Click "Approve" to publish a comment', 'detail' => 'Approved comments become visible to all visitors on your site'],
                ['label' => 'Click "Reply" to respond', 'detail' => 'Your reply appears nested under the comment — great for engagement'],
                ['label' => 'Click "Delete" to remove spam', 'detail' => 'Deleted comments are gone permanently — no recovery'],
                ['label' => 'Use bulk actions for efficiency', 'detail' => 'Select multiple comments and bulk-approve or bulk-delete'],
            ],
            'tips' => [
                '💡 Respond to comments within 24 hours — it builds community and trust',
                '💡 Enable email notifications for new comments in your profile settings',
                '💡 Approved comments contribute to SEO via fresh user-generated content',
            ],
        ],

        // ═══════════════════════════════════════════
        // CONTENT — CONTACT SUBMISSIONS
        // ═══════════════════════════════════════════

        '/admin/contact-submissions' => [
            'title' => 'Contact Submissions',
            'icon' => '📬',
            'intro' => 'View and manage all messages submitted through your contact forms.',
            'steps' => [
                ['label' => 'Filter by form or date', 'detail' => 'Use the dropdowns to see submissions from a specific form or date range'],
                ['label' => 'Click a submission to view full message', 'detail' => 'See the complete message, sender details, and submission timestamp'],
                ['label' => 'Reply via email', 'detail' => 'Click "Reply" to open your email client with the sender pre-filled'],
                ['label' => 'Mark as read or archive', 'detail' => 'Keep your inbox clean by archiving handled submissions'],
                ['label' => 'Export to CSV', 'detail' => 'Click "Export" to download all submissions as a spreadsheet'],
            ],
            'tips' => [
                '💡 Aim to respond within 24 hours — faster response = higher conversion',
                '💡 Export and archive submissions periodically for compliance (GDPR)',
                '💡 Set up email notifications so you\'re alerted immediately on new submission',
            ],
        ],

        // ═══════════════════════════════════════════
        // CONTENT — MENUS
        // ═══════════════════════════════════════════

        '/admin/menus' => [
            'title' => 'Navigation Menus',
            'icon' => '📋',
            'intro' => 'Manage your site\'s navigation menus — header, footer, sidebar — and their link structure.',
            'steps' => [
                ['label' => 'Select a menu from the dropdown', 'detail' => 'Available menus: Header (main nav), Footer, Sidebar, Mobile — each theme may differ'],
                ['label' => 'View and reorder menu items', 'detail' => 'Drag items up/down to reorder; nest items under another to create dropdowns'],
                ['label' => 'Edit an item\'s label', 'detail' => 'Click the item to rename it — useful for custom link text'],
                ['label' => 'Add new items from the left panel', 'detail' => 'Select Pages, Articles, Categories, or Custom URL then click "Add to Menu"'],
                ['label' => 'Click "+ New Menu" to create', 'detail' => 'Takes you to /admin/menus/create for a new menu'],
            ],
            'tips' => [
                '💡 Keep main navigation to 5-7 items max for best usability',
                '💡 Use dropdowns for sub-sections — nest items by dragging slightly to the right',
                '💡 Changes save automatically as you drag — click "Save Menu" to apply',
            ],
        ],

        '/admin/menus/create' => [
            'title' => 'New Menu',
            'icon' => '📋',
            'intro' => 'Create a new navigation menu and assign it to a theme location.',
            'steps' => [
                ['label' => 'Enter a menu name', 'detail' => 'Internal name for reference — e.g. "Footer Links", "Sidebar Categories"'],
                ['label' => 'Assign to a theme location', 'detail' => 'Choose where it appears: Header, Footer Left, Footer Right, Sidebar, etc.'],
                ['label' => 'Add menu items', 'detail' => 'Select pages, articles, categories, or enter custom URLs and labels'],
                ['label' => 'Drag to arrange the order', 'detail' => 'Set the display order by dragging items up/down'],
                ['label' => 'Save the menu', 'detail' => 'Click "Save Menu" — it appears on your site immediately'],
            ],
            'tips' => [
                '💡 Each theme location can only have one active menu',
                '💡 You can create multiple menus and swap them without losing their structure',
            ],
        ],

        // ═══════════════════════════════════════════
        // CONTENT — WIDGETS
        // ═══════════════════════════════════════════

        '/admin/widgets' => [
            'title' => 'Widgets',
            'icon' => '🧩',
            'intro' => 'Manage sidebar and footer widgets — add, remove, and reorder content blocks in widget areas.',
            'steps' => [
                ['label' => 'Select a widget area', 'detail' => 'Choose from Sidebar, Footer Column 1/2/3, or other theme-defined areas'],
                ['label' => 'Click "+ Add Widget"', 'detail' => 'Browse available widgets: Text, Recent Posts, Categories, Search, Image, HTML'],
                ['label' => 'Configure the widget', 'detail' => 'Click the widget to expand its settings — set title and content'],
                ['label' => 'Drag to reorder widgets', 'detail' => 'Widgets display in the order shown — drag to change sequence'],
                ['label' => 'Remove a widget', 'detail' => 'Click the trash icon — the widget is removed from the area'],
            ],
            'tips' => [
                '💡 Keep sidebars focused — 3-5 widgets max for clean layout',
                '💡 A search widget in the sidebar helps visitors find content quickly',
                '💡 Use HTML widget to embed custom code, forms, or social feeds',
            ],
        ],

        '/admin/widgets/create' => [
            'title' => 'New Widget',
            'icon' => '🧩',
            'intro' => 'Create a custom widget that can be placed in any widget area across your site.',
            'steps' => [
                ['label' => 'Choose a widget type', 'detail' => 'Text Block, HTML Code, Recent Posts, Category List, Image, Search Bar'],
                ['label' => 'Give it a title', 'detail' => 'The title displays above the widget in the sidebar or footer'],
                ['label' => 'Add the widget content', 'detail' => 'Depending on type: enter text, HTML code, configure posts count, etc.'],
                ['label' => 'Assign to a widget area', 'detail' => 'Select which area (Sidebar, Footer, etc.) this widget should appear in'],
                ['label' => 'Set display position and save', 'detail' => 'Click "Save Widget" — it appears on the site immediately'],
            ],
            'tips' => [
                '💡 Custom HTML widgets are powerful — embed contact forms, social buttons, or any code',
                '💡 Preview the frontend after saving to check how the widget looks',
            ],
        ],

        // ═══════════════════════════════════════════
        // CONTENT — FORM BUILDER
        // ═══════════════════════════════════════════

        '/admin/form-builder' => [
            'title' => 'Form Builder',
            'icon' => '📋',
            'intro' => 'View and manage all custom forms — contact, registration, surveys, and more.',
            'steps' => [
                ['label' => 'Click a form to edit it', 'detail' => 'Opens the form builder with all current fields — add, remove, or reorder'],
                ['label' => 'View submissions for a form', 'detail' => 'Click the "Submissions" count link to see all responses for that form'],
                ['label' => 'Copy the shortcode', 'detail' => 'Copy [form id="X"] and paste into any page to embed the form'],
                ['label' => 'Click "+ New Form" to create', 'detail' => 'Takes you to /admin/form-builder/create'],
                ['label' => 'Enable/disable a form', 'detail' => 'Toggle active/inactive — inactive forms show an "unavailable" message'],
            ],
            'tips' => [
                '💡 Use AI Forms for quick AI-generated form creation based on your description',
                '💡 Short forms get more completions — 3-5 fields is ideal for contact forms',
                '💡 Always set up email notification so you get alerted on new submissions',
            ],
        ],

        '/admin/form-builder/create' => [
            'title' => 'New Form',
            'icon' => '📋',
            'intro' => 'Build a custom form by adding fields, configuring validation, and setting up notifications.',
            'steps' => [
                ['label' => 'Enter a form name', 'detail' => 'Internal name — e.g. "Contact Form", "Quote Request", "Newsletter Signup"'],
                ['label' => 'Add form fields', 'detail' => 'Click "+ Add Field" — choose from Text, Email, Phone, Textarea, Select, Checkbox, File Upload, Date'],
                ['label' => 'Mark required fields', 'detail' => 'Toggle "Required" on fields that must be filled — validation is automatic'],
                ['label' => 'Configure email notification', 'detail' => 'Set recipient email, subject line, and which fields appear in the notification email'],
                ['label' => 'Set success message and save', 'detail' => 'Write a thank-you message shown after submission, then click "Save Form"'],
            ],
            'tips' => [
                '💡 Add a honeypot field to catch spam bots without CAPTCHA annoyance',
                '💡 Use conditional fields to show/hide fields based on previous answers',
                '💡 Test the form on the frontend after saving to check it works',
            ],
        ],

        // ═══════════════════════════════════════════
        // CONTENT — CONTENT SUGGESTIONS
        // ═══════════════════════════════════════════

        '/admin/content-suggestions' => [
            'title' => 'Content Suggestions',
            'icon' => '💡',
            'intro' => 'AI-powered content ideas based on your site\'s niche, existing content gaps, and trending topics.',
            'steps' => [
                ['label' => 'Browse the suggested topics list', 'detail' => 'AI analyzes your existing articles and suggests topics you haven\'t covered yet'],
                ['label' => 'Click "Generate Ideas" to refresh', 'detail' => 'Fetches fresh suggestions based on current trends in your niche'],
                ['label' => 'Click a suggestion to create content', 'detail' => 'Opens AI Content Creator with the topic pre-filled — just refine and generate'],
                ['label' => 'Mark ideas as "Saved" for later', 'detail' => 'Bookmark good ideas to your content calendar without creating immediately'],
            ],
            'tips' => [
                '💡 Check weekly — fresh suggestions appear as trends change',
                '💡 The more content you publish, the better the AI understands your niche',
                '💡 Combine a suggestion with keyword research for maximum SEO impact',
            ],
        ],

        // ═══════════════════════════════════════════
        // CONTENT — CONTENT CALENDAR
        // ═══════════════════════════════════════════

        '/admin/content-calendar' => [
            'title' => 'Content Calendar',
            'icon' => '📅',
            'intro' => 'Visual monthly calendar showing all scheduled and published content — plan your publishing schedule.',
            'steps' => [
                ['label' => 'Browse the calendar month view', 'detail' => 'See articles and pages plotted on their publish dates — published = solid, scheduled = outlined'],
                ['label' => 'Click a content item to edit', 'detail' => 'Opens the article/page editor directly from the calendar'],
                ['label' => 'Click an empty date to schedule new content', 'detail' => 'Opens the article creator with that date pre-set as the publish date'],
                ['label' => 'Drag items to reschedule', 'detail' => 'Drag a content block to a new date to change its publish date'],
                ['label' => 'Switch to Week or List view', 'detail' => 'Use the view toggle for a closer look at busy weeks'],
            ],
            'tips' => [
                '💡 Aim for consistent cadence — e.g. every Tuesday and Friday',
                '💡 Plan content 2-4 weeks ahead to avoid last-minute rushes',
                '💡 Use Content Suggestions to fill empty slots on the calendar',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI CHAT
        // ═══════════════════════════════════════════

        '/admin/ai-chat' => [
            'title' => 'AI Assistant Chat',
            'icon' => '💬',
            'intro' => 'Chat directly with AI about your CMS — ask questions, request content drafts, or troubleshoot issues.',
            'steps' => [
                ['label' => 'Select an AI model from the dropdown', 'detail' => 'Choose GPT-4, Claude, Gemini, or DeepSeek — different models have different strengths'],
                ['label' => 'Type your question or request', 'detail' => 'Ask about CMS features, request a content draft, or ask for help troubleshooting'],
                ['label' => 'Review the response and copy', 'detail' => 'Click the copy icon to copy the AI response to your clipboard'],
                ['label' => 'Continue the conversation', 'detail' => 'Follow up with additional questions — the AI retains conversation context'],
            ],
            'tips' => [
                '💡 Be specific: "Write a 200-word intro for my About page for a dental clinic" beats "write content"',
                '💡 Ask the AI to explain any CMS feature — it knows the full system',
                '💡 Use for brainstorming, writing, translation, and technical help',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI TUTOR
        // ═══════════════════════════════════════════

        '/admin/ai-tutor' => [
            'title' => 'AI Tutor',
            'icon' => '🎓',
            'intro' => 'Interactive AI tutor that teaches you how to use Jessie CMS through guided lessons and Q&A.',
            'steps' => [
                ['label' => 'Choose a topic to learn', 'detail' => 'Browse lessons: Getting Started, SEO Basics, Shop Setup, AI Tools, Theme Building'],
                ['label' => 'Follow the step-by-step tutorial', 'detail' => 'Each lesson has interactive steps with explanations and screenshots'],
                ['label' => 'Ask the tutor a question', 'detail' => 'Type any question about the CMS — the AI answers in plain language'],
                ['label' => 'Mark lessons as complete', 'detail' => 'Track your learning progress through the course catalog'],
            ],
            'tips' => [
                '💡 Start with "Getting Started" if you\'re new to the CMS',
                '💡 The AI Tutor knows your site — ask specific questions about YOUR setup',
                '💡 Revisit lessons after you\'ve tried the feature yourself for better retention',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI SETTINGS
        // ═══════════════════════════════════════════

        '/admin/ai-settings' => [
            'title' => 'AI Settings',
            'icon' => '⚙️',
            'intro' => 'Configure AI providers and API keys — required before any AI feature will work.',
            'steps' => [
                ['label' => 'Choose a provider tab', 'detail' => 'Tabs: OpenAI, Anthropic, Google Gemini, DeepSeek — each needs its own API key'],
                ['label' => 'Get an API key from the provider', 'detail' => 'OpenAI: platform.openai.com | Anthropic: console.anthropic.com | Google: aistudio.google.com'],
                ['label' => 'Paste the API key and select default model', 'detail' => 'E.g. gpt-4.1-mini for OpenAI, claude-3-5-haiku for Anthropic — choose speed vs quality'],
                ['label' => 'Set a default provider', 'detail' => 'The default is used when no specific model is requested by a tool'],
                ['label' => 'Click "Test Connection" to verify', 'detail' => 'Sends a test request — green = working, red = check the key or account credits'],
            ],
            'tips' => [
                '💡 Start with GPT-4.1-mini or Claude Haiku — fast and affordable for most tasks',
                '💡 Add multiple providers — if one is down, others are used as fallback',
                '💡 DeepSeek V3 is very cheap — great for high-volume tasks like bulk SEO',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI CONTENT CREATOR
        // ═══════════════════════════════════════════

        '/admin/ai-content-creator' => [
            'title' => 'AI Content Creator',
            'icon' => '✨',
            'intro' => 'Generate full-length articles, blog posts, and page content with AI in seconds.',
            'steps' => [
                ['label' => 'Enter a topic or article title', 'detail' => 'Be specific — "10 SEO Tips for Local Businesses in 2025" beats "SEO tips"'],
                ['label' => 'Choose tone and content type', 'detail' => 'Tone: Professional / Casual / Friendly / Formal. Type: Blog Post, How-To, Listicle, Review'],
                ['label' => 'Set the target length', 'detail' => 'Short (300-500w), Medium (800-1200w), Long (1500-2500w) — long articles rank better'],
                ['label' => 'Click "Generate" and review', 'detail' => 'AI creates a full structured article with introduction, headings, and conclusion'],
                ['label' => 'Edit and save to Articles', 'detail' => 'Personalize the content, add your expertise, then click "Save as Article"'],
            ],
            'tips' => [
                '💡 Always add your personal insights — AI drafts are starting points, not finished pieces',
                '💡 Enable "SEO Optimized" mode to auto-include target keywords and proper H2/H3 structure',
                '💡 Generate multiple variations with slightly different prompts and pick the best',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI COPYWRITER
        // ═══════════════════════════════════════════

        '/admin/ai-copywriter' => [
            'title' => 'AI Copywriter',
            'icon' => '✍️',
            'intro' => 'Generate high-converting marketing copy — headlines, product descriptions, social posts, email subjects, and ads.',
            'steps' => [
                ['label' => 'Select a copy template', 'detail' => 'Choose from: Product Description, Ad Headline, Email Subject, Social Post, Landing Page Hero, CTA Button, Meta Description'],
                ['label' => 'Fill in the brief', 'detail' => 'Enter product/service name, key benefits, target audience, and tone (bold, professional, playful)'],
                ['label' => 'Click "Generate" for multiple variations', 'detail' => 'AI produces 3-5 options — choose the strongest or combine elements'],
                ['label' => 'Refine with follow-up prompts', 'detail' => 'Click "Make it shorter", "Add urgency", or "More professional" to iterate'],
                ['label' => 'Copy and use in your content', 'detail' => 'Paste directly into pages, products, ads, or email campaigns'],
            ],
            'tips' => [
                '💡 A/B test 2-3 headline variations to find the best performer',
                '💡 Product descriptions with benefits (not just features) convert better',
                '💡 Use "AIDA" template for emails: Attention → Interest → Desire → Action',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI CONTENT REWRITE
        // ═══════════════════════════════════════════

        '/admin/ai-content-rewrite' => [
            'title' => 'AI Content Rewriter',
            'icon' => '🔄',
            'intro' => 'Improve, rewrite, or repurpose existing content — fix tone, simplify language, or make it more engaging.',
            'steps' => [
                ['label' => 'Paste or load existing content', 'detail' => 'Paste text into the input box, or click "Load from Page" to import from an existing page/article'],
                ['label' => 'Choose a rewrite mode', 'detail' => 'Modes: Improve Flow, Simplify Language, More Engaging, Formal Tone, Casual Tone, Expand, Condense'],
                ['label' => 'Click "Rewrite"', 'detail' => 'AI produces the rewritten version while preserving the core meaning'],
                ['label' => 'Compare original vs rewritten', 'detail' => 'Use the side-by-side diff view to see exactly what changed'],
                ['label' => 'Save changes back to the page', 'detail' => 'Click "Update Page" to save the rewritten content directly to the original source'],
            ],
            'tips' => [
                '💡 Great for refreshing old articles — updated content can recover lost rankings',
                '💡 Use "Expand" mode to turn short product descriptions into full compelling copy',
                '💡 Always review rewritten content — AI may change facts or specific details',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI TRANSLATE
        // ═══════════════════════════════════════════

        '/admin/ai-translate' => [
            'title' => 'AI Translate',
            'icon' => '🌍',
            'intro' => 'Translate pages, articles, and content into 30+ languages using AI for natural-sounding results.',
            'steps' => [
                ['label' => 'Select content to translate', 'detail' => 'Choose a page or article from the dropdown, or paste custom text'],
                ['label' => 'Choose target language', 'detail' => 'Select from 30+ languages: Spanish, French, German, Polish, Arabic, Chinese, Japanese, etc.'],
                ['label' => 'Choose translation quality', 'detail' => 'Standard = faster/cheaper | Premium (GPT-4/Claude) = more natural, idiomatic translations'],
                ['label' => 'Click "Translate" and review', 'detail' => 'Check the output carefully — AI may occasionally mistranslate technical terms'],
                ['label' => 'Save as new page/article', 'detail' => 'Creates a language variant — link it to the original via the Languages panel'],
            ],
            'tips' => [
                '💡 Premium models give much better translations for marketing copy and nuanced content',
                '💡 Have a native speaker review medical, legal, or financial translations',
                '💡 After translating, run through AI SEO to optimize the translated version too',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI IMAGES
        // ═══════════════════════════════════════════

        '/admin/ai-images' => [
            'title' => 'AI Image Generator',
            'icon' => '🖼️',
            'intro' => 'Generate original images from text descriptions using DALL-E or Stable Diffusion.',
            'steps' => [
                ['label' => 'Write a detailed image description', 'detail' => 'Be specific: "A modern dental clinic reception with white walls, plants, and warm lighting, photorealistic"'],
                ['label' => 'Choose image style', 'detail' => 'Photorealistic, Illustration, Watercolor, Cartoon, Oil Painting, Minimalist, etc.'],
                ['label' => 'Select size and aspect ratio', 'detail' => 'Square (1:1) for social, Landscape (16:9) for headers, Portrait (4:5) for Instagram'],
                ['label' => 'Click "Generate" and wait 15-30 seconds', 'detail' => 'AI produces 1-4 image variations — pick the best one'],
                ['label' => 'Save to Media Library', 'detail' => 'Click "Save to Library" — the image is ready to use anywhere in the CMS'],
            ],
            'tips' => [
                '💡 More detail in the prompt = better images. Include style, lighting, mood, subject',
                '💡 Add "photorealistic, high quality, 4K" for professional-looking results',
                '💡 Generated images are royalty-free — safe for commercial use',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI ALT GENERATOR
        // ═══════════════════════════════════════════

        '/admin/ai-alt-generator' => [
            'title' => 'AI Alt Text Generator',
            'icon' => '♿',
            'intro' => 'Automatically generate descriptive alt text for all images in your Media Library in bulk.',
            'steps' => [
                ['label' => 'Click "Scan Images" to find missing alt text', 'detail' => 'Shows all images without alt text — typically hundreds on established sites'],
                ['label' => 'Select images to process', 'detail' => 'Check "Select All" for bulk processing, or select specific images'],
                ['label' => 'Click "Generate Alt Text"', 'detail' => 'AI analyzes each image and writes a descriptive alt text string'],
                ['label' => 'Review and edit generated alt texts', 'detail' => 'AI is usually accurate — check for product names or specific details it might miss'],
                ['label' => 'Click "Save All" to apply', 'detail' => 'Alt texts are saved to all images — takes effect immediately on the site'],
            ],
            'tips' => [
                '💡 Alt text is critical for SEO and accessibility — Google reads it to understand images',
                '💡 Good alt text: "White labrador puppy playing in grass" — descriptive, not keyword-stuffed',
                '💡 Process your entire library once, then add alt text to new uploads individually',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI FORMS
        // ═══════════════════════════════════════════

        '/admin/ai-forms' => [
            'title' => 'AI Form Creator',
            'icon' => '📋',
            'intro' => 'Describe the form you need and AI builds it automatically — fields, labels, validation, and notifications.',
            'steps' => [
                ['label' => 'Describe the form in plain language', 'detail' => 'Example: "A contact form for a law firm with name, email, phone, case type dropdown, and message"'],
                ['label' => 'Click "Generate Form"', 'detail' => 'AI creates the full form with appropriate field types and validation rules'],
                ['label' => 'Review and adjust fields', 'detail' => 'Add, remove, or modify any field the AI created'],
                ['label' => 'Configure notification email', 'detail' => 'Set which email receives submissions and the notification format'],
                ['label' => 'Save and embed', 'detail' => 'Click "Save Form" — use the shortcode to embed on any page'],
            ],
            'tips' => [
                '💡 AI picks the right field types automatically — dropdowns for fixed options, text for free input',
                '💡 Review all required field markings — AI tends to make too many required',
                '💡 Faster than building manually — typical form generation takes under 10 seconds',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI LANDING
        // ═══════════════════════════════════════════

        '/admin/ai-landing' => [
            'title' => 'AI Landing Page Generator',
            'icon' => '🚀',
            'intro' => 'Generate complete, high-converting landing pages with AI — hero, features, testimonials, CTA sections included.',
            'steps' => [
                ['label' => 'Describe your product or service', 'detail' => 'Enter what you\'re promoting, target audience, and main benefit/offer'],
                ['label' => 'Choose a landing page template', 'detail' => 'Lead generation, Product launch, Service page, Event registration, Free trial signup'],
                ['label' => 'Click "Generate Landing Page"', 'detail' => 'AI creates a full multi-section page: Hero, Benefits, Features, Social Proof, CTA, FAQ'],
                ['label' => 'Review and edit each section', 'detail' => 'Click any section to edit text, change images, or adjust the layout'],
                ['label' => 'Publish as a new page', 'detail' => 'Click "Publish" — landing page is live and ready to drive conversions'],
            ],
            'tips' => [
                '💡 A single focused CTA (call to action) converts better than multiple competing ones',
                '💡 Add real testimonials to the generated template for maximum trust',
                '💡 Use AI Images to generate relevant visuals for the landing page',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI EMAIL CAMPAIGN
        // ═══════════════════════════════════════════

        '/admin/ai-email-campaign' => [
            'title' => 'AI Email Campaign Writer',
            'icon' => '📧',
            'intro' => 'Generate complete email marketing campaigns — subject lines, preview text, email body, and CTA.',
            'steps' => [
                ['label' => 'Enter the campaign goal', 'detail' => 'What is the email about? Product launch, sale announcement, newsletter, re-engagement?'],
                ['label' => 'Select email type and tone', 'detail' => 'Type: Promotional, Newsletter, Welcome, Drip, Re-engagement. Tone: Friendly / Professional / Urgent'],
                ['label' => 'Click "Generate Campaign"', 'detail' => 'AI writes subject line, preview text, and full email body with clear sections'],
                ['label' => 'Edit and personalize', 'detail' => 'Add specific offers, adjust pricing, include your brand voice'],
                ['label' => 'Send to Newsletter or Email Queue', 'detail' => 'Click "Use in Campaign" to send directly to your mailing list'],
            ],
            'tips' => [
                '💡 Generate 3 subject line options and A/B test — open rates vary hugely by subject',
                '💡 Keep emails under 300 words for best read-through rates',
                '💡 One clear CTA button per email — more CTAs = lower click rate',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI STUDENT MATERIALS
        // ═══════════════════════════════════════════

        '/admin/ai-student-materials' => [
            'title' => 'AI Student Materials',
            'icon' => '📚',
            'intro' => 'Generate educational content — lesson plans, quizzes, summaries, worksheets, and study guides.',
            'steps' => [
                ['label' => 'Choose material type', 'detail' => 'Lesson Plan, Quiz Questions, Study Guide, Summary, Worksheet, Flashcards, Assignment Brief'],
                ['label' => 'Enter the topic and level', 'detail' => 'Topic: "Photosynthesis" | Level: Beginner / Intermediate / Advanced / University'],
                ['label' => 'Set length and format', 'detail' => 'Number of questions for quizzes; number of pages for guides; time for lesson plans'],
                ['label' => 'Click "Generate" and review', 'detail' => 'AI creates structured educational content following pedagogical best practices'],
                ['label' => 'Save to LMS or export', 'detail' => 'Click "Add to Course" to use in LMS, or export as PDF/Word'],
            ],
            'tips' => [
                '💡 Pair with LMS module — generated quizzes can be imported directly into courses',
                '💡 Specify the age or grade level for age-appropriate content and language',
                '💡 Review all factual claims — AI is very good but occasionally makes errors',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI WORKFLOW GENERATOR
        // ═══════════════════════════════════════════

        '/admin/ai-workflow-generator' => [
            'title' => 'AI Workflow Generator',
            'icon' => '🔗',
            'intro' => 'Describe an automation need and AI generates a complete n8n or CMS automation workflow.',
            'steps' => [
                ['label' => 'Describe your automation in plain English', 'detail' => 'Example: "When a new contact form is submitted, add to CRM, send welcome email, and notify Slack"'],
                ['label' => 'Click "Generate Workflow"', 'detail' => 'AI creates the workflow JSON with all nodes, connections, and configurations'],
                ['label' => 'Review the generated workflow diagram', 'detail' => 'Visual diagram shows trigger → conditions → actions — verify the logic'],
                ['label' => 'Edit or adjust nodes', 'detail' => 'Modify specific node settings — email content, CRM field mappings, etc.'],
                ['label' => 'Deploy to n8n or Automations', 'detail' => 'Click "Send to n8n" or "Save as Automation Rule" to activate the workflow'],
            ],
            'tips' => [
                '💡 Start with simple 3-step workflows — trigger → condition → action',
                '💡 Test with sample data before activating on live data',
                '💡 Document each workflow\'s purpose in the name field for future reference',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI INSIGHTS
        // ═══════════════════════════════════════════

        '/admin/ai-insights' => [
            'title' => 'AI Analytics Insights',
            'icon' => '📈',
            'intro' => 'AI-powered interpretation of your analytics data — patterns, anomalies, and actionable recommendations.',
            'steps' => [
                ['label' => 'Select a date range to analyze', 'detail' => 'Choose last 7 days, 30 days, 90 days, or custom range'],
                ['label' => 'Click "Analyze with AI"', 'detail' => 'AI reviews your traffic, conversions, bounce rates, and content performance'],
                ['label' => 'Read the insights report', 'detail' => 'Highlights: top performing content, traffic sources, unusual patterns, dropped pages'],
                ['label' => 'Review AI recommendations', 'detail' => 'Actionable suggestions: "Update this article", "Fix this page\'s bounce rate", "Boost this product"'],
            ],
            'tips' => [
                '💡 Run monthly for strategic planning — weekly for ongoing optimization',
                '💡 AI spots trends humans miss — especially seasonal patterns in your data',
                '💡 Export the insights report as PDF to share with clients or team',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI LOGS
        // ═══════════════════════════════════════════

        '/admin/ai-logs' => [
            'title' => 'AI Usage Logs',
            'icon' => '📋',
            'intro' => 'View all AI API calls — model used, tokens consumed, cost, and response status.',
            'steps' => [
                ['label' => 'Filter by date range', 'detail' => 'Select today, this week, this month, or a custom date range'],
                ['label' => 'Filter by AI tool or model', 'detail' => 'See logs from specific tools: Content Creator, SEO, Chat, etc.'],
                ['label' => 'View token usage and cost', 'detail' => 'Each log entry shows input/output tokens and estimated API cost'],
                ['label' => 'Check for errors', 'detail' => 'Red entries = failed API calls — click to see the error message and retry if needed'],
            ],
            'tips' => [
                '💡 Monitor monthly spend — set a budget alert in your AI provider\'s dashboard',
                '💡 High token counts = verbose prompts. Tighten prompts to reduce costs',
                '💡 Check logs after AI features stop working — error messages tell you why',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — SEO ASSISTANT
        // ═══════════════════════════════════════════

        '/admin/ai-seo-assistant' => [
            'title' => 'SEO Assistant',
            'icon' => '🎯',
            'intro' => 'Page-by-page SEO scoring (0-100) with specific fix recommendations for each issue found.',
            'steps' => [
                ['label' => 'Select a page or article to analyze', 'detail' => 'Choose from the dropdown or paste a URL — analysis runs in seconds'],
                ['label' => 'Review the SEO score (0-100)', 'detail' => 'Score breakdown: Title (20pts), Meta (20pts), Headings (15pts), Content (25pts), Images (10pts), Links (10pts)'],
                ['label' => 'Work through the issues list', 'detail' => 'Red = critical, Orange = warning, Green = passed — fix critical issues first'],
                ['label' => 'Click "Fix with AI" on any issue', 'detail' => 'AI generates an improved meta title, description, or heading automatically'],
                ['label' => 'Re-run analysis to confirm fixes', 'detail' => 'After saving changes, click "Re-analyze" to verify the score improved'],
            ],
            'tips' => [
                '💡 Target score 75+ for good SEO. 90+ is excellent.',
                '💡 Fix your top 10 traffic pages first — biggest impact on existing rankings',
                '💡 Meta title: 50-60 chars | Meta description: 150-160 chars | H1: exactly one per page',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — SEO DASHBOARD
        // ═══════════════════════════════════════════

        '/admin/ai-seo-dashboard' => [
            'title' => 'SEO Dashboard',
            'icon' => '📊',
            'intro' => 'Overall site SEO health overview — average scores, issue counts, and progress over time.',
            'steps' => [
                ['label' => 'Review the site-wide SEO health score', 'detail' => 'Average score across all pages — shows your overall SEO health at a glance'],
                ['label' => 'Check the issues summary', 'detail' => 'See counts of critical, warning, and info-level issues across all pages'],
                ['label' => 'Browse pages sorted by score', 'detail' => 'Low-scoring pages are listed first — prioritize these for improvement'],
                ['label' => 'Track progress over time', 'detail' => 'The chart shows your site\'s average SEO score over the past 30 days'],
                ['label' => 'Click any page to analyze in detail', 'detail' => 'Goes to SEO Assistant with that page pre-selected'],
            ],
            'tips' => [
                '💡 Run a full site scan monthly to catch new issues as content grows',
                '💡 Even one new page with missing meta tags can drop your average — audit regularly',
                '💡 Share this dashboard with clients as an SEO progress report',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — SEO KEYWORDS
        // ═══════════════════════════════════════════

        '/admin/ai-seo-keywords' => [
            'title' => 'SEO Keywords',
            'icon' => '🔑',
            'intro' => 'Keyword research and rank tracking — find what your audience searches for and monitor your positions.',
            'steps' => [
                ['label' => 'Enter a seed keyword to research', 'detail' => 'Type your main topic — e.g. "web design" — AI returns related keywords with search volume estimates'],
                ['label' => 'Review keyword suggestions', 'detail' => 'See search volume, competition level, and keyword difficulty score for each suggestion'],
                ['label' => 'Add keywords to tracking list', 'detail' => 'Click "Track" on keywords you want to monitor — checks rankings weekly'],
                ['label' => 'View your tracked keyword rankings', 'detail' => 'See current position for each tracked keyword and position changes over time'],
            ],
            'tips' => [
                '💡 Long-tail keywords (3+ words) are easier to rank for and more targeted',
                '💡 Focus on keywords with difficulty under 50 as a new site — less competition',
                '💡 Group related keywords into topic clusters for stronger content strategy',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — SEO COMPETITORS
        // ═══════════════════════════════════════════

        '/admin/ai-seo-competitors' => [
            'title' => 'SEO Competitor Analysis',
            'icon' => '🏆',
            'intro' => 'Analyze your competitors\' SEO strategy — keywords they rank for, their top pages, and content gaps.',
            'steps' => [
                ['label' => 'Enter a competitor domain', 'detail' => 'Type the competitor\'s URL — e.g. "competitor.com" — without https://'],
                ['label' => 'Click "Analyze Competitor"', 'detail' => 'AI fetches their top-ranking pages, estimated traffic, and keyword profile'],
                ['label' => 'Review their top keywords', 'detail' => 'See which keywords drive the most traffic to your competitor'],
                ['label' => 'Find content gap opportunities', 'detail' => 'Click "Content Gaps" — shows keywords they rank for that you don\'t yet cover'],
                ['label' => 'Add gap keywords to your content plan', 'detail' => 'Click "Create Content" on any gap keyword to open the AI Content Creator'],
            ],
            'tips' => [
                '💡 Analyze 3-5 competitors for a complete picture of your market',
                '💡 Focus on gaps where competitor content is weak — easier to outrank',
                '💡 Check competitor pages for content length and format ideas that work in your niche',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — SEO LINKING
        // ═══════════════════════════════════════════

        '/admin/ai-seo-linking' => [
            'title' => 'Internal Link Optimizer',
            'icon' => '🔗',
            'intro' => 'Find internal linking opportunities — connect related pages to improve SEO and user navigation.',
            'steps' => [
                ['label' => 'Run the internal link scan', 'detail' => 'Click "Scan Site" — AI maps all pages and their existing internal links'],
                ['label' => 'Review the orphan pages list', 'detail' => 'Orphan pages = no links pointing to them — these get no SEO juice'],
                ['label' => 'See link suggestions for each page', 'detail' => 'AI suggests which other pages should link to each article based on topic relevance'],
                ['label' => 'Click "Add Link" to implement', 'detail' => 'Inserts a contextual link into the suggested source page automatically'],
                ['label' => 'Review the link density chart', 'detail' => 'Shows which pages are over- or under-linked in your site structure'],
            ],
            'tips' => [
                '💡 Every page should have at least 3 internal links pointing to it',
                '💡 Use descriptive anchor text — "our SEO guide" not "click here"',
                '💡 Link your most important pages most frequently — this signals priority to Google',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — SEO SCHEMA
        // ═══════════════════════════════════════════

        '/admin/ai-seo-schema' => [
            'title' => 'Schema Markup',
            'icon' => '🏗️',
            'intro' => 'Add structured data (JSON-LD) to pages for rich results in Google — stars, FAQs, recipes, events, products.',
            'steps' => [
                ['label' => 'Select a page and schema type', 'detail' => 'Types: Article, LocalBusiness, Product, FAQ, Event, Recipe, Review, Course, Person'],
                ['label' => 'Fill in the schema fields', 'detail' => 'Each type has specific required fields — AI pre-fills from page content where possible'],
                ['label' => 'Click "Generate Schema"', 'detail' => 'Creates valid JSON-LD structured data following Schema.org standards'],
                ['label' => 'Preview with Google\'s Rich Test Tool link', 'detail' => 'Paste the generated schema into Google\'s testing tool to verify it\'s valid'],
                ['label' => 'Click "Apply to Page"', 'detail' => 'Injects the JSON-LD script tag into that page\'s head section'],
            ],
            'tips' => [
                '💡 LocalBusiness schema is essential for any local/physical business — adds map data',
                '💡 FAQ schema creates expandable Q&A snippets directly in Google search results',
                '💡 Product schema enables star ratings and price display in Google Shopping',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — SEO REPORTS
        // ═══════════════════════════════════════════

        '/admin/ai-seo-reports' => [
            'title' => 'SEO Reports',
            'icon' => '📑',
            'intro' => 'Generate professional SEO progress reports — monthly snapshots, score trends, and improvement summaries.',
            'steps' => [
                ['label' => 'Select report period', 'detail' => 'Choose: Last 30 days, Last 90 days, Last 12 months, or custom date range'],
                ['label' => 'Choose what to include', 'detail' => 'Toggle sections: Score Trends, Issue Summary, Top Pages, Keyword Rankings, Fixed Issues'],
                ['label' => 'Click "Generate Report"', 'detail' => 'AI compiles data and writes a narrative summary of progress and recommendations'],
                ['label' => 'Download or share the PDF', 'detail' => 'Export as PDF to share with clients or save for records'],
            ],
            'tips' => [
                '💡 Send monthly SEO reports to clients — they love seeing progress in numbers',
                '💡 Include the "Fixed Issues" section to show the value of your work',
                '💡 Compare quarters to see the cumulative effect of SEO improvements',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — SEO BRIEF
        // ═══════════════════════════════════════════

        '/admin/ai-seo-brief' => [
            'title' => 'SEO Content Brief',
            'icon' => '📝',
            'intro' => 'Generate detailed content briefs based on keyword research — structure, headings, questions to answer, and word count.',
            'steps' => [
                ['label' => 'Enter a target keyword', 'detail' => 'Type the main keyword you want to rank for — e.g. "best running shoes for beginners"'],
                ['label' => 'Click "Generate Brief"', 'detail' => 'AI analyzes top-ranking pages and creates a brief to outrank them'],
                ['label' => 'Review the brief sections', 'detail' => 'See recommended: title, word count, H2/H3 headings, questions to answer, related keywords to include'],
                ['label' => 'Send to AI Content Creator', 'detail' => 'Click "Create Content from Brief" — uses the brief as instructions for content generation'],
            ],
            'tips' => [
                '💡 Briefs are based on what\'s already ranking — follow them closely for best results',
                '💡 Add your unique angle/expertise on top of the brief structure',
                '💡 The "Questions to Answer" section comes from People Also Ask — great for FAQs',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — SEO BULK
        // ═══════════════════════════════════════════

        '/admin/ai-seo-bulk' => [
            'title' => 'Bulk SEO Editor',
            'icon' => '⚡',
            'intro' => 'Edit meta titles and descriptions for all pages in a single spreadsheet-style view — no opening each page individually.',
            'steps' => [
                ['label' => 'Load all pages/articles', 'detail' => 'Click "Load All Content" — displays every page and article with current meta data'],
                ['label' => 'Filter by missing meta data', 'detail' => 'Click "Show Missing Only" to see all pages without meta title or description'],
                ['label' => 'Edit meta title in the row', 'detail' => 'Click the meta title cell and type directly — 50-60 characters target'],
                ['label' => 'Use "AI Fill" for bulk generation', 'detail' => 'Select multiple rows and click "AI Fill" — generates meta data for all selected pages at once'],
                ['label' => 'Click "Save All Changes"', 'detail' => 'Saves all edited fields in one batch — very efficient for large sites'],
            ],
            'tips' => [
                '💡 This is the fastest way to add missing meta data to hundreds of pages',
                '💡 Each meta title must be unique — duplicates can hurt SEO significantly',
                '💡 Export to CSV first as a backup before bulk-editing',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — SEO DECAY
        // ═══════════════════════════════════════════

        '/admin/ai-seo-decay' => [
            'title' => 'Content Decay Detector',
            'icon' => '📉',
            'intro' => 'Find pages that are losing search rankings over time — identify and rescue content before it drops off page 1.',
            'steps' => [
                ['label' => 'Connect search data source', 'detail' => 'Link Google Search Console or enter manual rank data for your tracked keywords'],
                ['label' => 'Click "Analyze Decay"', 'detail' => 'AI identifies pages with declining impressions, clicks, or keyword rankings over the past 90 days'],
                ['label' => 'Review the decay report', 'detail' => 'Pages sorted by severity — biggest drops appear first'],
                ['label' => 'Click a page to see why it\'s declining', 'detail' => 'AI suggests reasons: outdated content, new competitors, lost backlinks, technical issues'],
                ['label' => 'Click "Refresh with AI"', 'detail' => 'Opens AI Content Rewriter with the decaying page pre-loaded — update and republish'],
            ],
            'tips' => [
                '💡 Content decay is normal — pages older than 18 months often need refreshing',
                '💡 Refreshed content with today\'s date + updated stats often recovers rankings quickly',
                '💡 Run this analysis quarterly as a proactive ranking maintenance routine',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — SEO IMAGES
        // ═══════════════════════════════════════════

        '/admin/ai-seo-images' => [
            'title' => 'Image SEO',
            'icon' => '🖼️',
            'intro' => 'Optimize images for search — fix missing alt text, check file sizes, and improve image filenames.',
            'steps' => [
                ['label' => 'Click "Scan Images" for a full audit', 'detail' => 'Checks all images across your site for SEO issues'],
                ['label' => 'Review image issues list', 'detail' => 'Issues: missing alt text, oversized files (>500KB), non-descriptive filenames (IMG_1234.jpg)'],
                ['label' => 'Fix alt text with AI', 'detail' => 'Click "Auto-Generate Alt Text" for images missing descriptions'],
                ['label' => 'Compress oversized images', 'detail' => 'Click "Compress" on large images — AI compresses without visible quality loss'],
                ['label' => 'Rename files with better filenames', 'detail' => 'Rename "DSC_0042.jpg" to "red-leather-sofa-product.jpg" for SEO benefit'],
            ],
            'tips' => [
                '💡 Images over 500KB slow your page — Google penalizes slow pages in rankings',
                '💡 WebP format is 30% smaller than JPG — convert old JPGs where possible',
                '💡 Descriptive filenames DO help SEO — Google indexes image filenames',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — SEO LINKS (BROKEN)
        // ═══════════════════════════════════════════

        '/admin/ai-seo-links' => [
            'title' => 'Broken Link Checker',
            'icon' => '🔗',
            'intro' => 'Scan your entire site for broken internal and external links — 404 errors hurt SEO and user experience.',
            'steps' => [
                ['label' => 'Click "Start Scan"', 'detail' => 'Crawls all pages and checks every link — may take 2-5 minutes for large sites'],
                ['label' => 'Review broken links list', 'detail' => 'Sorted by page — see which page has the broken link and where it points'],
                ['label' => 'Fix or remove broken internal links', 'detail' => 'Click "Edit Page" to open the page and update or remove the broken link'],
                ['label' => 'Handle broken external links', 'detail' => 'Find the new URL for the resource, or remove the link if the resource is gone'],
                ['label' => 'Schedule automatic scans', 'detail' => 'Set weekly auto-scans so broken links are caught before they affect rankings'],
            ],
            'tips' => [
                '💡 Broken links signal poor site maintenance to Google — fix them promptly',
                '💡 If a popular external page is gone, consider creating your own version of that content',
                '💡 Internal broken links are higher priority than external — fix these first',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — CONTENT QUALITY
        // ═══════════════════════════════════════════

        '/admin/content-quality' => [
            'title' => 'Content Quality Checker',
            'icon' => '✅',
            'intro' => 'Analyze content quality across your site — readability, grammar, thin content, and engagement signals.',
            'steps' => [
                ['label' => 'Select content to check', 'detail' => 'Choose a specific page/article or click "Scan All" for site-wide analysis'],
                ['label' => 'Review readability score', 'detail' => 'Flesch-Kincaid score — aim for 60-70 (plain English, readable by most audiences)'],
                ['label' => 'Check for thin content warnings', 'detail' => 'Pages under 300 words are flagged — Google may not rank them well'],
                ['label' => 'Review grammar and style suggestions', 'detail' => 'AI flags passive voice, long sentences, jargon, and unclear writing'],
                ['label' => 'Apply AI improvements', 'detail' => 'Click "Fix with AI" on any issue — opens the Content Rewriter with targeted instructions'],
            ],
            'tips' => [
                '💡 Readability = SEO + User Experience. Hard-to-read content has high bounce rates',
                '💡 Use short sentences and paragraphs — readers scan, not read, on screen',
                '💡 Thin content (< 300 words) hurts rankings — expand or combine related short pages',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — CHAT SETTINGS
        // ═══════════════════════════════════════════

        '/admin/chat-settings' => [
            'title' => 'AI Chatbot Settings',
            'icon' => '🤖',
            'intro' => 'Configure the AI chatbot widget for your frontend — appearance, personality, knowledge base, and behavior.',
            'steps' => [
                ['label' => 'Enable the chatbot widget', 'detail' => 'Toggle "Enable Chatbot" on — the chat bubble appears on your frontend pages'],
                ['label' => 'Set chatbot name and greeting', 'detail' => 'Give it a name (e.g. "Aria") and write the opening greeting message'],
                ['label' => 'Configure the AI personality', 'detail' => 'Write a system prompt defining the bot\'s role, tone, and what it should/shouldn\'t discuss'],
                ['label' => 'Select AI model for the chatbot', 'detail' => 'Choose a fast, affordable model — GPT-4.1-mini or Claude Haiku work well for chat'],
                ['label' => 'Choose widget position and colors', 'detail' => 'Bottom-right or bottom-left; match your brand colors for the chat bubble'],
            ],
            'tips' => [
                '💡 Clear system prompt = more helpful chatbot. Tell it exactly what it\'s there for',
                '💡 Set "only answer questions about [your business]" to keep responses on-topic',
                '💡 View chat session logs at Chat Settings → Sessions to see real conversations',
            ],
        ],

        '/admin/chat-settings/sessions' => [
            'title' => 'Chatbot Sessions',
            'icon' => '💬',
            'intro' => 'View real conversation sessions from your frontend AI chatbot — see what visitors are asking.',
            'steps' => [
                ['label' => 'Browse the sessions list', 'detail' => 'Each session shows start time, message count, and visitor identifier'],
                ['label' => 'Click a session to read the full conversation', 'detail' => 'See the complete message thread between the visitor and your chatbot'],
                ['label' => 'Filter by date or session length', 'detail' => 'Find long sessions (engaged visitors) or recent sessions for quality review'],
                ['label' => 'Note common questions', 'detail' => 'Recurring questions your chatbot struggles with = gaps in your FAQ or content'],
            ],
            'tips' => [
                '💡 Review sessions weekly — they reveal what your visitors really want to know',
                '💡 Poor chatbot answers? Improve the system prompt in Chat Settings',
                '💡 Common visitor questions are excellent content ideas for new articles or FAQ pages',
            ],
        ],

        // ═══════════════════════════════════════════
        // AI & SEO — AI COMPONENTS
        // ═══════════════════════════════════════════

        '/admin/ai-components' => [
            'title' => 'AI Components',
            'icon' => '🧩',
            'intro' => 'Reusable AI-generated UI components — headers, footers, hero sections, cards — ready to use in the Page Builder.',
            'steps' => [
                ['label' => 'Browse the component library', 'detail' => 'Filter by type: Hero Sections, Feature Grids, Testimonials, CTA Banners, Pricing Tables'],
                ['label' => 'Preview a component', 'detail' => 'Click any component to see a live preview with sample content'],
                ['label' => 'Click "Generate New Component"', 'detail' => 'Describe what you need and AI creates a new custom component for your library'],
                ['label' => 'Insert into Page Builder', 'detail' => 'Click "Use in Builder" — component appears as a draggable module in JTB'],
                ['label' => 'Edit the component content', 'detail' => 'All text, images, and styles are editable after insertion'],
            ],
            'tips' => [
                '💡 Save time by generating once and reusing across multiple pages',
                '💡 Components are saved per-site — build your own design system over time',
                '💡 Describe components in detail: "A pricing table with 3 tiers, feature list, and highlighted middle column"',
            ],
        ],

        // ═══════════════════════════════════════════
        // COMMERCE — SHOP DASHBOARD
        // ═══════════════════════════════════════════

        '/admin/shop' => [
            'title' => 'Shop Dashboard',
            'icon' => '🛒',
            'intro' => 'E-commerce overview — today\'s sales, recent orders, revenue chart, and quick stats.',
            'steps' => [
                ['label' => 'Review today\'s stats at the top', 'detail' => 'Cards show: Revenue today, Orders today, Items sold, Conversion rate'],
                ['label' => 'Check the revenue chart', 'detail' => 'Switch between daily / weekly / monthly view to spot trends'],
                ['label' => 'Review recent orders', 'detail' => 'Latest orders listed with customer name, amount, and status — click to view details'],
                ['label' => 'Check low stock alerts', 'detail' => 'Products with low inventory appear in the alert section — restock before running out'],
            ],
            'tips' => [
                '💡 Check the dashboard daily to catch issues early — pending orders, stock alerts',
                '💡 Conversion rate below 2%? Check your checkout flow for friction points',
                '💡 Top Products widget shows your bestsellers — stock these first',
            ],
        ],

        '/admin/shop/products' => [
            'title' => 'Products',
            'icon' => '📦',
            'intro' => 'Browse and manage all shop products — search, filter, edit prices, and manage stock levels.',
            'steps' => [
                ['label' => 'Search products by name or SKU', 'detail' => 'Type in the search bar to find a specific product quickly'],
                ['label' => 'Filter by category or status', 'detail' => 'Use dropdowns to show only Active, Draft, or Out of Stock products'],
                ['label' => 'Click a product to edit', 'detail' => 'Edit all product details: title, description, price, images, categories, stock'],
                ['label' => 'Click "+ New Product" to create', 'detail' => 'Takes you to /admin/shop/products/create'],
                ['label' => 'Update stock quantities inline', 'detail' => 'Click the stock number directly in the list to edit without opening full editor'],
            ],
            'tips' => [
                '💡 Sort by "Stock" to quickly see which products need restocking',
                '💡 Duplicate a product to create similar variants quickly',
                '💡 Use AI Shop SEO to bulk-optimize product meta descriptions',
            ],
        ],

        '/admin/shop/products/create' => [
            'title' => 'New Product',
            'icon' => '📦',
            'intro' => 'Create a new product — fill in details, add images, set pricing, configure variants, and manage stock.',
            'steps' => [
                ['label' => 'Enter product title and description', 'detail' => 'Title: clear and specific. Description: benefits-focused, 100-300 words ideal for SEO'],
                ['label' => 'Upload product images', 'detail' => 'Add multiple images — first image is the main; drag to reorder. Min 800×800px recommended'],
                ['label' => 'Set regular price and sale price', 'detail' => 'Regular price is the full price; sale price shows a crossed-out original with discount badge'],
                ['label' => 'Configure variants (if applicable)', 'detail' => 'Add variant options: Size, Color, Material — each variant can have its own price and stock'],
                ['label' => 'Set stock quantity and SKU, then save', 'detail' => 'Enter current stock; set SKU (stock keeping unit) for inventory tracking; click "Publish"'],
            ],
            'tips' => [
                '💡 High-quality images from multiple angles increase sales by 30-40%',
                '💡 Include size guides, materials, and care instructions in the description',
                '💡 Use AI Copywriter to generate compelling product descriptions',
            ],
        ],

        '/admin/shop/categories' => [
            'title' => 'Product Categories',
            'icon' => '📁',
            'intro' => 'Organize shop products into categories to help customers browse and find what they need.',
            'steps' => [
                ['label' => 'View existing categories', 'detail' => 'See category name, product count, and slug for each category'],
                ['label' => 'Click a category to edit', 'detail' => 'Edit name, slug, description, featured image, and parent category'],
                ['label' => 'Click "+ New Category" to create', 'detail' => 'Add name, slug, optional parent for sub-categories, and a category image'],
                ['label' => 'Set display order', 'detail' => 'Drag categories to reorder how they appear in the shop navigation'],
            ],
            'tips' => [
                '💡 5-10 main categories is ideal — too many confuses shoppers',
                '💡 Category pages appear at /shop/category/{slug} — add good descriptions for SEO',
                '💡 Feature a category image — categories with images get more clicks',
            ],
        ],

        '/admin/shop/orders' => [
            'title' => 'Orders',
            'icon' => '📦',
            'intro' => 'View and manage all customer orders — filter by status, process payments, and update fulfillment.',
            'steps' => [
                ['label' => 'Filter by order status', 'detail' => 'Status filters: Pending, Processing, Shipped, Delivered, Cancelled, Refunded'],
                ['label' => 'Click an order to view details', 'detail' => 'See items ordered, customer info, shipping address, payment method, and timeline'],
                ['label' => 'Update order status', 'detail' => 'Click "Update Status" → select new status → optionally add a note to customer'],
                ['label' => 'Add tracking number', 'detail' => 'Enter the courier tracking number — customers receive an email with the tracking link'],
                ['label' => 'Process a refund', 'detail' => 'Click "Refund" to issue full or partial refund — automatically processes via payment gateway'],
            ],
            'tips' => [
                '💡 Process new orders within 24 hours to keep customers happy',
                '💡 Add tracking numbers — they reduce "where is my order?" support queries by 70%',
                '💡 Export orders monthly for accounting and tax records',
            ],
        ],

        '/admin/shop/coupons' => [
            'title' => 'Coupons',
            'icon' => '🎟️',
            'intro' => 'Manage discount coupons — view active/expired codes, track usage, and create new promotions.',
            'steps' => [
                ['label' => 'Review active coupons', 'detail' => 'See coupon code, discount type, amount, usage count, and expiry date'],
                ['label' => 'Check coupon usage statistics', 'detail' => 'Click a coupon to see how many times it\'s been used and total discount given'],
                ['label' => 'Deactivate expired promotions', 'detail' => 'Toggle inactive any coupons that should no longer work'],
                ['label' => 'Click "+ New Coupon" to create', 'detail' => 'Takes you to /admin/shop/coupons/create'],
            ],
            'tips' => [
                '💡 Limited-time coupons create urgency — set expiry dates for promotions',
                '💡 Track coupon source in the code: FACEBOOK10, EMAIL20, INFLUENCER15',
                '💡 Free shipping coupons often outperform percentage discounts',
            ],
        ],

        '/admin/shop/coupons/create' => [
            'title' => 'New Coupon',
            'icon' => '🎟️',
            'intro' => 'Create a new discount coupon — choose discount type, amount, usage limits, and expiry.',
            'steps' => [
                ['label' => 'Enter a coupon code', 'detail' => 'Memorable, uppercase: SUMMER25, WELCOME10, FREESHIP — or click "Generate" for random code'],
                ['label' => 'Choose discount type', 'detail' => 'Percentage off (e.g. 20%), Fixed amount off (e.g. €10 off), or Free Shipping'],
                ['label' => 'Set the discount value', 'detail' => 'For percentage: enter 10-50. For fixed: enter the euro/pound amount'],
                ['label' => 'Configure limits', 'detail' => 'Min order value (e.g. €50 minimum), max usage count, and one-per-customer restriction'],
                ['label' => 'Set expiry date and save', 'detail' => 'Choose when the coupon expires — or leave blank for no expiry. Click "Save Coupon"'],
            ],
            'tips' => [
                '💡 Welcome coupon (WELCOME10) converts new email subscribers into first-time buyers',
                '💡 Set usage limits to prevent coupon abuse — e.g. max 100 uses per code',
                '💡 Minimum order value coupons increase average order value',
            ],
        ],

        '/admin/shop/reviews' => [
            'title' => 'Product Reviews',
            'icon' => '⭐',
            'intro' => 'Moderate customer product reviews — approve genuine reviews and remove spam or inappropriate content.',
            'steps' => [
                ['label' => 'Filter by status: Pending, Approved, Rejected', 'detail' => 'New reviews await approval in the Pending tab — check regularly'],
                ['label' => 'Read the review and check legitimacy', 'detail' => 'Look for specific product mentions, verified purchase badge, and sensible content'],
                ['label' => 'Click "Approve" to publish', 'detail' => 'Approved reviews appear on the product page and contribute to the star rating'],
                ['label' => 'Click "Reject" to hide spam', 'detail' => 'Reject and optionally mark the email as a spammer to block future reviews'],
                ['label' => 'Reply to reviews', 'detail' => 'Click "Reply" to respond publicly — great for both positive and negative reviews'],
            ],
            'tips' => [
                '💡 Respond to negative reviews professionally — potential buyers read your response',
                '💡 Send post-purchase emails asking for reviews — timing is 7-14 days after delivery',
                '💡 Star ratings shown in Google Shopping require at least 3 approved reviews',
            ],
        ],

        '/admin/shop/abandoned-carts' => [
            'title' => 'Abandoned Carts',
            'icon' => '🛒',
            'intro' => 'View abandoned shopping carts and recover lost sales by sending reminder emails to customers.',
            'steps' => [
                ['label' => 'Review the abandoned carts list', 'detail' => 'See customer email, cart value, products left behind, and time since abandonment'],
                ['label' => 'Filter by cart value', 'detail' => 'Prioritize high-value carts first — they\'re worth the most to recover'],
                ['label' => 'Send a recovery email manually', 'detail' => 'Click "Send Reminder" on any cart to send a personalized recovery email with a direct checkout link'],
                ['label' => 'Configure automatic reminder sequence', 'detail' => 'Set up 3-email sequence: 1h, 24h, 72h after abandonment for best recovery rates'],
                ['label' => 'Include a discount in the 3rd email', 'detail' => 'A 10% coupon in the final reminder converts the most reluctant abandoners'],
            ],
            'tips' => [
                '💡 Industry average cart abandonment is 70% — recovering 10-20% is a big revenue boost',
                '💡 First reminder within 1 hour recovers the most carts — timing matters',
                '💡 Subject line: "You left something behind 🛒" outperforms generic "Complete your order"',
            ],
        ],

        '/admin/shop/analytics' => [
            'title' => 'Shop Analytics',
            'icon' => '📊',
            'intro' => 'E-commerce analytics — revenue, average order value, conversion rate, and top products.',
            'steps' => [
                ['label' => 'Select a date range', 'detail' => 'Compare this week vs last week, or this month vs last month to spot trends'],
                ['label' => 'Review key metrics', 'detail' => 'Revenue, Orders, Average Order Value (AOV), Conversion Rate, Return Rate'],
                ['label' => 'Check top products by revenue', 'detail' => 'See which products generate the most money — prioritize these in stock and marketing'],
                ['label' => 'Review sales by traffic source', 'detail' => 'Organic search, social media, direct, email — see which channel drives sales'],
                ['label' => 'Export for reporting', 'detail' => 'Download revenue data as CSV for accounting or client reports'],
            ],
            'tips' => [
                '💡 AOV below €30? Use order-level free shipping thresholds to encourage larger baskets',
                '💡 Conversion rate under 1%? Audit your product pages and checkout for friction',
                '💡 Your top 20% of products likely generate 80% of revenue — focus on these',
            ],
        ],

        '/admin/shop/seo' => [
            'title' => 'Shop SEO',
            'icon' => '🎯',
            'intro' => 'AI-powered SEO for your products — bulk optimize titles, descriptions, and meta tags for all products.',
            'steps' => [
                ['label' => 'Click "Scan Products" for SEO audit', 'detail' => 'Checks all products for missing meta titles, descriptions, alt text, and thin content'],
                ['label' => 'Filter products by SEO score', 'detail' => 'Sort ascending to see worst-performing products first'],
                ['label' => 'Click "AI Optimize" on a product', 'detail' => 'AI rewrites the meta title and description with keywords and compelling copy'],
                ['label' => 'Use "Bulk AI Optimize"', 'detail' => 'Select all products and run AI optimization on all at once — takes 2-5 minutes'],
                ['label' => 'Review and save changes', 'detail' => 'Preview each optimized product before committing — edit any AI suggestions you\'re not happy with'],
            ],
            'tips' => [
                '💡 Product meta titles should include: brand + product name + key feature',
                '💡 Good product descriptions include materials, dimensions, and use-cases for SEO',
                '💡 After optimizing, submit your sitemap to Google Search Console to get re-indexed',
            ],
        ],

        '/admin/shop/settings' => [
            'title' => 'Shop Settings',
            'icon' => '⚙️',
            'intro' => 'Configure your shop — currency, tax rates, shipping zones, payment methods, and checkout options.',
            'steps' => [
                ['label' => 'Set currency and number format', 'detail' => 'Choose currency (GBP, EUR, USD, etc.) and decimal separator — this affects all product prices'],
                ['label' => 'Configure tax settings', 'detail' => 'Set tax rates by country/region, or use flat-rate tax. Toggle "Prices include tax" for VAT-inclusive pricing'],
                ['label' => 'Set up shipping zones and rates', 'detail' => 'Create zones by country/region, then set rates: flat rate, free threshold, or weight-based'],
                ['label' => 'Enable payment methods', 'detail' => 'Connect Stripe (cards), PayPal, bank transfer, or cash on delivery — add API keys for each'],
                ['label' => 'Configure checkout options', 'detail' => 'Guest checkout, account creation requirement, order confirmation email settings'],
            ],
            'tips' => [
                '💡 Enable guest checkout — forcing account creation reduces conversions significantly',
                '💡 Free shipping above a threshold (e.g. €50) increases average order value',
                '💡 Test the full checkout flow after any settings change',
            ],
        ],

        // ═══════════════════════════════════════════
        // COMMERCE — RESTAURANT
        // ═══════════════════════════════════════════

        '/admin/restaurant' => [
            'title' => 'Restaurant Dashboard',
            'icon' => '🍕',
            'intro' => 'Restaurant overview — today\'s orders, popular dishes, revenue, and operational status.',
            'steps' => [
                ['label' => 'Check today\'s order summary', 'detail' => 'Cards show: total orders today, pending orders, revenue, and avg. order value'],
                ['label' => 'Review the active orders', 'detail' => 'See orders awaiting preparation — click any to view items and customer details'],
                ['label' => 'Check kitchen status', 'detail' => 'Click "Kitchen Display" to open the live order queue for kitchen staff'],
                ['label' => 'Review most ordered dishes', 'detail' => 'Popular items widget shows top dishes — consider featuring these prominently on the menu'],
            ],
            'tips' => [
                '💡 Bookmark /admin/restaurant/kitchen for the kitchen display on a tablet',
                '💡 Check settings to ensure opening hours are correct — orders outside hours are blocked',
                '💡 High-quality dish photos significantly increase online orders',
            ],
        ],

        '/admin/restaurant/menu' => [
            'title' => 'Menu Items',
            'icon' => '🍽️',
            'intro' => 'View and manage all menu items — search, filter by category, edit prices and availability.',
            'steps' => [
                ['label' => 'Filter by category', 'detail' => 'Filter by Starters, Mains, Desserts, Drinks to manage specific sections'],
                ['label' => 'Toggle item availability', 'detail' => 'Click the toggle to mark items as unavailable — useful for sold-out dishes'],
                ['label' => 'Click an item to edit', 'detail' => 'Edit name, price, description, image, category, and allergen information'],
                ['label' => 'Click "+ New Item" to create', 'detail' => 'Takes you to /admin/restaurant/menu/create'],
                ['label' => 'Reorder items within categories', 'detail' => 'Drag items to change their display order in the online menu'],
            ],
            'tips' => [
                '💡 Mark seasonal or daily-special items as featured to highlight them',
                '💡 Update prices directly in the list for quick changes without opening full editor',
                '💡 Unavailable items show greyed-out on the menu — better than hiding them completely',
            ],
        ],

        '/admin/restaurant/menu/create' => [
            'title' => 'New Menu Item',
            'icon' => '🍽️',
            'intro' => 'Add a new dish or drink to your menu — fill in name, price, description, photo, and allergens.',
            'steps' => [
                ['label' => 'Enter item name and description', 'detail' => 'Name: clear and appetizing. Description: ingredients and cooking method — 50-100 words ideal'],
                ['label' => 'Set the price', 'detail' => 'Enter the selling price in your configured currency — e.g. 12.50'],
                ['label' => 'Upload a food photo', 'detail' => 'High-quality overhead or angle shot — 800×600px minimum. This drives orders!'],
                ['label' => 'Select category', 'detail' => 'Assign to Starters, Mains, Desserts, Drinks, or a custom category'],
                ['label' => 'Mark allergens and save', 'detail' => 'Check all applicable allergens: Gluten, Dairy, Nuts, Eggs, Shellfish, etc. — legal requirement in EU'],
            ],
            'tips' => [
                '💡 Dishes with photos get 65% more orders than text-only items',
                '💡 Allergen information is a legal requirement in the EU — never skip this',
                '💡 Use AI Copywriter to write mouth-watering descriptions from ingredient lists',
            ],
        ],

        '/admin/restaurant/categories' => [
            'title' => 'Menu Categories',
            'icon' => '📁',
            'intro' => 'Manage menu sections — Starters, Mains, Desserts, Drinks, and any custom categories.',
            'steps' => [
                ['label' => 'View existing menu categories', 'detail' => 'See each category with its item count and current display order'],
                ['label' => 'Click a category to edit', 'detail' => 'Change the name, description, or icon for the category'],
                ['label' => 'Drag to reorder categories', 'detail' => 'The order here determines the order in the online menu — put most popular first'],
                ['label' => 'Click "+ New Category" to add', 'detail' => 'Add custom sections like "Chef\'s Specials", "Vegan Options", "Kids Menu"'],
            ],
            'tips' => [
                '💡 Keep to 4-8 categories — too many sections overwhelm diners',
                '💡 Put high-margin items in their own category to make them easier to find',
            ],
        ],

        '/admin/restaurant/orders' => [
            'title' => 'Online Orders',
            'icon' => '📋',
            'intro' => 'View and manage online orders — filter by status, update progress, and contact customers.',
            'steps' => [
                ['label' => 'Filter by status: New, Preparing, Ready, Delivered', 'detail' => 'Focus on "New" orders first — accept them quickly to avoid cancellations'],
                ['label' => 'Click an order to view full details', 'detail' => 'See ordered items, quantities, special instructions, customer info, and payment status'],
                ['label' => 'Update order status', 'detail' => 'Move from New → Preparing → Ready → Delivered as the order progresses'],
                ['label' => 'Add estimated time', 'detail' => 'Enter preparation/delivery time — customer receives an SMS/email update'],
                ['label' => 'Issue a refund if needed', 'detail' => 'Click "Refund" for cancelled or incorrect orders — processed via payment gateway'],
            ],
            'tips' => [
                '💡 Accept/reject new orders within 5 minutes — long waits frustrate customers',
                '💡 Use Kitchen Display for real-time order management during service',
                '💡 Add the order page to a tablet for easy staff access during service',
            ],
        ],

        '/admin/restaurant/kitchen' => [
            'title' => 'Kitchen Display',
            'icon' => '👨‍🍳',
            'intro' => 'Real-time kitchen display showing the live order queue — designed for kitchen staff on a tablet or screen.',
            'steps' => [
                ['label' => 'Mount a tablet or screen in the kitchen', 'detail' => 'Open this URL on a tablet — it auto-refreshes every 30 seconds'],
                ['label' => 'Review incoming orders in real-time', 'detail' => 'New orders appear automatically with a sound alert — items listed clearly'],
                ['label' => 'Tap an order to start preparing', 'detail' => 'Status changes to "Preparing" — customer sees the update in their order tracker'],
                ['label' => 'Tap "Ready" when the order is complete', 'detail' => 'Triggers notification to front of house or delivery driver'],
            ],
            'tips' => [
                '💡 Use full-screen mode on the tablet (F11 or browser full-screen) for best view',
                '💡 Orders are color-coded by age: green = new, yellow = 10min+, red = overdue',
                '💡 This display replaces paper tickets — more efficient and eco-friendly',
            ],
        ],

        '/admin/restaurant/settings' => [
            'title' => 'Restaurant Settings',
            'icon' => '⚙️',
            'intro' => 'Configure restaurant operations — opening hours, delivery zones, minimum order, and payment options.',
            'steps' => [
                ['label' => 'Set opening hours for each day', 'detail' => 'Configure open/close times per day — orders outside hours are blocked automatically'],
                ['label' => 'Configure delivery zones', 'detail' => 'Draw delivery areas on the map and set delivery fee per zone'],
                ['label' => 'Set minimum order amount', 'detail' => 'Minimum order prevents unprofitable small orders — e.g. £15 minimum'],
                ['label' => 'Enable payment methods', 'detail' => 'Online card payment (Stripe), Cash on delivery — enable what you accept'],
                ['label' => 'Set estimated preparation time', 'detail' => 'Default time shown to customers — e.g. "Ready in 25-35 minutes"'],
            ],
            'tips' => [
                '💡 Update opening hours for holidays in advance to avoid unexpected orders',
                '💡 Limit delivery radius to areas you can serve in under 30 minutes',
                '💡 Stripe online payment reduces cash-handling and no-shows for deliveries',
            ],
        ],

        // ═══════════════════════════════════════════
        // COMMERCE — DROPSHIPPING
        // ═══════════════════════════════════════════

        '/admin/dropshipping' => [
            'title' => 'Dropshipping Dashboard',
            'icon' => '🚚',
            'intro' => 'Dropshipping overview — imported products, pending orders, supplier sync status, and profit margins.',
            'steps' => [
                ['label' => 'Check supplier sync status', 'detail' => 'See when each supplier\'s feed was last synced — prices and stock update from suppliers'],
                ['label' => 'Review pending supplier orders', 'detail' => 'Customer orders that need to be placed with suppliers appear here'],
                ['label' => 'Check margin overview', 'detail' => 'See average markup and total profit across all dropshipping products'],
                ['label' => 'Use AI Research to find opportunities', 'detail' => 'Click "AI Research" to analyze trending niches and products'],
            ],
            'tips' => [
                '💡 Place supplier orders within 24 hours of customer purchase to avoid delays',
                '💡 Sync supplier feeds daily to keep prices and stock accurate',
                '💡 Focus on 1-2 niches rather than selling everything — easier to market',
            ],
        ],

        '/admin/dropshipping/suppliers' => [
            'title' => 'Suppliers',
            'icon' => '🏭',
            'intro' => 'Manage your dropshipping supplier connections — view feed status, product counts, and sync settings.',
            'steps' => [
                ['label' => 'View connected suppliers', 'detail' => 'See supplier name, product count, last sync time, and connection status'],
                ['label' => 'Click a supplier to edit settings', 'detail' => 'Update feed URL, sync frequency, markup rules, and contact details'],
                ['label' => 'Trigger a manual sync', 'detail' => 'Click "Sync Now" to pull the latest products, prices, and stock from the supplier'],
                ['label' => 'Click "+ New Supplier" to add', 'detail' => 'Takes you to /admin/dropshipping/suppliers/create'],
                ['label' => 'Deactivate a supplier', 'detail' => 'Toggle inactive to pause imports without deleting the supplier configuration'],
            ],
            'tips' => [
                '💡 Set up daily automatic syncs to keep prices and stock current',
                '💡 Verify supplier terms before adding — check shipping times and return policies',
                '💡 Test order one product from each supplier before importing their full catalog',
            ],
        ],

        '/admin/dropshipping/suppliers/create' => [
            'title' => 'New Supplier',
            'icon' => '🏭',
            'intro' => 'Connect a new dropshipping supplier by entering their feed URL and configuration settings.',
            'steps' => [
                ['label' => 'Enter supplier name and contact details', 'detail' => 'Name, email, website URL, and phone — for your reference'],
                ['label' => 'Enter the product feed URL', 'detail' => 'XML, CSV, or JSON feed URL provided by the supplier — contains all products and prices'],
                ['label' => 'Configure field mapping', 'detail' => 'Map supplier fields to CMS fields: their "product_title" → your "title", etc.'],
                ['label' => 'Set sync frequency', 'detail' => 'How often to pull updates: Hourly, Daily, or Weekly'],
                ['label' => 'Set default markup rule', 'detail' => 'Apply a price rule immediately: e.g. supplier price × 2.5 or supplier + €15'],
            ],
            'tips' => [
                '💡 Test with a small product import first before syncing the full catalog',
                '💡 Keep the supplier contact info updated — you\'ll need it when orders need chasing',
            ],
        ],

        '/admin/dropshipping/products' => [
            'title' => 'Imported Products',
            'icon' => '📦',
            'intro' => 'View all products imported from dropshipping suppliers — manage prices, visibility, and sync status.',
            'steps' => [
                ['label' => 'Filter by supplier', 'detail' => 'Use the supplier dropdown to see only products from a specific source'],
                ['label' => 'Search by product name', 'detail' => 'Find specific products to edit their selling price or description'],
                ['label' => 'Edit selling price per product', 'detail' => 'Override the default price rule for individual products where margin should be higher'],
                ['label' => 'Toggle product visibility', 'detail' => 'Hide products you don\'t want to sell without deleting the import record'],
                ['label' => 'Sync individual products', 'detail' => 'Click "Sync" on a product to pull the latest supplier price and stock'],
            ],
            'tips' => [
                '💡 Always customize the product description — supplier text is generic and duplicate',
                '💡 Out-of-stock products are auto-hidden if you enable that setting',
                '💡 Sort by "Margin" to focus energy on your most profitable products',
            ],
        ],

        '/admin/dropshipping/import' => [
            'title' => 'Import Products',
            'icon' => '📥',
            'intro' => 'Browse supplier catalogs and selectively import products to your shop.',
            'steps' => [
                ['label' => 'Select a supplier catalog', 'detail' => 'Choose which supplier\'s catalog to browse from the dropdown'],
                ['label' => 'Browse and filter products', 'detail' => 'Filter by category, price range, or search by product name'],
                ['label' => 'Preview product details', 'detail' => 'Click any product to see full details, images, and supplier cost'],
                ['label' => 'Select products to import', 'detail' => 'Check the box on each product you want to sell — or "Select All in Category"'],
                ['label' => 'Click "Import Selected"', 'detail' => 'Imports products to your shop with the configured price rule applied'],
            ],
            'tips' => [
                '💡 Start with 20-50 products in a focused niche — easier to market than a huge catalog',
                '💡 Filter by high margin + low competition before importing',
                '💡 Customize descriptions and images after importing for better SEO',
            ],
        ],

        '/admin/dropshipping/price-rules' => [
            'title' => 'Price Rules',
            'icon' => '💰',
            'intro' => 'Configure automatic markup rules — set selling prices based on supplier cost automatically.',
            'steps' => [
                ['label' => 'View existing price rules', 'detail' => 'Rules are applied in order — more specific rules override general ones'],
                ['label' => 'Create a default markup rule', 'detail' => 'Click "+ New Rule" → set to "All Products" → choose markup: e.g. cost × 2.5'],
                ['label' => 'Create category-specific rules', 'detail' => 'Higher-margin categories (e.g. accessories) can have higher markup than main products'],
                ['label' => 'Set minimum margin protection', 'detail' => 'Add a rule ensuring you never sell below a minimum profit — e.g. cost + €10 minimum'],
                ['label' => 'Test rules on sample products', 'detail' => 'Enter a supplier cost and preview the selling price before applying to all products'],
            ],
            'tips' => [
                '💡 2.5-3x markup is standard for physical dropshipping — covers ads, fees, and profit',
                '💡 Check competitor prices before setting markup — your price must be competitive',
                '💡 Rules auto-apply when supplier prices update — your margin stays protected',
            ],
        ],

        '/admin/dropshipping/orders' => [
            'title' => 'Supplier Orders',
            'icon' => '📋',
            'intro' => 'Track orders placed with suppliers for customer purchases — view status and shipping information.',
            'steps' => [
                ['label' => 'View pending supplier orders', 'detail' => 'Customer orders that need to be forwarded to the supplier are shown here'],
                ['label' => 'Place the order with supplier', 'detail' => 'Click "Place Order" — for automated suppliers, this sends the order automatically'],
                ['label' => 'Enter tracking number when received', 'detail' => 'When supplier provides tracking, enter it here — auto-forwards to the customer'],
                ['label' => 'Track order status', 'detail' => 'Status flows: Pending → Ordered → Shipped → Delivered'],
            ],
            'tips' => [
                '💡 Place supplier orders within hours of customer purchase — faster delivery = better reviews',
                '💡 Keep a record of all supplier order confirmations in case of disputes',
                '💡 Automate order placement where possible — reduces manual work significantly',
            ],
        ],

        '/admin/dropshipping/research' => [
            'title' => 'AI Product Research', 'icon' => '🔍',
            'intro' => 'AI-powered product research — find trending niches and winning products.',
            'steps' => [
                ['label' => 'Enter a niche or category', 'detail' => 'E.g. "pet accessories", "fitness", "home office gadgets"'],
                ['label' => 'Click "Research"', 'detail' => 'AI analyzes trends, competition, and margin potential'],
                ['label' => 'Review product recommendations', 'detail' => 'See suggested products with estimated margins and demand signals'],
            ],
            'tips' => ['💡 Test niches with 5-10 products before scaling', '💡 High margins + steady demand = ideal product'],
        ],
        '/admin/dropshipping/settings' => [
            'title' => 'Dropshipping Settings', 'icon' => '⚙️',
            'intro' => 'Configure dropshipping defaults — markup, notifications, and sync.',
            'steps' => [
                ['label' => 'Set default markup rules', 'detail' => 'Default price multiplier applied to new imported products'],
                ['label' => 'Configure supplier notifications', 'detail' => 'Auto-email suppliers when customer places an order'],
                ['label' => 'Set sync frequency', 'detail' => 'How often product prices and stock are synced from suppliers'],
            ],
            'tips' => ['💡 Keep your margins at 2-3x cost minimum', '💡 Enable stock sync to avoid overselling'],
        ],

        // ─── Booking ───
        '/admin/booking' => [
            'title' => 'Booking Dashboard', 'icon' => '📅',
            'intro' => 'Overview of today\'s appointments, upcoming bookings, and key metrics.',
            'steps' => [
                ['label' => 'Check today\'s schedule', 'detail' => 'See how many appointments are booked for today'],
                ['label' => 'Review upcoming bookings', 'detail' => 'Quick view of the next 7 days of appointments'],
                ['label' => 'Monitor booking stats', 'detail' => 'Total bookings, revenue, cancellation rate, popular services'],
            ],
            'tips' => ['💡 Check dashboard daily to stay on top of appointments', '💡 High cancellation rate? Enable payment upfront'],
        ],
        '/admin/booking/services' => [
            'title' => 'Booking Services', 'icon' => '🛎️',
            'intro' => 'Manage the services customers can book — each with its own duration and price.',
            'steps' => [
                ['label' => 'Click "+ New Service" to create one', 'detail' => 'Set name, description, duration, and price'],
                ['label' => 'Set service duration', 'detail' => 'How long each appointment takes (e.g. 30min, 1hr)'],
                ['label' => 'Assign to staff members', 'detail' => 'Which staff can perform this service'],
                ['label' => 'Set buffer time', 'detail' => 'Add gap between appointments for prep time'],
            ],
            'tips' => ['💡 Keep service names short and clear for customers', '💡 Add a description to help customers choose the right service'],
        ],
        '/admin/booking/services/create' => [
            'title' => 'New Booking Service', 'icon' => '➕',
            'intro' => 'Create a new bookable service with pricing and duration.',
            'steps' => [
                ['label' => 'Enter service name', 'detail' => 'Clear name: "Haircut", "Consultation", "Massage 60min"'],
                ['label' => 'Set duration in minutes', 'detail' => 'Total time including the service itself'],
                ['label' => 'Set price', 'detail' => 'Leave 0 for free services; set price for paid bookings'],
                ['label' => 'Write a description', 'detail' => 'Help customers understand what\'s included'],
                ['label' => 'Save', 'detail' => 'Service appears in the booking page for customers'],
            ],
            'tips' => ['💡 Include what\'s included in the description', '💡 Test the booking flow after creating a new service'],
        ],
        '/admin/booking/staff' => [
            'title' => 'Staff Members', 'icon' => '👤',
            'intro' => 'Manage staff who provide services — each with their own schedule.',
            'steps' => [
                ['label' => 'Click "+ New Staff" to add a team member', 'detail' => 'Enter name, email, photo, and which services they offer'],
                ['label' => 'Assign services', 'detail' => 'Check which services this person can perform'],
                ['label' => 'Set individual schedule', 'detail' => 'Override default working hours per staff member'],
            ],
            'tips' => ['💡 Add a photo — customers like seeing who they\'ll meet', '💡 Each staff member can have different working hours'],
        ],
        '/admin/booking/staff/create' => [
            'title' => 'Add Staff Member', 'icon' => '➕',
            'intro' => 'Add a new team member who can accept bookings.',
            'steps' => [
                ['label' => 'Enter name and email', 'detail' => 'Email is used for appointment notifications'],
                ['label' => 'Upload a photo (optional)', 'detail' => 'Shows on the booking page next to their name'],
                ['label' => 'Select services they offer', 'detail' => 'Check all services this staff member can perform'],
                ['label' => 'Set their availability', 'detail' => 'Working days and hours — can differ from main schedule'],
            ],
            'tips' => ['💡 Staff receive email notifications for new bookings'],
        ],
        '/admin/booking/calendar' => [
            'title' => 'Booking Calendar', 'icon' => '📆',
            'intro' => 'Visual calendar showing all appointments — day, week, or month view.',
            'steps' => [
                ['label' => 'Switch between day/week/month view', 'detail' => 'Use the toggle buttons at the top of the calendar'],
                ['label' => 'Click an appointment to see details', 'detail' => 'View customer info, service, and payment status'],
                ['label' => 'Click empty slots to create manual bookings', 'detail' => 'Book appointments on behalf of customers'],
            ],
            'tips' => ['💡 Week view is best for daily planning', '💡 Color-coded by service type for quick scanning'],
        ],
        '/admin/booking/appointments' => [
            'title' => 'All Appointments', 'icon' => '📋',
            'intro' => 'Complete list of all appointments with search, filter, and status management.',
            'steps' => [
                ['label' => 'Filter by status', 'detail' => 'Filter: Confirmed, Pending, Cancelled, Completed'],
                ['label' => 'Search by customer name or email', 'detail' => 'Quick lookup of specific bookings'],
                ['label' => 'Click to view details', 'detail' => 'See full appointment info, update status, or add notes'],
                ['label' => 'Cancel or reschedule', 'detail' => 'Change appointment date/time or cancel with notification'],
            ],
            'tips' => ['💡 Mark appointments as "Completed" after they happen for accurate stats', '💡 Export appointments as CSV for reporting'],
        ],
        '/admin/booking/settings' => [
            'title' => 'Booking Settings', 'icon' => '⚙️',
            'intro' => 'Configure booking system — hours, payments, reminders, and policies.',
            'steps' => [
                ['label' => 'Set working hours', 'detail' => 'Days and time ranges when bookings are accepted'],
                ['label' => 'Configure payment methods', 'detail' => 'Enable Stripe, PayPal, bank transfer, or free booking'],
                ['label' => 'Set reminder emails', 'detail' => 'Auto-send reminders 24h or 1h before appointment'],
                ['label' => 'Set cancellation policy', 'detail' => 'Allow/disallow cancellations and set minimum notice period'],
                ['label' => 'Enable/disable auto-confirm', 'detail' => 'Auto-confirm bookings or require manual approval'],
            ],
            'tips' => ['💡 Enable payment upfront to reduce no-shows by 50%+', '💡 Reminder emails dramatically reduce no-shows'],
        ],

        // ─── LMS ───
        '/admin/lms' => [
            'title' => 'LMS Dashboard', 'icon' => '🎓',
            'intro' => 'Learning Management overview — courses, enrollments, and revenue.',
            'steps' => [
                ['label' => 'Check active enrollments', 'detail' => 'See how many students are currently taking courses'],
                ['label' => 'Review course revenue', 'detail' => 'Total earnings from course sales'],
                ['label' => 'See completion rates', 'detail' => 'Percentage of students who finish courses — aim for 60%+'],
            ],
            'tips' => ['💡 Low completion? Break courses into shorter lessons', '💡 Add quizzes to boost engagement by 40%'],
        ],
        '/admin/lms/courses' => [
            'title' => 'Courses', 'icon' => '📚',
            'intro' => 'Manage your online courses — create, edit, publish, and track performance.',
            'steps' => [
                ['label' => 'Click "+ New Course" to create', 'detail' => 'Enter title, description, price, and featured image'],
                ['label' => 'Add lessons to the course', 'detail' => 'Each lesson has content, video, and downloadable resources'],
                ['label' => 'Set course price or make free', 'detail' => 'Free courses build audience, paid courses generate revenue'],
                ['label' => 'Publish when ready', 'detail' => 'Published courses appear at /courses for students'],
            ],
            'tips' => ['💡 Start with one free course to build trust', '💡 5-15 minute lessons work best for engagement'],
        ],
        '/admin/lms/courses/create' => [
            'title' => 'Create Course', 'icon' => '➕',
            'intro' => 'Build a new online course with lessons, quizzes, and certificates.',
            'steps' => [
                ['label' => 'Enter course title and description', 'detail' => 'Title should be compelling — "Master X in 30 Days"'],
                ['label' => 'Upload a featured image', 'detail' => 'Course thumbnail displayed in the catalog'],
                ['label' => 'Set price (or leave 0 for free)', 'detail' => 'Students pay once for lifetime access'],
                ['label' => 'Add curriculum', 'detail' => 'Create sections and lessons with content, video embeds, files'],
                ['label' => 'Save and add quizzes', 'detail' => 'Optional quizzes test knowledge after each section'],
            ],
            'tips' => ['💡 Plan your curriculum first, then fill in content', '💡 Include a compelling course description to drive enrollments'],
        ],
        '/admin/lms/enrollments' => [
            'title' => 'Student Enrollments', 'icon' => '👩‍🎓',
            'intro' => 'Track which students are enrolled in courses and their progress.',
            'steps' => [
                ['label' => 'Filter by course', 'detail' => 'See enrollments for a specific course'],
                ['label' => 'Check progress bars', 'detail' => 'See how far each student has progressed through the course'],
                ['label' => 'View student details', 'detail' => 'Click a student to see completed lessons and quiz scores'],
            ],
            'tips' => ['💡 Follow up with students who stall — a nudge email can re-engage them', '💡 Students at 80%+ completion almost always finish — celebrate them'],
        ],
        '/admin/lms/reviews' => [
            'title' => 'Course Reviews', 'icon' => '⭐',
            'intro' => 'Moderate student reviews and ratings for your courses.',
            'steps' => [
                ['label' => 'Review pending submissions', 'detail' => 'New reviews need approval before they\'re public'],
                ['label' => 'Approve or reject', 'detail' => 'Approve genuine reviews, reject spam or irrelevant'],
                ['label' => 'Respond to reviews', 'detail' => 'Reply to student feedback — shows you care'],
            ],
            'tips' => ['💡 Always respond to negative reviews constructively', '💡 Good reviews increase course conversions significantly'],
        ],
        '/admin/lms/certificates' => [
            'title' => 'Certificates', 'icon' => '🏅',
            'intro' => 'View issued completion certificates with unique verification codes.',
            'steps' => [
                ['label' => 'View issued certificates', 'detail' => 'List of all certificates with student name, course, and date'],
                ['label' => 'Verify a certificate', 'detail' => 'Enter a verification code to check if a certificate is valid'],
                ['label' => 'Download or share', 'detail' => 'Certificates are downloadable PDF by the student'],
            ],
            'tips' => ['💡 Certificates are auto-generated when a student completes all lessons', '💡 Each certificate has a unique verification code'],
        ],
        '/admin/lms/settings' => [
            'title' => 'LMS Settings', 'icon' => '⚙️',
            'intro' => 'Configure learning management — enrollment, certificates, and notifications.',
            'steps' => [
                ['label' => 'Set enrollment options', 'detail' => 'Open enrollment or approval-required'],
                ['label' => 'Configure certificate template', 'detail' => 'Logo, colors, and text for auto-generated certificates'],
                ['label' => 'Set notification emails', 'detail' => 'Emails for enrollment confirmation, completion, and quiz results'],
            ],
            'tips' => ['💡 Enable certificates to increase course value', '💡 Auto-email on completion encourages student referrals'],
        ],

        // ─── Events ───
        '/admin/events' => [
            'title' => 'Events Dashboard', 'icon' => '🎫',
            'intro' => 'Overview of your events — upcoming, ticket sales, and attendance.',
            'steps' => [
                ['label' => 'Check upcoming events', 'detail' => 'See next events with ticket sales count'],
                ['label' => 'Review ticket revenue', 'detail' => 'Total earnings from ticket sales'],
                ['label' => 'Quick-create a new event', 'detail' => 'Click "+ New Event" to start'],
            ],
            'tips' => ['💡 Start promoting events 4-6 weeks before the date', '💡 Early bird pricing drives early ticket sales'],
        ],
        '/admin/events/list' => [
            'title' => 'All Events', 'icon' => '📋',
            'intro' => 'List of all events — past, current, and upcoming.',
            'steps' => [
                ['label' => 'Filter by status', 'detail' => 'Upcoming, Active, Past, Draft'],
                ['label' => 'Click an event to edit', 'detail' => 'Update details, ticket prices, or capacity'],
                ['label' => 'Duplicate an event', 'detail' => 'Copy a past event to create a similar one quickly'],
            ],
            'tips' => ['💡 Keep past events visible for SEO value', '💡 Regularly update event descriptions as the date approaches'],
        ],
        '/admin/events/create' => [
            'title' => 'Create Event', 'icon' => '➕',
            'intro' => 'Create a new event with tickets, venue details, and capacity.',
            'steps' => [
                ['label' => 'Enter event title and description', 'detail' => 'Make it compelling — include what attendees will experience'],
                ['label' => 'Set date, time, and venue', 'detail' => 'Add location address and any online/virtual links'],
                ['label' => 'Define ticket types', 'detail' => 'General, VIP, Early Bird — each with its own price and capacity'],
                ['label' => 'Set total capacity', 'detail' => 'Maximum attendees across all ticket types'],
                ['label' => 'Upload event image and publish', 'detail' => 'Eye-catching image + publish to make it live at /events'],
            ],
            'tips' => ['💡 Add early bird tickets with an expiry date for urgency', '💡 Include a clear agenda or schedule in the description'],
        ],
        '/admin/events/orders' => [
            'title' => 'Ticket Orders', 'icon' => '🧾',
            'intro' => 'View and manage ticket purchases for your events.',
            'steps' => [
                ['label' => 'Filter by event', 'detail' => 'See orders for a specific event'],
                ['label' => 'Check payment status', 'detail' => 'Paid, pending, refunded — filter by status'],
                ['label' => 'Process refunds if needed', 'detail' => 'Click an order to view details and initiate refund'],
            ],
            'tips' => ['💡 Export attendee list for check-in at the event', '💡 Send reminder emails to ticket holders before the event'],
        ],
        '/admin/events/settings' => [
            'title' => 'Events Settings', 'icon' => '⚙️',
            'intro' => 'Configure event defaults — payment, notifications, and display.',
            'steps' => [
                ['label' => 'Set payment methods', 'detail' => 'Enable Stripe, PayPal, or free registration'],
                ['label' => 'Configure notification emails', 'detail' => 'Confirmation, reminder, and post-event follow-up emails'],
                ['label' => 'Set display options', 'detail' => 'How events appear on the frontend listing page'],
            ],
            'tips' => ['💡 Enable payment to secure ticket sales upfront'],
        ],

        // ─── Membership ───
        '/admin/membership' => [
            'title' => 'Membership Dashboard', 'icon' => '🔑',
            'intro' => 'Overview of member subscriptions — active, revenue, and churn.',
            'steps' => [
                ['label' => 'Check active member count', 'detail' => 'Total currently active subscriptions'],
                ['label' => 'Review monthly revenue', 'detail' => 'Recurring revenue from membership plans'],
                ['label' => 'Monitor churn rate', 'detail' => 'Percentage of members cancelling — aim for under 5%'],
            ],
            'tips' => ['💡 High churn? Add more exclusive content to retain members', '💡 Send a monthly "what\'s new" email to members'],
        ],
        '/admin/membership/plans' => [
            'title' => 'Membership Plans', 'icon' => '📋',
            'intro' => 'Manage subscription tiers that members can sign up for.',
            'steps' => [
                ['label' => 'Click "+ New Plan" to create a tier', 'detail' => 'E.g. Free, Basic ($9/mo), Premium ($29/mo), Enterprise ($99/mo)'],
                ['label' => 'Set pricing and billing cycle', 'detail' => 'Monthly, annual, or one-time payment'],
                ['label' => 'Define plan features', 'detail' => 'List what each tier includes (content access, downloads, support)'],
            ],
            'tips' => ['💡 3-4 tiers is ideal — too many overwhelms customers', '💡 Annual plans with 20% discount improve retention'],
        ],
        '/admin/membership/plans/create' => [
            'title' => 'Create Plan', 'icon' => '➕',
            'intro' => 'Create a new membership subscription plan.',
            'steps' => [
                ['label' => 'Enter plan name', 'detail' => 'Clear name: "Starter", "Professional", "Enterprise"'],
                ['label' => 'Set price and billing cycle', 'detail' => 'Monthly and/or annual pricing'],
                ['label' => 'List included features', 'detail' => 'What members get: content access, downloads, support level'],
                ['label' => 'Save and publish', 'detail' => 'Plan appears at /membership/signup for registration'],
            ],
            'tips' => ['💡 Include a free or trial tier to reduce signup friction'],
        ],
        '/admin/membership/members' => [
            'title' => 'Members', 'icon' => '👥',
            'intro' => 'All members with subscription status, plan, and payment history.',
            'steps' => [
                ['label' => 'Search by name or email', 'detail' => 'Quick lookup of specific members'],
                ['label' => 'Filter by plan or status', 'detail' => 'Active, expired, cancelled, trial'],
                ['label' => 'Click to view member details', 'detail' => 'See plan, payment history, and content access'],
                ['label' => 'Manually upgrade/downgrade', 'detail' => 'Change a member\'s plan if needed'],
            ],
            'tips' => ['💡 Follow up with expired members — a discount offer can win them back'],
        ],
        '/admin/membership/members/add' => [
            'title' => 'Add Member', 'icon' => '➕',
            'intro' => 'Manually add a member and assign them a plan.',
            'steps' => [
                ['label' => 'Enter member details', 'detail' => 'Name, email, and optional phone number'],
                ['label' => 'Select a plan', 'detail' => 'Choose which membership tier to assign'],
                ['label' => 'Set start date', 'detail' => 'When membership begins — today or a future date'],
                ['label' => 'Save', 'detail' => 'Member gets access immediately and receives a welcome email'],
            ],
            'tips' => ['💡 Useful for adding VIP guests or comp memberships'],
        ],
        '/admin/membership/content' => [
            'title' => 'Content Restrictions', 'icon' => '🔒',
            'intro' => 'Define which pages and articles are restricted to which membership plans.',
            'steps' => [
                ['label' => 'Select a page or article', 'detail' => 'Choose content to restrict'],
                ['label' => 'Set minimum plan level', 'detail' => 'E.g. "Premium only" or "Basic and above"'],
                ['label' => 'Set fallback message', 'detail' => 'What non-members see: "Upgrade to access this content"'],
            ],
            'tips' => ['💡 Keep some content free to attract visitors', '💡 Tease locked content with a preview paragraph'],
        ],
        '/admin/membership/settings' => [
            'title' => 'Membership Settings', 'icon' => '⚙️',
            'intro' => 'Configure membership system — payments, emails, and policies.',
            'steps' => [
                ['label' => 'Configure payment gateway', 'detail' => 'Connect Stripe or PayPal for recurring billing'],
                ['label' => 'Set welcome and renewal emails', 'detail' => 'Auto-emails on signup, renewal, and expiry'],
                ['label' => 'Set cancellation policy', 'detail' => 'Allow immediate or end-of-period cancellations'],
            ],
            'tips' => ['💡 Enable grace period for failed payments — reduces involuntary churn'],
        ],

        // ─── Newsletter ───
        '/admin/newsletter' => [
            'title' => 'Newsletter Dashboard', 'icon' => '📧',
            'intro' => 'Email marketing overview — subscribers, campaigns, and open rates.',
            'steps' => [
                ['label' => 'Check subscriber growth', 'detail' => 'Total subscribers and recent signups'],
                ['label' => 'Review campaign performance', 'detail' => 'Open rates, click rates, and unsubscribe rate'],
                ['label' => 'Create a new campaign', 'detail' => 'Click through to Campaigns to start'],
            ],
            'tips' => ['💡 Aim for 20%+ open rate', '💡 Unsubscribe rate above 1% means content isn\'t matching expectations'],
        ],
        '/admin/newsletter/campaigns' => [
            'title' => 'Campaigns', 'icon' => '📨',
            'intro' => 'All email campaigns — drafts, sent, and scheduled.',
            'steps' => [
                ['label' => 'Click "+ New Campaign"', 'detail' => 'Start creating a new email campaign'],
                ['label' => 'Filter by status', 'detail' => 'Draft, Scheduled, Sent, Archived'],
                ['label' => 'Click any campaign for stats', 'detail' => 'See opens, clicks, bounces, and unsubscribes'],
            ],
            'tips' => ['💡 Resend to non-openers after 3 days with different subject line'],
        ],
        '/admin/newsletter/campaigns/create' => [
            'title' => 'Create Campaign', 'icon' => '➕',
            'intro' => 'Design and send a new email campaign to your subscribers.',
            'steps' => [
                ['label' => 'Enter campaign name and subject', 'detail' => 'Subject line is critical — keep under 50 characters'],
                ['label' => 'Choose a template', 'detail' => 'Pick from your templates or start from scratch'],
                ['label' => 'Write email content', 'detail' => 'Use the visual editor — add images, buttons, links'],
                ['label' => 'Select recipient list', 'detail' => 'Choose which mailing list or segment to send to'],
                ['label' => 'Send test, then schedule or send', 'detail' => 'ALWAYS send test to yourself first, then send/schedule'],
            ],
            'tips' => ['💡 Best send times: Tue-Thu, 10am or 2pm', '💡 Include a clear CTA button in every email'],
        ],
        '/admin/newsletter/subscribers' => [
            'title' => 'Subscribers', 'icon' => '👥',
            'intro' => 'Manage your email subscriber list — add, search, filter, export.',
            'steps' => [
                ['label' => 'Search by email or name', 'detail' => 'Quick lookup of specific subscribers'],
                ['label' => 'Filter by list or status', 'detail' => 'Active, unsubscribed, bounced'],
                ['label' => 'Click to view details', 'detail' => 'See subscription date, campaigns received, open/click history'],
                ['label' => 'Export as CSV', 'detail' => 'Download subscriber list for backup or external tools'],
            ],
            'tips' => ['💡 Clean your list regularly — remove bounced emails', '💡 Segment subscribers by interest for targeted campaigns'],
        ],
        '/admin/newsletter/subscribers/add' => [
            'title' => 'Add Subscriber', 'icon' => '➕',
            'intro' => 'Manually add a subscriber to your mailing list.',
            'steps' => [
                ['label' => 'Enter email address', 'detail' => 'Required — the subscriber\'s email'],
                ['label' => 'Enter name (optional)', 'detail' => 'Used for personalization in campaigns'],
                ['label' => 'Select mailing list(s)', 'detail' => 'Choose which lists they should be added to'],
                ['label' => 'Save', 'detail' => 'Subscriber is added and will receive future campaigns'],
            ],
            'tips' => ['💡 Always have consent before adding subscribers — GDPR compliance'],
        ],
        '/admin/newsletter/subscribers/import' => [
            'title' => 'Import Subscribers', 'icon' => '📥',
            'intro' => 'Bulk import subscribers from a CSV file.',
            'steps' => [
                ['label' => 'Prepare your CSV file', 'detail' => 'Columns: email (required), name (optional), phone (optional)'],
                ['label' => 'Upload the CSV', 'detail' => 'Drag and drop or click to browse'],
                ['label' => 'Map columns', 'detail' => 'Match CSV columns to subscriber fields'],
                ['label' => 'Select target list', 'detail' => 'Choose which mailing list to import into'],
                ['label' => 'Import', 'detail' => 'Duplicates are automatically skipped'],
            ],
            'tips' => ['💡 Clean your CSV first — remove invalid emails', '💡 Max 10,000 rows per import'],
        ],
        '/admin/newsletter/lists' => [
            'title' => 'Mailing Lists', 'icon' => '📋',
            'intro' => 'Organize subscribers into lists for targeted campaigns.',
            'steps' => [
                ['label' => 'Click "+ New List"', 'detail' => 'Create a list: "General", "VIP Customers", "Blog Subscribers"'],
                ['label' => 'View list members', 'detail' => 'Click any list to see its subscribers'],
                ['label' => 'Delete empty or unused lists', 'detail' => 'Keep things organized'],
            ],
            'tips' => ['💡 Segment by interest — targeted emails get 2x better open rates'],
        ],
        '/admin/newsletter/lists/create' => [
            'title' => 'Create Mailing List', 'icon' => '➕',
            'intro' => 'Create a new subscriber list for organizing your audience.',
            'steps' => [
                ['label' => 'Enter list name', 'detail' => 'Descriptive: "Product Updates", "Event Attendees", "VIP"'],
                ['label' => 'Add optional description', 'detail' => 'Internal note about what this list is for'],
                ['label' => 'Save', 'detail' => 'List is ready — add subscribers manually or via signup forms'],
            ],
            'tips' => ['💡 Create separate lists for different audience segments'],
        ],
        '/admin/newsletter/templates' => [
            'title' => 'Email Templates', 'icon' => '🎨',
            'intro' => 'Design and manage reusable email templates for campaigns.',
            'steps' => [
                ['label' => 'Browse existing templates', 'detail' => 'Pre-built templates ready to customize'],
                ['label' => 'Create a new template', 'detail' => 'Design from scratch with the visual editor'],
                ['label' => 'Edit template HTML/CSS', 'detail' => 'Advanced: edit the raw HTML for pixel-perfect control'],
            ],
            'tips' => ['💡 Keep templates mobile-responsive — 60%+ of emails are read on phones', '💡 Include your logo and brand colors in every template'],
        ],
        '/admin/newsletter/settings' => [
            'title' => 'Newsletter Settings', 'icon' => '⚙️',
            'intro' => 'Configure newsletter system — sender, unsubscribe, and compliance.',
            'steps' => [
                ['label' => 'Set sender name and email', 'detail' => 'The "From" name and email for all campaigns'],
                ['label' => 'Configure unsubscribe settings', 'detail' => 'Unsubscribe page text and confirmation'],
                ['label' => 'Set GDPR compliance options', 'detail' => 'Double opt-in, consent tracking, data retention'],
            ],
            'tips' => ['💡 Use a recognizable sender name — your brand or personal name', '💡 Double opt-in reduces spam complaints significantly'],
        ],

        // ─── Directory ───
        '/admin/directory' => [
            'title' => 'Directory Dashboard', 'icon' => '📍',
            'intro' => 'Business directory overview — listings, reviews, and activity.',
            'steps' => [
                ['label' => 'Check total listings', 'detail' => 'Active business listings and pending reviews'],
                ['label' => 'Review recent submissions', 'detail' => 'New listings or claims awaiting approval'],
                ['label' => 'Monitor user reviews', 'detail' => 'Recent reviews needing moderation'],
            ],
            'tips' => ['💡 More listings = more value for visitors', '💡 Frontend has map view at /directory'],
        ],
        '/admin/directory/listings' => [
            'title' => 'Directory Listings', 'icon' => '🏢',
            'intro' => 'Manage all business listings in your directory.',
            'steps' => [
                ['label' => 'Click "+ New Listing"', 'detail' => 'Add a business: name, address, phone, website, hours, images'],
                ['label' => 'Search and filter', 'detail' => 'Find listings by name, category, or status'],
                ['label' => 'Edit listing details', 'detail' => 'Click any listing to update info, images, or map location'],
                ['label' => 'Feature important listings', 'detail' => 'Mark as featured — shows with orange marker on map'],
            ],
            'tips' => ['💡 Complete listings (with photos, hours, description) rank better', '💡 Featured listings get prominent map placement'],
        ],
        '/admin/directory/listings/create' => [
            'title' => 'Add Listing', 'icon' => '➕',
            'intro' => 'Add a new business to the directory.',
            'steps' => [
                ['label' => 'Enter business name and category', 'detail' => 'Name as it should appear in the directory'],
                ['label' => 'Enter address, phone, website', 'detail' => 'Full contact details for the business'],
                ['label' => 'Set map coordinates', 'detail' => 'Enter lat/lng for map pin placement (or use geocoding)'],
                ['label' => 'Upload photos', 'detail' => 'Business photos, logo, and storefront images'],
                ['label' => 'Write description and set hours', 'detail' => 'Detailed description + opening hours per day'],
            ],
            'tips' => ['💡 Add at least 3 photos per listing', '💡 Accurate map coordinates ensure the pin is in the right place'],
        ],
        '/admin/directory/categories' => [
            'title' => 'Directory Categories', 'icon' => '📁',
            'intro' => 'Manage business categories for organizing listings.',
            'steps' => [
                ['label' => 'Create categories', 'detail' => 'Restaurant, Hotel, Gym, Store, Services, etc.'],
                ['label' => 'Set icons or images', 'detail' => 'Visual icons for each category on the frontend'],
                ['label' => 'Organize hierarchy', 'detail' => 'Parent/child categories: Food → Restaurant, Cafe, Bakery'],
            ],
            'tips' => ['💡 10-15 top-level categories is ideal'],
        ],
        '/admin/directory/reviews' => [
            'title' => 'Directory Reviews', 'icon' => '⭐',
            'intro' => 'Moderate user reviews and ratings for business listings.',
            'steps' => [
                ['label' => 'Review pending submissions', 'detail' => 'New reviews needing approval'],
                ['label' => 'Approve or reject', 'detail' => 'Approve genuine reviews, reject spam'],
                ['label' => 'Respond to reviews', 'detail' => 'Reply on behalf of the directory as admin'],
            ],
            'tips' => ['💡 Fast review moderation encourages more submissions'],
        ],
        '/admin/directory/claims' => [
            'title' => 'Business Claims', 'icon' => '✋',
            'intro' => 'Handle requests from business owners to claim and manage their listing.',
            'steps' => [
                ['label' => 'Review claim requests', 'detail' => 'Verify the person is the actual business owner'],
                ['label' => 'Approve or deny', 'detail' => 'Approved owners can edit their own listing'],
                ['label' => 'Contact claimant if needed', 'detail' => 'Request proof of ownership before approving'],
            ],
            'tips' => ['💡 Claimed businesses tend to keep their info more up-to-date'],
        ],
        '/admin/directory/settings' => [
            'title' => 'Directory Settings', 'icon' => '⚙️',
            'intro' => 'Configure directory — map, reviews, claims, and display.',
            'steps' => [
                ['label' => 'Set map defaults', 'detail' => 'Default center point, zoom level, and marker colors'],
                ['label' => 'Configure review settings', 'detail' => 'Require moderation, allow anonymous, star rating options'],
                ['label' => 'Enable/disable claims', 'detail' => 'Allow business owners to claim their listings'],
            ],
            'tips' => ['💡 Set the default map center to your main city/area'],
        ],

        // ─── Jobs ───
        '/admin/jobs' => [
            'title' => 'Jobs Dashboard', 'icon' => '💼',
            'intro' => 'Job board overview — active listings, recent applications.',
            'steps' => [
                ['label' => 'Check active job listings', 'detail' => 'Number of currently published jobs'],
                ['label' => 'Review new applications', 'detail' => 'Recent submissions from candidates'],
                ['label' => 'Post a new job', 'detail' => 'Quick link to create a new listing'],
            ],
            'tips' => ['💡 Refresh old listings — jobs older than 30 days get fewer applications'],
        ],
        '/admin/jobs/listings' => [
            'title' => 'Job Listings', 'icon' => '📋',
            'intro' => 'All job postings — create, edit, and manage.',
            'steps' => [
                ['label' => 'Click "+ New Job" to post', 'detail' => 'Create a new job listing with full details'],
                ['label' => 'Filter by status', 'detail' => 'Active, expired, draft'],
                ['label' => 'Edit existing listings', 'detail' => 'Update salary, requirements, or close a filled position'],
            ],
            'tips' => ['💡 Include salary range — 30% more applications', '💡 Close filled positions promptly'],
        ],
        '/admin/jobs/listings/create' => [
            'title' => 'Post a Job', 'icon' => '➕',
            'intro' => 'Create a new job listing with all details.',
            'steps' => [
                ['label' => 'Enter job title and company', 'detail' => 'Clear title: "Senior PHP Developer at TechCo"'],
                ['label' => 'Write job description', 'detail' => 'Responsibilities, requirements, benefits, culture info'],
                ['label' => 'Set salary range', 'detail' => 'Salary range or "Competitive" — range is better for applicants'],
                ['label' => 'Set location and type', 'detail' => 'City, remote/hybrid/onsite, full-time/part-time/contract'],
                ['label' => 'Publish', 'detail' => 'Job appears at /jobs for candidates'],
            ],
            'tips' => ['💡 Structure: About Us → Role → Requirements → Benefits → How to Apply'],
        ],
        '/admin/jobs/applications' => [
            'title' => 'Applications', 'icon' => '📬',
            'intro' => 'Review and manage candidate applications.',
            'steps' => [
                ['label' => 'Filter by job listing', 'detail' => 'See applications for a specific position'],
                ['label' => 'Review candidate details', 'detail' => 'Name, email, resume/CV, cover letter'],
                ['label' => 'Mark status', 'detail' => 'New → Reviewed → Shortlisted → Interview → Hired/Rejected'],
            ],
            'tips' => ['💡 Respond to all applicants — even rejections deserve a reply', '💡 Export shortlisted candidates for team review'],
        ],
        '/admin/jobs/companies' => [
            'title' => 'Companies', 'icon' => '🏢',
            'intro' => 'Manage employer company profiles.',
            'steps' => [
                ['label' => 'Add company profiles', 'detail' => 'Name, logo, description, website, industry'],
                ['label' => 'Link to job listings', 'detail' => 'Each job is associated with a company'],
                ['label' => 'Edit company details', 'detail' => 'Update logo, description, or contact info'],
            ],
            'tips' => ['💡 Complete company profiles build trust with applicants'],
        ],
        '/admin/jobs/settings' => [
            'title' => 'Jobs Settings', 'icon' => '⚙️',
            'intro' => 'Configure job board — application form, expiry, and notifications.',
            'steps' => [
                ['label' => 'Set default expiry period', 'detail' => 'Auto-expire listings after X days (e.g. 30)'],
                ['label' => 'Configure application form fields', 'detail' => 'Required fields: name, email, resume. Optional: cover letter, phone'],
                ['label' => 'Set notification emails', 'detail' => 'Notify hiring manager when new applications arrive'],
            ],
            'tips' => ['💡 30-day expiry keeps the job board fresh'],
        ],

        // ─── Real Estate ───
        '/admin/realestate' => [
            'title' => 'Real Estate Dashboard', 'icon' => '🏠',
            'intro' => 'Property listing overview — active listings, inquiries, and performance.',
            'steps' => [
                ['label' => 'Check active properties', 'detail' => 'Total for sale, for rent, and featured'],
                ['label' => 'Review recent inquiries', 'detail' => 'Messages from interested buyers/renters'],
                ['label' => 'Add a new property', 'detail' => 'Quick link to create a listing'],
            ],
            'tips' => ['💡 Properties with 10+ photos get 3x more inquiries'],
        ],
        '/admin/realestate/properties' => [
            'title' => 'Properties', 'icon' => '🏘️',
            'intro' => 'All property listings — search, filter, and manage.',
            'steps' => [
                ['label' => 'Click "+ New Property"', 'detail' => 'Create a property listing with full details'],
                ['label' => 'Filter by type', 'detail' => 'Sale, rent, featured, draft'],
                ['label' => 'Edit listing details', 'detail' => 'Update price, images, or mark as sold/rented'],
                ['label' => 'Feature top properties', 'detail' => 'Featured properties get orange markers on the map'],
            ],
            'tips' => ['💡 Mark sold/rented properties instead of deleting — good for portfolio'],
        ],
        '/admin/realestate/properties/create' => [
            'title' => 'Add Property', 'icon' => '➕',
            'intro' => 'Create a new property listing with all details.',
            'steps' => [
                ['label' => 'Enter title and description', 'detail' => 'Compelling title: "Modern 3-Bed Apartment, City Center"'],
                ['label' => 'Set price and type', 'detail' => 'For sale or for rent, price amount'],
                ['label' => 'Enter property details', 'detail' => 'Bedrooms, bathrooms, area (sqm/sqft), year built, parking'],
                ['label' => 'Upload photos (10+ recommended)', 'detail' => 'High-quality photos of every room, exterior, views'],
                ['label' => 'Set map location', 'detail' => 'Enter coordinates for map pin — appears on /properties map view'],
            ],
            'tips' => ['💡 First photo is the thumbnail — make it the best shot', '💡 Include neighborhood info in the description'],
        ],
        '/admin/realestate/agents' => [
            'title' => 'Real Estate Agents', 'icon' => '👔',
            'intro' => 'Manage agent profiles — contact info, photo, and assigned properties.',
            'steps' => [
                ['label' => 'Add agent profiles', 'detail' => 'Name, photo, phone, email, bio'],
                ['label' => 'Assign to properties', 'detail' => 'Each property can have an assigned agent'],
                ['label' => 'Edit agent details', 'detail' => 'Update contact info or specialties'],
            ],
            'tips' => ['💡 Professional headshot photos increase trust'],
        ],
        '/admin/realestate/inquiries' => [
            'title' => 'Property Inquiries', 'icon' => '📩',
            'intro' => 'Messages from visitors interested in your properties.',
            'steps' => [
                ['label' => 'Review new inquiries', 'detail' => 'See which property, visitor\'s name, email, and message'],
                ['label' => 'Respond quickly', 'detail' => 'Reply via email — speed matters in real estate'],
                ['label' => 'Mark as handled', 'detail' => 'Update status: New → Contacted → Viewing → Closed'],
            ],
            'tips' => ['💡 Respond within 1 hour for best conversion — speed wins in real estate'],
        ],
        '/admin/realestate/settings' => [
            'title' => 'Real Estate Settings', 'icon' => '⚙️',
            'intro' => 'Configure property listings — map, units, and display.',
            'steps' => [
                ['label' => 'Set area unit', 'detail' => 'Square meters (m²) or square feet (ft²)'],
                ['label' => 'Set currency', 'detail' => 'Currency for property prices'],
                ['label' => 'Configure map defaults', 'detail' => 'Default center and zoom for the property map'],
            ],
            'tips' => ['💡 Match area units to your local market conventions'],
        ],

        // ─── Portfolio ───
        '/admin/portfolio' => [
            'title' => 'Portfolio Dashboard', 'icon' => '🎨',
            'intro' => 'Showcase your work — projects, testimonials, and categories.',
            'steps' => [
                ['label' => 'Add your best projects', 'detail' => 'Go to Projects to create portfolio items'],
                ['label' => 'Organize by category', 'detail' => 'Web Design, Branding, Photography, etc.'],
                ['label' => 'Collect testimonials', 'detail' => 'Add client quotes and ratings'],
            ],
            'tips' => ['💡 Quality over quantity — 10-20 best projects is ideal'],
        ],
        '/admin/portfolio/projects' => [
            'title' => 'Portfolio Projects', 'icon' => '📂',
            'intro' => 'Manage your portfolio projects — add, edit, and reorder.',
            'steps' => [
                ['label' => 'Click "+ New Project"', 'detail' => 'Add a project with images, description, and client info'],
                ['label' => 'Reorder projects', 'detail' => 'Drag to set display order — put best work first'],
                ['label' => 'Edit project details', 'detail' => 'Update images, description, or category'],
            ],
            'tips' => ['💡 Put your most impressive work first'],
        ],
        '/admin/portfolio/projects/create' => [
            'title' => 'Add Project', 'icon' => '➕',
            'intro' => 'Add a new project to your portfolio.',
            'steps' => [
                ['label' => 'Enter project title', 'detail' => 'Descriptive: "E-commerce Redesign for TechCo"'],
                ['label' => 'Upload project images', 'detail' => 'Screenshots, photos, mockups — first image is the thumbnail'],
                ['label' => 'Write description', 'detail' => 'Brief case study: challenge, solution, results'],
                ['label' => 'Set client name and category', 'detail' => 'Client attribution + category for filtering'],
                ['label' => 'Add project URL (optional)', 'detail' => 'Link to the live project if applicable'],
            ],
            'tips' => ['💡 Include a brief case study — it tells the story behind the work'],
        ],
        '/admin/portfolio/categories' => [
            'title' => 'Portfolio Categories', 'icon' => '📁',
            'intro' => 'Organize projects by type for easy browsing.',
            'steps' => [
                ['label' => 'Create categories', 'detail' => 'Web Design, Branding, Photography, Illustration, etc.'],
                ['label' => 'Assign projects', 'detail' => 'Each project belongs to one or more categories'],
            ],
            'tips' => ['💡 5-8 categories is ideal for portfolio navigation'],
        ],
        '/admin/portfolio/testimonials' => [
            'title' => 'Testimonials', 'icon' => '💬',
            'intro' => 'Manage client testimonials and quotes.',
            'steps' => [
                ['label' => 'Add a testimonial', 'detail' => 'Client name, company, quote, rating, and photo'],
                ['label' => 'Edit or reorder', 'detail' => 'Put most impactful testimonials first'],
            ],
            'tips' => ['💡 Testimonials with photos are 3x more convincing'],
        ],
        '/admin/portfolio/settings' => [
            'title' => 'Portfolio Settings', 'icon' => '⚙️',
            'intro' => 'Configure portfolio display — layout, columns, and lightbox.',
            'steps' => [
                ['label' => 'Choose grid layout', 'detail' => 'Grid columns, masonry, or list layout'],
                ['label' => 'Enable lightbox', 'detail' => 'Click-to-zoom image gallery for projects'],
                ['label' => 'Set items per page', 'detail' => 'How many projects show before pagination'],
            ],
            'tips' => ['💡 Masonry layout works great for mixed aspect ratio images'],
        ],

        // ─── Affiliate ───
        '/admin/affiliate' => [
            'title' => 'Affiliate Dashboard', 'icon' => '🤝',
            'intro' => 'Affiliate program overview — clicks, conversions, and payouts.',
            'steps' => [
                ['label' => 'Check total affiliate clicks', 'detail' => 'How many visitors came through affiliate links'],
                ['label' => 'Review conversions', 'detail' => 'Sales attributed to affiliates and commission earned'],
                ['label' => 'Process pending payouts', 'detail' => 'Pay affiliates their earned commissions'],
            ],
            'tips' => ['💡 Healthy affiliate program: 3-5% conversion rate on clicks'],
        ],
        '/admin/affiliate/programs' => [
            'title' => 'Affiliate Programs', 'icon' => '📋',
            'intro' => 'Manage your affiliate programs with commission rates and terms.',
            'steps' => [
                ['label' => 'Click "+ New Program"', 'detail' => 'Create a program with commission rate and rules'],
                ['label' => 'Set commission structure', 'detail' => 'Percentage per sale, or fixed amount'],
                ['label' => 'Edit program terms', 'detail' => 'Cookie duration, payout threshold, restrictions'],
            ],
            'tips' => ['💡 10-30% commission is standard for digital products', '💡 Longer cookie duration (30-90 days) = more attributed sales'],
        ],
        '/admin/affiliate/programs/create' => [
            'title' => 'Create Program', 'icon' => '➕',
            'intro' => 'Set up a new affiliate program.',
            'steps' => [
                ['label' => 'Enter program name', 'detail' => 'E.g. "Standard Affiliate Program", "VIP Partners"'],
                ['label' => 'Set commission rate', 'detail' => 'Percentage (e.g. 20%) or fixed amount per sale'],
                ['label' => 'Set cookie duration', 'detail' => 'Days the referral link stays active (30-90 days typical)'],
                ['label' => 'Define terms and conditions', 'detail' => 'What affiliates can/cannot do when promoting'],
            ],
            'tips' => ['💡 Start with one program, add VIP tier later for top performers'],
        ],
        '/admin/affiliate/affiliates' => [
            'title' => 'Affiliates', 'icon' => '👥',
            'intro' => 'Manage affiliate partners — approve, track, and communicate.',
            'steps' => [
                ['label' => 'Review pending applications', 'detail' => 'New affiliates waiting for approval'],
                ['label' => 'View affiliate stats', 'detail' => 'Clicks, conversions, and earnings per affiliate'],
                ['label' => 'Approve or reject', 'detail' => 'Approve legitimate partners, reject spam applicants'],
            ],
            'tips' => ['💡 Provide marketing materials to approved affiliates to help them promote'],
        ],
        '/admin/affiliate/conversions' => [
            'title' => 'Conversions', 'icon' => '💰',
            'intro' => 'Track sales attributed to affiliate referrals.',
            'steps' => [
                ['label' => 'View conversion list', 'detail' => 'Each conversion shows: affiliate, order, amount, commission'],
                ['label' => 'Filter by date or affiliate', 'detail' => 'See performance over specific periods'],
                ['label' => 'Verify conversions', 'detail' => 'Confirm legitimate sales before paying commissions'],
            ],
            'tips' => ['💡 Review conversions for fraud before processing payouts'],
        ],
        '/admin/affiliate/payouts' => [
            'title' => 'Payouts', 'icon' => '💸',
            'intro' => 'Process commission payments to your affiliates.',
            'steps' => [
                ['label' => 'Review pending payouts', 'detail' => 'Affiliates who have earned above the minimum threshold'],
                ['label' => 'Process payment', 'detail' => 'Mark as paid after sending payment via PayPal/bank transfer'],
                ['label' => 'View payout history', 'detail' => 'Record of all past payments'],
            ],
            'tips' => ['💡 Pay on a regular schedule (monthly) to keep affiliates motivated'],
        ],
        '/admin/affiliate/payouts/create' => [
            'title' => 'Create Payout', 'icon' => '➕',
            'intro' => 'Process a commission payment to an affiliate.',
            'steps' => [
                ['label' => 'Select affiliate', 'detail' => 'Choose the affiliate to pay'],
                ['label' => 'Review earned amount', 'detail' => 'Total unpaid commission balance'],
                ['label' => 'Choose payment method', 'detail' => 'PayPal, bank transfer, or other'],
                ['label' => 'Mark as paid', 'detail' => 'Record the payment and notify the affiliate'],
            ],
            'tips' => ['💡 Include a transaction reference for tracking'],
        ],
        '/admin/affiliate/settings' => [
            'title' => 'Affiliate Settings', 'icon' => '⚙️',
            'intro' => 'Configure affiliate program — payouts, registration, and tracking.',
            'steps' => [
                ['label' => 'Set minimum payout threshold', 'detail' => 'Minimum amount before affiliates can request payment'],
                ['label' => 'Configure registration', 'detail' => 'Open registration or approval-only'],
                ['label' => 'Set cookie tracking method', 'detail' => 'Cookie duration and attribution model'],
            ],
            'tips' => ['💡 $50-$100 minimum payout reduces transaction costs'],
        ],

        // ─── CRM ───
        '/admin/crm' => [
            'title' => 'CRM Dashboard', 'icon' => '👥',
            'intro' => 'Customer Relationship Management — pipeline value, contacts, and deals.',
            'steps' => [
                ['label' => 'Check pipeline value', 'detail' => 'Total value of deals in your sales pipeline'],
                ['label' => 'Review recent contacts', 'detail' => 'Latest added or updated contacts'],
                ['label' => 'Check deals won this month', 'detail' => 'Closed deals and their total value'],
            ],
            'tips' => ['💡 Update your pipeline daily for accurate forecasting'],
        ],
        '/admin/crm/contacts' => [
            'title' => 'Contacts', 'icon' => '📇',
            'intro' => 'All contacts — leads, customers, and partners.',
            'steps' => [
                ['label' => 'Search by name, email, or company', 'detail' => 'Quick lookup of any contact'],
                ['label' => 'Filter by tags or status', 'detail' => 'Lead, Customer, Partner, VIP, etc.'],
                ['label' => 'Click to view full profile', 'detail' => 'See contact history, deals, notes, and activity'],
                ['label' => 'Add tags for segmentation', 'detail' => 'Tag contacts for targeted communication'],
            ],
            'tips' => ['💡 Add notes after every interaction — your future self will thank you'],
        ],
        '/admin/crm/contacts/create' => [
            'title' => 'Add Contact', 'icon' => '➕',
            'intro' => 'Add a new contact to your CRM.',
            'steps' => [
                ['label' => 'Enter name and email', 'detail' => 'Primary identification fields'],
                ['label' => 'Add phone and company', 'detail' => 'Contact info and company association'],
                ['label' => 'Add tags', 'detail' => 'E.g. "lead", "from-website", "enterprise"'],
                ['label' => 'Write initial notes', 'detail' => 'How you met, what they need, context'],
            ],
            'tips' => ['💡 Always add context notes — they\'re gold for follow-ups'],
        ],
        '/admin/crm/pipeline' => [
            'title' => 'Sales Pipeline', 'icon' => '📊',
            'intro' => 'Visual kanban board — drag deals between stages.',
            'steps' => [
                ['label' => 'View deal stages', 'detail' => 'Default: Lead → Qualified → Proposal → Negotiation → Won/Lost'],
                ['label' => 'Drag deals between stages', 'detail' => 'Move deals as they progress through your sales process'],
                ['label' => 'Click a deal for details', 'detail' => 'See contact, value, notes, and activity timeline'],
                ['label' => 'Add a new deal', 'detail' => 'Create deal with value, contact, and expected close date'],
            ],
            'tips' => ['💡 Move deals daily — stale pipelines hide problems', '💡 Set expected close dates to forecast revenue'],
        ],
        '/admin/crm/import' => [
            'title' => 'Import Contacts', 'icon' => '📥',
            'intro' => 'Bulk import contacts from a CSV file.',
            'steps' => [
                ['label' => 'Prepare CSV', 'detail' => 'Columns: name, email, phone, company, notes (optional)'],
                ['label' => 'Upload and map fields', 'detail' => 'Match CSV columns to CRM fields'],
                ['label' => 'Review and import', 'detail' => 'Preview the data before importing — duplicates are merged'],
            ],
            'tips' => ['💡 Clean your CSV before importing — remove duplicates and invalid emails'],
        ],

        // ─── Marketing ───
        '/admin/email-campaigns' => [
            'title' => 'Email Campaigns', 'icon' => '📨',
            'intro' => 'Create and manage email marketing campaigns with analytics.',
            'steps' => [
                ['label' => 'Click "+ New Campaign"', 'detail' => 'Create a campaign with subject, content, and recipients'],
                ['label' => 'View campaign stats', 'detail' => 'Opens, clicks, bounces, and unsubscribes per campaign'],
                ['label' => 'Duplicate successful campaigns', 'detail' => 'Copy a campaign that performed well and modify'],
            ],
            'tips' => ['💡 A/B test subject lines for better open rates'],
        ],
        '/admin/email-queue' => [
            'title' => 'Email Queue', 'icon' => '📤',
            'intro' => 'View outgoing email queue — pending, sent, and failed.',
            'steps' => [
                ['label' => 'Check queue status', 'detail' => 'See how many emails are pending, sent, or failed'],
                ['label' => 'Retry failed emails', 'detail' => 'Resend emails that failed due to temporary errors'],
                ['label' => 'Clear old entries', 'detail' => 'Remove old sent emails from the queue'],
            ],
            'tips' => ['💡 Check the queue if campaigns seem stuck — failed emails need attention'],
        ],
        '/admin/email-queue/compose' => [
            'title' => 'Compose Email', 'icon' => '✉️',
            'intro' => 'Compose and send a single email or notification.',
            'steps' => [
                ['label' => 'Enter recipient email', 'detail' => 'The email address to send to'],
                ['label' => 'Write subject and body', 'detail' => 'Use the rich editor for formatted emails'],
                ['label' => 'Send or schedule', 'detail' => 'Send now or pick a future date/time'],
            ],
            'tips' => ['💡 For bulk sends use Campaigns, for individual use Compose'],
        ],
        '/admin/email-settings' => [
            'title' => 'Email Settings', 'icon' => '⚙️',
            'intro' => 'Configure SMTP, sender identity, and email templates.',
            'steps' => [
                ['label' => 'Configure SMTP server', 'detail' => 'Host, port, username, password, encryption (TLS/SSL)'],
                ['label' => 'Set sender name and email', 'detail' => 'The "From" field for all outgoing emails'],
                ['label' => 'Test email delivery', 'detail' => 'Send a test email to verify SMTP is working'],
                ['label' => 'Configure email templates', 'detail' => 'Default header, footer, and branding for system emails'],
            ],
            'tips' => ['💡 Use a dedicated email service (SendGrid, Mailgun) for reliable delivery', '💡 Always test after changing SMTP settings'],
        ],
        '/admin/social-media' => [
            'title' => 'Social Media', 'icon' => '📱',
            'intro' => 'Manage social media — compose, schedule, and track engagement.',
            'steps' => [
                ['label' => 'Compose a post', 'detail' => 'Write content for multiple platforms at once'],
                ['label' => 'View scheduled posts', 'detail' => 'Calendar view of upcoming posts'],
                ['label' => 'Track engagement', 'detail' => 'Likes, shares, comments across platforms'],
            ],
            'tips' => ['💡 Post consistently — 3-5 times per week per platform'],
        ],
        '/admin/social-media/accounts' => [
            'title' => 'Social Accounts', 'icon' => '🔗',
            'intro' => 'Connect and manage your social media accounts.',
            'steps' => [
                ['label' => 'Connect accounts', 'detail' => 'Link Facebook, Twitter/X, Instagram, LinkedIn'],
                ['label' => 'Verify connections', 'detail' => 'Ensure each account is properly authorized'],
                ['label' => 'Remove old connections', 'detail' => 'Disconnect accounts you no longer use'],
            ],
            'tips' => ['💡 Re-authenticate accounts if posting stops working'],
        ],
        '/admin/social-media/calendar' => [
            'title' => 'Social Calendar', 'icon' => '📅',
            'intro' => 'Visual content calendar for scheduled social media posts.',
            'steps' => [
                ['label' => 'View scheduled posts', 'detail' => 'See posts on a monthly/weekly calendar view'],
                ['label' => 'Click a date to schedule', 'detail' => 'Add a new post for that date and time'],
                ['label' => 'Drag to reschedule', 'detail' => 'Move posts to a different date by dragging'],
            ],
            'tips' => ['💡 Plan a week ahead for consistent posting'],
        ],
        '/admin/analytics' => [
            'title' => 'Analytics', 'icon' => '📊',
            'intro' => 'Website analytics — traffic, visitors, top pages, and conversions.',
            'steps' => [
                ['label' => 'Review key metrics', 'detail' => 'Pageviews, unique visitors, bounce rate, avg. time on site'],
                ['label' => 'Check top pages', 'detail' => 'Which pages get the most traffic'],
                ['label' => 'Review traffic sources', 'detail' => 'Where visitors come from: search, social, direct, referral'],
                ['label' => 'Set conversion goals', 'detail' => 'Track form submissions, purchases, signups as goals'],
            ],
            'tips' => ['💡 Check weekly to spot trends', '💡 High bounce rate? Improve content and page speed'],
        ],
        '/admin/analytics/realtime' => [
            'title' => 'Realtime Analytics', 'icon' => '⚡',
            'intro' => 'See who\'s on your site right now — live visitor tracking.',
            'steps' => [
                ['label' => 'View active visitors', 'detail' => 'Current number of people browsing your site'],
                ['label' => 'See which pages they\'re on', 'detail' => 'Real-time view of what visitors are looking at'],
                ['label' => 'Check traffic source', 'detail' => 'Where current visitors came from'],
            ],
            'tips' => ['💡 Check realtime during campaigns to see immediate impact'],
        ],
        '/admin/notifications' => [
            'title' => 'Notifications', 'icon' => '🔔',
            'intro' => 'System notifications — new orders, comments, signups, etc.',
            'steps' => [
                ['label' => 'Review unread notifications', 'detail' => 'New events that need your attention'],
                ['label' => 'Click to take action', 'detail' => 'Each notification links to the relevant page'],
                ['label' => 'Mark as read', 'detail' => 'Clear notifications after reviewing'],
            ],
            'tips' => ['💡 Check notifications daily to stay responsive'],
        ],
        '/admin/ab-testing' => [
            'title' => 'A/B Testing', 'icon' => '🧪',
            'intro' => 'Run A/B tests to optimize your pages and content.',
            'steps' => [
                ['label' => 'Create a new test', 'detail' => 'Define variant A and B, set the conversion goal'],
                ['label' => 'Set traffic split', 'detail' => 'Typically 50/50 between variants'],
                ['label' => 'Monitor results', 'detail' => 'Track which variant performs better'],
                ['label' => 'End test and apply winner', 'detail' => 'When statistically significant, apply the winning variant'],
            ],
            'tips' => ['💡 Test one change at a time for clear results', '💡 Run tests for at least 2 weeks for reliable data'],
        ],
        '/admin/ab-testing/create' => [
            'title' => 'Create A/B Test', 'icon' => '➕',
            'intro' => 'Set up a new A/B test with two variants.',
            'steps' => [
                ['label' => 'Name your test', 'detail' => 'Descriptive: "Homepage CTA Button Color"'],
                ['label' => 'Define variants', 'detail' => 'Variant A (control) and Variant B (change)'],
                ['label' => 'Set conversion goal', 'detail' => 'Click, form submit, purchase — what counts as success'],
                ['label' => 'Set traffic allocation', 'detail' => 'Percentage of visitors who see each variant'],
                ['label' => 'Start the test', 'detail' => 'Test goes live immediately for visitors'],
            ],
            'tips' => ['💡 Change only ONE element per test — button text, color, or layout'],
        ],
        '/admin/popups' => [
            'title' => 'Pop-ups', 'icon' => '💬',
            'intro' => 'Manage website pop-ups for promotions, email capture, and announcements.',
            'steps' => [
                ['label' => 'Click "+ New Popup"', 'detail' => 'Create a popup with trigger rules and content'],
                ['label' => 'View active popups', 'detail' => 'See which popups are currently live'],
                ['label' => 'Check conversion rates', 'detail' => 'Views vs. clicks/submissions for each popup'],
            ],
            'tips' => ['💡 Don\'t show popups immediately — wait 5-10 seconds or on exit intent'],
        ],
        '/admin/popups/create' => [
            'title' => 'Create Popup', 'icon' => '➕',
            'intro' => 'Design a new popup with targeting and trigger rules.',
            'steps' => [
                ['label' => 'Set trigger', 'detail' => 'Time delay, scroll depth, exit intent, or page-specific'],
                ['label' => 'Design content', 'detail' => 'Headline, text, image, form fields, CTA button'],
                ['label' => 'Set targeting', 'detail' => 'Which pages, devices, or visitor types see the popup'],
                ['label' => 'Set frequency', 'detail' => 'Show once, once per session, or every visit'],
                ['label' => 'Activate', 'detail' => 'Enable the popup — it goes live immediately'],
            ],
            'tips' => ['💡 Exit-intent popups convert best for email capture', '💡 Keep popup content concise — one clear CTA'],
        ],

        // ─── SaaS ───
        '/admin/saas' => [
            'title' => 'SaaS Dashboard', 'icon' => '☁️',
            'intro' => 'White-label SaaS platform — AI tools for your clients.',
            'steps' => [
                ['label' => 'Review active tools', 'detail' => 'SEO Writer, Copywriter, Image Studio, Social, Email, Analytics'],
                ['label' => 'Manage client users', 'detail' => 'View, add, or disable SaaS client accounts'],
                ['label' => 'Set pricing plans', 'detail' => 'Create subscription tiers for clients'],
                ['label' => 'Track revenue', 'detail' => 'Monthly recurring revenue and subscriber metrics'],
            ],
            'tips' => ['💡 Each tool has a frontend app at /saas/{tool}', '💡 Clients authenticate via API key (X-API-Key header)'],
        ],

        // ─── Design ───
        '/admin/themes' => [
            'title' => 'Themes', 'icon' => '🎨',
            'intro' => 'Browse, preview, and activate visual themes for your website.',
            'steps' => [
                ['label' => 'Browse available themes', 'detail' => '5 built-in themes + any AI-generated themes'],
                ['label' => 'Click "Preview" to see how it looks', 'detail' => 'Preview shows your actual content in the new theme'],
                ['label' => 'Click "Activate" to apply', 'detail' => 'Site immediately uses the new theme'],
            ],
            'tips' => ['💡 Generate unique themes with AI Theme Builder!', '💡 Fine-tune with Theme Studio after activating'],
        ],
        '/admin/ai-theme-builder' => [
            'title' => 'AI Theme Builder', 'icon' => '🤖',
            'intro' => 'Generate a completely custom, unique theme for your website using AI.',
            'steps' => [
                ['label' => 'Click "Create New Theme" to start wizard', 'detail' => 'Opens the step-by-step theme creation wizard'],
                ['label' => 'Select industry (130+ options)', 'detail' => 'AI tailors design, content, and images to your business'],
                ['label' => 'Choose style, colors, and language', 'detail' => 'Style preset, color palette, and site language'],
                ['label' => 'Build theme (1-2 min)', 'detail' => 'AI generates header, footer, all pages, and styles'],
                ['label' => 'Preview and activate', 'detail' => 'Review result, then activate or rebuild with different settings'],
            ],
            'tips' => ['💡 Use Claude or GPT-4+ for best quality', '💡 You can generate unlimited themes — experiment!', '💡 Fine-tune after with Theme Studio'],
        ],
        '/admin/ai-theme-builder/wizard' => [
            'title' => 'Theme Wizard', 'icon' => '🧙',
            'intro' => 'Step-by-step guided theme creation — industry, style, and generation.',
            'steps' => [
                ['label' => 'Step 1: Choose industry', 'detail' => 'Select your business type from 130+ options'],
                ['label' => 'Step 2: Customize style', 'detail' => 'Colors, fonts, style preset, language, and creativity level'],
                ['label' => 'Step 3: Review brief', 'detail' => 'Confirm all settings before building'],
                ['label' => 'Step 4: Build', 'detail' => 'AI generates the theme — wait 1-2 minutes'],
                ['label' => 'Step 5: Preview & Activate', 'detail' => 'Browse generated pages and activate when happy'],
            ],
            'tips' => ['💡 Higher creativity = more unique but less predictable results'],
        ],
        '/admin/ai-theme-builder/preview' => [
            'title' => 'Theme Preview', 'icon' => '👁️',
            'intro' => 'Preview a generated theme before activating it.',
            'steps' => [
                ['label' => 'Browse all generated pages', 'detail' => 'Home, About, Services, Contact — check each one'],
                ['label' => 'Check responsive view', 'detail' => 'Resize browser to test mobile appearance'],
                ['label' => 'Activate or go back', 'detail' => 'Happy? Activate. Not quite? Rebuild with different settings.'],
            ],
            'tips' => ['💡 Check header and footer consistency across all pages'],
        ],
        '/admin/theme-studio' => [
            'title' => 'Theme Studio', 'icon' => '🎯',
            'intro' => 'Visually customize your active theme — colors, fonts, spacing, and more.',
            'steps' => [
                ['label' => 'Select customization tool', 'detail' => 'Colors, fonts, spacing, logo, header/footer, custom CSS'],
                ['label' => 'Use color picker', 'detail' => 'Change primary, secondary, and accent colors'],
                ['label' => 'Adjust fonts', 'detail' => 'Choose heading and body fonts from 100+ options'],
                ['label' => 'Preview live', 'detail' => 'See changes in real-time before saving'],
                ['label' => 'Save changes', 'detail' => 'Changes apply to your live site immediately'],
            ],
            'tips' => ['💡 Stick to 2-3 colors for professional look', '💡 Test on mobile after changes'],
        ],
        '/admin/theme-studio/preview' => [
            'title' => 'Studio Preview', 'icon' => '👁️',
            'intro' => 'Live preview of Theme Studio customizations.',
            'steps' => [
                ['label' => 'Review all changes', 'detail' => 'See how your customizations look on the live site'],
                ['label' => 'Test different pages', 'detail' => 'Navigate through pages in the preview'],
                ['label' => 'Apply or discard', 'detail' => 'Save changes or revert to previous state'],
            ],
            'tips' => ['💡 Check on multiple browser sizes before saving'],
        ],
        '/admin/jessie-theme-builder' => [
            'title' => 'Page Builder (JTB)', 'icon' => '🏗️',
            'intro' => 'Drag & drop page builder with 79 modules for advanced page layouts.',
            'steps' => [
                ['label' => 'Select a page to edit', 'detail' => 'Choose existing page or create new one'],
                ['label' => 'Drag modules from sidebar', 'detail' => '79 modules: heading, image, gallery, pricing, FAQ, testimonials, hero, CTA, etc.'],
                ['label' => 'Click any module to edit', 'detail' => 'Each module has content fields and style options'],
                ['label' => 'Arrange by dragging sections', 'detail' => 'Reorder page sections to build your layout'],
                ['label' => 'Preview and save', 'detail' => 'Check result and publish'],
            ],
            'tips' => ['💡 Start with Section → then add content inside', '💡 Save layouts as templates for reuse', '💡 Use Full Width modules for impact: Hero, CTA, Testimonials'],
        ],
        '/admin/website-builder' => [
            'title' => 'Website Builder', 'icon' => '🌐',
            'intro' => 'AI generates an entire multi-page website in one go.',
            'steps' => [
                ['label' => 'Describe your website', 'detail' => 'Business type, pages needed, style preferences'],
                ['label' => 'AI builds all pages', 'detail' => 'Multi-agent pipeline: mockup → architecture → content → styling → SEO'],
                ['label' => 'Review generated pages', 'detail' => 'Preview all pages before applying'],
                ['label' => 'Apply to your site', 'detail' => 'Save all pages and layouts to CMS'],
            ],
            'tips' => ['💡 Fastest way to build a complete website from scratch'],
        ],

        // ─── System ───
        '/admin/settings' => [
            'title' => 'General Settings', 'icon' => '⚙️',
            'intro' => 'Core site configuration — name, URL, timezone, SMTP, and more.',
            'steps' => [
                ['label' => 'Set site name and tagline', 'detail' => 'Appears in browser tab, search results, emails'],
                ['label' => 'Set site URL', 'detail' => 'Must match your actual domain — wrong URL breaks everything'],
                ['label' => 'Configure timezone', 'detail' => 'Affects publishing times, events, and scheduling'],
                ['label' => 'Set up SMTP email', 'detail' => 'Required for sending any emails from the CMS'],
            ],
            'tips' => ['💡 Configure SMTP first — many features need email', '💡 Always verify site URL is correct'],
        ],
        '/admin/users' => [
            'title' => 'Users', 'icon' => '👥',
            'intro' => 'Manage admin users, roles, and permissions.',
            'steps' => [
                ['label' => 'View all users', 'detail' => 'See usernames, roles, and last login'],
                ['label' => 'Click "+ New User" to add', 'detail' => 'Set name, email, password, and role'],
                ['label' => 'Edit user roles', 'detail' => 'Admin (full), Editor (content), Viewer (read-only)'],
            ],
            'tips' => ['💡 Change default admin password immediately!', '💡 Give only the access needed — least privilege'],
        ],
        '/admin/users/create' => [
            'title' => 'Create User', 'icon' => '➕',
            'intro' => 'Add a new admin user account.',
            'steps' => [
                ['label' => 'Enter username and email', 'detail' => 'Username for login, email for notifications'],
                ['label' => 'Set a strong password', 'detail' => 'At least 8 characters with mix of letters, numbers, symbols'],
                ['label' => 'Assign a role', 'detail' => 'Admin: full access | Editor: content only | Viewer: read-only'],
                ['label' => 'Save', 'detail' => 'User can login immediately with these credentials'],
            ],
            'tips' => ['💡 Share credentials securely — never via plain email'],
        ],
        '/admin/plugins' => [
            'title' => 'Plugins', 'icon' => '🧩',
            'intro' => 'Install, enable, and configure plugins to add features.',
            'steps' => [
                ['label' => 'Browse available plugins', 'detail' => 'See all plugins with descriptions and status'],
                ['label' => 'Toggle on/off', 'detail' => 'Enable plugins you need, disable ones you don\'t'],
                ['label' => 'Configure settings', 'detail' => 'Some plugins have their own settings page'],
            ],
            'tips' => ['💡 Fewer active plugins = faster site', '💡 Each plugin adds its section to the navigation'],
        ],
        '/admin/plugins-marketplace' => [
            'title' => 'Plugin Marketplace', 'icon' => '🛒',
            'intro' => 'Browse and discover new plugins to add features.',
            'steps' => [
                ['label' => 'Browse by category', 'detail' => 'Commerce, content, marketing, etc.'],
                ['label' => 'Read plugin descriptions', 'detail' => 'Understand what each plugin does before installing'],
                ['label' => 'Click "Install" to add', 'detail' => 'Plugin is installed and ready to enable'],
            ],
            'tips' => ['💡 Read reviews before installing'],
        ],
        '/admin/modules' => [
            'title' => 'Modules', 'icon' => '📦',
            'intro' => 'Core CMS modules that can be enabled or disabled.',
            'steps' => [
                ['label' => 'View available modules', 'detail' => 'Core features like comments, forms, SEO, etc.'],
                ['label' => 'Toggle modules on/off', 'detail' => 'Disable unused modules to simplify the admin panel'],
            ],
            'tips' => ['💡 Disable modules you don\'t use to reduce clutter'],
        ],
        '/admin/security-dashboard' => [
            'title' => 'Security Dashboard', 'icon' => '🔒',
            'intro' => 'Security overview — login activity, threats, and 2FA status.',
            'steps' => [
                ['label' => 'Review login activity', 'detail' => 'See recent login attempts — successful and failed'],
                ['label' => 'Enable 2FA', 'detail' => 'Two-factor authentication for admin accounts'],
                ['label' => 'Block suspicious IPs', 'detail' => 'Block IPs with too many failed login attempts'],
                ['label' => 'Review active sessions', 'detail' => 'See who\'s currently logged in and end sessions if needed'],
            ],
            'tips' => ['💡 Enable 2FA for ALL admin accounts', '💡 Review login activity monthly for suspicious patterns'],
        ],
        '/admin/gdpr-tools' => [
            'title' => 'GDPR Tools', 'icon' => '🛡️',
            'intro' => 'Privacy compliance — data export, deletion, and consent management.',
            'steps' => [
                ['label' => 'Handle data export requests', 'detail' => 'Export all data for a specific user'],
                ['label' => 'Process deletion requests', 'detail' => 'Remove all personal data for a user'],
                ['label' => 'Manage consent records', 'detail' => 'View and manage consent given by users'],
            ],
            'tips' => ['💡 Respond to GDPR requests within 30 days as required by law'],
        ],
        '/admin/gdpr' => [
            'title' => 'GDPR Settings', 'icon' => '🛡️',
            'intro' => 'Configure GDPR compliance — cookie notice, privacy policy, consent.',
            'steps' => [
                ['label' => 'Set up cookie notice', 'detail' => 'Configure the cookie consent banner for visitors'],
                ['label' => 'Link privacy policy', 'detail' => 'Set the URL to your privacy policy page'],
                ['label' => 'Configure data retention', 'detail' => 'How long user data is stored before auto-deletion'],
            ],
            'tips' => ['💡 Cookie notice is legally required in the EU/UK'],
        ],
        '/admin/api-keys' => [
            'title' => 'API Keys', 'icon' => '🔑',
            'intro' => 'Manage API keys for external integrations and SaaS access.',
            'steps' => [
                ['label' => 'Generate a new key', 'detail' => 'Click "+ New Key" — set name and permissions'],
                ['label' => 'Copy the key securely', 'detail' => 'Key shown once — copy and store safely'],
                ['label' => 'Revoke unused keys', 'detail' => 'Delete keys no longer in use for security'],
            ],
            'tips' => ['💡 Rotate keys periodically', '💡 Never share API keys in plain text'],
        ],
        '/admin/languages' => [
            'title' => 'Languages', 'icon' => '🌐',
            'intro' => 'Multi-language support — add languages and translate interface.',
            'steps' => [
                ['label' => 'Add languages', 'detail' => 'Select languages to support on your site'],
                ['label' => 'Translate interface strings', 'detail' => 'Translate menu items, buttons, and system messages'],
                ['label' => 'Set default language', 'detail' => 'Primary language for your site'],
            ],
            'tips' => ['💡 Use AI Translate for quick content translation'],
        ],
        '/admin/white-label' => [
            'title' => 'White Label', 'icon' => '🏷️',
            'intro' => 'Rebrand the CMS — your logo, name, and colors.',
            'steps' => [
                ['label' => 'Upload your logo', 'detail' => 'Replaces Jessie logo throughout admin panel'],
                ['label' => 'Set brand name', 'detail' => 'Appears in header, login page, and page titles'],
                ['label' => 'Choose accent color', 'detail' => 'Primary UI color throughout the admin'],
                ['label' => 'Customize login page', 'detail' => 'Brand the admin login screen'],
            ],
            'tips' => ['💡 Perfect for agencies delivering branded CMS to clients', '💡 Use SVG logos for crisp display'],
        ],
        '/admin/logs' => [
            'title' => 'System Logs', 'icon' => '📋',
            'intro' => 'View system logs — errors, warnings, and activity.',
            'steps' => [
                ['label' => 'Review recent entries', 'detail' => 'See errors and warnings sorted by date'],
                ['label' => 'Filter by severity', 'detail' => 'Error, Warning, Info, Debug'],
                ['label' => 'Clear old logs', 'detail' => 'Remove old entries to keep things clean'],
            ],
            'tips' => ['💡 Check logs after updates or changes to catch issues early'],
        ],
        '/admin/logs/files' => [
            'title' => 'Log Files', 'icon' => '📄',
            'intro' => 'Browse and download raw log files.',
            'steps' => [
                ['label' => 'Select a log file', 'detail' => 'Choose from available log files by date'],
                ['label' => 'View contents', 'detail' => 'Read raw log entries'],
                ['label' => 'Download', 'detail' => 'Download log file for external analysis'],
            ],
            'tips' => ['💡 Share log files with support when troubleshooting'],
        ],
        '/admin/logs/view' => [
            'title' => 'Log Viewer', 'icon' => '🔍',
            'intro' => 'Detailed log viewer with search and filtering.',
            'steps' => [
                ['label' => 'Search log entries', 'detail' => 'Search by keyword, error code, or date'],
                ['label' => 'Filter by level', 'detail' => 'Show only errors, warnings, or info messages'],
                ['label' => 'Click entry for details', 'detail' => 'See full stack trace and context'],
            ],
            'tips' => ['💡 Search for "ERROR" to find critical issues quickly'],
        ],
        '/admin/backup' => [
            'title' => 'Backups', 'icon' => '💾',
            'intro' => 'Create and restore database and file backups.',
            'steps' => [
                ['label' => 'Click "Create Backup"', 'detail' => 'Creates snapshot of database and files'],
                ['label' => 'Download backup', 'detail' => 'Store off-server: cloud, local drive, or external disk'],
                ['label' => 'Schedule automatic backups', 'detail' => 'Set daily or weekly automatic backups'],
                ['label' => 'Restore from backup', 'detail' => 'Upload a backup file to restore to a previous state'],
            ],
            'tips' => ['💡 ALWAYS backup before major changes', '💡 Keep 3+ recent backups at all times'],
        ],
        '/admin/scheduler' => [
            'title' => 'Task Scheduler', 'icon' => '⏰',
            'intro' => 'Schedule automated tasks — backups, cache clearing, reports.',
            'steps' => [
                ['label' => 'View scheduled tasks', 'detail' => 'See all active cron jobs and their next run time'],
                ['label' => 'Create a new task', 'detail' => 'Set action, schedule (daily/weekly/monthly), and time'],
                ['label' => 'Edit or disable tasks', 'detail' => 'Pause tasks without deleting them'],
            ],
            'tips' => ['💡 Schedule backups to run daily at off-peak hours'],
        ],
        '/admin/scheduler/create' => [
            'title' => 'Create Task', 'icon' => '➕',
            'intro' => 'Schedule a new automated task.',
            'steps' => [
                ['label' => 'Select task type', 'detail' => 'Backup, cache clear, email digest, report generation'],
                ['label' => 'Set schedule', 'detail' => 'Daily, weekly, monthly, or custom cron expression'],
                ['label' => 'Set time', 'detail' => 'When the task should run'],
                ['label' => 'Enable and save', 'detail' => 'Task runs automatically on schedule'],
            ],
            'tips' => ['💡 Run heavy tasks at night to avoid affecting site performance'],
        ],
        '/admin/updates' => [
            'title' => 'Updates', 'icon' => '🔄',
            'intro' => 'Check for and apply CMS updates.',
            'steps' => [
                ['label' => 'Check for updates', 'detail' => 'See if a newer CMS version is available'],
                ['label' => 'Review changelog', 'detail' => 'Read what\'s new before updating'],
                ['label' => 'Backup first!', 'detail' => 'Always create a backup before updating'],
                ['label' => 'Apply update', 'detail' => 'Click to update — may take a few minutes'],
            ],
            'tips' => ['💡 ALWAYS backup before updating', '💡 Test after updating to ensure everything works'],
        ],
        '/admin/version-control' => [
            'title' => 'Version Control', 'icon' => '📜',
            'intro' => 'Track content changes and revert to previous versions.',
            'steps' => [
                ['label' => 'View revision history', 'detail' => 'See all saved versions of pages and articles'],
                ['label' => 'Compare versions', 'detail' => 'Side-by-side diff between two versions'],
                ['label' => 'Restore a version', 'detail' => 'Revert content to a previous state'],
            ],
            'tips' => ['💡 Versions are saved automatically on each edit'],
        ],
        '/admin/maintenance' => [
            'title' => 'Maintenance Mode', 'icon' => '🔧',
            'intro' => 'Put your site in maintenance mode while making changes.',
            'steps' => [
                ['label' => 'Enable maintenance mode', 'detail' => 'Visitors see a "Site under maintenance" page'],
                ['label' => 'Set maintenance message', 'detail' => 'Custom message and expected return time'],
                ['label' => 'Disable when done', 'detail' => 'Site goes back to normal for visitors'],
            ],
            'tips' => ['💡 Use during major updates or content migrations', '💡 Admins can still access the site normally'],
        ],
        '/admin/profile' => [
            'title' => 'Your Profile', 'icon' => '👤',
            'intro' => 'Manage your admin account — name, email, and avatar.',
            'steps' => [
                ['label' => 'Update display name', 'detail' => 'Name shown in the admin panel and content attribution'],
                ['label' => 'Update email', 'detail' => 'Used for login and notifications'],
                ['label' => 'Upload avatar', 'detail' => 'Profile picture shown in the admin UI'],
            ],
            'tips' => ['💡 Use a recognizable name and photo for team collaboration'],
        ],
        '/admin/profile/password' => [
            'title' => 'Change Password', 'icon' => '🔐',
            'intro' => 'Update your admin account password.',
            'steps' => [
                ['label' => 'Enter current password', 'detail' => 'Verify your identity first'],
                ['label' => 'Enter new password', 'detail' => 'At least 8 characters, mix of letters/numbers/symbols'],
                ['label' => 'Confirm new password', 'detail' => 'Type it again to prevent typos'],
                ['label' => 'Save', 'detail' => 'Password updated — use new password next login'],
            ],
            'tips' => ['💡 Change your password every 3-6 months', '💡 Use a password manager for strong unique passwords'],
        ],
        '/admin/search' => [
            'title' => 'Site Search', 'icon' => '🔍',
            'intro' => 'Configure and manage your site\'s internal search.',
            'steps' => [
                ['label' => 'Review search settings', 'detail' => 'What content types are searchable'],
                ['label' => 'View popular searches', 'detail' => 'See what visitors are searching for'],
                ['label' => 'Optimize results', 'detail' => 'Boost certain content in search results'],
            ],
            'tips' => ['💡 Popular searches with no results = content gaps to fill'],
        ],
        '/admin/search/analytics' => [
            'title' => 'Search Analytics', 'icon' => '📊',
            'intro' => 'What visitors are searching for on your site.',
            'steps' => [
                ['label' => 'View top search queries', 'detail' => 'Most frequently searched terms'],
                ['label' => 'Check zero-result searches', 'detail' => 'Queries that returned no results — content gaps!'],
                ['label' => 'Track click-through rates', 'detail' => 'Which results visitors actually click on'],
            ],
            'tips' => ['💡 Create content for popular zero-result searches — instant SEO win'],
        ],
        '/admin/urls' => [
            'title' => 'URL Management', 'icon' => '🔗',
            'intro' => 'Manage URL redirects and custom short URLs.',
            'steps' => [
                ['label' => 'View existing redirects', 'detail' => 'List of all URL redirects (301, 302)'],
                ['label' => 'Add new redirect', 'detail' => 'Redirect old URLs to new ones (important for SEO)'],
                ['label' => 'Delete obsolete redirects', 'detail' => 'Clean up redirects no longer needed'],
            ],
            'tips' => ['💡 Use 301 redirects when content permanently moves — preserves SEO'],
        ],
        '/admin/urls/create' => [
            'title' => 'Create Redirect', 'icon' => '➕',
            'intro' => 'Create a URL redirect from an old path to a new one.',
            'steps' => [
                ['label' => 'Enter source URL', 'detail' => 'The old URL path that should redirect'],
                ['label' => 'Enter destination URL', 'detail' => 'Where visitors should be sent'],
                ['label' => 'Choose redirect type', 'detail' => '301 (permanent) or 302 (temporary)'],
                ['label' => 'Save', 'detail' => 'Redirect is active immediately'],
            ],
            'tips' => ['💡 301 for permanent moves, 302 for temporary — 301 passes SEO value'],
        ],
        '/admin/n8n-settings' => [
            'title' => 'Workflow Settings', 'icon' => '🔗',
            'intro' => 'Configure n8n workflow integration — connection and webhook URLs.',
            'steps' => [
                ['label' => 'Set n8n server URL', 'detail' => 'Where your n8n instance is running'],
                ['label' => 'Configure authentication', 'detail' => 'API key or credentials for n8n access'],
                ['label' => 'Test connection', 'detail' => 'Verify CMS can communicate with n8n'],
            ],
            'tips' => ['💡 n8n handles complex automations beyond what built-in automations can do'],
        ],
        '/admin/n8n-bindings' => [
            'title' => 'Workflow Bindings', 'icon' => '🔗',
            'intro' => 'Connect CMS events to n8n workflows — triggers and actions.',
            'steps' => [
                ['label' => 'View existing bindings', 'detail' => 'See which CMS events trigger which workflows'],
                ['label' => 'Create new binding', 'detail' => 'Map a CMS event (new order, form submit) to a workflow'],
                ['label' => 'Test a binding', 'detail' => 'Trigger the event and verify the workflow runs'],
            ],
            'tips' => ['💡 Popular: new order → email notification, form submit → CRM entry'],
        ],
        '/admin/automations' => [
            'title' => 'Automations', 'icon' => '🤖',
            'intro' => 'Set up automated actions triggered by CMS events.',
            'steps' => [
                ['label' => 'View active automations', 'detail' => 'See all configured automation rules'],
                ['label' => 'Create new automation', 'detail' => 'Set trigger event and action to perform'],
                ['label' => 'Enable/disable', 'detail' => 'Toggle automations without deleting'],
            ],
            'tips' => ['💡 Start simple: auto-email on form submission or new order'],
        ],
        '/admin/automation-rules' => [
            'title' => 'Automation Rules', 'icon' => '📋',
            'intro' => 'Define rules for automatic actions — conditions and actions.',
            'steps' => [
                ['label' => 'Create a rule', 'detail' => 'If [trigger] + [condition] then [action]'],
                ['label' => 'Set trigger', 'detail' => 'New order, form submit, user signup, page view, etc.'],
                ['label' => 'Add conditions (optional)', 'detail' => 'Only run when: order > $50, user is VIP, etc.'],
                ['label' => 'Define action', 'detail' => 'Send email, update record, notify admin, call webhook'],
            ],
            'tips' => ['💡 Test rules with small triggers first before applying broadly'],
        ],
        '/admin/docs' => [
            'title' => 'Documentation', 'icon' => '📖',
            'intro' => 'Built-in CMS documentation and help resources.',
            'steps' => [
                ['label' => 'Browse by topic', 'detail' => 'Content, design, SEO, commerce, plugins, and more'],
                ['label' => 'Search for answers', 'detail' => 'Use the search bar to find specific help topics'],
                ['label' => 'Follow tutorials', 'detail' => 'Step-by-step guides for common tasks'],
            ],
            'tips' => ['💡 Check docs before asking support — answer might be here'],
        ],
        '/admin/extensions' => [
            'title' => 'Extensions', 'icon' => '🔌',
            'intro' => 'Manage third-party extensions and integrations.',
            'steps' => [
                ['label' => 'Browse installed extensions', 'detail' => 'See all active third-party integrations'],
                ['label' => 'Configure extension settings', 'detail' => 'Click an extension to adjust its configuration'],
                ['label' => 'Add new extensions', 'detail' => 'Install additional integrations'],
            ],
            'tips' => ['💡 Keep extensions updated for security and compatibility'],
        ],
        '/admin/migrations' => [
            'title' => 'Database Migrations', 'icon' => '🗄️',
            'intro' => 'Manage database schema migrations and updates.',
            'steps' => [
                ['label' => 'View migration status', 'detail' => 'See which migrations have been applied'],
                ['label' => 'Run pending migrations', 'detail' => 'Apply new database changes'],
                ['label' => 'Check for issues', 'detail' => 'Verify all tables are up to date'],
            ],
            'tips' => ['💡 Always backup before running migrations'],
        ],
    ];
}
