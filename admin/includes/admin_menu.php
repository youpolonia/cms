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
            ['label' => '📋 Navigation', 'url' => '/admin/menus'],
            ['label' => '🧩 Widgets', 'url' => '/admin/widgets'],
            ['label' => '💡 Suggestions', 'url' => '/admin/content-suggestions'],
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
        ]
    ],
    
    'marketing' => [
        'label' => '📢 Marketing',
        'type' => 'dropdown',
        'items' => [
            ['label' => '📧 Email Campaigns', 'url' => '/admin/email-campaigns'],
            ['label' => '📫 Email Queue', 'url' => '/admin/email-queue'],
            ['label' => '⚙️ Email Settings', 'url' => '/admin/email-settings'],
            ['label' => '📈 Analytics', 'url' => '/admin/analytics'],
            ['label' => '🔔 Notifications', 'url' => '/admin/notifications'],
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
            ['label' => '🔥 Maintenance', 'url' => '/admin/maintenance'],
            ['label' => '🔒 Security', 'url' => '/admin/security-dashboard'],
            ['label' => '🛡️ GDPR Tools', 'url' => '/admin/gdpr-tools'],
            ['label' => '📜 Version Control', 'url' => '/admin/version-control'],
            ['label' => '🞱 Clear Cache', 'url' => '/admin/clear-cache'],
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