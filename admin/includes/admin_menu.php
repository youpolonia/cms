<?php
/**
 * Centralized Admin Menu Configuration v2.0 — Mega-Menu
 * Reduced from 24 → 8 top-level items with logical grouping
 * Dynamic: reads installed_plugins.json to show/hide module sections
 */

// ── Helper: check if a plugin is enabled ──
$_installedPlugins = null;
function _pluginEnabled(string $slug): bool {
    global $_installedPlugins;
    if ($_installedPlugins === null) {
        $path = (defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__, 2)) . '/config/installed_plugins.json';
        $_installedPlugins = file_exists($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];
    }
    return !empty($_installedPlugins[$slug]['enabled']);
}

// ── Build module sections dynamically ──
$moduleSections = [];

if (_pluginEnabled('jessie-lms')) {
    $moduleSections[] = [
        'title' => '🎓 LMS',
        'items' => [
            ['label' => 'Dashboard', 'url' => '/admin/lms'],
            ['label' => 'Courses', 'url' => '/admin/lms/courses'],
        ]
    ];
}
if (_pluginEnabled('jessie-booking')) {
    $moduleSections[] = [
        'title' => '📅 Booking',
        'items' => [
            ['label' => 'Dashboard', 'url' => '/admin/booking'],
            ['label' => 'Services', 'url' => '/admin/booking/services'],
            ['label' => 'Staff', 'url' => '/admin/booking/staff'],
            ['label' => 'Calendar', 'url' => '/admin/booking/calendar'],
            ['label' => 'Appointments', 'url' => '/admin/booking/appointments'],
            ['label' => 'Settings', 'url' => '/admin/booking/settings'],
        ]
    ];
}
if (_pluginEnabled('jessie-events')) {
    $moduleSections[] = [
        'title' => '🎫 Events',
        'items' => [
            ['label' => 'Dashboard', 'url' => '/admin/events'],
            ['label' => 'Events', 'url' => '/admin/events/list'],
            ['label' => 'Orders', 'url' => '/admin/events/orders'],
            ['label' => 'Settings', 'url' => '/admin/events/settings'],
        ]
    ];
}
if (_pluginEnabled('jessie-membership')) {
    $moduleSections[] = [
        'title' => '🔑 Membership',
        'items' => [
            ['label' => 'Dashboard', 'url' => '/admin/membership'],
            ['label' => 'Plans', 'url' => '/admin/membership/plans'],
            ['label' => 'Members', 'url' => '/admin/membership/members'],
            ['label' => 'Content Rules', 'url' => '/admin/membership/content'],
        ]
    ];
}
if (_pluginEnabled('jessie-newsletter')) {
    $moduleSections[] = [
        'title' => '📧 Newsletter',
        'items' => [
            ['label' => 'Dashboard', 'url' => '/admin/newsletter'],
            ['label' => 'Campaigns', 'url' => '/admin/newsletter/campaigns'],
            ['label' => 'Subscribers', 'url' => '/admin/newsletter/subscribers'],
            ['label' => 'Lists', 'url' => '/admin/newsletter/lists'],
            ['label' => 'Templates', 'url' => '/admin/newsletter/templates'],
        ]
    ];
}
if (_pluginEnabled('jessie-directory')) {
    $moduleSections[] = [
        'title' => '📍 Directory',
        'items' => [
            ['label' => 'Dashboard', 'url' => '/admin/directory'],
            ['label' => 'Listings', 'url' => '/admin/directory/listings'],
            ['label' => 'Categories', 'url' => '/admin/directory/categories'],
            ['label' => 'Reviews', 'url' => '/admin/directory/reviews'],
            ['label' => 'Claims', 'url' => '/admin/directory/claims'],
        ]
    ];
}
if (_pluginEnabled('jessie-jobs')) {
    $moduleSections[] = [
        'title' => '💼 Jobs',
        'items' => [
            ['label' => 'Dashboard', 'url' => '/admin/jobs'],
            ['label' => 'Listings', 'url' => '/admin/jobs/listings'],
            ['label' => 'Applications', 'url' => '/admin/jobs/applications'],
            ['label' => 'Companies', 'url' => '/admin/jobs/companies'],
        ]
    ];
}
if (_pluginEnabled('jessie-realestate')) {
    $moduleSections[] = [
        'title' => '🏠 Real Estate',
        'items' => [
            ['label' => 'Dashboard', 'url' => '/admin/realestate'],
            ['label' => 'Properties', 'url' => '/admin/realestate/properties'],
            ['label' => 'Agents', 'url' => '/admin/realestate/agents'],
            ['label' => 'Inquiries', 'url' => '/admin/realestate/inquiries'],
        ]
    ];
}
if (_pluginEnabled('jessie-portfolio')) {
    $moduleSections[] = [
        'title' => '🎨 Portfolio',
        'items' => [
            ['label' => 'Dashboard', 'url' => '/admin/portfolio'],
            ['label' => 'Projects', 'url' => '/admin/portfolio/projects'],
            ['label' => 'Categories', 'url' => '/admin/portfolio/categories'],
            ['label' => 'Testimonials', 'url' => '/admin/portfolio/testimonials'],
        ]
    ];
}
if (_pluginEnabled('jessie-affiliate')) {
    $moduleSections[] = [
        'title' => '🤝 Affiliate',
        'items' => [
            ['label' => 'Dashboard', 'url' => '/admin/affiliate'],
            ['label' => 'Programs', 'url' => '/admin/affiliate/programs'],
            ['label' => 'Affiliates', 'url' => '/admin/affiliate/affiliates'],
            ['label' => 'Conversions', 'url' => '/admin/affiliate/conversions'],
            ['label' => 'Payouts', 'url' => '/admin/affiliate/payouts'],
        ]
    ];
}

// CRM is core, always visible
$moduleSections[] = [
    'title' => '👥 CRM',
    'items' => [
        ['label' => 'Dashboard', 'url' => '/admin/crm'],
        ['label' => 'Contacts', 'url' => '/admin/crm/contacts'],
        ['label' => 'Pipeline', 'url' => '/admin/crm/pipeline'],
    ]
];

// ── Build commerce sections dynamically ──
$commerceSections = [
    [
        'title' => '🛒 Shop',
        'items' => [
            ['label' => 'Dashboard', 'url' => '/admin/shop'],
            ['label' => 'Products', 'url' => '/admin/shop/products'],
            ['label' => 'Categories', 'url' => '/admin/shop/categories'],
            ['label' => 'Orders', 'url' => '/admin/shop/orders'],
            ['label' => 'Reviews', 'url' => '/admin/shop/reviews'],
            ['label' => 'Coupons', 'url' => '/admin/shop/coupons'],
            ['label' => 'Abandoned Carts', 'url' => '/admin/shop/abandoned-carts'],
            ['label' => 'Analytics', 'url' => '/admin/shop/analytics'],
            ['label' => 'AI SEO', 'url' => '/admin/shop/seo'],
            ['label' => 'Settings', 'url' => '/admin/shop/settings'],
        ]
    ]
];
if (_pluginEnabled('jessie-restaurant')) {
    $commerceSections[] = [
        'title' => '🍕 Restaurant',
        'items' => [
            ['label' => 'Dashboard', 'url' => '/admin/restaurant'],
            ['label' => 'Menu Items', 'url' => '/admin/restaurant/menu'],
            ['label' => 'Categories', 'url' => '/admin/restaurant/categories'],
            ['label' => 'Orders', 'url' => '/admin/restaurant/orders'],
            ['label' => 'Kitchen', 'url' => '/admin/restaurant/kitchen'],
            ['label' => 'Settings', 'url' => '/admin/restaurant/settings'],
        ]
    ];
}
$commerceSections[] = [
    'title' => '🚚 Dropshipping',
    'items' => [
        ['label' => 'Dashboard', 'url' => '/admin/dropshipping'],
        ['label' => 'Suppliers', 'url' => '/admin/dropshipping/suppliers'],
        ['label' => 'Products', 'url' => '/admin/dropshipping/products'],
        ['label' => 'Import', 'url' => '/admin/dropshipping/import'],
        ['label' => 'Price Rules', 'url' => '/admin/dropshipping/price-rules'],
        ['label' => 'Orders', 'url' => '/admin/dropshipping/orders'],
        ['label' => 'AI Research', 'url' => '/admin/dropshipping/research'],
        ['label' => 'Settings', 'url' => '/admin/dropshipping/settings'],
    ]
];

// ── Build SaaS sections dynamically ──
$saasSections = [];
$hasSaas = _pluginEnabled('jessie-saas-core');
if ($hasSaas) {
    $saasItems = [
        ['label' => 'SaaS Dashboard', 'url' => '/admin/saas'],
        ['label' => 'Users', 'url' => '/admin/saas/users'],
        ['label' => 'Plans', 'url' => '/admin/saas/plans'],
        ['label' => 'Revenue', 'url' => '/admin/saas/revenue'],
    ];
    if (_pluginEnabled('jessie-seowriter'))      $saasItems[] = ['label' => 'SEO Writer', 'url' => '/admin/seowriter'];
    if (_pluginEnabled('jessie-copywriter'))      $saasItems[] = ['label' => 'Copywriter', 'url' => '/admin/copywriter'];
    if (_pluginEnabled('jessie-imagestudio'))     $saasItems[] = ['label' => 'Image Studio', 'url' => '/admin/imagestudio'];
    if (_pluginEnabled('jessie-social'))          $saasItems[] = ['label' => 'Social Media', 'url' => '/admin/social'];
    if (_pluginEnabled('jessie-emailmarketing'))  $saasItems[] = ['label' => 'Email Marketing', 'url' => '/admin/emailmarketing'];
    if (_pluginEnabled('jessie-analytics'))       $saasItems[] = ['label' => 'Analytics', 'url' => '/admin/analytics'];

    $saasSections[] = [
        'title' => '☁️ SaaS Tools',
        'items' => $saasItems
    ];
}

// ── Main menu ──
return [
    // ─── 1. Dashboard ───
    'dashboard' => [
        'label' => '📊 Dashboard',
        'url'   => '/admin',
        'type'  => 'link'
    ],

    // ─── 2. Content ───
    'content' => [
        'label' => '📄 Content',
        'type'  => 'dropdown',
        'items' => [
            ['label' => '📄 Pages', 'url' => '/admin/pages'],
            ['label' => '📰 Articles', 'url' => '/admin/articles'],
            ['label' => '📁 Categories', 'url' => '/admin/categories'],
            ['label' => '🖼️ Media', 'url' => '/admin/media'],
            ['label' => '🎨 Galleries', 'url' => '/admin/galleries'],
            ['label' => '💬 Comments', 'url' => '/admin/comments'],
            ['label' => '📬 Contact Forms', 'url' => '/admin/contact-submissions'],
            ['label' => '📋 Navigation', 'url' => '/admin/menus'],
            ['label' => '🧩 Widgets', 'url' => '/admin/widgets'],
            ['label' => '📋 Form Builder', 'url' => '/admin/form-builder'],
            ['label' => '💡 Suggestions', 'url' => '/admin/content-suggestions'],
            ['label' => '📅 Calendar', 'url' => '/admin/content-calendar'],
        ]
    ],

    // ─── 3. AI & SEO ───
    'ai_seo' => [
        'label' => '🤖 AI & SEO',
        'type'  => 'mega',
        'badge' => 'AI',
        'columns' => 3,
        'sections' => [
            [
                'title' => '🎯 SEO',
                'items' => [
                    ['label' => 'SEO Assistant', 'url' => '/admin/ai-seo-assistant'],
                    ['label' => 'SEO Dashboard', 'url' => '/admin/ai-seo-dashboard'],
                    ['label' => 'Keywords', 'url' => '/admin/ai-seo-keywords'],
                    ['label' => 'Competitors', 'url' => '/admin/ai-seo-competitors'],
                    ['label' => 'Internal Links', 'url' => '/admin/ai-seo-linking'],
                    ['label' => 'Schema', 'url' => '/admin/ai-seo-schema'],
                    ['label' => 'Reports', 'url' => '/admin/ai-seo-reports'],
                    ['label' => 'Content Brief', 'url' => '/admin/ai-seo-brief'],
                    ['label' => 'Bulk Editor', 'url' => '/admin/ai-seo-bulk'],
                    ['label' => 'Content Decay', 'url' => '/admin/ai-seo-decay'],
                    ['label' => 'Image Alt Text', 'url' => '/admin/ai-seo-images'],
                    ['label' => 'Broken Links', 'url' => '/admin/ai-seo-links'],
                ]
            ],
            [
                'title' => '✨ AI Content',
                'items' => [
                    ['label' => 'Content Creator', 'url' => '/admin/ai-content-creator'],
                    ['label' => 'Copywriter', 'url' => '/admin/ai-copywriter'],
                    ['label' => 'Rewriter', 'url' => '/admin/ai-content-rewrite'],
                    ['label' => 'Translate', 'url' => '/admin/ai-translate'],
                    ['label' => 'AI Images', 'url' => '/admin/ai-images'],
                    ['label' => 'Alt Generator', 'url' => '/admin/ai-alt-generator'],
                    ['label' => 'AI Forms', 'url' => '/admin/ai-forms'],
                    ['label' => 'Landing Pages', 'url' => '/admin/ai-landing'],
                    ['label' => 'Quality Check', 'url' => '/admin/content-quality'],
                ]
            ],
            [
                'title' => '⚙️ AI System',
                'items' => [
                    ['label' => '🎓 AI Tutor', 'url' => '/admin/ai-tutor'],
                    ['label' => '💬 AI Assistant', 'url' => '/admin/ai-chat'],
                    ['label' => '🤖 AI Chatbot', 'url' => '/admin/chat-settings'],
                    ['label' => 'Email Campaign AI', 'url' => '/admin/ai-email-campaign'],
                    ['label' => 'Student Materials', 'url' => '/admin/ai-student-materials'],
                    ['label' => 'Workflow Gen', 'url' => '/admin/ai-workflow-generator'],
                    ['label' => 'AI Insights', 'url' => '/admin/ai-insights'],
                    ['label' => 'AI Logs', 'url' => '/admin/ai-logs'],
                    ['label' => 'AI Settings', 'url' => '/admin/ai-settings'],
                ]
            ],
        ]
    ],

    // ─── 4. Commerce ───
    'commerce' => [
        'label'    => '🛒 Commerce',
        'type'     => 'mega',
        'columns'  => count($commerceSections),
        'sections' => $commerceSections,
    ],

    // ─── 5. Modules ───
    'modules' => [
        'label'    => '📦 Modules',
        'type'     => 'mega',
        'columns'  => min(count($moduleSections), 4),
        'sections' => $moduleSections,
    ],

    // ─── 6. Marketing ───
    'marketing' => [
        'label' => '📢 Marketing',
        'type'  => 'mega',
        'columns' => $hasSaas ? 2 : 1,
        'sections' => array_merge(
            [
                [
                    'title' => '📈 Marketing',
                    'items' => [
                        ['label' => 'Email Campaigns', 'url' => '/admin/email-campaigns'],
                        ['label' => 'Email Queue', 'url' => '/admin/email-queue'],
                        ['label' => 'Email Settings', 'url' => '/admin/email-settings'],
                        ['label' => 'Social Media', 'url' => '/admin/social-media'],
                        ['label' => 'Social Calendar', 'url' => '/admin/social-media/calendar'],
                        ['label' => 'Analytics', 'url' => '/admin/analytics'],
                        ['label' => 'Notifications', 'url' => '/admin/notifications'],
                        ['label' => 'A/B Testing', 'url' => '/admin/ab-testing'],
                        ['label' => 'Pop-ups', 'url' => '/admin/popups'],
                    ]
                ]
            ],
            $saasSections
        ),
    ],

    // ─── 7. Design ───
    'design' => [
        'label' => '🎨 Design',
        'type'  => 'dropdown',
        'items' => [
            ['label' => '🌐 Website Builder', 'url' => '/admin/website-builder'],
            ['label' => '🏗️ Page Builder', 'url' => '/admin/jessie-theme-builder'],
            ['label' => '🎨 Themes', 'url' => '/admin/themes'],
            ['label' => '🎯 Theme Studio', 'url' => '/admin/theme-studio'],
            ['label' => '🤖 AI Theme Builder', 'url' => '/admin/ai-theme-builder'],
            ['label' => '🧩 AI Components', 'url' => '/admin/ai-components'],
        ]
    ],

    // ─── 8. System ───
    'system' => [
        'label' => '⚙️ System',
        'type'  => 'dropdown',
        'items' => [
            ['label' => '👥 Users', 'url' => '/admin/users'],
            ['label' => '⚙️ Settings', 'url' => '/admin/settings'],
            ['label' => '🧩 Plugins', 'url' => '/admin/plugins'],
            ['label' => '📦 Modules', 'url' => '/admin/modules'],
            ['label' => '🔒 Security', 'url' => '/admin/security-dashboard'],
            ['label' => '🛡️ GDPR Tools', 'url' => '/admin/gdpr-tools'],
            ['label' => '🔑 API Keys', 'url' => '/admin/api-keys'],
            ['label' => '🌐 Languages', 'url' => '/admin/languages'],
            ['label' => '🏷️ White Label', 'url' => '/admin/white-label'],
            ['label' => '📋 Logs', 'url' => '/admin/logs'],
            ['label' => '💾 Backup', 'url' => '/admin/backup'],
            ['label' => '⏰ Scheduler', 'url' => '/admin/scheduler'],
            ['label' => '🔄 Updates', 'url' => '/admin/updates'],
            ['label' => '📜 Version Control', 'url' => '/admin/version-control'],
            ['label' => '🔥 Maintenance', 'url' => '/admin/maintenance'],
            ['label' => '🗑️ Clear Cache', 'url' => '/admin/clear-cache'],
            ['label' => '🔗 n8n Workflows', 'url' => '/admin/n8n-workflows'],
            ['label' => '🛠️ Workflow Builder', 'url' => '/admin/n8n-workflow-builder'],
            ['label' => '🤖 Automations', 'url' => '/admin/automations'],
            ['label' => '📋 Automation Rules', 'url' => '/admin/automation-rules'],
        ]
    ],

    // ─── User menu (rendered in avatar dropdown) ───
    'user' => [
        'label' => '👤 User',
        'type'  => 'dropdown',
        'items' => [
            ['label' => '👤 Profile', 'url' => '/admin/profile'],
            ['label' => '⚙️ Settings', 'url' => '/admin/settings'],
            ['label' => '📖 Documentation', 'url' => '/admin/docs'],
            ['label' => '🚪 Logout', 'url' => '/admin/logout'],
        ]
    ],
];
