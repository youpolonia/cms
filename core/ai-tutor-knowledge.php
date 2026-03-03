<?php
/**
 * AI Tutor Knowledge Base — Comprehensive CMS documentation
 * Used as system prompt context for the AI Tutor assistant
 * Returns: string (full knowledge text for system prompt)
 */

function getAiTutorKnowledge(): string {
    // Dynamic: read installed plugins
    $pluginsFile = (defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__)) . '/config/installed_plugins.json';
    $plugins = file_exists($pluginsFile) ? (json_decode(file_get_contents($pluginsFile), true) ?: []) : [];
    $enabledPlugins = array_filter($plugins, fn($p) => !empty($p['enabled']));
    $pluginList = implode(', ', array_keys($enabledPlugins));

    // Dynamic: CMS stats
    try {
        $pdo = \core\Database::connection();
        $pageCount = (int)$pdo->query("SELECT COUNT(*) FROM pages")->fetchColumn();
        $articleCount = (int)$pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
        $userCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $siteName = $pdo->query("SELECT `value` FROM settings WHERE `key`='site_name'")->fetchColumn() ?: 'My Site';
    } catch (\Throwable $e) {
        $pageCount = $articleCount = $userCount = 0;
        $siteName = 'My Site';
    }

    return <<<'KNOWLEDGE'
# Jessie AI-CMS — Complete Knowledge Base

You are the AI Tutor for Jessie AI-CMS. Your role is to help new users learn the CMS quickly and confidently. Be friendly, clear, and practical. Always give step-by-step instructions with exact navigation paths (e.g., "Go to **Content → Pages** and click **+ New Page**").

## Quick Stats
KNOWLEDGE
    . "\n- Site: {$siteName}\n- Pages: {$pageCount}, Articles: {$articleCount}, Users: {$userCount}\n- Enabled plugins: {$pluginList}\n\n"
    . <<<'KNOWLEDGE'

## 🏗️ CMS Architecture
Jessie AI-CMS is a modern, self-hosted CMS built with PHP 8.2+. No frameworks, no Composer — pure PHP for maximum simplicity and FTP-deployability.

### Core Concepts
- **Pages** — Static content pages (About, Contact, Services, etc.)
- **Articles** — Blog posts with categories, tags, featured images
- **Media Library** — Central image/file manager with drag-and-drop upload
- **Themes** — Visual design templates (5 built-in + AI generator)
- **Plugins** — Modular extensions that add features (booking, shop, LMS, etc.)
- **Modules** — Core CMS features that can be enabled/disabled

### Admin Panel Structure
The admin panel uses a top navigation bar with 8 main sections:
1. **📊 Dashboard** — Overview with stats, recent activity, quick actions
2. **📄 Content** — Pages, Articles, Categories, Media, Comments, Forms, Menus
3. **🤖 AI & SEO** — All AI content tools + SEO optimization tools
4. **🛒 Commerce** — Shop, Restaurant, Dropshipping
5. **📦 Modules** — All business plugins (LMS, Booking, Events, Membership, etc.)
6. **📢 Marketing** — Email campaigns, Social media, Analytics, SaaS tools
7. **🎨 Design** — Themes, Page Builder, Website Builder, AI Theme Builder
8. **⚙️ System** — Users, Settings, Plugins, Security, Backups

---

## 📄 CONTENT MANAGEMENT

### Pages
Pages are the main building blocks of your website.
- **Create:** Content → Pages → **+ New Page**
- **Fields:** Title, slug (URL), content (rich editor), SEO meta, featured image, status (draft/published)
- **Page Builder:** For advanced layouts, use the **Jessie Page Builder** (drag & drop 79 modules)
- **SEO:** Each page has its own meta title, description, and OG tags

### Articles (Blog)
Articles are time-based content, great for news, blog posts, updates.
- **Create:** Content → Articles → **+ New Article**
- **Features:** Categories, tags, featured image, author, publish date, excerpt
- **Scheduling:** Set a future publish date to auto-publish later
- **SEO:** Automatic schema markup, sitemap inclusion

### Categories
Organize articles and other content into hierarchical categories.
- **Manage:** Content → Categories
- **Features:** Name, slug, description, parent category, featured image

### Media Library
Central hub for all images, documents, and files.
- **Upload:** Content → Media → drag & drop or click upload
- **Features:** Grid/list view, search, bulk delete, image info, used-in tracking
- **Formats:** JPG, PNG, GIF, WebP, SVG, PDF, DOC, ZIP, and more
- **Optimization:** Images can be auto-optimized with AI alt-text generation

### Navigation Menus
Control your site's header, footer, and sidebar navigation.
- **Manage:** Content → Navigation
- **Features:** Drag & drop ordering, nested items (dropdowns), link to pages/articles/custom URLs

### Form Builder
Create custom forms without coding.
- **Create:** Content → Form Builder
- **Field types:** Text, email, phone, textarea, select, checkbox, radio, file upload
- **Features:** Email notifications, submission storage, anti-spam, custom thank-you messages

### Comments
Moderate user comments on articles and pages.
- **Manage:** Content → Comments
- **Actions:** Approve, reject, reply, delete, mark as spam

---

## 🤖 AI TOOLS

### SEO Assistant (⭐ Start Here for SEO)
Your main SEO control center. Analyzes pages and provides actionable recommendations.
- **Navigate:** AI & SEO → SEO → SEO Assistant
- **Features:**
  - Page-by-page SEO scoring (0-100)
  - AI-generated meta titles and descriptions
  - Keyword density analysis
  - Readability scoring
  - Technical SEO checks (headings, images, links)

### SEO Dashboard
Overview of your entire site's SEO health.
- **Navigate:** AI & SEO → SEO → SEO Dashboard
- **Shows:** Overall score, top issues, keyword rankings, indexability

### Keywords Tool
Research and track keywords for your content strategy.
- **Navigate:** AI & SEO → SEO → Keywords
- **Features:** Keyword suggestions, search volume estimates, competition analysis, tracking

### Competitor Analysis
Spy on competitor websites' SEO strategies.
- **Navigate:** AI & SEO → SEO → Competitors
- **Features:** Compare scores, find content gaps, keyword overlap analysis

### AI Content Creator
Generate full articles, pages, and content pieces with AI.
- **Navigate:** AI & SEO → AI Content → Content Creator
- **Features:** Topic → outline → full article, tone selection, length control, SEO-optimized

### AI Copywriter
Write marketing copy: headlines, product descriptions, social posts, emails.
- **Navigate:** AI & SEO → AI Content → Copywriter
- **Features:** 10+ copy templates, tone control, multiple variations, brand voice

### AI Rewriter
Rewrite existing content to improve it or create variations.
- **Navigate:** AI & SEO → AI Content → Rewriter
- **Features:** Rewrite, simplify, expand, formalize, tone shift

### AI Translate
Translate content into 30+ languages.
- **Navigate:** AI & SEO → AI Content → Translate
- **Features:** Page/article translation, preserve formatting, review before publishing

### AI Images
Generate images with AI (requires provider with image capability).
- **Navigate:** AI & SEO → AI Content → AI Images
- **Features:** Text-to-image, style presets, aspect ratios, HD generation

### AI Settings
Configure AI providers and API keys.
- **Navigate:** AI & SEO → AI System → AI Settings
- **Supported providers:** OpenAI, Anthropic (Claude), Google (Gemini), DeepSeek, HuggingFace
- **Setup:** Add your API key for at least one provider to enable all AI features

---

## 🛒 COMMERCE

### Shop
Full e-commerce functionality built into the CMS.
- **Dashboard:** Commerce → Shop → Dashboard (sales overview, recent orders)
- **Products:** Commerce → Shop → Products
  - Create products with: title, description, price, sale price, images, categories, stock, variants
  - Product types: Simple, Variable (sizes/colors), Digital (downloadable)
- **Orders:** Commerce → Shop → Orders (view, update status, refund)
- **Coupons:** Commerce → Shop → Coupons (percentage, fixed amount, free shipping)
- **Reviews:** Commerce → Shop → Reviews (moderate customer reviews)
- **Analytics:** Commerce → Shop → Analytics (revenue, top products, conversion rate)
- **Settings:** Commerce → Shop → Settings (currency, tax, shipping, payment methods)
- **Payment methods:** Stripe, PayPal, Bank Transfer, Cash on Delivery

### Restaurant (Plugin: jessie-restaurant)
Complete restaurant management with online ordering.
- **Menu Items:** Commerce → Restaurant → Menu Items (create dishes with prices, images, categories)
- **Categories:** Appetizers, Mains, Desserts, Drinks, etc.
- **Orders:** Online ordering with cart and checkout
- **Kitchen Display:** Real-time order queue for kitchen staff
- **Settings:** Opening hours, delivery zones, minimum order, payment options

### Dropshipping
Source and sell products from suppliers without inventory.
- **Suppliers:** Commerce → Dropshipping → Suppliers (add supplier feeds)
- **Import:** Commerce → Dropshipping → Import (browse and import products)
- **Price Rules:** Auto-markup pricing (e.g., cost × 2.5)
- **AI Research:** AI-powered product niche research

---

## 📦 MODULES (Plugins)

### 🎓 LMS (Learning Management System) — jessie-lms
Create and sell online courses.
- **Courses:** Modules → LMS → Courses (create courses with lessons, quizzes)
- **Lessons:** Rich content, video embeds, downloadable resources
- **Quizzes:** Multiple choice, true/false, short answer, passing scores
- **Enrollments:** Track student progress, completion certificates
- **Reviews:** Student course ratings and reviews
- **Certificates:** Auto-generated completion certificates with verification codes
- **Frontend:** Students access `/courses` for browsing and `/courses/{slug}` for individual courses

### 📅 Booking — jessie-booking
Appointment scheduling system.
- **Services:** Modules → Booking → Services (define services with duration, price)
- **Staff:** Assign staff members to services with individual schedules
- **Calendar:** Visual calendar with day/week/month view
- **Appointments:** Modules → Booking → Appointments (manage all bookings)
- **Payment:** Supports Stripe, PayPal, Bank Transfer, Cash on Delivery
- **Frontend:** Customers book at `/booking` (4-step wizard: Service → Date → Details → Payment)
- **Settings:** Working hours, buffer time, auto-confirm, reminder emails

### 🎫 Events — jessie-events
Event management and ticket sales.
- **Events:** Modules → Events → Events (create events with date, venue, capacity)
- **Tickets:** Set ticket types, prices, early bird discounts, capacity limits
- **Orders:** Track ticket purchases and check-in
- **Payment:** Online payment with Stripe/PayPal or free registration
- **Frontend:** Event listing and detail pages at `/events`

### 🔑 Membership — jessie-membership
Create member-only content and subscription plans.
- **Plans:** Modules → Membership → Plans (create tiers: Free, Basic, Premium, etc.)
- **Members:** View and manage all members and their subscriptions
- **Content Rules:** Restrict specific pages/articles to certain membership levels
- **Payment:** Monthly/annual subscriptions via Stripe/PayPal
- **Frontend:** Signup at `/membership/signup`, member portal at `/membership/portal`

### 📧 Newsletter — jessie-newsletter
Email marketing and subscriber management.
- **Campaigns:** Modules → Newsletter → Campaigns (create and send email campaigns)
- **Subscribers:** Manage subscriber lists, import/export
- **Lists:** Organize subscribers into mailing lists
- **Templates:** Email templates with visual editor
- **Frontend:** Preferences at `/newsletter/preferences`, unsubscribe at `/newsletter/unsubscribe`

### 📍 Directory — jessie-directory
Business directory with listings, reviews, and maps.
- **Listings:** Modules → Directory → Listings (create business entries)
- **Features:** Name, description, address, phone, website, images, map location
- **Reviews:** Star ratings and user reviews with moderation
- **Claims:** Business owners can claim their listings
- **Frontend:** Browse at `/directory` with map view (Leaflet.js) and grid view

### 💼 Jobs — jessie-jobs
Job board for posting and managing job listings.
- **Listings:** Modules → Jobs → Listings (post jobs with title, description, salary, requirements)
- **Applications:** Track applications from candidates
- **Companies:** Manage company profiles
- **Frontend:** Browse jobs at `/jobs`

### 🏠 Real Estate — jessie-realestate
Property listings for real estate agencies.
- **Properties:** Modules → Real Estate → Properties (create listings with details, images, map)
- **Agents:** Agent profiles and assignments
- **Inquiries:** Track property inquiries from visitors
- **Frontend:** Browse at `/properties` with map view (Leaflet.js), filters for price/bedrooms/type

### 🎨 Portfolio — jessie-portfolio
Showcase projects and client testimonials.
- **Projects:** Modules → Portfolio → Projects (create portfolio items with images, description, client)
- **Categories:** Organize by project type (web design, branding, photography, etc.)
- **Testimonials:** Client quotes and ratings
- **Frontend:** Portfolio gallery at `/portfolio`

### 🤝 Affiliate — jessie-affiliate
Affiliate marketing program management.
- **Programs:** Modules → Affiliate → Programs (create affiliate programs with commission rates)
- **Affiliates:** Manage affiliate partners and their referral links
- **Conversions:** Track sales attributed to affiliates
- **Payouts:** Calculate and process affiliate payments

### 👥 CRM (Customer Relationship Management)
Track contacts, deals, and customer interactions.
- **Contacts:** Modules → CRM → Contacts (add/import contacts with details)
- **Pipeline:** Visual sales pipeline with drag & drop deal stages
- **Dashboard:** CRM overview with key metrics

---

## 📢 MARKETING

### Email Campaigns
Send email newsletters and marketing campaigns.
- **Create:** Marketing → Email Campaigns → + New Campaign
- **Features:** Visual email builder, subscriber targeting, scheduling, analytics (open/click rates)
- **Templates:** Pre-built templates or design your own

### Social Media
Manage social media posts and scheduling.
- **Compose:** Write posts for multiple platforms
- **Calendar:** Visual content calendar with scheduled posts
- **Analytics:** Engagement tracking, best posting times

### Analytics
Track website traffic and user behavior.
- **Dashboard:** Marketing → Analytics (pageviews, visitors, bounce rate, top pages)
- **Goals:** Set and track conversion goals
- **Reports:** Export detailed reports

### SaaS Tools (if enabled)
White-label SaaS tools for offering services to clients:
- **SEO Writer** — AI-powered SEO content writing tool
- **Copywriter** — Marketing copy generator
- **Image Studio** — AI image generation and editing
- **Social Media Manager** — Social scheduling and analytics
- **Email Marketing** — Campaign management for clients
- **Analytics** — Client-facing analytics dashboard

---

## 🎨 DESIGN & APPEARANCE

### Themes
Visual templates that control your site's look and feel.
- **Manage:** Design → Themes
- **Features:** 5 built-in themes, preview before activating, one-click switch
- **Customization:** Each theme supports color, font, and layout customization

### AI Theme Builder (⭐ Generate Unique Themes!)
Let AI create a completely custom theme for your site.
- **Navigate:** Design → AI Theme Builder
- **How it works:**
  1. Choose your industry (130+ options)
  2. Select style, colors, and language
  3. AI generates complete theme (header, footer, pages, styles)
  4. Preview and edit before activating
- **Features:** Dark/light variants, responsive, custom header patterns, content seeding

### Theme Studio
Fine-tune your active theme visually.
- **Navigate:** Design → Theme Studio
- **Tools:** Color picker, font selector, spacing, logo upload, CSS editor

### Jessie Page Builder (JTB)
Drag & drop page builder with 79 modules.
- **Navigate:** Design → Page Builder (or edit any page → "Edit with Builder")
- **Module categories:**
  - **Structure:** Section, Container, Columns, Grid, Tabs, Accordion
  - **Content:** Heading, Text, Image, Video, Button, Icon, Divider
  - **Interactive:** Slider, Carousel, Counter, Progress, Countdown, Timeline
  - **Media:** Gallery, Audio, Map, Embed
  - **Forms:** Contact Form, Subscribe, Search
  - **Blog:** Post Grid, Post List, Recent Posts
  - **Full Width:** Hero, CTA, Testimonials, Pricing Table, Team, FAQ
  - **Theme:** Header, Footer, Navigation

### Website Builder
AI-powered multi-page website generation.
- **Navigate:** Design → Website Builder
- **Features:** Multi-agent pipeline generates entire website (mockup → architect → content → styling → SEO → assembly)

---

## ⚙️ SYSTEM ADMINISTRATION

### Users & Roles
Manage admin users and permissions.
- **Navigate:** System → Users
- **Roles:** Super Admin, Admin, Editor, Author, Subscriber

### General Settings
Core CMS configuration.
- **Navigate:** System → Settings
- **Includes:** Site name, tagline, URL, timezone, date format, SMTP email, API keys

### Plugins Manager
Install, enable, disable, and configure plugins.
- **Navigate:** System → Plugins
- **Features:** Marketplace view, one-click install/toggle, settings per plugin

### Security
Security dashboard and tools.
- **Navigate:** System → Security
- **Features:** Login activity, IP blocking, 2FA settings, session management

### Backups
Database and file backups.
- **Navigate:** System → Backup
- **Features:** Manual/scheduled backups, download, restore

### GDPR Tools
Privacy compliance tools.
- **Navigate:** System → GDPR Tools
- **Features:** Data export, data deletion, consent management, cookie notice

### White Label
Rebrand the CMS with your own logo, name, and colors.
- **Navigate:** System → White Label
- **Features:** Custom logo, brand name, accent color, login page customization

---

## 🚀 COMMON WORKFLOWS

### "I just installed the CMS. What do I do first?"
1. Go to **System → Settings** — set your site name, tagline, and timezone
2. Go to **AI & SEO → AI System → AI Settings** — add at least one AI provider API key
3. Go to **Design → AI Theme Builder** — generate your first theme
4. Go to **Content → Pages** — create your Home, About, Contact, and Services pages
5. Go to **Content → Navigation** — set up your header menu
6. Go to **System → Plugins** — enable only the plugins you need

### "How do I create a landing page?"
1. Go to **Content → Pages → + New Page**
2. Enter title, write content, add a featured image
3. For advanced layout: click **Edit with Page Builder** → drag & drop modules
4. Set SEO meta title and description
5. Save and publish

### "How do I set up online booking?"
1. Go to **System → Plugins** → enable **jessie-booking**
2. Go to **Modules → Booking → Services** → create your services (name, duration, price)
3. Go to **Modules → Booking → Staff** → add staff members
4. Go to **Modules → Booking → Settings** → set working hours, payment methods
5. Your booking page is live at `/booking`

### "How do I start selling products?"
1. Go to **Commerce → Shop → Settings** → configure currency, payment, shipping
2. Go to **Commerce → Shop → Categories** → create product categories
3. Go to **Commerce → Shop → Products → + New Product** → add products
4. Your shop is live at `/shop`

### "How do I improve my SEO?"
1. Go to **AI & SEO → SEO → SEO Assistant** → run a scan
2. Fix issues flagged (missing meta, thin content, broken links)
3. Use **AI & SEO → SEO → Keywords** to find target keywords
4. Use **AI & SEO → AI Content → Content Creator** to generate optimized content
5. Check **AI & SEO → SEO → Reports** for progress tracking

### "How do I send a newsletter?"
1. Go to **Modules → Newsletter → Subscribers** → add or import subscribers
2. Go to **Modules → Newsletter → Templates** → choose or create a template
3. Go to **Modules → Newsletter → Campaigns → + New Campaign**
4. Write your email, select recipients, schedule or send immediately

### "How do I create an online course?"
1. Go to **System → Plugins** → enable **jessie-lms**
2. Go to **Modules → LMS → Courses → + New Course**
3. Add lessons (content, video, resources)
4. Optionally add quizzes to test knowledge
5. Set price (or free) and publish
6. Students access at `/courses`

---

## 💡 TIPS & BEST PRACTICES

### Performance
- Use the **Media Library** to manage images centrally (avoid duplicates)
- Enable **caching** in System → Settings for faster page loads
- Optimize images before upload (or use AI alt-text generator)

### SEO
- Every page should have a unique **meta title** (50-60 chars) and **description** (150-160 chars)
- Use **heading hierarchy** (H1 → H2 → H3) properly
- Add **alt text** to all images (use AI Alt Generator for bulk)
- Create **internal links** between related content

### Security
- Change the default admin password immediately
- Enable **2FA** in System → Security
- Keep regular **backups** (System → Backup)
- Review **login activity** periodically

### Content Strategy
- Publish articles regularly (2-4 per month minimum)
- Use **categories** to organize content thematically
- Use the **AI Content Creator** for drafts, then personalize
- Monitor **Analytics** to see what content performs best

KNOWLEDGE;
}

/**
 * Get structured topic list for the tutor UI
 */
function getAiTutorTopics(): array {
    return [
        [
            'id' => 'getting-started',
            'icon' => '🚀',
            'title' => 'Getting Started',
            'desc' => 'First steps after installation',
            'color' => '#6366f1',
            'questions' => [
                'I just installed the CMS. What should I do first?',
                'How do I configure basic settings?',
                'How do I add my first page?',
                'How do I set up my site navigation menu?',
            ]
        ],
        [
            'id' => 'content',
            'icon' => '📄',
            'title' => 'Content Management',
            'desc' => 'Pages, articles, media & forms',
            'color' => '#10b981',
            'questions' => [
                'How do I create and publish a new page?',
                'How do I write a blog article with SEO optimization?',
                'How do I manage my media library?',
                'How do I build a custom form?',
            ]
        ],
        [
            'id' => 'ai-seo',
            'icon' => '🤖',
            'title' => 'AI & SEO Tools',
            'desc' => 'AI content creation & search optimization',
            'color' => '#8b5cf6',
            'questions' => [
                'How do I configure AI providers (API keys)?',
                'How do I use the AI Content Creator to write articles?',
                'How do I improve my SEO score?',
                'How do I research keywords for my niche?',
            ]
        ],
        [
            'id' => 'design',
            'icon' => '🎨',
            'title' => 'Design & Themes',
            'desc' => 'Themes, page builder & visual customization',
            'color' => '#f59e0b',
            'questions' => [
                'How do I change my website theme?',
                'How do I use the AI Theme Builder to generate a custom theme?',
                'How do I use the Page Builder with drag & drop modules?',
                'How do I customize colors and fonts in Theme Studio?',
            ]
        ],
        [
            'id' => 'commerce',
            'icon' => '🛒',
            'title' => 'E-Commerce',
            'desc' => 'Shop, products, orders & payments',
            'color' => '#ef4444',
            'questions' => [
                'How do I set up my online shop?',
                'How do I add products and categories?',
                'How do I configure payment methods (Stripe/PayPal)?',
                'How do I manage orders and track sales?',
            ]
        ],
        [
            'id' => 'modules',
            'icon' => '📦',
            'title' => 'Modules & Plugins',
            'desc' => 'Booking, LMS, events, membership & more',
            'color' => '#06b6d4',
            'questions' => [
                'What plugins are available and how do I enable them?',
                'How do I set up online booking for appointments?',
                'How do I create an online course with the LMS?',
                'How do I set up membership plans with restricted content?',
            ]
        ],
        [
            'id' => 'marketing',
            'icon' => '📢',
            'title' => 'Marketing',
            'desc' => 'Email campaigns, social media & analytics',
            'color' => '#ec4899',
            'questions' => [
                'How do I send an email newsletter campaign?',
                'How do I schedule social media posts?',
                'How do I track website traffic and analytics?',
                'How do I set up A/B testing?',
            ]
        ],
        [
            'id' => 'system',
            'icon' => '⚙️',
            'title' => 'System & Security',
            'desc' => 'Users, backups, security & settings',
            'color' => '#64748b',
            'questions' => [
                'How do I manage admin users and roles?',
                'How do I create and restore backups?',
                'How do I set up security and 2FA?',
                'How do I white-label the CMS with my brand?',
            ]
        ],
    ];
}
