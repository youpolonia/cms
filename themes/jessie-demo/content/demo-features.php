<?php
/**
 * Full Features page — detailed breakdown of Jessie CMS capabilities
 */
?>
<section class="jd-section" style="padding-top: 120px;">
    <div class="jd-section-header jd-fade-up">
        <span class="jd-section-badge"><i class="fas fa-list"></i> Full Feature List</span>
        <h2 class="jd-section-title">Complete Feature Breakdown</h2>
        <p class="jd-section-desc">Everything included in Jessie CMS — no premium tiers, no feature gates.</p>
    </div>

    <div class="jd-features-grid" style="max-width: var(--jd-container); margin: 0 auto; margin-bottom: 64px;">
        <!-- CMS Core -->
        <div class="jd-feature-card jd-fade-up">
            <div class="jd-feature-icon purple"><i class="fas fa-database"></i></div>
            <h3>CMS Core</h3>
            <p>Pages, articles, categories, media library, menus, widgets, SEO metadata, redirects, multi-language support, version history, content blocks.</p>
        </div>
        <div class="jd-feature-card jd-fade-up">
            <div class="jd-feature-icon cyan"><i class="fas fa-users-cog"></i></div>
            <h3>User Management</h3>
            <p>Roles & permissions, admin panel, user profiles, login attempt tracking, password policies, session management, activity logging.</p>
        </div>
        <div class="jd-feature-card jd-fade-up">
            <div class="jd-feature-icon amber"><i class="fas fa-wand-magic-sparkles"></i></div>
            <h3>AI Theme Builder</h3>
            <p>4-step wizard, 129+ industries, 49 themes generated, SSE streaming, CSS validation, header pattern registry (25 patterns), content seeding with Pexels images.</p>
        </div>
        <div class="jd-feature-card jd-fade-up">
            <div class="jd-feature-icon green"><i class="fas fa-th-large"></i></div>
            <h3>JTB Page Builder</h3>
            <p>79 drag & drop modules in 8 categories: structure, content, interactive, media, forms, blog, fullwidth, theme. Visual editor with live preview and template library.</p>
        </div>
        <div class="jd-feature-card jd-fade-up">
            <div class="jd-feature-icon pink"><i class="fas fa-shield-alt"></i></div>
            <h3>Security</h3>
            <p>CSRF on all POST, rate limiting, IP blocking, security policies, encryption, login attempts, 2FA-ready, CORS headers, CSP headers.</p>
        </div>
        <div class="jd-feature-card jd-fade-up">
            <div class="jd-feature-icon red"><i class="fas fa-cogs"></i></div>
            <h3>Automation</h3>
            <p>n8n integration, scheduled tasks, webhook support, automation rules, email queue, notification system, event-driven architecture.</p>
        </div>
    </div>

    <!-- Plugin details -->
    <div class="jd-section-header jd-fade-up">
        <span class="jd-section-badge"><i class="fas fa-puzzle-piece"></i> Plugins in Detail</span>
        <h2 class="jd-section-title">What Each Plugin Includes</h2>
    </div>
    <div style="max-width: var(--jd-container); margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px;">
        <?php
        $details = [
            ['Booking System', 'fa-calendar-check', '34 methods, 16 API endpoints, 9 admin views', 'Services, staff management, appointment scheduling, calendar view, availability rules, email/SMS notifications, recurring appointments, buffer time, customer history, waitlist, export.'],
            ['Newsletter', 'fa-newspaper', '33 methods, 19 API endpoints, 10 views', 'Mailing lists, subscriber management, campaign builder, send engine, open/click tracking, CSV import, AI content generation, automations, A/B testing, segmentation, bounce handling.'],
            ['Membership', 'fa-id-card', '27 methods, 11 API endpoints, 7 views', 'Membership plans, member management, access rules, transactions, payment integration, signup flow, member portal, content gating, trial periods.'],
            ['LMS', 'fa-graduation-cap', '24+ methods, 30 API endpoints, 6 views', 'Courses, lessons, quizzes with grading, enrollments, progress tracking, certificates (auto-generated), reviews & ratings, course catalog, student dashboard.'],
            ['Directory', 'fa-building', '23 methods, 16 API endpoints, 10 views', 'Listings, hierarchical categories, reviews & ratings, business claims, search engine, map integration, hours of operation, photo galleries, featured listings.'],
            ['Restaurant', 'fa-utensils', '24 methods, 15 API endpoints, 9 views', 'Menu categories & items, online ordering, table reservations, dietary/allergen tags, kitchen display, delivery zones, promo codes, order notifications.'],
            ['Real Estate', 'fa-house', '19 methods, 10 API endpoints, 7 views', 'Property listings, agent profiles, inquiry system, advanced search, mortgage calculator, floor plans, neighborhood info, virtual tour links.'],
            ['Jobs', 'fa-briefcase', '23 methods, 10 API endpoints, 7 views', 'Job board, company profiles, application system, job alerts, resume upload, ATS tracking (new→reviewed→interview→offer→hired), skills matching.'],
            ['Events', 'fa-ticket', '27 methods, 12 API endpoints, 8 views', 'Event management, ticket types & pricing, order processing, QR check-in, categories & cities, recurring events, speaker profiles, sponsor management.'],
            ['Affiliate', 'fa-handshake', '28 methods, 10 API endpoints, 8 views', 'Affiliate programs, partner management, conversion tracking, payout processing, cookie-based attribution, multi-tier commissions, promo materials, leaderboard.'],
            ['Portfolio', 'fa-images', '25 methods, 8 API endpoints, 7 views', 'Project showcase, categories, client testimonials, related projects, gallery with lightbox, video embeds, technologies/skills tags, case studies.'],
            ['CRM', 'fa-users', '20+ methods, full REST API', 'Contact management, deal pipeline, activity tracking, analytics, import/export, custom fields, email integration, task management.'],
        ];
        foreach ($details as $d): ?>
        <div class="jd-feature-card jd-fade-up">
            <div class="jd-feature-icon purple"><i class="fas <?= $d[1] ?>"></i></div>
            <h3><?= $d[0] ?></h3>
            <p style="font-size: 0.8rem; color: var(--jd-cyan); margin-bottom: 8px;"><?= $d[2] ?></p>
            <p><?= $d[3] ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>
