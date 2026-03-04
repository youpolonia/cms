<?php
/**
 * AI Tutorials — Part 2: All remaining admin pages
 */
function getAdminTutorialsPart2(): array {
    return [

'/admin' => <<<'MD'
## Admin Dashboard

Your command center — see everything at a glance.

### What's Here
- **Quick Stats** — pages, articles, users, orders at the top
- **Recent Activity** — latest content changes and user actions
- **Quick Links** — shortcuts to common tasks
- **System Health** — server status, PHP version, disk space

### Daily Routine
1. Check for new **orders** or **contact submissions**
2. Review **pending comments** for moderation
3. Check **low stock alerts** (if using Shop)
4. Review any **security alerts**
5. Plan today's content updates
MD,

'/admin/galleries' => <<<'MD'
## Photo Galleries

Create beautiful image galleries for your site.

### Creating a Gallery
1. Click **"+ New Gallery"**
2. Enter a **Title** and optional description
3. **Upload images** or select from Media Library
4. Drag to **reorder** images
5. Set **Display Style** — grid, masonry, carousel, or lightbox
6. Save and embed in any page using the gallery shortcode

### Tips
- Optimize images before uploading (compress to WebP)
- Add captions to images for SEO and accessibility
- Use categories to organize galleries by topic
MD,

'/admin/contact-submissions' => <<<'MD'
## Contact Form Submissions

View and manage messages from your website's contact forms.

### Managing Submissions
1. New submissions appear with status **"New"**
2. Click to view the full message, sender details, and IP
3. Update status: **New → Read → Replied → Closed**
4. Use **Bulk Actions** to archive or delete old submissions

### Tips
- Reply quickly — under 24 hours improves customer satisfaction
- Export to CSV for CRM integration
- Check spam folder — occasionally legitimate messages get flagged
MD,

'/admin/widgets' => <<<'MD'
## Widgets

Add dynamic content blocks to your theme's widget areas (sidebars, footers).

### Available Widgets
- **Recent Posts** — latest articles
- **Categories** — article category list
- **Search** — search form
- **Custom HTML** — any HTML/embed code
- **Newsletter** — email subscribe form
- **Social Links** — social media icons

### Adding a Widget
1. Click **"+ New Widget"**
2. Select **Widget Type** and **Widget Area** (sidebar, footer, etc.)
3. Configure the widget settings
4. Set **Order** — position within the area
5. Save
MD,

'/admin/content-suggestions' => <<<'MD'
## Content Suggestions

AI-powered ideas for new articles and pages based on your niche.

### How It Works
1. AI analyzes your existing content and site category
2. Suggests **topic ideas** with estimated search volume
3. Click a suggestion to start writing immediately
4. Mark suggestions as "Done" or "Not Interested"

### Tips
- Review suggestions weekly for fresh content ideas
- Prioritize topics with high search volume and low competition
- Use AI Content Creator to draft articles from suggestions
MD,

'/admin/content-calendar' => <<<'MD'
## Content Calendar

Visual calendar to plan and schedule your content publishing.

### Using the Calendar
1. **Drag articles** to schedule their publish date
2. **Color coding** — green (published), yellow (scheduled), gray (draft)
3. Click any day to **create new content** for that date
4. Switch between **month**, **week**, and **list** views

### Planning Tips
- Aim for consistent posting — 2-3x per week minimum
- Plan seasonal content in advance (holidays, events)
- Mix content types — tutorials, news, opinion pieces
- Batch-write articles and schedule them throughout the week
MD,

'/admin/ai-chat' => <<<'MD'
## AI Chat Assistant

Chat with AI directly in your admin panel for help with anything.

### What You Can Ask
- "Write a product description for [product name]"
- "Suggest blog topics about [your niche]"
- "Help me write an email to customers about [topic]"
- "How do I set up shipping in the shop?"
- "Explain what SEO meta descriptions do"

### Tips
- Be specific in your prompts for better results
- You can copy AI responses directly into the editor
- Chat history is saved per session
- Use for quick tasks — for bigger content, use AI Content Creator
MD,

'/admin/ai-tutor' => <<<'MD'
## AI Tutor

Interactive learning assistant that teaches you how to use every feature of the CMS.

### How It Works
1. Select a **Topic** from the sidebar or ask a question
2. AI provides step-by-step explanations
3. Follow along in your admin panel
4. Ask follow-up questions if anything is unclear

### Topics Covered
- Setting up your site from scratch
- Managing content (pages, articles, media)
- E-commerce setup and management
- SEO optimization
- Email marketing
- Plugin configuration
- Security best practices
MD,

'/admin/ai-settings' => <<<'MD'
## AI Settings

Configure AI providers and API keys for all AI features.

### Setup
1. Enter your **OpenAI API Key** (or compatible provider)
2. Select the default **Model** (GPT-4, GPT-3.5, etc.)
3. Set **Temperature** — lower = more focused, higher = more creative
4. Configure **Rate Limits** — max requests per minute
5. Save

### API Keys
- **OpenAI** — required for content generation, SEO analysis, chat
- **Pexels** — for stock images (already configured)
- **HuggingFace** — for AI image generation

### Usage Tracking
Monitor API usage and costs in **AI → Logs**.
MD,

'/admin/ai-copywriter' => <<<'MD'
## AI Copywriter

Generate marketing copy for any platform — social media, ads, emails, websites.

### Platforms
1. **Social Media** — Instagram, Twitter/X, LinkedIn, Facebook posts
2. **Ads** — Google Ads, Facebook Ads headlines and descriptions
3. **Email** — subject lines, body copy, CTAs
4. **Website** — headlines, taglines, product descriptions
5. **Video** — YouTube titles, descriptions, scripts
6. **SMS** — short promotional messages
7. **Press Release** — structured announcements

### Using the Copywriter
1. Select **Platform** and **Content Type**
2. Describe your product/service/topic
3. Choose **Tone** and **Brand Voice**
4. Click **Generate** — get multiple options
5. Edit and refine, then copy to clipboard

### Brand Voices
Save custom brand voices to maintain consistent messaging across all copy.
MD,

'/admin/ai-content-rewrite' => <<<'MD'
## AI Content Rewriter

Improve, rephrase, or completely rewrite existing content.

### Modes
- **Improve** — polish grammar, clarity, and flow
- **Simplify** — make complex text easier to understand
- **Expand** — add more detail and depth
- **Shorten** — condense without losing meaning
- **Change Tone** — formal ↔ casual, technical ↔ simple
- **Translate** — rewrite in another language

### How to Use
1. Paste your existing text
2. Select the rewrite mode
3. Click **"Rewrite"**
4. Compare original vs rewrite side-by-side
5. Accept, edit, or try another variation
MD,

'/admin/ai-translate' => <<<'MD'
## AI Translation

Translate pages and articles into multiple languages.

### Translating Content
1. Select the **Page or Article** to translate
2. Choose the **Target Language**
3. Click **"Translate"** — AI translates while preserving formatting
4. **Review** the translation — AI is good but not perfect
5. Click **"Save as New Page"** to create the translated version

### Tips
- Always have a native speaker review translations for important content
- Translate SEO meta data separately for each language
- Link translated pages together with hreflang tags
MD,

'/admin/ai-images' => <<<'MD'
## AI Image Generator

Create custom images using AI for your content.

### Generating Images
1. Describe what you want — "modern office with plants, bright lighting"
2. Choose **Style** — photo-realistic, illustration, cartoon, abstract
3. Select **Size** — square, landscape, portrait
4. Click **"Generate"**
5. Download or insert directly into your content

### Tips
- Detailed prompts produce better results
- Use for: hero images, blog illustrations, product mockups
- Always check AI images for artifacts or errors
- Add proper alt text after inserting
MD,

'/admin/ai-alt-generator' => <<<'MD'
## AI Alt Text Generator

Automatically generate descriptive alt text for all your images.

### How It Works
1. AI analyzes each image in your Media Library
2. Generates a descriptive **alt text** (what the image shows)
3. Review and approve/edit suggestions
4. Apply in bulk to update all images at once

### Why Alt Text Matters
- **Accessibility** — screen readers read alt text to visually impaired users
- **SEO** — Google uses alt text to understand images
- **Broken Images** — alt text shows when images fail to load
- It's the **law** in many countries (WCAG compliance)
MD,

'/admin/ai-forms' => <<<'MD'
## AI Form Generator

Generate complete forms using AI — describe what you need, get a ready form.

### Using AI Forms
1. Describe your form: "Job application form with resume upload"
2. AI generates fields, labels, validation rules, and layout
3. Review and customize the generated form
4. Save to Form Builder for further editing
5. Embed on any page

### Example Prompts
- "Contact form with name, email, phone, and message"
- "Event registration with ticket type selection"
- "Customer feedback survey with rating scales"
- "Multi-step booking form with date picker"
MD,

'/admin/ai-landing' => <<<'MD'
## AI Landing Page Generator

Create high-converting landing pages in minutes.

### Creating a Landing Page
1. Describe your **product/service** and **goal** (signups, sales, etc.)
2. Choose a **Style** — minimal, bold, corporate, creative
3. AI generates a complete page with:
   - Hero section with headline and CTA
   - Features/benefits section
   - Social proof / testimonials
   - Pricing (if applicable)
   - Final CTA section
4. Preview and customize
5. Publish as a new page

### Conversion Tips
- One page = one goal. Don't confuse visitors with multiple CTAs
- Use specific numbers — "Join 2,847 customers" beats "Join many customers"
- Add urgency when appropriate — limited time offers, countdown timers
MD,

'/admin/ai-email-campaign' => <<<'MD'
## AI Email Campaign Generator

Create complete email campaigns with AI — sequences, subject lines, and content.

### Creating a Campaign
1. Define your **Goal** — welcome series, product launch, re-engagement
2. Set the **Audience** — who should receive it
3. AI generates a multi-email sequence with timing
4. Review each email: subject line, preview text, body
5. Edit and customize, then send to Email Campaigns

### Campaign Types
- **Welcome Series** — onboard new subscribers (3-5 emails)
- **Product Launch** — build anticipation and announce
- **Abandoned Cart** — recover lost sales (1-3 emails)
- **Re-engagement** — win back inactive subscribers
- **Newsletter** — regular content digest
MD,

'/admin/ai-student-materials' => <<<'MD'
## AI Student Materials

Generate educational content for your LMS courses.

### What It Creates
- **Lesson Plans** — structured curriculum outlines
- **Quiz Questions** — multiple choice, true/false, short answer
- **Study Guides** — summaries and key points
- **Flashcards** — term/definition pairs
- **Assignments** — practical exercises with rubrics

### How to Use
1. Select or describe the **Course Topic**
2. Choose **Material Type**
3. Set **Difficulty Level** (beginner/intermediate/advanced)
4. Click **Generate**
5. Review, edit, and add to your LMS course
MD,

'/admin/ai-workflow-generator' => <<<'MD'
## AI Workflow Generator

Create n8n automation workflows with natural language.

### How It Works
1. Describe what you want automated:
   - "When a new order comes in, send a Slack notification"
   - "Every Monday, email me a weekly sales report"
   - "When a form is submitted, add contact to CRM and send welcome email"
2. AI generates the workflow configuration
3. Review the workflow steps
4. Export to n8n or save as automation rule

### Tips
- Start simple — one trigger, one action
- Build complexity gradually
- Test workflows with sample data before activating
MD,

'/admin/ai-insights' => <<<'MD'
## AI Insights

AI-generated analysis of your site's performance and content.

### What You Get
- **Traffic Insights** — why traffic went up or down
- **Content Performance** — which articles perform best and why
- **SEO Opportunities** — quick wins for better rankings
- **User Behavior** — how visitors navigate your site
- **Revenue Analysis** — sales trends and predictions (if using Shop)

### How to Use
1. Click **"Generate Insights"**
2. AI analyzes your recent data
3. Review key findings and recommendations
4. Click any recommendation to take action
MD,

'/admin/ai-logs' => <<<'MD'
## AI Logs

Monitor all AI API calls, usage, and costs.

### What's Tracked
- Every AI request: timestamp, feature, model, tokens used
- **Cost estimation** per request and total
- **Error logs** — failed requests with error details
- **Usage by feature** — which AI tools use the most tokens

### Managing Costs
- Review weekly to avoid surprise bills
- Set **rate limits** in AI Settings
- Use GPT-3.5 for simple tasks, GPT-4 for complex ones
- Clear old logs periodically to save storage
MD,

'/admin/ai-seo-dashboard' => <<<'MD'
## SEO Dashboard

Bird's eye view of your site's SEO health.

### Metrics
- **Overall SEO Score** — aggregate of all pages
- **Pages Analyzed** — how many pages have been audited
- **Critical Issues** — problems that need immediate attention
- **Opportunities** — easy wins for quick improvement

### Key Sections
- **Top Issues** — most common problems across your site
- **Score Distribution** — histogram of page scores
- **Recent Changes** — latest SEO improvements made
- **Competitor Comparison** — how you stack up
MD,

'/admin/ai-seo-keywords' => <<<'MD'
## Keyword Research

Find and track keywords your site should target.

### Researching Keywords
1. Enter a **seed keyword** related to your business
2. AI suggests related keywords with:
   - Search volume (monthly)
   - Competition level (low/medium/high)
   - Current ranking position (if applicable)
3. Save keywords to your **tracking list**
4. Monitor rankings over time

### Keyword Strategy
- Target a mix of **head terms** (high volume) and **long-tail** (low competition)
- Map each keyword to a specific page or article
- One primary keyword per page + 3-5 secondary keywords
- Update content quarterly to maintain rankings
MD,

'/admin/ai-seo-competitors' => <<<'MD'
## Competitor Analysis

See what your competitors rank for and find gaps.

### How to Use
1. Add **competitor URLs** (up to 10)
2. AI analyzes their content, keywords, and structure
3. Compare side-by-side with your site
4. Identify **keyword gaps** — terms they rank for that you don't
5. Find **content opportunities** — topics they cover that you should

### Insights
- Their top-performing pages and content types
- Backlink profile comparison
- Content freshness — how often they publish
- Technical SEO comparison
MD,

'/admin/ai-seo-linking' => <<<'MD'
## Internal Linking

Optimize your site's internal link structure for SEO.

### How It Works
1. AI scans all published pages and articles
2. Identifies pages that should link to each other
3. Suggests **anchor text** and **placement**
4. Apply suggestions with one click

### Why Internal Links Matter
- Help Google discover and index all your pages
- Pass SEO authority between pages
- Keep visitors on your site longer
- Create topical clusters that boost rankings

### Tips
- Link from high-authority pages to important pages
- Use descriptive anchor text (not "click here")
- Every page should have at least 3 internal links
MD,

'/admin/ai-seo-schema' => <<<'MD'
## Schema Markup

Add structured data to help search engines understand your content.

### Schema Types
- **Article** — for blog posts
- **Product** — for shop items (price, availability, reviews)
- **LocalBusiness** — for local businesses
- **FAQ** — for FAQ pages
- **Event** — for events
- **BreadcrumbList** — navigation breadcrumbs
- **Organization** — your company info

### How to Use
1. Select a **page or article**
2. Choose the **Schema type**
3. AI auto-fills fields from your content
4. Review and adjust
5. Save — schema is auto-injected into the page

### Benefits
Schema can give you **rich snippets** in Google — star ratings, prices, FAQ dropdowns, etc. These dramatically increase click-through rates.
MD,

'/admin/ai-seo-reports' => <<<'MD'
## SEO Reports

Generate comprehensive SEO reports for your site.

### Report Types
- **Full Site Audit** — complete analysis of all pages
- **Page-Level Report** — deep dive into one specific page
- **Keyword Report** — ranking positions and trends
- **Competitor Report** — comparison with competitors
- **Monthly Summary** — progress over the last 30 days

### Scheduling
Set up **automatic monthly reports** to be emailed to you or your clients.
MD,

'/admin/ai-seo-brief' => <<<'MD'
## Content Brief Generator

AI creates detailed content briefs for writers.

### Creating a Brief
1. Enter the **target keyword**
2. AI researches the topic and generates:
   - Suggested title options
   - Recommended word count
   - Heading structure (H2, H3 outline)
   - Key points to cover
   - Questions to answer
   - Competitor content analysis
   - Internal linking suggestions
3. Send the brief to your writer or use it yourself

### Tips
- A good brief saves hours of research time
- Follow the suggested heading structure for SEO
- Answer the suggested questions — Google loves Q&A content
MD,

'/admin/ai-seo-bulk' => <<<'MD'
## Bulk SEO Editor

Edit meta titles and descriptions for multiple pages at once.

### How to Use
1. View all pages/articles in a spreadsheet-like view
2. See current: Title, Meta Description, Focus Keyword
3. **Edit inline** — click any field to change it
4. AI can **auto-generate** optimized meta for empty fields
5. Save all changes at once

### Tips
- Title: 50-60 characters, keyword near the start
- Description: 150-160 characters, include a call to action
- Every page should have unique meta — no duplicates
MD,

'/admin/ai-seo-decay' => <<<'MD'
## Content Decay Detection

Find articles that are losing traffic and rankings over time.

### How It Works
1. AI analyzes your content performance trends
2. Flags articles with declining traffic (30, 60, 90 day trends)
3. Prioritizes by impact — biggest traffic losses first
4. Suggests refresh strategies for each article

### Refresh Strategies
- **Update facts and dates** — outdated info loses rankings
- **Add new sections** — expand thin content
- **Improve title and meta** — boost click-through rate
- **Add images and media** — visual content engages readers
- **Update internal links** — link to newer related content
MD,

'/admin/ai-seo-images' => <<<'MD'
## Image SEO

Optimize all images across your site for search engines.

### What It Checks
- Missing **alt text** — biggest image SEO issue
- **File names** — descriptive names beat IMG_001.jpg
- **File size** — large images slow your site
- **Dimensions** — oversized images waste bandwidth
- **Format** — WebP is 30% smaller than JPG

### Bulk Fix
1. View all images with SEO issues
2. AI generates alt text suggestions
3. Apply individually or in bulk
4. Track improvement over time
MD,

'/admin/ai-seo-links' => <<<'MD'
## Broken Link Checker

Find and fix broken links across your entire site.

### How It Works
1. Crawler checks every link on every page
2. Reports: **404 Not Found**, **500 Server Error**, **Timeout**
3. Shows which page the broken link is on
4. Fix by editing the page or setting up a redirect

### Why It Matters
- Broken links frustrate visitors
- Google penalizes sites with many broken links
- Check monthly — external sites change their URLs
MD,

'/admin/content-quality' => <<<'MD'
## Content Quality Check

AI evaluates your content for readability, accuracy, and engagement.

### Quality Metrics
- **Readability Score** — Flesch-Kincaid grade level
- **Grammar & Spelling** — error detection
- **Keyword Usage** — density and placement
- **Content Structure** — headings, paragraphs, lists
- **Engagement Signals** — questions, CTAs, formatting variety

### How to Use
1. Select a page or article
2. Click **"Check Quality"**
3. Review the score and recommendations
4. Fix issues directly in the editor
MD,

'/admin/chat-settings' => <<<'MD'
## AI Chatbot Settings

Configure the customer-facing AI chatbot on your website.

### Setup
1. **Enable/Disable** the chatbot
2. Set the **welcome message** visitors see
3. Configure **knowledge base** — what the chatbot knows about your business
4. Set **operating hours** — or run 24/7
5. Configure **handoff** — when to transfer to a human

### Customization
- **Avatar** and chatbot name
- **Colors** to match your brand
- **Position** — bottom-left or bottom-right
- **Pre-set questions** — common questions visitors can click
MD,

'/admin/ai-components' => <<<'MD'
## AI Components

Generate reusable UI components with AI for your website.

### What It Creates
- Hero sections, feature grids, testimonial sliders
- Pricing tables, team sections, FAQ accordions
- CTA blocks, stat counters, timeline sections
- Contact forms, newsletter signups

### How to Use
1. Describe the component you need
2. Select **Style** — modern, classic, minimal, bold
3. AI generates HTML + CSS
4. Preview in real-time
5. Save to your component library for reuse
6. Insert into any page via the editor
MD,

'/admin/email-campaigns' => <<<'MD'
## Email Campaigns

Create and send HTML email campaigns to your subscribers.

### Creating a Campaign
1. Click **"+ New Campaign"**
2. Enter **Subject Line** and **Preview Text**
3. Design the email using the template editor
4. Select **Recipients** — all subscribers or a segment
5. **Preview & Test** — always send yourself a test first
6. **Schedule** or **Send Now**

### Best Practices
- Subject lines under 50 characters get higher open rates
- Include a clear **call to action** (one main CTA per email)
- **Personalize** — use subscriber's name when possible
- Send at optimal times — Tuesday-Thursday, 10am tends to perform best
- Always include an **unsubscribe link** (legally required)
MD,

'/admin/email-queue' => <<<'MD'
## Email Queue

Monitor outgoing emails and manage delivery.

### Queue Status
- **Pending** — waiting to be sent
- **Sent** — delivered successfully
- **Failed** — delivery error (click to see why)
- **Retry** — will attempt again automatically

### Troubleshooting Failed Emails
1. Check the **error message** — usually SMTP connection or authentication
2. Verify **SMTP settings** in Email Settings
3. **Retry** individual emails or retry all failed
4. Check your email provider's sending limits
MD,

'/admin/email-settings' => <<<'MD'
## Email Settings

Configure how your CMS sends emails.

### SMTP Setup
1. Enter your SMTP server details:
   - **Host** — e.g. smtp.gmail.com, smtp.sendgrid.net
   - **Port** — usually 587 (TLS) or 465 (SSL)
   - **Username** — your email or API key
   - **Password** — your email password or API secret
   - **Encryption** — TLS (recommended) or SSL
2. Set **From Name** and **From Email**
3. Click **"Send Test Email"** to verify

### Recommended Providers
| Provider | Free Tier | Notes |
|----------|-----------|-------|
| SendGrid | 100/day | Best for transactional |
| Mailgun | 5000/month | Good API |
| Gmail SMTP | 500/day | Easiest setup |
| Amazon SES | 62K/month | Cheapest at scale |
MD,

'/admin/social-media' => <<<'MD'
## Social Media Manager

Schedule and publish posts across multiple social platforms.

### Connecting Accounts
1. Go to **Social Media → Accounts**
2. Click **"Connect"** for each platform
3. Authorize access via OAuth
4. Supported: Facebook, Instagram, Twitter/X, LinkedIn

### Creating Posts
1. Click **"+ New Post"**
2. Write your content (platform-specific character limits shown)
3. Attach **images or video**
4. Select which **platforms** to publish on
5. **Schedule** for later or **Post Now**
6. Preview how it looks on each platform

### Calendar View
Visual calendar showing all scheduled posts. Drag to reschedule.

### Analytics
Track per-post performance: impressions, clicks, engagement, followers gained.
MD,

'/admin/notifications' => <<<'MD'
## Notifications

System notifications and alerts for admin users.

### Notification Types
- **Orders** — new orders, payment received, refund requested
- **Comments** — new comments awaiting moderation
- **Contact Forms** — new form submissions
- **Security** — failed login attempts, suspicious activity
- **System** — updates available, backup reminders, errors

### Managing Notifications
- Click to **read** and view details
- **Mark All Read** to clear the badge
- **Delete** individual notifications or **Clear All**
MD,

'/admin/ab-testing' => <<<'MD'
## A/B Testing

Test different versions of pages to see which performs better.

### Setting Up a Test
1. Click **"+ New Test"**
2. Select the **Original Page** (control)
3. Create a **Variant** — change headline, image, layout, CTA, etc.
4. Set **Traffic Split** — e.g. 50/50, 70/30
5. Define **Success Metric** — clicks, conversions, time on page
6. Set **Duration** — minimum 2 weeks for reliable results
7. Start the test

### Reading Results
- **Statistical Significance** — wait until 95%+ before deciding
- **Confidence Interval** — shown as percentage
- Green = winner is clear, Yellow = need more data

### Tips
- Test **one thing at a time** — headline OR image, not both
- Run tests for at least 1000 visitors per variant
- Document results for future reference
MD,

'/admin/popups' => <<<'MD'
## Pop-ups

Create targeted pop-ups for lead generation and promotions.

### Creating a Pop-up
1. Click **"+ New Pop-up"**
2. Choose **Type** — modal, slide-in, top bar, full-screen
3. Design the content — headline, text, image, form
4. Set **Triggers**:
   - Exit intent (mouse leaves viewport)
   - Time delay (show after X seconds)
   - Scroll depth (show after scrolling X%)
   - Page count (show on 2nd+ page view)
5. Set **Display Rules** — which pages, how often, date range
6. Activate

### Tips
- Don't show pop-ups on mobile (annoying + Google penalty)
- Limit to 1 pop-up per visit
- Offer real value — discount, free content, not "subscribe to our newsletter"
MD,

'/admin/themes' => <<<'MD'
## Theme Management

Browse, install, and switch website themes.

### Changing Your Theme
1. Browse available themes in the gallery
2. Click **"Preview"** to see how your content looks
3. Click **"Activate"** to make it live
4. Customize in **Theme Studio**

### Theme Types
- **Starter Themes** — clean starting points for AI Theme Builder
- **AI-Generated** — themes created by AI Theme Builder
- **Custom** — manually coded themes

### Tips
- Always **preview before activating** — check all pages
- Your content stays the same when switching themes
- Create a **backup** before major theme changes
MD,

'/admin/theme-studio' => <<<'MD'
## Theme Studio

Customize your active theme's colors, fonts, layout, and content.

### What You Can Customize
- **Brand** — logo, site name, primary colors
- **Typography** — heading and body fonts, sizes
- **Header** — layout, navigation style, CTA button
- **Footer** — columns, links, copyright text
- **Colors** — primary, secondary, accent, background
- **Sections** — hero, features, testimonials, etc.

### How to Use
1. Select a **section** from the left panel
2. Edit settings in the right panel
3. See changes in **real-time preview**
4. Click **"Save"** when happy

### Tips
- Stick to 2-3 colors for a professional look
- Use high-contrast text colors for readability
- Test on mobile — click the device toggle in preview
MD,

'/admin/jessie-theme-builder' => <<<'MD'
## Page Builder (JTB)

Drag-and-drop page builder with 79 modules.

### Building a Page
1. Select or create a page
2. Drag **modules** from the library onto the canvas
3. Configure each module's content and style
4. Reorder by dragging
5. Preview and publish

### Module Categories
- **Layout** — containers, columns, rows, spacers
- **Content** — headings, text, images, videos
- **Interactive** — accordions, tabs, sliders, lightbox
- **Commerce** — product grids, pricing tables
- **Forms** — contact, subscribe, custom fields
- **Navigation** — menus, breadcrumbs, pagination
MD,

'/admin/website-builder' => <<<'MD'
## Website Builder

Visual site-wide builder for managing your website structure.

### What You Can Do
- **Add Pages** — create new pages with pre-built templates
- **Organize** — drag pages to reorder in navigation
- **Set Homepage** — choose which page is your landing page
- **Preview** — see your entire site structure at a glance

### Quick Start
1. Choose a **template** — blog, business, portfolio, shop
2. AI generates starter pages
3. Customize content on each page
4. Configure navigation and footer
5. Launch!
MD,

'/admin/modules' => <<<'MD'
## Module Management

Enable and configure CMS modules (plugins).

### Available Modules
Modules extend your CMS with major features:
- **Booking** — appointment scheduling
- **LMS** — online courses
- **Events** — ticketing and events
- **Newsletter** — email campaigns
- **Membership** — paid content access
- **Directory** — business listings
- And more...

### Enabling a Module
1. Find the module in the list
2. Click **"Enable"**
3. Database tables are created automatically
4. Configure in the module's admin section
MD,

'/admin/gdpr-tools' => <<<'MD'
## GDPR Tools

Manage data privacy compliance.

### Features
- **Data Export** — export all data for a specific user (right to portability)
- **Data Deletion** — anonymize or delete user data (right to erasure)
- **Consent Log** — track who consented to what and when
- **Cookie Audit** — list all cookies your site sets
- **Privacy Impact Assessment** — evaluate data processing risks

### Compliance Checklist
- ✅ Privacy Policy page published
- ✅ Cookie consent banner active
- ✅ Data export available to users
- ✅ Account deletion available to users
- ✅ Consent recorded before data processing
- ✅ Data retention policy defined
MD,

'/admin/api-keys' => <<<'MD'
## API Keys

Manage authentication keys for the CMS REST API.

### Creating an API Key
1. Click **"+ New Key"**
2. Enter a **Name** (what it's for)
3. Set **Permissions** — read-only, read-write, or specific resources
4. Set **Expiry** — optional, recommended for security
5. Copy the generated key — **it won't be shown again**

### Using API Keys
Include in requests as a header:
```
Authorization: Bearer YOUR_API_KEY
```

### Security Tips
- Use separate keys for different applications
- Set minimal permissions — principle of least privilege
- Rotate keys periodically (every 90 days)
- Delete unused keys immediately
MD,

'/admin/languages' => <<<'MD'
## Language Management

Configure languages for your multi-language site.

### Adding Languages
1. Click **"+ Add Language"**
2. Select from the list (50+ languages available)
3. Set as **Default** or secondary language
4. Enable/disable languages as needed

### Translation Workflow
1. Create content in the default language
2. Use **AI Translate** to generate translations
3. Review and publish translated versions
4. Language switcher appears automatically on the frontend
MD,

'/admin/white-label' => <<<'MD'
## White Label

Remove Jessie CMS branding and replace with your own.

### Customization Options
- **Admin Logo** — your logo in the admin panel header
- **Login Logo** — shown on the admin login page
- **Admin Title** — custom text instead of "Jessie CMS"
- **Footer Text** — custom admin panel footer
- **Color Scheme** — match your brand colors
- **Favicon** — custom browser tab icon

### Use Cases
- Building sites for clients — hide CMS branding
- Agency dashboard — show your agency brand
- Enterprise deployment — company branding
MD,

'/admin/logs' => <<<'MD'
## System Logs

View application logs for debugging and monitoring.

### Log Types
- **Application Logs** — PHP errors, warnings, notices
- **Access Logs** — page requests and response codes
- **Error Logs** — detailed error traces
- **AI Logs** — API calls and responses

### Using Logs
1. Select log **type** and **date range**
2. **Search** for specific errors or patterns
3. **Filter** by severity (error, warning, info)
4. Click any entry for full details

### Tips
- Check logs after deployments for new errors
- Set up **email alerts** for critical errors
- Clear old logs monthly to save disk space
MD,

'/admin/scheduler' => <<<'MD'
## Task Scheduler

Automate recurring tasks on a schedule.

### Creating a Scheduled Task
1. Click **"+ New Task"**
2. Select the **Task Type** — backup, cache clear, report, custom
3. Set the **Schedule** — daily, weekly, monthly, custom cron
4. Configure task-specific options
5. Enable and save

### Common Tasks
- **Daily backup** at 3 AM
- **Weekly SEO report** every Monday
- **Monthly cleanup** of old logs and sessions
- **Hourly cache clear** for high-traffic sites
MD,

'/admin/updates' => <<<'MD'
## System Updates

Check for and install CMS updates.

### Update Process
1. Check for available updates
2. Review the **changelog** — what's new, fixed, changed
3. **Create a backup** before updating (always!)
4. Click **"Update"** to apply
5. Verify everything works after updating

### Tips
- Always backup before updating
- Test updates on a staging site first (if available)
- Read the changelog — breaking changes are noted
MD,

'/admin/version-control' => <<<'MD'
## Version Control

Track content changes and restore previous versions.

### How It Works
- Every edit to a page or article creates a **version**
- View the **version history** — who changed what, when
- **Compare** two versions side-by-side (diff view)
- **Restore** any previous version with one click

### Tips
- Use version control instead of manual backups for content
- Check who made changes for accountability
- Restore if an edit introduces errors
MD,

'/admin/maintenance' => <<<'MD'
## Maintenance Mode

Take your site offline temporarily for maintenance.

### Enabling Maintenance Mode
1. Click **"Enable Maintenance Mode"**
2. Customize the **maintenance page** — message, estimated time
3. Admins can still access the site normally
4. Visitors see the maintenance page

### When to Use
- Major theme changes
- Database migrations
- Plugin installations that affect the frontend
- Content reorganization

### Tips
- Set a realistic **estimated time** — visitors appreciate honesty
- Enable **auto-disable** — maintenance mode turns off after X hours
MD,

'/admin/profile' => <<<'MD'
## Admin Profile

Manage your admin account settings.

### What You Can Change
- **Display Name** — shown in the admin panel and activity logs
- **Email Address** — used for notifications and password reset
- **Password** — change regularly for security
- **Avatar** — profile picture shown in the admin panel

### Security
- Use a **strong password** — 12+ characters, mix of letters, numbers, symbols
- Change password every 90 days
- Never share your admin credentials
MD,

'/admin/search' => <<<'MD'
## Admin Search

Search across all content in your CMS.

### What's Searchable
- Pages, Articles, Products
- Users, Orders, Comments
- Media files
- Settings

### Search Analytics
View what visitors search for on your site in **Search → Analytics**:
- Top search terms
- Searches with no results (= content gaps!)
- Search trends over time
MD,

'/admin/urls' => <<<'MD'
## URL Redirects

Manage URL redirects (301, 302) for your site.

### Creating a Redirect
1. Click **"+ New Redirect"**
2. Enter the **Source URL** (old URL)
3. Enter the **Destination URL** (new URL)
4. Select **Type**:
   - **301 Permanent** — for moved pages (passes SEO value)
   - **302 Temporary** — for temporary changes
5. Save

### When to Use
- Page slug changed → redirect old URL to new
- Deleted a page → redirect to a related page
- Merging content → redirect old pages to the merged page
- Domain migration → redirect all old domain URLs
MD,

'/admin/n8n-settings' => <<<'MD'
## n8n Integration Settings

Connect your CMS to n8n for workflow automation.

### Setup
1. Enter your **n8n Instance URL** (e.g. https://n8n.yourdomain.com)
2. Enter your **API Key** from n8n
3. Click **"Test Connection"** to verify
4. Enable **Event Forwarding** — send CMS events to n8n webhooks

### Events You Can Forward
- New order, page published, form submitted
- User registered, comment posted
- Product updated, stock low
MD,

'/admin/n8n-bindings' => <<<'MD'
## n8n Event Bindings

Map CMS events to n8n webhook URLs.

### Creating a Binding
1. Select the **CMS Event** (e.g. "order.created")
2. Enter the **n8n Webhook URL**
3. Optionally set **Filters** (e.g. only orders > $100)
4. Enable and save

### Common Automations
- Order placed → Send Slack notification
- Contact form submitted → Create CRM contact
- Article published → Post to social media
- Low stock → Email purchasing team
MD,

'/admin/automations' => <<<'MD'
## Automations

Visual automation builder for CMS workflows.

### Creating an Automation
1. Click **"+ New Automation"**
2. Set a **Trigger** — what starts it (event, schedule, condition)
3. Add **Actions** — what happens (send email, update record, notify)
4. Add **Conditions** — optional filters (if order > $50, if user is VIP)
5. Enable and save

### Example Automations
- Welcome email when user registers
- Thank you email after first purchase
- Reminder email for abandoned carts (after 24 hours)
- Admin notification for high-value orders
- Auto-approve comments from trusted users
MD,

'/admin/automation-rules' => <<<'MD'
## Automation Rules

Define conditions and actions for automated workflows.

### Rule Structure
```
WHEN [trigger event]
IF [conditions match]
THEN [perform actions]
```

### Examples
- WHEN order.created IF amount > 500 THEN notify_admin + tag_vip
- WHEN comment.created IF user.trust_score > 80 THEN auto_approve
- WHEN user.registered THEN send_welcome_email + add_to_newsletter
MD,

'/admin/docs' => <<<'MD'
## Documentation

Built-in CMS documentation and guides.

### What's Here
- **Getting Started** — setup and first steps
- **Content Management** — pages, articles, media
- **E-commerce** — shop, orders, payments
- **SEO** — optimization and analytics
- **Developer Guide** — API, templates, customization
- **Troubleshooting** — common issues and solutions

Use the **search bar** to find specific topics quickly.
MD,

'/admin/extensions' => <<<'MD'
## Extensions

Browse and install additional extensions for your CMS.

### Finding Extensions
- Browse by **category** (content, commerce, marketing, etc.)
- **Search** by name or function
- Check **ratings and reviews** before installing
- Review **compatibility** with your CMS version

### Installing
1. Find the extension you want
2. Click **"Install"**
3. Configure in the extension's settings page
4. Enable on the pages where you want it
MD,

'/admin/migrations' => <<<'MD'
## Database Migrations

Manage database schema changes and upgrades.

### What It Does
- Tracks which database changes have been applied
- Safely applies new migrations on update
- Rollback capability for failed migrations

### When You See This
- After a CMS update with database changes
- When installing a new module
- During initial setup

### Troubleshooting
If a migration fails:
1. Check the **error message**
2. Ensure database user has ALTER/CREATE permissions
3. Try running the migration again
4. If stuck, restore from backup and contact support
MD,

    ];
}
