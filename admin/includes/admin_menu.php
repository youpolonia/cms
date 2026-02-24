<?php
/**
 * Centralized Admin Menu Configuration
 * Single source of truth for all admin navigation
 * Used by both legacy and MVC layouts
 */

return [
    'dashboard' => [
        'label' => '📊 Dashboard',
        'url' => '/admin',
        'type' => 'link'
    ],
    
    'content' => [
        'label' => '📄 Content',
        'type' => 'dropdown',
        'items' => [
            ['label' => '👄 Pages', 'url' => '/admin/pages'],
            ['label' => '📰 Articles', 'url' => '/admin/articles'],
            ['label' => '📁 Categories', 'url' => '/admin/categories'],
            ['label' => '🖼️ Media', 'url' => '/admin/media'],
            ['label' => '🎨 Galleries', 'url' => '/admin/galleries'],
            ['label' => '💬 Comments', 'url' => '/admin/comments'],
            ['label' => '📬 Contact Forms', 'url' => '/admin/contact-submissions'],
            ['label' => '📋 Navigation', 'url' => '/admin/menus'],
            ['label' => '🧩 Widgets', 'url' => '/admin/widgets'],
            ['label' => '💡 Suggestions', 'url' => '/admin/content-suggestions'],
            ['label' => '📅 Calendar', 'url' => '/admin/content-calendar'],
            ['label' => '📋 Form Builder', 'url' => '/admin/form-builder'],
        ]
    ],
    
    'seo' => [
        'label' => '🎯 SEO',
        'badge' => 'AI',
        'type' => 'dropdown',
        'items' => [
            ['label' => '🌯 SEO Assistant', 'url' => '/admin/ai-seo-assistant'],
            ['label' => '📈 SEO Dashboard', 'url' => '/admin/ai-seo-dashboard'],
            ['label' => '📋 SEO Content', 'url' => '/admin/ai-seo-content'],
            ['label' => '✏️ Bulk SEO Editor', 'url' => '/admin/ai-seo-bulk'],
            ['label' => '🔑 Keywords', 'url' => '/admin/ai-seo-keywords'],
            ['label' => '🏆 Competitors', 'url' => '/admin/ai-seo-competitors'],
            ['label' => '🔬 Research', 'url' => '/admin/ai-seo-research'],
            ['label' => '🔗 Internal Links', 'url' => '/admin/ai-seo-linking'],
            ['label' => '📋 Content Brief', 'url' => '/admin/ai-seo-brief'],
            ['label' => '📊 Score Timeline', 'url' => '/admin/ai-seo-timeline'],
            ['label' => '🖼️ Image Alt Text', 'url' => '/admin/ai-seo-images'],
            ['label' => '🔗 Broken Links', 'url' => '/admin/ai-seo-links'],
            ['label' => '⏰ Content Decay', 'url' => '/admin/ai-seo-decay'],
            ['label' => '🏷️ Schema', 'url' => '/admin/ai-seo-schema'],
            ['label' => '💈 Reports', 'url' => '/admin/ai-seo-reports'],
        ]
    ],
    
    'ai_tools' => [
        'label' => '🤖 AI Tools',
        'type' => 'dropdown',
        'items' => [
            ['label' => '✨ Content Creator', 'url' => '/admin/ai-content-creator'],
            ['label' => '📊 Quality Check', 'url' => '/admin/content-quality'],
            ['label' => '✍️ Copywriter', 'url' => '/admin/ai-copywriter'],
            ['label' => '🔀 Rwriter', 'url' => '/admin/ai-content-rewrite'],
            ['label' => '🌍 Translate', 'url' => '/admin/ai-translate'],
            ['label' => '🎨 AI Images', 'url' => '/admin/ai-images'],
            ['label' => '🖣️ Alt Generator', 'url' => '/admin/ai-alt-generator'],
            ['label' => '📝 AI Forms', 'url' => '/admin/ai-forms'],
            ['label' => '🚀 Landing Pages', 'url' => '/admin/ai-landing'],
            ['label' => '📚 Student Materials', 'url' => '/admin/ai-student-materials'],
            ['label' => '📧 Email Campaign', 'url' => '/admin/ai-email-campaign'],
            ['label' => '⚗ Workflows', 'url' => '/admin/ai-workflow-generator'],
            ['label' => '🔍 Insights', 'url' => '/admin/ai-insights'],
            ['label' => '📋 AI Logs', 'url' => '/admin/ai-logs'],
            ['label' => '⚙️ AI Settings', 'url' => '/admin/ai-settings'],
            ['label' => '🤖 AI Chatbot', 'url' => '/admin/chat-settings'],
        ]
    ],
    
    'shop' => [
        'label' => '🛒 Shop',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📊 Dashboard', 'url' => '/admin/shop'],
            ['label' => '📦 Products', 'url' => '/admin/shop/products'],
            ['label' => '📁 Categories', 'url' => '/admin/shop/categories'],
            ['label' => '🧾 Orders', 'url' => '/admin/shop/orders'],
            ['label' => '⭐ Reviews', 'url' => '/admin/shop/reviews'],
            ['label' => '🏷️ Coupons', 'url' => '/admin/shop/coupons'],
            ['label' => '🛒 Abandoned Carts', 'url' => '/admin/shop/abandoned-carts'],
            ['label' => '📈 Analytics', 'url' => '/admin/shop/analytics'],
            ['label' => '🔍 AI SEO', 'url' => '/admin/shop/seo'],
            ['label' => '⚙️ Settings', 'url' => '/admin/shop/settings'],
        ]
    ],
    'restaurant' => [
        'label' => '🍕 Restaurant',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📊 Dashboard', 'url' => '/admin/restaurant'],
            ['label' => '🍽️ Menu Items', 'url' => '/admin/restaurant/menu'],
            ['label' => '📁 Categories', 'url' => '/admin/restaurant/categories'],
            ['label' => '📋 Orders', 'url' => '/admin/restaurant/orders'],
            ['label' => '👨‍🍳 Kitchen', 'url' => '/admin/restaurant/kitchen'],
            ['label' => '⚙️ Settings', 'url' => '/admin/restaurant/settings'],
        ]
    ],
    'realestate' => [
        'label' => '🏠 Real Estate',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📊 Dashboard', 'url' => '/admin/realestate'],
            ['label' => '🏘️ Properties', 'url' => '/admin/realestate/properties'],
            ['label' => '👤 Agents', 'url' => '/admin/realestate/agents'],
            ['label' => '📩 Inquiries', 'url' => '/admin/realestate/inquiries'],
        ]
    ],
    'jobs' => [
        'label' => '💼 Jobs',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📊 Dashboard', 'url' => '/admin/jobs'],
            ['label' => '💼 Listings', 'url' => '/admin/jobs/listings'],
            ['label' => '📋 Applications', 'url' => '/admin/jobs/applications'],
            ['label' => '🏢 Companies', 'url' => '/admin/jobs/companies'],
        ]
    ],
    'portfolio' => [
        'label' => '🎨 Portfolio',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📊 Dashboard', 'url' => '/admin/portfolio'],
            ['label' => '💼 Projects', 'url' => '/admin/portfolio/projects'],
            ['label' => '📁 Categories', 'url' => '/admin/portfolio/categories'],
            ['label' => '💬 Testimonials', 'url' => '/admin/portfolio/testimonials'],
        ]
    ],
    'affiliate' => [
        'label' => '🤝 Affiliate',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📊 Dashboard', 'url' => '/admin/affiliate'],
            ['label' => '📋 Programs', 'url' => '/admin/affiliate/programs'],
            ['label' => '👥 Affiliates', 'url' => '/admin/affiliate/affiliates'],
            ['label' => '🎯 Conversions', 'url' => '/admin/affiliate/conversions'],
            ['label' => '💰 Payouts', 'url' => '/admin/affiliate/payouts'],
        ]
    ],
    'events' => [
        'label' => '🎫 Events',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📊 Dashboard', 'url' => '/admin/events'],
            ['label' => '🎪 Events', 'url' => '/admin/events/list'],
            ['label' => '📋 Orders', 'url' => '/admin/events/orders'],
            ['label' => '⚙️ Settings', 'url' => '/admin/events/settings'],
        ]
    ],
    'directory' => [
        'label' => '📍 Directory',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📊 Dashboard', 'url' => '/admin/directory'],
            ['label' => '🏢 Listings', 'url' => '/admin/directory/listings'],
            ['label' => '📁 Categories', 'url' => '/admin/directory/categories'],
            ['label' => '⭐ Reviews', 'url' => '/admin/directory/reviews'],
            ['label' => '🏢 Claims', 'url' => '/admin/directory/claims'],
        ]
    ],
    'lms' => [
        'label' => '🎓 LMS',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📊 Dashboard', 'url' => '/admin/lms'],
            ['label' => '📚 Courses', 'url' => '/admin/lms/courses'],
        ]
    ],
    'membership' => [
        'label' => '🔑 Membership',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📊 Dashboard', 'url' => '/admin/membership'],
            ['label' => '💎 Plans', 'url' => '/admin/membership/plans'],
            ['label' => '👥 Members', 'url' => '/admin/membership/members'],
            ['label' => '🔒 Content Rules', 'url' => '/admin/membership/content'],
        ]
    ],
    'newsletter' => [
        'label' => '📧 Newsletter',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📊 Dashboard', 'url' => '/admin/newsletter'],
            ['label' => '✉️ Campaigns', 'url' => '/admin/newsletter/campaigns'],
            ['label' => '👥 Subscribers', 'url' => '/admin/newsletter/subscribers'],
            ['label' => '📋 Lists', 'url' => '/admin/newsletter/lists'],
            ['label' => '🎨 Templates', 'url' => '/admin/newsletter/templates'],
        ]
    ],
    'booking' => [
        'label' => '📅 Booking',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📊 Dashboard', 'url' => '/admin/booking'],
            ['label' => '📋 Services', 'url' => '/admin/booking/services'],
            ['label' => '👤 Staff', 'url' => '/admin/booking/staff'],
            ['label' => '📅 Calendar', 'url' => '/admin/booking/calendar'],
            ['label' => '📋 Appointments', 'url' => '/admin/booking/appointments'],
            ['label' => '⚙️ Settings', 'url' => '/admin/booking/settings'],
        ]
    ],
    'dropshipping' => [
        'label' => '🚚 Dropshipping',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📊 Dashboard', 'url' => '/admin/dropshipping'],
            ['label' => '🏭 Suppliers', 'url' => '/admin/dropshipping/suppliers'],
            ['label' => '📦 Products', 'url' => '/admin/dropshipping/products'],
            ['label' => '📥 Import', 'url' => '/admin/dropshipping/import'],
            ['label' => '💰 Price Rules', 'url' => '/admin/dropshipping/price-rules'],
            ['label' => '🚚 Orders', 'url' => '/admin/dropshipping/orders'],
            ['label' => '🔬 AI Research', 'url' => '/admin/dropshipping/research'],
            ['label' => '⚙️ Settings', 'url' => '/admin/dropshipping/settings'],
        ]
    ],
    'crm' => [
        'label' => '👥 CRM',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📊 Dashboard', 'url' => '/admin/crm'],
            ['label' => '👤 Contacts', 'url' => '/admin/crm/contacts'],
            ['label' => '🏗️ Pipeline', 'url' => '/admin/crm/pipeline'],
            ['label' => '➕ Add Contact', 'url' => '/admin/crm/contacts/create'],
        ]
    ],
    'marketing' => [
        'label' => '📢 Marketing',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📱 Social Media', 'url' => '/admin/social-media'],
            ['label' => '📅 Social Calendar', 'url' => '/admin/social-media/calendar'],
            ['label' => '🔗 Social Accounts', 'url' => '/admin/social-media/accounts'],
            ['label' => '📧 Email Campaigns', 'url' => '/admin/email-campaigns'],
            ['label' => '📫 Email Queue', 'url' => '/admin/email-queue'],
            ['label' => '⚙️ Email Settings', 'url' => '/admin/email-settings'],
            ['label' => '📈 Analytics', 'url' => '/admin/analytics'],
            ['label' => '🔔 Notifications', 'url' => '/admin/notifications'],
            ['label' => '🔬 A/B Testing', 'url' => '/admin/ab-testing'],
            ['label' => '🎯 Pop-ups', 'url' => '/admin/popups'],
        ]
    ],
    
    'appearance' => [
        'label' => '🎨 Appearance',
        'type' => 'dropdown',
        'items' => [
            ['label' => '🌐 Website Builder', 'url' => '/admin/website-builder'],
            ['label' => '🏗️ Page Builder', 'url' => '/admin/jessie-theme-builder'],
            ['label' => '🎨 Themes', 'url' => '/admin/themes'],
            ['label' => '🎯 Theme Studio', 'url' => '/admin/theme-studio'],
            ['label' => '🤖 AI Theme Builder', 'url' => '/admin/ai-theme-builder'],
            ['label' => '🧩 AI Components', 'url' => '/admin/ai-components'],
        ]
    ],

    'workflows' => [
        'label' => '⚗ Workflows',
        'type' => 'dropdown',
        'items' => [
            ['label' => '🔗 n8n Workflows', 'url' => '/admin/n8n-workflows'],
            ['label' => '🛻️ Workflow Builder', 'url' => '/admin/n8n-workflow-builder'],
            ['label' => '🔌 n8n Bindings', 'url' => '/admin/n8n-bindings'],
            ['label' => '☙️ n8n Settings', 'url' => '/admin/n8n-settings'],
            ['label' => '🤖 Automations', 'url' => '/admin/automations'],
            ['label' => '📋 Automation Rules', 'url' => '/admin/automation-rules'],
        ]
    ],
    
    'ai-chat' => [
        'label' => '💬 AI Assistant',
        'url' => '/admin/ai-chat',
    ],
    'system' => [
        'label' => '⚙️ System',
        'type' => 'dropdown',
        'items' => [
            ['label' => '👥 Users', 'url' => '/admin/users'],
            ['label' => '⚙️ Settings', 'url' => '/admin/settings'],
            ['label' => '🧩 Plugins', 'url' => '/admin/plugins'],
            ['label' => '📦 Modules', 'url' => '/admin/modules'],
            ['label' => 'Ⱀ Scheduler', 'url' => '/admin/scheduler'],
            ['label' => '📋 Logs', 'url' => '/admin/logs'],
            ['label' => '💾 Backup', 'url' => '/admin/backup'],
            ['label' => '🔑 API Keys', 'url' => '/admin/api-keys'],
            ['label' => '🌐 Languages', 'url' => '/admin/languages'],
            ['label' => '🏷️ White Label', 'url' => '/admin/white-label'],
            ['label' => '🔥 Maintenance', 'url' => '/admin/maintenance'],
            ['label' => '🔒 Security', 'url' => '/admin/security-dashboard'],
            ['label' => '🛡️ GDPR Tools', 'url' => '/admin/gdpr-tools'],
            ['label' => '📜 Version Control', 'url' => '/admin/version-control'],
            ['label' => '🞱 Clear Cache', 'url' => '/admin/clear-cache'],
            ['label' => '🔄 Updates', 'url' => '/admin/updates'],
        ]
    ],
    
    'user' => [
        'label' => '👤 User',
        'type' => 'dropdown',
        'items' => [
            ['label' => '👤 Profile', 'url' => '/admin/profile'],
            ['label' => '☙️ Settings', 'url' => '/admin/settings'],
            ['label' => '📖 Documentation', 'url' => '/admin/docs'],
            ['label' => '🚪 Logout', 'url' => '/admin/logout'],
        ]
    ],
];