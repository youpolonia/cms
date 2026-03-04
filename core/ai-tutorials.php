<?php
/**
 * AI Assistant Tutorials — Markdown content per admin section
 * Loaded by ai-assistant-context.php
 * Each key = URL prefix, value = markdown tutorial string
 */

function getAdminTutorials(): array {
    return [

// ═══════════════════════════════════════
// CONTENT MANAGEMENT
// ═══════════════════════════════════════

'/admin/pages' => <<<'MD'
## Managing Pages

Pages are the building blocks of your website. Each page has a unique URL (slug) and can use different templates.

### Creating a Page
1. Click **"+ New Page"** at the top right
2. Enter a **Title** — the slug is auto-generated (e.g. "About Us" → `/about-us`)
3. Write your content using the **visual editor** (or switch to HTML mode)
4. Set the **Status** — Draft (hidden) or Published (live)
5. Optionally set a **Featured Image** for social sharing
6. Click **Save**

### SEO Settings
Each page has SEO fields:
- **Meta Title** — shown in browser tab and search results (50-60 chars ideal)
- **Meta Description** — shown under title in Google (150-160 chars ideal)
- **Canonical URL** — only if page exists at multiple URLs

### Page Templates
Your theme may offer different templates (e.g. full-width, sidebar, landing). Select in the **Template** dropdown.

### Tips
- Use **Preview** to see how the page looks before publishing
- Reorder pages with the **Menu Order** field
- Delete sends to trash — you can restore within 30 days
MD,

'/admin/articles' => <<<'MD'
## Blog Articles

Articles power your blog. They support categories, tags, featured images, and SEO.

### Writing an Article
1. Go to **Content → Articles** and click **"+ New Article"**
2. Enter a compelling **Title**
3. Choose a **Category** (create new ones in Content → Categories)
4. Write your content — use headings (H2, H3) to structure it
5. Add a **Featured Image** — this appears in article lists and social shares
6. Set **Published Date** — you can backdate or schedule future posts
7. Click **Publish**

### Best Practices
- **Write 800+ words** for SEO — longer content ranks better
- **Use H2 and H3 headings** — break content into scannable sections
- **Add internal links** — link to other articles and pages on your site
- **Optimize images** — compress before uploading, always add alt text
- **Write a meta description** — 150-160 chars that sell the click

### Article Statuses
| Status | Meaning |
|--------|---------|
| Draft | Not visible, work in progress |
| Published | Live on the site |
| Scheduled | Will publish at set date/time |
MD,

'/admin/categories' => <<<'MD'
## Categories

Categories organize your articles into logical groups. Visitors can browse by category.

### Creating Categories
1. Click **"+ New Category"**
2. Enter a **Name** (slug auto-generates)
3. Optionally add a **Description** — shown on category archive pages
4. Optionally set a **Parent Category** for hierarchical structure
5. Click **Save**

### Tips
- Keep categories broad (5-10 is ideal) — use tags for specific topics
- Each article should have exactly one primary category
- Category pages are great for SEO — write unique descriptions for each
MD,

'/admin/media' => <<<'MD'
## Media Library

Upload, organize, and manage all your images, documents, and files.

### Uploading Files
1. Click **"Upload"** or drag files into the library
2. Supported: JPG, PNG, GIF, WebP, SVG, PDF, MP4, MP3
3. Max file size depends on your server config (typically 10-50MB)

### Managing Media
- **Search** by filename or type
- **Edit** — click any image to change title, alt text, caption
- **Alt Text** is critical for SEO and accessibility — describe what's in the image
- **Copy URL** — click the link icon to copy the file URL for embedding

### Image Optimization
- Upload images at **2x the display size** (e.g. 1200px wide for a 600px container)
- Use **WebP format** when possible — 30% smaller than JPG
- The built-in optimizer auto-creates responsive sizes
MD,

'/admin/comments' => <<<'MD'
## Comment Moderation

Review, approve, or remove user comments on your articles and pages.

### Moderation Workflow
1. New comments arrive as **Pending** (unless auto-approve is enabled)
2. Review the comment content and author
3. Click **Approve** to make it visible, or **Spam/Trash** to remove
4. Use **Bulk Actions** to moderate multiple comments at once

### Settings
- Enable **Auto-Approve** in Settings to skip moderation (not recommended for public sites)
- Comments include the author's IP and user agent for spam detection

### Anti-Spam Tips
- The built-in **honeypot** catches most bots automatically
- Rate limiting prevents comment flooding (10/hour/IP)
- Review comments daily to keep discussions healthy
MD,

'/admin/menus' => <<<'MD'
## Navigation Menus

Build and manage your site's navigation structure.

### Creating a Menu
1. Click **"+ New Menu"** and give it a name (e.g. "Main Navigation")
2. Add items from the left panel — Pages, Categories, Custom Links
3. **Drag to reorder** items
4. **Drag right to nest** items (creates dropdown sub-menus)
5. Assign the menu to a **Location** (Header, Footer, Sidebar — depends on your theme)

### Menu Item Types
- **Page Link** — auto-updates if you change the page slug
- **Category Link** — links to the category archive
- **Custom Link** — any URL (external sites, anchors, etc.)

### Tips
- Keep main navigation to **5-7 items** — more causes decision fatigue
- Put your most important pages first (left = highest priority)
- Use dropdowns sparingly — 1 level deep is usually enough
MD,

'/admin/form-builder' => <<<'MD'
## Form Builder

Create custom forms for contact, surveys, applications, and more.

### Building a Form
1. Click **"+ New Form"**
2. Enter a **Form Name** (internal) and optional **Description**
3. Add fields using the field palette:
   - **Text** — single line input
   - **Textarea** — multi-line text
   - **Email** — validated email field
   - **Select** — dropdown with options
   - **Checkbox/Radio** — multiple choice
   - **File Upload** — accept attachments
4. Configure each field: label, placeholder, required, validation
5. Set up **Email Notifications** — who gets notified on submission
6. Click **Save**

### Embedding Forms
- Use the **shortcode**: `[form id="123"]` in any page or article
- Or use the **embed URL**: `/form/embed/123` in an iframe

### Submissions
View all submissions in **Content → Contact Forms**. Export to CSV for analysis.
MD,

// ═══════════════════════════════════════
// E-COMMERCE
// ═══════════════════════════════════════

'/admin/shop' => <<<'MD'
## Shop — Getting Started

Your online store supports products, categories, orders, coupons, reviews, and analytics.

### Quick Setup
1. Go to **Shop → Settings** and configure:
   - **Store Name** and currency
   - **Payment Gateways** — Stripe, PayPal, Bank Transfer, or COD
   - **Shipping** — flat rate, free shipping threshold, or per-item
   - **Tax** — percentage and whether prices include tax
2. Create **Categories** to organize products
3. Add your first **Product**
4. Test the checkout flow yourself

### Adding a Product
1. Go to **Shop → Products → + New Product**
2. Fill in: Title, Description, Price, SKU
3. Upload **Product Images** (first = main image)
4. Select a **Category**
5. Set **Stock Quantity** (or mark as unlimited)
6. Enable **Reviews** if you want customer ratings
7. Click **Publish**

### Order Management
- New orders appear in **Shop → Orders**
- Update status: Pending → Processing → Shipped → Delivered
- Click an order to view details, add tracking numbers, issue refunds

### Coupons
Create discount codes in **Shop → Coupons**:
- **Percentage** (e.g. 20% off) or **Fixed** (e.g. $10 off)
- Set minimum order, expiry date, usage limits
- Restrict to specific products or categories

### Analytics
Track revenue, conversion rates, and top products in **Shop → Analytics**.
MD,

'/admin/shop/products' => <<<'MD'
## Products

### Product Types
- **Simple** — single item with one price
- **Variable** — multiple options (size, color) with different prices
- **Digital** — downloadable files (PDF, software, music)

### Bulk Editing
- Use the list view to quickly update prices and stock
- Filter by category, status, or stock level
- Export/Import via CSV for bulk changes

### SEO for Products
- Write unique **descriptions** (don't copy from suppliers)
- Add **alt text** to all product images
- Use **AI SEO** tool to generate optimized titles and descriptions
MD,

'/admin/dropshipping' => <<<'MD'
## Dropshipping

Connect external suppliers and sell their products without holding inventory.

### Setup
1. Go to **Dropshipping → Settings** and enable the module
2. Add a **Supplier** with their website URL and API details
3. **Import Products** — browse supplier catalogs and import to your shop
4. Set **Price Rules** — markup percentage or fixed amount above supplier price
5. Products appear in your shop automatically

### Price Rules
| Rule | Example |
|------|---------|
| Percentage markup | Supplier: $10 + 50% = Your price: $15 |
| Fixed markup | Supplier: $10 + $5 = Your price: $15 |
| Round up to .99 | $14.50 → $14.99 |

### Order Flow
1. Customer orders on your site
2. You see the order in **Dropshipping → Orders**
3. Forward the order to your supplier (manual or API)
4. Supplier ships directly to customer
5. You keep the markup as profit

### AI Research
Use **Dropshipping → AI Research** to find trending products and profitable niches.
MD,

// ═══════════════════════════════════════
// BOOKING
// ═══════════════════════════════════════

'/admin/booking' => <<<'MD'
## Booking System

Let customers book appointments, services, and consultations online.

### Quick Setup
1. **Create Services** — what you offer (e.g. "Haircut — 30 min — $25")
2. **Add Staff** — who provides the services, with their schedules
3. **Set Availability** — working hours, break times, days off
4. **Configure Payments** — require prepayment or allow pay-on-arrival
5. **Enable the booking page** — customers can self-book 24/7

### Creating a Service
1. Go to **Booking → Services → + New Service**
2. Set: Name, Duration, Price, Description
3. Assign which **Staff Members** can provide this service
4. Set **Buffer Time** between appointments (e.g. 15 min cleanup)
5. Set max bookings per slot (group classes vs 1-on-1)

### Managing Appointments
- View all bookings in **Calendar** or **List** view
- Change status: Pending → Confirmed → Completed / Cancelled
- Send confirmation and reminder emails automatically

### Payment Integration
Supports Stripe, PayPal, Bank Transfer, and Cash on Delivery.
Configure in **Booking → Settings → Payment**.
MD,

// ═══════════════════════════════════════
// LMS (Learning Management System)
// ═══════════════════════════════════════

'/admin/lms' => <<<'MD'
## Learning Management System

Create and sell online courses with lessons, quizzes, and certificates.

### Creating a Course
1. Go to **LMS → Courses → + New Course**
2. Fill in: Title, Description, Featured Image, Price (or free)
3. Select a **Category** and difficulty level
4. Add **Lessons** — each lesson can have:
   - Text content (rich editor)
   - Video (YouTube, Vimeo, or uploaded)
   - Downloadable resources (PDF, files)
   - Duration estimate
5. Add **Quizzes** — multiple choice, true/false, or short answer
6. Set **Pass Percentage** for certification
7. **Publish** the course

### Course Structure
```
Course: "Web Development 101"
├── Module 1: HTML Basics
│   ├── Lesson 1: What is HTML?
│   ├── Lesson 2: Tags and Elements
│   └── Quiz 1: HTML Fundamentals
├── Module 2: CSS Styling
│   ├── Lesson 3: Selectors
│   └── Lesson 4: Flexbox & Grid
└── Final Exam
```

### Student Progress
- Track enrollment and completion rates in the dashboard
- View individual student progress
- Issue **Certificates** automatically when students pass

### Reviews
Students can leave reviews and ratings after completing a course.
MD,

// ═══════════════════════════════════════
// NEWSLETTER
// ═══════════════════════════════════════

'/admin/newsletter' => <<<'MD'
## Newsletter & Email Marketing

Build your email list and send beautiful campaigns.

### Getting Started
1. **Create Lists** — segment your subscribers (e.g. "Customers", "Blog Readers")
2. **Build Subscribe Forms** — embed in your site footer or sidebar
3. **Import Subscribers** — CSV upload for existing contacts
4. **Create a Campaign** — design your first email
5. **Send or Schedule** — review and deliver

### Creating a Campaign
1. Go to **Newsletter → Campaigns → + New Campaign**
2. Choose a **Template** or start blank
3. Write your **Subject Line** (keep under 50 chars, make it compelling)
4. Design the email content — drag-and-drop or HTML
5. Select **Recipients** — choose lists or segments
6. **Preview & Test** — send a test to yourself first
7. **Schedule or Send Now**

### Subscriber Management
- View subscriber stats: active, unsubscribed, bounced
- **Segments** — filter by signup date, activity, tags
- **Double Opt-in** — recommended for GDPR compliance
- Automatic **unsubscribe link** in every email (required by law)

### Analytics
Track for each campaign:
- **Open Rate** — % who opened (aim for 20%+)
- **Click Rate** — % who clicked a link (aim for 2%+)
- **Unsubscribes** — keep under 0.5% per campaign
- **Bounces** — remove invalid emails regularly

### AI Content
Use the **AI Content** button to generate email copy. Provide a topic and tone, and AI drafts the email for you.
MD,

// ═══════════════════════════════════════
// MEMBERSHIP
// ═══════════════════════════════════════

'/admin/membership' => <<<'MD'
## Membership System

Create premium content areas with paid access plans.

### Setting Up
1. **Create Plans** — e.g. "Basic ($9/mo)", "Pro ($29/mo)", "Lifetime ($199)"
2. **Set Content Rules** — which pages/articles require membership
3. **Configure Payments** — connect Stripe or PayPal
4. **Customize the Signup Page** — what visitors see before joining

### Creating a Plan
1. Go to **Membership → Plans → + New Plan**
2. Set: Name, Price, Billing Period (monthly/yearly/lifetime)
3. Choose what content this plan unlocks
4. Set **Trial Period** if you want to offer free trials
5. Save

### Content Gating
- Mark any page or article as **Members Only**
- Choose which plans can access it
- Non-members see a teaser with a signup CTA

### Managing Members
- View all members, their plan, and payment status
- Cancel or upgrade memberships manually
- Track **MRR** (Monthly Recurring Revenue) in the dashboard
MD,

// ═══════════════════════════════════════
// EVENTS
// ═══════════════════════════════════════

'/admin/events' => <<<'MD'
## Events & Ticketing

Create events, sell tickets, and manage check-ins.

### Creating an Event
1. Go to **Events → + New Event**
2. Fill in: Title, Description, Date & Time, Location
3. Add **Ticket Types** — e.g. "General ($25)", "VIP ($75)", "Early Bird ($15)"
4. Set **Capacity** for each ticket type
5. Upload a **Cover Image**
6. **Publish** — the event appears on your site with a booking form

### Ticket Management
- View sold tickets in **Events → Orders**
- Each ticket gets a unique **QR Code** for check-in
- Use the **Check-in Scanner** on event day (works on mobile)
- Export attendee list to CSV

### Calendar Integration
- Events auto-generate **iCal files** — attendees can add to Google/Apple Calendar
- Embed an events calendar on your site

### Reminders
Automatic email reminders sent:
- On booking confirmation
- 24 hours before the event
- After the event (with feedback request)
MD,

// ═══════════════════════════════════════
// DIRECTORY
// ═══════════════════════════════════════

'/admin/directory' => <<<'MD'
## Business Directory

Create a searchable directory of businesses, services, or professionals.

### Adding Listings
1. Go to **Directory → Listings → + New Listing**
2. Fill in: Business Name, Description, Category
3. Add contact details: Phone, Email, Website
4. Set the **Address** — displays on map
5. Upload **Photos** (logo + gallery)
6. Set **Business Hours**
7. Publish

### Categories
Organize listings into categories (e.g. "Restaurants", "Lawyers", "Plumbers").
Each category gets its own browse page with filters.

### Reviews & Ratings
- Visitors can leave star ratings and reviews
- Review moderation: approve/reject in **Directory → Reviews**
- Average rating shown on listing cards

### Search
Full-text search across name, description, and address. Visitors can filter by:
- Category
- Location / Distance
- Rating
- Business hours (open now)

### Claim Requests
Business owners can **claim** their listing to manage it themselves.
Review claims in **Directory → Claims**.
MD,

// ═══════════════════════════════════════
// RESTAURANT
// ═══════════════════════════════════════

'/admin/restaurant' => <<<'MD'
## Restaurant Management

Digital menu, online ordering, and kitchen display system.

### Setup
1. **Create Menu Categories** (Starters, Mains, Desserts, Drinks)
2. **Add Menu Items** — name, description, price, photo, allergens
3. **Configure Ordering** — dine-in, takeaway, delivery
4. **Set Operating Hours**
5. **Enable Online Ordering** on your site

### Adding Menu Items
1. Go to **Restaurant → Menu → + New Item**
2. Fill in: Name, Description, Price
3. Select **Category** (Starters, Mains, etc.)
4. Add a mouth-watering **Photo**
5. Mark **Allergens** (gluten, dairy, nuts, etc.)
6. Toggle **Available** on/off (for seasonal items)

### Order Management
- New orders appear in **Restaurant → Orders** (auto-refreshes)
- **Kitchen Display** — full-screen view for kitchen staff
- Update status: New → Preparing → Ready → Delivered

### Kitchen Display
Go to **Restaurant → Kitchen** for a dedicated kitchen view:
- Large cards showing each order
- Timer shows how long each order has been waiting
- Mark items as ready with one click
MD,

// ═══════════════════════════════════════
// REAL ESTATE
// ═══════════════════════════════════════

'/admin/realestate' => <<<'MD'
## Real Estate Listings

List properties for sale or rent with advanced search and maps.

### Adding a Property
1. Go to **Real Estate → Properties → + New Property**
2. Fill in: Title, Description, Price, Property Type (House/Apartment/Land)
3. Set: Bedrooms, Bathrooms, Area (sq ft/m²), Year Built
4. Enter the **Address** — auto-places on map
5. Upload **Photos** (up to 20) — first photo = thumbnail
6. Set **Listing Type**: For Sale, For Rent, or Both
7. Assign an **Agent**
8. Publish

### Agents
Manage your real estate agents in **Real Estate → Agents**:
- Photo, bio, phone, email
- License number
- Assigned properties

### Property Search
Visitors can search by:
- Location / Map area
- Price range
- Bedrooms / Bathrooms
- Property type
- Keywords

### Inquiries
Visitor inquiries arrive in **Real Estate → Inquiries**. Each includes contact details and which property they're interested in.
MD,

// ═══════════════════════════════════════
// JOBS
// ═══════════════════════════════════════

'/admin/jobs' => <<<'MD'
## Job Board

Post job listings and manage applications.

### Posting a Job
1. Go to **Jobs → Listings → + New Job**
2. Fill in: Title, Description, Company
3. Set: Location (or "Remote"), Salary Range, Job Type (Full-time/Part-time/Contract)
4. Add **Requirements** and **Benefits**
5. Set **Application Deadline**
6. Publish

### Managing Applications
- Applications arrive in **Jobs → Applications**
- Each includes: applicant name, email, cover letter, resume (if uploaded)
- Update status: New → Reviewing → Interview → Offered → Hired / Rejected
- Filter by job listing, status, or date

### Companies
Create company profiles in **Jobs → Companies**:
- Logo, description, website, industry
- All jobs from that company grouped together
MD,

// ═══════════════════════════════════════
// AFFILIATE
// ═══════════════════════════════════════

'/admin/affiliate' => <<<'MD'
## Affiliate Program

Let partners promote your products and earn commissions.

### Setup
1. **Create a Program** — set commission rate (e.g. 15% per sale)
2. **Set Cookie Duration** — how long referrals are tracked (default: 30 days)
3. **Set Minimum Payout** — minimum balance before payout (e.g. $50)
4. **Enable Registration** — affiliates can sign up on your site

### How It Works
1. Affiliate signs up and gets a unique **referral link**
2. They share the link on their website, social media, etc.
3. When someone clicks and buys, the affiliate earns commission
4. You review and approve payouts in **Affiliate → Payouts**

### Tracking
- View all conversions in **Affiliate → Conversions**
- See which affiliates drive the most sales
- Track clicks, conversions, and revenue per affiliate

### Payouts
- Affiliates accumulate earnings
- When they reach the minimum payout threshold, request a payout
- You approve and send payment (PayPal, bank transfer, etc.)
MD,

// ═══════════════════════════════════════
// PORTFOLIO
// ═══════════════════════════════════════

'/admin/portfolio' => <<<'MD'
## Portfolio

Showcase your projects, case studies, and client work.

### Adding a Project
1. Go to **Portfolio → Projects → + New Project**
2. Fill in: Title, Description, Client Name
3. Upload **Images** — gallery of project photos
4. Select **Categories** (e.g. "Web Design", "Branding", "Photography")
5. Add a **Link** to the live project (optional)
6. Add **Technologies/Tags** used
7. Publish

### Testimonials
Collect client testimonials in **Portfolio → Testimonials**:
- Client name, company, photo
- Quote text and rating
- Link to related project

### Display
Projects display in a **filterable grid** on your site. Visitors can filter by category and view project details in a lightbox or detail page.
MD,

// ═══════════════════════════════════════
// CRM
// ═══════════════════════════════════════

'/admin/crm' => <<<'MD'
## CRM — Customer Relationship Management

Track leads, manage contacts, and monitor your sales pipeline.

### Contacts
1. Go to **CRM → Contacts → + New Contact**
2. Fill in: Name, Email, Phone, Company
3. Add **Tags** for segmentation (e.g. "Lead", "Customer", "VIP")
4. Add **Notes** — record meeting notes, calls, important details
5. Track **Activities** — schedule follow-ups, calls, meetings

### Pipeline
Visual sales pipeline with drag-and-drop stages:
1. **Lead** → **Qualified** → **Proposal** → **Negotiation** → **Won/Lost**
2. Drag contacts between stages as deals progress
3. Track deal value and expected close date

### Tips
- Log every interaction — future you will thank present you
- Set follow-up reminders so no lead falls through the cracks
- Use tags to segment contacts for targeted email campaigns
MD,

// ═══════════════════════════════════════
// SaaS TOOLS
// ═══════════════════════════════════════

'/admin/saas' => <<<'MD'
## SaaS Dashboard

Overview of your SaaS platform — users, revenue, and usage.

### Key Metrics
- **Total Users** — registered accounts
- **Active Users** — logged in within 30 days
- **Total Revenue** — from credit purchases
- **API Usage** — requests today

### Managing Users
Go to **SaaS → Users** to view all registered users:
- Plan, credits remaining, signup date
- Manually add credits or change plans
- Ban or deactivate users

### Plans & Pricing
Manage subscription plans in **SaaS → Plans**:
- Set credits per plan, price, features
- 43 pre-configured plans across all services
- Create custom plans for enterprise clients

### Revenue
Track monthly revenue and transaction history in **SaaS → Revenue**.
MD,

// ═══════════════════════════════════════
// AI & SEO
// ═══════════════════════════════════════

'/admin/ai-seo-assistant' => <<<'MD'
## AI SEO Assistant

Comprehensive SEO analysis and optimization for any page or article.

### Running an Audit
1. Select a **Page or Article** from the dropdown
2. Click **"Analyze"** — AI reviews 50+ SEO factors
3. Review the report:
   - **Score** — overall SEO health (aim for 80+)
   - **Critical Issues** — fix these first (missing title, broken links)
   - **Warnings** — important but not urgent
   - **Passed** — things you're doing right
4. Click any issue for detailed fix instructions

### What It Checks
- Title tag length and keyword placement
- Meta description quality
- Heading structure (H1, H2, H3)
- Image alt text coverage
- Internal and external links
- Content length and readability
- Mobile friendliness indicators
- Schema markup presence

### Auto-Fix
For some issues, click **"Fix Now"** to let AI generate optimized content automatically.
MD,

'/admin/ai-content-creator' => <<<'MD'
## AI Content Creator

Generate articles, blog posts, and page content with AI.

### Creating Content
1. Enter a **Topic** or title idea
2. Select **Content Type** — blog post, product description, landing page, etc.
3. Choose **Tone** — professional, casual, friendly, technical
4. Set **Length** — short (300 words), medium (800), or long (1500+)
5. Click **"Generate"**
6. Review, edit, and refine the output
7. Click **"Create as Article"** to save directly as a draft article

### Tips
- The more specific your topic, the better the output
- Always review AI content before publishing — add your expertise
- Use **"Regenerate"** for a fresh take on the same topic
- AI works best for first drafts — human editing makes it great
MD,

'/admin/ai-theme-builder' => <<<'MD'
## AI Theme Builder

Generate complete website themes with AI — just describe what you want.

### Creating a Theme
1. Click **"+ New Theme"** or use the **Wizard**
2. Describe your website: industry, style, colors, features
3. AI generates a complete theme with:
   - Homepage with hero, features, testimonials
   - About, Contact, Blog pages
   - Navigation and footer
   - Color scheme and typography
   - Responsive CSS
4. **Preview** the generated theme
5. Click **"Install"** to activate it

### Customization
After generation, customize via **Theme Studio**:
- Change colors, fonts, spacing
- Edit section content
- Rearrange page sections
- Add/remove pages

### Tips
- Be specific about your industry — "dental clinic" beats "medical"
- Mention color preferences — "blue and white, modern" guides the AI
- Generate multiple themes and pick the best one
MD,

// ═══════════════════════════════════════
// SYSTEM
// ═══════════════════════════════════════

'/admin/settings' => <<<'MD'
## Site Settings

Configure your website's core settings.

### General
- **Site Name** — appears in header, emails, and SEO
- **Site Description** — used for homepage meta description
- **Site URL** — your domain (e.g. https://example.com)
- **Admin Email** — receives notifications and contact form submissions

### Email
- Configure **SMTP settings** for reliable email delivery
- Test with the **"Send Test Email"** button
- Set **From Name** and **From Email** for outgoing messages

### Maintenance Mode
Enable to show a "Coming Soon" page to visitors while you work on the site. Admins can still access everything.
MD,

'/admin/users' => <<<'MD'
## User Management

Create, edit, and manage admin users.

### Creating an Admin
1. Click **"+ New User"**
2. Enter: Username, Email, Password
3. Select **Role**:
   - **Admin** — full access to everything
   - **Editor** — can manage content but not settings/users
   - **Author** — can create and manage own content only
4. Click **Create**

### Security
- All passwords are hashed with **bcrypt** (cost 12)
- Enforce strong passwords (8+ chars)
- Review the **Activity Log** periodically for suspicious access
- Deactivate users instead of deleting (preserves audit trail)
MD,

'/admin/backup' => <<<'MD'
## Backup & Restore

Create database backups and restore when needed.

### Creating a Backup
1. Click **"Create Backup"**
2. The system exports all database tables as SQL
3. Download the backup file for safe keeping

### Best Practices
- Create a backup **before major changes** (theme switch, plugin install)
- Keep backups in **multiple locations** (local + cloud)
- Test restore on a staging site periodically
- Automate with the **Scheduler** for daily backups
MD,

'/admin/plugins' => <<<'MD'
## Plugin Management

Enable, disable, and configure CMS plugins.

### Available Plugins
Each plugin adds features to your CMS:
- **Domain Plugins** — Booking, LMS, Events, etc.
- **SaaS Plugins** — SEO Writer, Copywriter, Image Studio, etc.
- **Utility Plugins** — Newsletter, Membership, Affiliate, etc.

### Enabling a Plugin
1. Go to **System → Plugins**
2. Find the plugin and click **"Enable"**
3. The plugin installs its database tables automatically
4. Configure the plugin in its dedicated admin section

### Plugin Settings
Most plugins have a **Settings** page accessible from their admin section. Common settings:
- API keys for external services
- Email notification preferences
- Display options
MD,

'/admin/security-dashboard' => <<<'MD'
## Security Dashboard

Monitor your site's security posture.

### What It Shows
- **Login Attempts** — recent successful and failed logins
- **Security Headers** — which HTTP security headers are active
- **File Integrity** — check for unexpected changes
- **User Activity** — who did what recently

### Hardening Tips
- Enable **HTTPS** (required for cookies, payment, SEO)
- Keep CMS updated to latest version
- Use strong, unique passwords for all admin accounts
- Review **login attempts** weekly — block suspicious IPs
- Enable **2FA** when available
- Regular **backups** — your safety net against everything
MD,

'/admin/analytics' => <<<'MD'
## Analytics

Track visitor behavior, page views, and conversions.

### Dashboard
- **Visitors** — unique visitors over time
- **Page Views** — most viewed pages
- **Traffic Sources** — where visitors come from
- **Device Split** — desktop vs mobile vs tablet

### Goals & Funnels
1. Define **Goals** — e.g. "Contact form submitted", "Product purchased"
2. Set up **Funnels** — track the steps leading to conversion
3. Identify where visitors drop off and optimize

### AI Insights
Click **"AI Insights"** for automatic analysis:
- Traffic trend explanations
- Anomaly detection (unusual spikes/drops)
- Optimization suggestions
MD,

    ]; // end of tutorials array
}
