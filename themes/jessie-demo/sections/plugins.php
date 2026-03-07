<?php /** Plugins Showcase */ ?>
<section class="jd-section" id="plugins">
    <div class="jd-section-header jd-fade-up">
        <span class="jd-section-badge"><i class="fas fa-puzzle-piece"></i> Plugin Ecosystem</span>
        <h2 class="jd-section-title">18 Production-Ready Plugins</h2>
        <p class="jd-section-desc">Every plugin includes admin panel, REST API, frontend views, AI integration, and database migrations. Install in one click.</p>
    </div>
    <div class="jd-plugins-grid">
        <?php
        $plugins = [
            ['icon' => 'fa-calendar-check', 'name' => 'Booking', 'desc' => 'Services, staff, appointments, calendar, availability, notifications, recurring', 'tag' => 'Business'],
            ['icon' => 'fa-newspaper', 'name' => 'Newsletter', 'desc' => 'Lists, campaigns, automation, A/B testing, tracking, import/export, templates', 'tag' => 'Marketing'],
            ['icon' => 'fa-id-card', 'name' => 'Membership', 'desc' => 'Plans, members, access rules, transactions, signup flow, member portal', 'tag' => 'Business'],
            ['icon' => 'fa-graduation-cap', 'name' => 'LMS', 'desc' => 'Courses, lessons, quizzes, enrollments, certificates, reviews, progress tracking', 'tag' => 'Education'],
            ['icon' => 'fa-building', 'name' => 'Directory', 'desc' => 'Listings, categories, reviews, claims, search, map integration', 'tag' => 'Marketplace'],
            ['icon' => 'fa-utensils', 'name' => 'Restaurant', 'desc' => 'Menu management, online orders, reservations, dietary tags, kitchen display', 'tag' => 'Food & Drink'],
            ['icon' => 'fa-house', 'name' => 'Real Estate', 'desc' => 'Properties, agents, inquiries, search, mortgage calculator, floor plans', 'tag' => 'Real Estate'],
            ['icon' => 'fa-briefcase', 'name' => 'Jobs', 'desc' => 'Job board, companies, applications, alerts, resume upload, ATS tracking', 'tag' => 'HR'],
            ['icon' => 'fa-ticket', 'name' => 'Events', 'desc' => 'Events, tickets, orders, QR check-in, categories, recurring, speakers', 'tag' => 'Events'],
            ['icon' => 'fa-handshake', 'name' => 'Affiliate', 'desc' => 'Programs, affiliates, conversions, payouts, tracking, multi-tier commissions', 'tag' => 'Marketing'],
            ['icon' => 'fa-images', 'name' => 'Portfolio', 'desc' => 'Projects, categories, testimonials, gallery, video embeds, case studies', 'tag' => 'Creative'],
            ['icon' => 'fa-users', 'name' => 'CRM', 'desc' => 'Contacts, deals, activities, pipeline, analytics, import/export', 'tag' => 'Core'],
            ['icon' => 'fa-file-lines', 'name' => 'Forms', 'desc' => 'Drag & drop form builder, submissions, conditional logic, email notifications', 'tag' => 'Core'],
            ['icon' => 'fa-cart-shopping', 'name' => 'E-Commerce', 'desc' => 'Products, variants, orders, coupons, digital downloads, wishlists, reviews', 'tag' => 'Core'],
            ['icon' => 'fa-truck-fast', 'name' => 'Dropshipping', 'desc' => 'Suppliers, product links, auto-pricing, order forwarding, margin tracking', 'tag' => 'E-Commerce'],
            ['icon' => 'fa-robot', 'name' => 'Shop AI SEO', 'desc' => 'AI product descriptions, meta tags, bulk scan, SEO scoring, keyword optimization', 'tag' => 'AI'],
            ['icon' => 'fa-wand-sparkles', 'name' => 'Shop AI Images', 'desc' => 'Background removal, ALT text gen, image enhance, AI generate via HuggingFace', 'tag' => 'AI'],
            ['icon' => 'fa-palette', 'name' => 'Theme Builder', 'desc' => '79 drag & drop modules, visual editor, template library, live preview', 'tag' => 'Builder'],
        ];
        foreach ($plugins as $i => $p): ?>
        <div class="jd-plugin-card jd-fade-up" style="transition-delay: <?= $i * 30 ?>ms">
            <div class="jd-plugin-icon"><i class="fas <?= $p['icon'] ?>"></i></div>
            <div class="jd-plugin-info">
                <h3><?= $p['name'] ?></h3>
                <p><?= $p['desc'] ?></p>
                <span class="jd-plugin-tag"><?= $p['tag'] ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
