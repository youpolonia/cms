<?php
/**
 * JTB AI Content Generator
 * Generates dynamic content for all module types using AI
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

class JTB_AI_Content
{
    // ========================================
    // CONTENT POOLS (Fallback when AI unavailable)
    // ========================================

    private static array $headlinePool = [
        'technology' => [
            'hero' => ['Build Something Extraordinary', 'Innovation Meets Simplicity', 'The Future Is Now', 'Transform Your Digital Experience', 'Power Your Growth'],
            'features' => ['Why Choose Us', 'Powerful Features', 'Everything You Need', 'Built for Scale', 'Advanced Capabilities'],
            'demonstrate' => ['See It In Action', 'How It Works', 'Use Cases', 'Real-World Applications'],
            'convince' => ['The Benefits', 'Why It Matters', 'What You Get', 'The Difference'],
            'pricing' => ['Simple, Transparent Pricing', 'Choose Your Plan', 'Pricing That Scales With You', 'Fair Pricing for Everyone'],
            'testimonials' => ['Trusted by Thousands', 'What Our Customers Say', 'Success Stories', 'Real Results, Real People'],
            'cta' => ['Ready to Get Started?', 'Start Your Journey Today', 'Join Thousands of Happy Users', 'Take the Next Step'],
            'faq' => ['Frequently Asked Questions', 'Got Questions?', 'Everything You Need to Know', 'Common Questions'],
        ],
        'agency' => [
            'hero' => ['We Create Digital Experiences', 'Design That Drives Results', 'Your Vision, Our Expertise', 'Creativity Without Limits'],
            'features' => ['Our Services', 'What We Do', 'Our Expertise', 'How We Help'],
            'demonstrate' => ['Our Process', 'How We Work', 'Our Approach'],
            'convince' => ['Why Choose Us', 'The Difference', 'What Sets Us Apart'],
            'pricing' => ['Our Packages', 'Investment Options', 'Flexible Pricing'],
            'testimonials' => ['Client Success Stories', 'What Clients Say', 'Our Track Record'],
            'cta' => ['Let\'s Work Together', 'Start Your Project', 'Get in Touch'],
            'faq' => ['Common Questions', 'FAQ', 'Have Questions?'],
        ],
        'saas' => [
            'hero' => ['The Platform That Grows With You', 'Simplify Your Workflow', 'Work Smarter, Not Harder', 'One Tool, Endless Possibilities'],
            'features' => ['Features Built for Teams', 'Everything in One Place', 'Powerful Integrations', 'Smart Automation'],
            'demonstrate' => ['See It In Action', 'Platform Tour', 'How Teams Use It'],
            'convince' => ['Why Teams Choose Us', 'The Benefits', 'What Makes It Different'],
            'pricing' => ['Plans for Every Team', 'Start Free, Scale Anytime', 'Pricing Made Simple'],
            'testimonials' => ['Teams Love Us', 'See Why 10,000+ Teams Trust Us', 'Customer Stories'],
            'cta' => ['Start Your Free Trial', 'Try Free for 14 Days', 'No Credit Card Required'],
            'faq' => ['Questions? We\'ve Got Answers', 'FAQ', 'Support Center'],
        ],
        'default' => [
            'hero' => ['Welcome to the Future', 'Your Success Starts Here', 'Discover What\'s Possible'],
            'features' => ['Our Features', 'Why We\'re Different', 'What We Offer'],
            'demonstrate' => ['How It Works', 'See It In Action', 'Platform Overview'],
            'convince' => ['Why Choose Us', 'The Benefits', 'What We Deliver'],
            'pricing' => ['Our Pricing', 'Choose Your Plan', 'Flexible Options'],
            'testimonials' => ['What People Say', 'Testimonials', 'Customer Reviews'],
            'cta' => ['Get Started Today', 'Contact Us', 'Learn More'],
            'faq' => ['Frequently Asked Questions', 'FAQ', 'Questions & Answers'],
        ],
    ];

    private static array $subheadlinePool = [
        'technology' => [
            'hero' => [
                'Powerful tools that help you build, deploy, and scale faster than ever before.',
                'Enterprise-grade infrastructure that grows with your ambitions.',
                'The modern toolkit for teams who ship fast and iterate faster.',
                'Build smarter, deploy faster, scale infinitely.',
            ],
            'features' => [
                'Everything you need to succeed, all in one powerful platform.',
                'Designed for developers who value simplicity and power.',
                'From prototype to production in record time.',
                'The tools you need, without the complexity you don\'t.',
                'Streamlined workflows for maximum productivity.',
                'Built by engineers, for engineers.',
            ],
            'cta' => [
                'Join over 10,000 companies already using our platform.',
                'See why top teams choose us for their critical infrastructure.',
                'Ready to transform your development workflow?',
            ],
        ],
        'agency' => [
            'hero' => [
                'We partner with brands to create memorable digital experiences that drive real business results.',
                'Where creativity meets strategy to build brands that matter.',
                'Crafting digital experiences that captivate and convert.',
                'Your vision, our expertise, extraordinary results.',
            ],
            'features' => [
                'From strategy to execution, we handle every aspect of your digital presence.',
                'A full-service approach tailored to your unique challenges.',
                'Creative solutions backed by data-driven insights.',
                'Award-winning design meets proven marketing strategies.',
                'We don\'t just build websites, we build business growth engines.',
                'Every pixel purposeful, every strategy measurable.',
            ],
            'cta' => [
                'Let\'s discuss how we can help you achieve your goals.',
                'Start your transformation journey today.',
                'Ready to elevate your brand? Let\'s talk.',
            ],
        ],
        'saas' => [
            'hero' => [
                'The all-in-one platform that helps teams collaborate, automate, and deliver faster.',
                'Work smarter with tools that adapt to how your team operates.',
                'Eliminate busywork and focus on what actually matters.',
                'The platform that scales with your ambitions.',
            ],
            'features' => [
                'Integrates with your favorite tools and scales with your business.',
                'Powerful automation that saves hours every week.',
                'Real-time collaboration without the chaos.',
                'Analytics that actually help you make better decisions.',
                'Security and compliance built into every feature.',
                'Customizable workflows for every team and process.',
            ],
            'cta' => [
                'Start free and upgrade when you\'re ready. No commitment required.',
                'Join thousands of teams already working smarter.',
                'See the difference in your first week.',
            ],
        ],
        'default' => [
            'hero' => [
                'Discover a better way to achieve your goals.',
                'The solution you\'ve been searching for is finally here.',
                'Transform the way you work, starting today.',
                'Experience the difference that quality makes.',
            ],
            'features' => [
                'Explore all the ways we can help you succeed.',
                'Built with care, designed for results.',
                'Simple enough to start, powerful enough to grow.',
                'Features that make a real difference in your daily work.',
                'Quality and reliability you can count on.',
                'Everything you need, nothing you don\'t.',
            ],
            'cta' => [
                'Take the first step towards your success.',
                'Your journey starts here.',
                'Ready when you are. Let\'s begin.',
            ],
        ],
    ];

    private static array $buttonTextPool = [
        // HIGH commitment - for Final CTA (scoreConversion +2)
        // Keywords: trial, buy, start, sign up, get started, subscribe, purchase, order
        'primary' => ['Start Free Trial', 'Sign Up Now', 'Get Started Free', 'Start Now', 'Try It Free', 'Subscribe Today'],

        // LOW commitment - for Hero buttons (scoreConversion +1)
        // Keywords: explore, learn, see, discover, view, how it works
        'secondary' => ['Explore Features', 'Learn More', 'See How It Works', 'Discover More', 'View Demo', 'See Pricing'],

        'cta' => ['Contact Us', 'Book a Demo', 'Schedule Call', 'Talk to Sales', 'Request Quote'],
        'submit' => ['Submit', 'Send Message', 'Get in Touch', 'Send Request'],
    ];

    private static array $featurePool = [
        'technology' => [
            ['icon' => 'zap', 'title' => 'Lightning Fast', 'desc' => 'Optimized for speed with sub-second response times.'],
            ['icon' => 'shield', 'title' => 'Enterprise Security', 'desc' => 'Bank-grade encryption and SOC 2 compliance.'],
            ['icon' => 'refresh-cw', 'title' => 'Real-time Sync', 'desc' => 'Changes sync instantly across all devices.'],
            ['icon' => 'layers', 'title' => 'Scalable Infrastructure', 'desc' => 'Grows with your business from startup to enterprise.'],
            ['icon' => 'code', 'title' => 'API First', 'desc' => 'Full REST API for seamless integrations.'],
            ['icon' => 'bar-chart-2', 'title' => 'Advanced Analytics', 'desc' => 'Deep insights with custom dashboards.'],
        ],
        'agency' => [
            ['icon' => 'pen-tool', 'title' => 'Creative Design', 'desc' => 'Award-winning designs that captivate and convert.'],
            ['icon' => 'target', 'title' => 'Strategic Planning', 'desc' => 'Data-driven strategies for maximum impact.'],
            ['icon' => 'trending-up', 'title' => 'Growth Marketing', 'desc' => 'Campaigns that drive measurable results.'],
            ['icon' => 'monitor', 'title' => 'Web Development', 'desc' => 'Custom solutions built for performance.'],
            ['icon' => 'smartphone', 'title' => 'Mobile Experience', 'desc' => 'Native and hybrid apps that delight users.'],
            ['icon' => 'users', 'title' => 'Brand Strategy', 'desc' => 'Building brands that stand out and connect.'],
        ],
        'saas' => [
            ['icon' => 'users', 'title' => 'Team Collaboration', 'desc' => 'Work together seamlessly with real-time editing.'],
            ['icon' => 'git-branch', 'title' => 'Version Control', 'desc' => 'Track changes and restore previous versions.'],
            ['icon' => 'calendar', 'title' => 'Smart Scheduling', 'desc' => 'Automate your workflow with intelligent scheduling.'],
            ['icon' => 'mail', 'title' => 'Integrations', 'desc' => 'Connect with 100+ apps you already use.'],
            ['icon' => 'pie-chart', 'title' => 'Reports & Insights', 'desc' => 'Make data-driven decisions with detailed analytics.'],
            ['icon' => 'clock', 'title' => 'Time Tracking', 'desc' => 'Track time spent and improve productivity.'],
        ],
        'default' => [
            ['icon' => 'star', 'title' => 'Quality Service', 'desc' => 'Exceptional quality in everything we do.'],
            ['icon' => 'heart', 'title' => 'Customer First', 'desc' => 'Your satisfaction is our top priority.'],
            ['icon' => 'award', 'title' => 'Proven Results', 'desc' => 'Track record of success with measurable outcomes.'],
            ['icon' => 'clock', 'title' => 'Fast Delivery', 'desc' => 'Quick turnaround without compromising quality.'],
            ['icon' => 'shield', 'title' => 'Reliable Support', 'desc' => '24/7 support whenever you need help.'],
            ['icon' => 'thumbs-up', 'title' => 'Easy to Use', 'desc' => 'Simple and intuitive, no learning curve.'],
        ],
    ];

    private static array $testimonialPool = [
        [
            'quote' => 'This platform has completely transformed how we work. Our team productivity increased by 40% in just the first month.',
            'author' => 'Sarah Johnson',
            'position' => 'CEO',
            'company' => 'TechStart Inc.',
        ],
        [
            'quote' => 'The best decision we made this year. The support team is incredible and the product just works.',
            'author' => 'Michael Chen',
            'position' => 'CTO',
            'company' => 'InnovateCo',
        ],
        [
            'quote' => 'We\'ve tried many solutions but nothing comes close. It\'s intuitive, powerful, and our clients love it.',
            'author' => 'Emily Rodriguez',
            'position' => 'Director of Operations',
            'company' => 'Global Solutions',
        ],
        [
            'quote' => 'Outstanding value for money. We saved thousands in the first quarter alone.',
            'author' => 'David Kim',
            'position' => 'Founder',
            'company' => 'StartupX',
        ],
        [
            'quote' => 'The analytics features alone are worth the investment. Now we have visibility we never had before.',
            'author' => 'Amanda Foster',
            'position' => 'Marketing Director',
            'company' => 'BrandWorks',
        ],
        [
            'quote' => 'Implementation was smooth and the team was incredibly helpful throughout the process.',
            'author' => 'James Wilson',
            'position' => 'IT Manager',
            'company' => 'Enterprise Solutions',
        ],
    ];

    private static array $pricingPool = [
        'starter' => [
            'name' => 'Starter',
            'price' => '29',
            'period' => 'month',
            'desc' => 'Perfect for individuals and small projects',
            'features' => ['Up to 5 projects', '10GB storage', 'Email support', 'Basic analytics', 'API access'],
            'cta' => 'Start Free Trial',
            'featured' => false,
        ],
        'professional' => [
            'name' => 'Professional',
            'price' => '79',
            'period' => 'month',
            'desc' => 'Best for growing teams and businesses',
            'features' => ['Unlimited projects', '100GB storage', 'Priority support', 'Advanced analytics', 'API access', 'Team collaboration', 'Custom integrations'],
            'cta' => 'Start Free Trial',
            'featured' => true,
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'price' => '199',
            'period' => 'month',
            'desc' => 'For large organizations with advanced needs',
            'features' => ['Everything in Professional', 'Unlimited storage', '24/7 phone support', 'Dedicated account manager', 'Custom development', 'SLA guarantee', 'On-premise option'],
            'cta' => 'Contact Sales',
            'featured' => false,
        ],
    ];

    private static array $faqPool = [
        [
            'question' => 'How do I get started?',
            'answer' => 'Getting started is easy! Simply sign up for a free account, and you\'ll be guided through our quick setup process. Most users are up and running within minutes.',
        ],
        [
            'question' => 'Is there a free trial?',
            'answer' => 'Yes! We offer a 14-day free trial with full access to all features. No credit card required. You can upgrade to a paid plan at any time.',
        ],
        [
            'question' => 'Can I cancel anytime?',
            'answer' => 'Absolutely. There are no long-term contracts or commitments. You can cancel your subscription at any time, and you\'ll retain access until the end of your billing period.',
        ],
        [
            'question' => 'What payment methods do you accept?',
            'answer' => 'We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and bank transfers for annual plans.',
        ],
        [
            'question' => 'Do you offer refunds?',
            'answer' => 'Yes, we offer a 30-day money-back guarantee. If you\'re not satisfied with our service, contact us within 30 days for a full refund.',
        ],
        [
            'question' => 'Is my data secure?',
            'answer' => 'Security is our top priority. We use industry-standard encryption, regular security audits, and are SOC 2 compliant. Your data is safe with us.',
        ],
        [
            'question' => 'Can I upgrade or downgrade my plan?',
            'answer' => 'Yes, you can change your plan at any time. When you upgrade, the new features are available immediately. Downgrades take effect at the next billing cycle.',
        ],
        [
            'question' => 'Do you offer team or enterprise plans?',
            'answer' => 'Absolutely! We have team plans for growing businesses and custom enterprise solutions for larger organizations. Contact our sales team for details.',
        ],
        [
            'question' => 'What kind of support do you offer?',
            'answer' => 'We provide 24/7 email support for all users, priority chat support for Pro plans, and dedicated account managers for Enterprise customers.',
        ],
        [
            'question' => 'Can I import my existing data?',
            'answer' => 'Yes, we support importing data from most major platforms. Our migration team can assist with complex migrations for Enterprise customers.',
        ],
        [
            'question' => 'How does billing work?',
            'answer' => 'We offer monthly and annual billing options. Annual plans include a 20% discount. Invoices are generated automatically and sent to your registered email.',
        ],
    ];

    private static array $statsPool = [
        ['number' => '10K+', 'label' => 'Happy Customers'],
        ['number' => '99.9%', 'label' => 'Uptime'],
        ['number' => '50M+', 'label' => 'Tasks Completed'],
        ['number' => '24/7', 'label' => 'Support'],
        ['number' => '150+', 'label' => 'Countries'],
        ['number' => '4.9', 'label' => 'Average Rating'],
        ['number' => '500K+', 'label' => 'Active Users'],
        ['number' => '98%', 'label' => 'Satisfaction Rate'],
    ];

    private static array $teamPool = [
        ['name' => 'Alex Morgan', 'position' => 'CEO & Founder', 'bio' => 'Visionary leader with 15+ years in tech.'],
        ['name' => 'Sarah Chen', 'position' => 'CTO', 'bio' => 'Engineering expert passionate about scalable solutions.'],
        ['name' => 'Michael Ross', 'position' => 'Head of Design', 'bio' => 'Award-winning designer focused on user experience.'],
        ['name' => 'Emily Watson', 'position' => 'VP of Marketing', 'bio' => 'Growth strategist with proven track record.'],
        ['name' => 'David Kim', 'position' => 'Head of Product', 'bio' => 'Product visionary obsessed with customer success.'],
        ['name' => 'Lisa Johnson', 'position' => 'Head of Sales', 'bio' => 'Relationship builder who turns prospects into partners.'],
    ];

    // ========================================
    // PUBLIC GENERATORS
    // ========================================

    /**
     * Generate content for any module type
     */
    public static function generateModuleContent(string $moduleType, array $context = []): array
    {
        $industry = $context['industry'] ?? 'default';
        $role = $context['role'] ?? 'default';
        $style = $context['style'] ?? 'modern';

        // Get module-specific index from context (set by generateRow in JTB_AI_Generator)
        // Each module type has its own counter: blurb_index, testimonial_index, etc.
        $index = match ($moduleType) {
            'blurb' => $context['blurb_index'] ?? $context['module_index'] ?? 0,
            'testimonial' => $context['testimonial_index'] ?? $context['module_index'] ?? 0,
            'pricing_table' => $context['pricing_index'] ?? $context['module_index'] ?? 0,
            'team_member' => $context['team_index'] ?? $context['module_index'] ?? 0,
            'number_counter', 'circle_counter', 'bar_counter' => $context['counter_index'] ?? $context['module_index'] ?? 0,
            'accordion', 'accordion_item' => $context['faq_index'] ?? $context['module_index'] ?? 0,
            default => $context['module_index'] ?? $context['index'] ?? 0,
        };

        $result = match ($moduleType) {
            'heading' => self::generateHeadingContent($role, $industry, $context),
            'text' => self::generateTextContent($role, $industry, $context),
            'button' => self::generateButtonContent($role, $context),
            'blurb' => self::generateBlurbContent($index, $industry, $context),
            'testimonial' => self::generateTestimonialContent($index, $context),
            'pricing_table' => self::generatePricingContent($index, $context),
            'number_counter', 'circle_counter', 'bar_counter' => self::generateCounterContent($index, $context),
            'accordion', 'accordion_item' => self::generateFaqContent($index, $context),
            'team_member' => self::generateTeamMemberContent($index, $context),
            'image' => self::generateImageContent($role, $industry, $context),
            'cta' => self::generateCtaContent($industry, $context),
            'contact_form' => self::generateContactFormContent($context),
            default => null,
        };

        // Dynamic fallback for any module not in the match - reads fields from Registry
        if ($result === null) {
            $result = self::generateDynamicContent($moduleType, $context);
        }

        return $result;
    }

    /**
     * Generate headline/heading content with complete styling
     */
    public static function generateHeadingContent(string $role, string $industry, array $context = []): array
    {
        $pool = self::$headlinePool[$industry] ?? self::$headlinePool['default'];
        $level = $context['level'] ?? 'h2';
        $purpose = $context['purpose'] ?? null;
        $sectionIndex = $context['section_index'] ?? 0;
        $elementIndex = $context['element_index'] ?? ($context['index'] ?? 0);
        // Unique index for variety across all elements
        $uniqueIndex = $sectionIndex * 20 + $elementIndex * 5 + ($context['iteration'] ?? 0);
        $itemIndex = $uniqueIndex;  // Use unique index instead of plain index
        $style = $context['style'] ?? 'modern';

        // GOLDEN PRESET CONTRACT: Use colors from context if available
        // Colors are propagated from renderPattern based on visual_context
        $typography = JTB_AI_Styles::getTypography($style);
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);

        $fontFamily = $typography['heading_font'] ?? 'Inter';
        $fontWeight = $typography['heading_weight'] ?? '700';
        $lineHeight = $typography['heading_line_height'] ?? 1.2;
        $letterSpacing = $typography['letter_spacing'] ?? '-0.02em';
        $textColor = $colors['heading'] ?? '#111827';

        // Helper to build complete heading attrs
        $buildHeadingAttrs = function($text, $headingLevel, $align = 'center', $isHero = false) use (
            $style, $fontFamily, $fontWeight, $lineHeight, $letterSpacing, $textColor, $colors
        ) {
            $fontSize = self::getHeadingSize($headingLevel, $style);
            $fontSizeTablet = (int)($fontSize * 0.85);
            $fontSizePhone = (int)($fontSize * 0.7);

            // Margin bottom based on heading level
            $marginBottom = match($headingLevel) {
                'h1' => 24,
                'h2' => 20,
                'h3' => 16,
                'h4' => 12,
                default => 16,
            };

            $attrs = [
                'text' => $text,
                'level' => $headingLevel,
                'font_family' => $fontFamily,
                'font_size' => $fontSize,
                'font_size__tablet' => $fontSizeTablet,
                'font_size__phone' => $fontSizePhone,
                'font_weight' => $fontWeight,
                'line_height' => $lineHeight,
                'letter_spacing' => $letterSpacing,
                'text_color' => $isHero ? '#FFFFFF' : $textColor,
                'text_align' => $align,
                'margin' => ['top' => 0, 'right' => 0, 'bottom' => $marginBottom, 'left' => 0],
            ];

            // Hero headings get special treatment
            if ($isHero) {
                $attrs['text_shadow'] = '0 2px 4px rgba(0,0,0,0.1)';
            }

            return $attrs;
        };

        // Special handling for beat_title role (narrative patterns like zigzag)
        if ($role === 'beat_title') {
            $beatTitles = self::getBeatTitles($purpose ?? 'default');
            $text = $beatTitles[$itemIndex % count($beatTitles)];
            return $buildHeadingAttrs($text, 'h3', $context['align'] ?? 'left', false);
        }

        // Special handling for category_title role (FAQ categories)
        if ($role === 'category_title') {
            $categories = ['General Questions', 'Technical Support', 'Billing & Payments', 'Account & Security'];
            $text = $categories[$itemIndex % count($categories)];
            return $buildHeadingAttrs($text, 'h4', $context['align'] ?? 'left', false);
        }

        // Special handling for trust_label (small uppercase)
        if ($role === 'trust_label') {
            $labels = ['Trusted by Industry Leaders', 'Used by 10,000+ Teams', 'Powering Innovation'];
            $text = $labels[$uniqueIndex % count($labels)];
            $attrs = $buildHeadingAttrs($text, 'h6', 'center', false);
            $attrs['text_transform'] = 'uppercase';
            $attrs['letter_spacing'] = '0.1em';
            $attrs['font_weight'] = '600';
            $attrs['text_color'] = $colors['text_light'] ?? '#6B7280';
            return $attrs;
        }

        // Map purpose to section type (purpose comes from Composer)
        $sectionType = match($purpose) {
            'capture' => 'hero',
            'explain', 'overview' => 'features',
            'demonstrate' => 'demonstrate',
            'convince' => 'convince',
            'convert' => 'pricing',
            'proof', 'credibility' => 'testimonials',
            'close' => 'cta',
            'reassure' => 'faq',
            default => null,
        };

        // Fallback: determine section type from role if purpose not matched
        if (!$sectionType) {
            if (str_contains($role, 'hero') || $role === 'main_heading') {
                $sectionType = 'hero';
            } elseif (str_contains($role, 'pricing')) {
                $sectionType = 'pricing';
            } elseif (str_contains($role, 'testimonial') || str_contains($role, 'proof')) {
                $sectionType = 'testimonials';
            } elseif (str_contains($role, 'cta') || str_contains($role, 'action')) {
                $sectionType = 'cta';
            } elseif (str_contains($role, 'faq') || str_contains($role, 'question')) {
                $sectionType = 'faq';
            } else {
                $sectionType = 'features';
            }
        }

        $headlines = $pool[$sectionType] ?? $pool['hero'] ?? ['Welcome'];

        // Use unique index (section + element + iteration) for variety
        $textIndex = $uniqueIndex % count($headlines);
        $text = $headlines[$textIndex];

        // Determine if this is a hero section (for white text)
        $isHero = $sectionType === 'hero' || $role === 'hero_title';
        $isCta = $sectionType === 'cta' || str_contains($role, 'cta');

        // CTA headings should also be white (usually on colored background)
        $attrs = $buildHeadingAttrs($text, $level, $context['align'] ?? 'center', $isHero || $isCta);

        return $attrs;
    }

    /**
     * Get beat titles for narrative patterns
     * Returns titles based on pattern purpose
     */
    private static function getBeatTitles(string $purpose): array
    {
        $beats = [
            'explain' => ['Understand Your Needs', 'Design the Solution', 'Implement & Iterate', 'Measure Success', 'Scale & Grow'],
            'benefits' => ['Save Time', 'Reduce Costs', 'Increase Quality', 'Scale Effortlessly', 'Stay Ahead'],
            'convince' => ['Save Time & Money', 'Boost Productivity', 'Reduce Risk', 'Scale With Ease', 'Future-Proof Your Business'],
            'demonstrate' => ['See How It Works', 'Watch It In Action', 'Real-World Examples', 'Success Stories'],
            'process' => ['Discovery', 'Strategy', 'Execution', 'Optimization', 'Growth'],
            'story' => ['Where We Started', 'The Turning Point', 'Our Mission Today', 'Looking Forward'],
            'case_studies' => ['The Challenge', 'Our Approach', 'The Solution', 'The Results', 'What\'s Next'],
            'features' => ['Smart Automation', 'Seamless Integration', 'Real-time Analytics', 'Enterprise Security', 'Global Scale'],
            'default' => ['First', 'Then', 'Finally', 'Beyond', 'Forever'],
        ];

        return $beats[$purpose] ?? $beats['default'];
    }

    /**
     * Generate paragraph/text content
     */
    public static function generateTextContent(string $role, string $industry, array $context = []): array
    {
        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET CONTRACT: Use colors from context if available
        $typography = JTB_AI_Styles::getTypography($style);
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);

        $purpose = $context['purpose'] ?? null;
        $itemIndex = $context['index'] ?? 0;

        // Base text styling
        $baseAttrs = [
            'font_size' => $typography['body_size'] ?? 18,
            'font_size__tablet' => 16,
            'font_size__phone' => 16,
            'line_height' => $typography['line_height'] ?? 1.7,
            'text_color' => $colors['text'] ?? '#4B5563',
            'margin' => ['top' => 0, 'right' => 0, 'bottom' => 20, 'left' => 0],
        ];

        // Special handling for beat_content role (narrative patterns like zigzag)
        if ($role === 'beat_content') {
            $beatPurpose = $purpose ?? 'benefits'; // Default to 'benefits' if purpose is null
            $beatContent = self::getBeatContent($beatPurpose);
            $text = $beatContent[$itemIndex % count($beatContent)];

            return array_merge($baseAttrs, [
                'content' => '<p>' . $text . '</p>',
                'text_align' => $context['align'] ?? 'left',
            ]);
        }

        $pool = self::$subheadlinePool[$industry] ?? self::$subheadlinePool['default'];

        // Match role to content type
        $contentType = 'features';
        if (str_contains($role, 'hero') || str_contains($role, 'intro')) {
            $contentType = 'hero';
        } elseif (str_contains($role, 'cta')) {
            $contentType = 'cta';
        }

        // Get text array for this content type
        $textPool = $pool[$contentType] ?? $pool['hero'] ?? ['Discover what makes us different.'];
        // Ensure it's an array (backward compatibility)
        if (!is_array($textPool)) {
            $textPool = [$textPool];
        }

        // Calculate unique index using section + element + iteration
        $sectionIndex = $context['section_index'] ?? 0;
        $elementIndex = $context['element_index'] ?? ($context['index'] ?? 0);
        $uniqueIndex = $sectionIndex * 20 + $elementIndex * 5 + ($context['iteration'] ?? 0);

        $text = $textPool[$uniqueIndex % count($textPool)];

        // Hero text is larger and lighter colored
        if ($contentType === 'hero') {
            $baseAttrs['font_size'] = 20;
            $baseAttrs['font_size__tablet'] = 18;
            $baseAttrs['text_color'] = $colors['text_light'] ?? '#6B7280';
            $baseAttrs['max_width'] = '600px';
        }

        // CTA text may be white (on colored backgrounds)
        if ($contentType === 'cta') {
            $baseAttrs['text_color'] = '#FFFFFF';
            $baseAttrs['opacity'] = 0.9;
        }

        return array_merge($baseAttrs, [
            'content' => '<p>' . $text . '</p>',
            'text_align' => $context['align'] ?? 'center',
        ]);
    }

    /**
     * Get beat content for narrative patterns
     * Returns descriptions based on pattern purpose
     */
    private static function getBeatContent(string $purpose): array
    {
        $contents = [
            'explain' => [
                'We start by understanding your unique challenges and goals through in-depth discovery sessions.',
                'Our team crafts a tailored solution that addresses your specific needs and fits your workflow.',
                'We work closely with you to implement the solution, iterating based on your feedback.',
                'Clear metrics and reporting help you see the real impact on your business.',
                'As you grow, our platform scales with you, supporting your continued success.',
            ],
            'benefits' => [
                'Automate repetitive tasks and focus on what matters most to your business.',
                'Eliminate inefficiencies and reduce operational costs across your organization.',
                'Maintain consistent quality with built-in checks and standardized processes.',
                'Handle growth without adding complexity or overhead to your operations.',
                'Stay competitive with tools that evolve alongside industry trends.',
            ],
            'convince' => [
                'Cut hours off your workweek by automating tedious manual tasks. More time for what matters.',
                'Teams using our platform see an average 40% increase in output. Real results, not promises.',
                'Enterprise-grade security and reliability built-in. Sleep well knowing your data is protected.',
                'From startup to enterprise, our infrastructure grows with you. No migration headaches.',
                'Built on modern architecture with regular updates. Your investment is protected for years to come.',
            ],
            'demonstrate' => [
                'See exactly how the platform handles your most common workflows in real-time.',
                'Watch as tasks that used to take hours are completed in minutes.',
                'Explore real implementations from companies in your industry.',
                'Discover how teams like yours have achieved measurable results.',
            ],
            'story' => [
                'What started as a simple idea in a small garage has grown into something we\'re truly proud of.',
                'A pivotal moment changed everything - we realized there had to be a better way.',
                'Today, we\'re on a mission to empower teams worldwide with tools they actually love using.',
                'The future is bright, and we\'re just getting started. Join us on this journey.',
            ],
            'features' => [
                'Let intelligent workflows handle the heavy lifting while you focus on strategy.',
                'Connect with all your favorite tools in minutes. No complex setup required.',
                'Make data-driven decisions with insights delivered when you need them most.',
                'Your data is protected with bank-level encryption and compliance certifications.',
                'Serve customers anywhere in the world with our distributed infrastructure.',
            ],
            'default' => [
                'Discover a better way to achieve your goals.',
                'Take the next step towards success.',
                'Experience the difference for yourself.',
            ],
        ];

        return $contents[$purpose] ?? $contents['default'];
    }

    /**
     * Generate button content
     */
    public static function generateButtonContent(string $role, array $context = []): array
    {
        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET CONTRACT: Use colors from context if available
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);
        $preset = JTB_AI_Styles::getStylePreset($style, $context);
        $buttonStyles = $preset['buttons'] ?? [];
        $shadows = $preset['shadows'] ?? [];

        // Get purpose from context for better CTA text selection
        $purpose = $context['purpose'] ?? $context['pattern_purpose'] ?? null;
        $sectionType = $context['section_type'] ?? $context['pattern_name'] ?? '';

        // SCORING OPTIMIZATION:
        // Hero buttons should be LOW commitment (secondary pool) - scoreConversion +1
        // Final CTA buttons should be HIGH commitment (primary pool) - scoreConversion +2
        $buttonType = 'primary';

        // Purpose-based selection (takes priority)
        if ($purpose === 'capture' || str_contains($sectionType, 'hero')) {
            // Hero needs LOW commitment text for scoring
            $buttonType = 'secondary';
        } elseif ($purpose === 'close' || str_contains($sectionType, 'final_cta') || str_contains($sectionType, 'cta')) {
            // Final CTA needs HIGH commitment text for scoring
            $buttonType = 'primary';
        } elseif (str_contains($role, 'secondary') || str_contains($role, 'learn') || str_contains($role, 'watch')) {
            $buttonType = 'secondary';
        } elseif (str_contains($role, 'cta') || str_contains($role, 'contact')) {
            $buttonType = 'cta';
        } elseif (str_contains($role, 'submit') || str_contains($role, 'send')) {
            $buttonType = 'submit';
        }

        $pool = self::$buttonTextPool[$buttonType] ?? self::$buttonTextPool['primary'];
        // Use element_index for variety (unique per element in column)
        $sectionIndex = $context['section_index'] ?? 0;
        $elementIndex = $context['element_index'] ?? ($context['index'] ?? 0);
        $uniqueIndex = $sectionIndex * 20 + $elementIndex * 5 + ($context['iteration'] ?? 0);
        $text = $pool[$uniqueIndex % count($pool)];

        // Base button attributes
        $attrs = [
            'text' => $text,
            'link_url' => '#',
            'font_size' => $buttonStyles['font_size'] ?? 16,
            'font_weight' => $buttonStyles['font_weight'] ?? '600',
            'border_radius' => $buttonStyles['border_radius'] ?? ['top_left' => 8, 'top_right' => 8, 'bottom_right' => 8, 'bottom_left' => 8],
            'padding' => ['top' => 14, 'right' => 28, 'bottom' => 14, 'left' => 28],
            'transition_duration' => '300ms',
        ];

        // Style based on button type
        if ($buttonType === 'secondary') {
            // Ghost/outline button
            $attrs['background_color'] = 'transparent';
            $attrs['text_color'] = $colors['primary'] ?? '#3B82F6';
            $attrs['border_width'] = ['top' => 2, 'right' => 2, 'bottom' => 2, 'left' => 2];
            $attrs['border_color'] = $colors['primary'] ?? '#3B82F6';
            // Hover: fill with primary
            $attrs['background_color__hover'] = $colors['primary'] ?? '#3B82F6';
            $attrs['text_color__hover'] = '#FFFFFF';
            $attrs['border_color__hover'] = $colors['primary'] ?? '#3B82F6';
        } else {
            // Primary/CTA button
            $attrs['background_color'] = $colors['primary'] ?? '#3B82F6';
            $attrs['text_color'] = '#FFFFFF';
            $attrs['border_width'] = ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0];
            $attrs['box_shadow'] = $shadows['button'] ?? '0 4px 6px -1px rgba(0,0,0,0.1)';
            // Hover: darker shade
            $attrs['background_color__hover'] = $colors['secondary'] ?? '#1E40AF';
            $attrs['text_color__hover'] = '#FFFFFF';
            $attrs['box_shadow__hover'] = '0 6px 12px -2px rgba(0,0,0,0.15)';
            $attrs['transform__hover'] = 'translateY(-2px)';
        }

        // Large buttons for hero sections
        if (str_contains($role, 'hero') || str_contains($role, 'cta')) {
            $attrs['padding'] = ['top' => 16, 'right' => 32, 'bottom' => 16, 'left' => 32];
            $attrs['font_size'] = 18;
        }

        return $attrs;
    }

    /**
     * Generate blurb/feature content with complete card styling
     */
    public static function generateBlurbContent(int $index, string $industry, array $context = []): array
    {
        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET CONTRACT: Use colors from context if available
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);
        $preset = JTB_AI_Styles::getStylePreset($style, $context);
        $typography = $preset['typography'] ?? [];
        $shadows = $preset['shadows'] ?? [];
        $borders = $preset['borders'] ?? [];

        $pool = self::$featurePool[$industry] ?? self::$featurePool['default'];
        $feature = $pool[$index % count($pool)];

        return [
            // Content
            'title' => $feature['title'],
            'content' => '<p>' . $feature['desc'] . '</p>',
            'font_icon' => $feature['icon'],
            'use_icon' => true,

            // Icon styling
            'icon_color' => $colors['primary'] ?? '#3B82F6',
            'icon_font_size' => 32,
            'icon_placement' => 'top',
            'use_icon_circle' => true,
            'icon_circle_color' => ($colors['primary'] ?? '#3B82F6') . '15', // 15% opacity
            'icon_circle_size' => 72,

            // Title styling
            'title_font_size' => 20,
            'title_font_weight' => $typography['heading_weight'] ?? '700',
            'title_color' => $colors['heading'] ?? '#111827',
            'title_line_height' => 1.3,

            // Content/body styling
            'content_font_size' => $typography['body_size'] ?? 16,
            'content_color' => $colors['text_light'] ?? '#6B7280',
            'content_line_height' => 1.6,

            // Card styling
            'text_orientation' => $context['align'] ?? 'center',
            'background_color' => '#FFFFFF',
            'padding' => ['top' => 32, 'right' => 24, 'bottom' => 32, 'left' => 24],
            'border_radius' => ['top_left' => 12, 'top_right' => 12, 'bottom_right' => 12, 'bottom_left' => 12],
            'box_shadow' => $shadows['card'] ?? '0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06)',

            // Hover effects
            'box_shadow__hover' => $shadows['elevated'] ?? '0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04)',
            'transform__hover' => 'translateY(-4px)',
            'transition_duration' => '300ms',

            // Border accent (optional top border in primary color)
            'border_width' => ['top' => 3, 'right' => 0, 'bottom' => 0, 'left' => 0],
            'border_color' => $colors['primary'] ?? '#3B82F6',
        ];
    }

    /**
     * Generate testimonial content with complete card styling
     */
    public static function generateTestimonialContent(int $index, array $context = []): array
    {
        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET CONTRACT: Use colors from context if available
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);
        $preset = JTB_AI_Styles::getStylePreset($style, $context);
        $typography = $preset['typography'] ?? [];
        $shadows = $preset['shadows'] ?? [];

        $testimonial = self::$testimonialPool[$index % count(self::$testimonialPool)];

        // Try to get person image from Pexels
        $imageUrl = '';
        if (class_exists(__NAMESPACE__ . '\\JTB_AI_Pexels') && JTB_AI_Pexels::isConfigured()) {
            $gender = $index % 2 === 0 ? 'woman' : 'man';
            $result = JTB_AI_Pexels::getPersonPhoto(['gender' => $gender]);
            if ($result['ok']) {
                $imageUrl = $result['url'];
            }
        }

        // Fallback portrait placeholder if Pexels not configured
        if (empty($imageUrl)) {
            $placeholderColors = ['4F46E5', '7C3AED', '059669', '0891B2', 'DC2626'];
            $color = $placeholderColors[$index % count($placeholderColors)];
            $initials = substr($testimonial['author'], 0, 1);
            $imageUrl = "https://placehold.co/100x100/{$color}/ffffff?text=" . urlencode($initials);
        }

        return [
            // Content
            'content' => '<p>"' . $testimonial['quote'] . '"</p>',
            'author' => $testimonial['author'],
            'job_title' => $testimonial['position'],
            'company' => $testimonial['company'],
            'portrait_url' => $imageUrl,

            // Quote styling
            'quote_font_size' => 18,
            'quote_font_style' => 'italic',
            'quote_color' => $colors['text'] ?? '#1F2937',
            'quote_line_height' => 1.7,

            // Author styling
            'author_font_size' => 16,
            'author_font_weight' => '600',
            'author_color' => $colors['heading'] ?? '#111827',

            // Position styling
            'position_font_size' => 14,
            'position_color' => $colors['text_light'] ?? '#6B7280',

            // Portrait styling
            'portrait_width' => 64,
            'portrait_height' => 64,
            'portrait_border_radius' => '50%',
            'portrait_border_width' => 3,
            'portrait_border_color' => $colors['primary'] ?? '#3B82F6',

            // Card styling
            'background_color' => '#FFFFFF',
            'padding' => ['top' => 32, 'right' => 32, 'bottom' => 32, 'left' => 32],
            'border_radius' => ['top_left' => 16, 'top_right' => 16, 'bottom_right' => 16, 'bottom_left' => 16],
            'box_shadow' => $shadows['card'] ?? '0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06)',

            // Quote icon
            'use_quote_icon' => true,
            'quote_icon_color' => ($colors['primary'] ?? '#3B82F6') . '20',
            'quote_icon_size' => 48,

            // Hover effect
            'box_shadow__hover' => $shadows['elevated'] ?? '0 20px 25px -5px rgba(0,0,0,0.1)',
            'transform__hover' => 'translateY(-4px)',
            'transition_duration' => '300ms',
        ];
    }

    /**
     * Generate pricing table content with complete styling
     */
    public static function generatePricingContent(int $index, array $context = []): array
    {
        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET CONTRACT: Use colors from context if available
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);
        $preset = JTB_AI_Styles::getStylePreset($style, $context);
        $typography = $preset['typography'] ?? [];
        $shadows = $preset['shadows'] ?? [];

        $plans = array_values(self::$pricingPool);
        $plan = $plans[$index % count($plans)];
        $isFeatured = $plan['featured'];

        return [
            // Content
            'title' => $plan['name'],
            'subtitle' => $plan['desc'],
            'currency' => '$',
            'price' => $plan['price'],
            'per' => $plan['period'],
            'content' => '<ul><li>' . implode('</li><li>', $plan['features']) . '</li></ul>',
            'button_text' => $plan['cta'],
            'link_url' => '#',
            'featured' => $isFeatured,
            'featured_text' => $isFeatured ? 'Most Popular' : '',

            // Card styling
            'background_color' => $isFeatured ? $colors['primary'] : '#FFFFFF',
            'padding' => ['top' => 40, 'right' => 32, 'bottom' => 40, 'left' => 32],
            'border_radius' => ['top_left' => 16, 'top_right' => 16, 'bottom_right' => 16, 'bottom_left' => 16],
            'box_shadow' => $isFeatured
                ? ($shadows['elevated'] ?? '0 20px 25px -5px rgba(0,0,0,0.15)')
                : ($shadows['card'] ?? '0 4px 6px -1px rgba(0,0,0,0.1)'),

            // Scale featured plan
            'transform' => $isFeatured ? 'scale(1.05)' : 'none',
            'z_index' => $isFeatured ? 10 : 1,

            // Title styling
            'title_font_size' => 24,
            'title_font_weight' => '700',
            'title_color' => $isFeatured ? '#FFFFFF' : ($colors['heading'] ?? '#111827'),

            // Price styling
            'price_font_size' => 48,
            'price_font_weight' => '800',
            'price_color' => $isFeatured ? '#FFFFFF' : ($colors['heading'] ?? '#111827'),
            'currency_font_size' => 24,
            'per_font_size' => 16,
            'per_color' => $isFeatured ? 'rgba(255,255,255,0.8)' : ($colors['text_light'] ?? '#6B7280'),

            // Features list styling
            'features_font_size' => 15,
            'features_color' => $isFeatured ? 'rgba(255,255,255,0.9)' : ($colors['text'] ?? '#4B5563'),
            'features_icon_color' => $isFeatured ? '#FFFFFF' : ($colors['primary'] ?? '#3B82F6'),
            'features_spacing' => 12,

            // Button styling (inside pricing card)
            'button_background_color' => $isFeatured ? '#FFFFFF' : ($colors['primary'] ?? '#3B82F6'),
            'button_text_color' => $isFeatured ? ($colors['primary'] ?? '#3B82F6') : '#FFFFFF',
            'button_font_weight' => '600',
            'button_padding' => ['top' => 14, 'right' => 24, 'bottom' => 14, 'left' => 24],
            'button_border_radius' => ['top_left' => 8, 'top_right' => 8, 'bottom_right' => 8, 'bottom_left' => 8],
            'button_width' => '100%',

            // Featured badge styling
            'badge_background_color' => $colors['accent'] ?? '#F59E0B',
            'badge_text_color' => '#FFFFFF',
            'badge_font_size' => 12,
            'badge_font_weight' => '600',
            'badge_padding' => ['top' => 6, 'right' => 16, 'bottom' => 6, 'left' => 16],
            'badge_border_radius' => 20,

            // Hover effects
            'box_shadow__hover' => $shadows['elevated'] ?? '0 25px 35px -5px rgba(0,0,0,0.15)',
            'transform__hover' => $isFeatured ? 'scale(1.07)' : 'translateY(-4px)',
            'transition_duration' => '300ms',

            // Border for non-featured
            'border_width' => $isFeatured ? ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0] : ['top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1],
            'border_color' => $colors['border'] ?? '#E5E7EB',
        ];
    }

    /**
     * Generate counter/stats content with styling
     */
    public static function generateCounterContent(int $index, array $context = []): array
    {
        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET CONTRACT: Use colors from context if available
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);
        $typography = JTB_AI_Styles::getTypography($style);

        $stat = self::$statsPool[$index % count(self::$statsPool)];

        // Parse number (remove K+, M+, %, etc.)
        $number = preg_replace('/[^0-9.]/', '', $stat['number']);
        $suffix = preg_replace('/[0-9.]/', '', $stat['number']);

        return [
            // Content
            'number' => (float) $number,
            'title' => $stat['label'],
            'percent_sign' => str_contains($stat['number'], '%'),
            'number_suffix' => $suffix,

            // Number styling
            'number_font_size' => 48,
            'number_font_size__tablet' => 40,
            'number_font_size__phone' => 32,
            'number_font_weight' => '800',
            'number_color' => $colors['primary'] ?? '#3B82F6',
            'number_line_height' => 1.1,

            // Title styling
            'title_font_size' => 16,
            'title_font_weight' => '500',
            'title_color' => $colors['text_light'] ?? '#6B7280',
            'title_text_transform' => 'uppercase',
            'title_letter_spacing' => '0.05em',

            // Container styling
            'text_align' => 'center',
            'padding' => ['top' => 24, 'right' => 16, 'bottom' => 24, 'left' => 16],

            // Animation
            'animation' => 'countUp',
            'animation_duration' => '2000ms',
        ];
    }

    /**
     * Generate FAQ/accordion content
     */
    public static function generateFaqContent(int $index, array $context = []): array
    {
        // Create unique offset based on context to avoid repetition across groups
        $sectionIndex = $context['section_index'] ?? 0;
        $childIndex = $context['child_index'] ?? $index;
        $parentIndex = $context['parent_index'] ?? 0;
        $colIndex = $context['col_index'] ?? 0;

        // Combine indices to create unique offset - use column index to differentiate accordion groups
        $poolSize = count(self::$faqPool);
        $uniqueIndex = ($colIndex * 3) + $childIndex; // Each column gets different set of 3
        $faq = self::$faqPool[$uniqueIndex % $poolSize];

        return [
            'title' => $faq['question'],
            'content' => '<p>' . $faq['answer'] . '</p>',
            'open' => $childIndex === 0, // First one in group open by default
        ];
    }

    /**
     * Generate team member content
     */
    public static function generateTeamMemberContent(int $index, array $context = []): array
    {
        $member = self::$teamPool[$index % count(self::$teamPool)];

        // Try to get person image from Pexels
        $imageUrl = '';
        if (class_exists(__NAMESPACE__ . '\\JTB_AI_Pexels') && JTB_AI_Pexels::isConfigured()) {
            $gender = $index % 2 === 0 ? 'man' : 'woman';
            $result = JTB_AI_Pexels::getPersonPhoto(['gender' => $gender, 'role' => strtolower($member['position'])]);
            if ($result['ok']) {
                $imageUrl = $result['url'];
            }
        }

        return [
            'name' => $member['name'],
            'position' => $member['position'],
            'bio' => $member['bio'],
            'image_url' => $imageUrl,
            'facebook_url' => '#',
            'twitter_url' => '#',
            'linkedin_url' => '#',
        ];
    }

    /**
     * Generate image placeholder/actual content
     */
    public static function generateImageContent(string $role, string $industry, array $context = []): array
    {
        $imageUrl = '';
        $itemIndex = $context['index'] ?? 0;
        $alt = ucfirst(str_replace('_', ' ', $role ?? 'image'));

        // Try to get relevant image from Pexels
        if (class_exists(__NAMESPACE__ . '\\JTB_AI_Pexels') && JTB_AI_Pexels::isConfigured()) {
            if (str_contains($role, 'hero')) {
                $result = JTB_AI_Pexels::getHeroImage(['industry' => $industry]);
                $alt = 'Hero image for ' . $industry;
            } elseif (str_contains($role, 'beat')) {
                // Beat images for narrative patterns - use index for variety
                $result = JTB_AI_Pexels::getFeatureImage(['industry' => $industry, 'index' => $itemIndex]);
                $alt = 'Feature illustration ' . ($itemIndex + 1);
            } elseif (str_contains($role, 'about')) {
                $result = JTB_AI_Pexels::getAboutImage(['industry' => $industry]);
                $alt = 'About ' . $industry;
            } elseif (str_contains($role, 'feature')) {
                $result = JTB_AI_Pexels::getFeatureImage(['industry' => $industry, 'index' => $itemIndex]);
                $alt = 'Feature illustration';
            } elseif (str_contains($role, 'logo')) {
                // Logos are usually brand assets, use placeholder
                return [
                    'src' => '',
                    'alt' => 'Company logo',
                    '_placeholder' => true,
                    '_placeholder_text' => 'Logo',
                ];
            } else {
                $result = JTB_AI_Pexels::searchPhotos($industry . ' business', ['per_page' => 1]);
            }

            if (isset($result['ok']) && $result['ok'] && !empty($result['url'])) {
                $imageUrl = $result['url'];
            }
        }

        // Fallback to placeholder if no image from Pexels
        if (empty($imageUrl)) {
            // Use different colors based on role and index for visual variety
            $colorSets = [
                'hero' => ['4F46E5', '7C3AED', '2563EB', '0891B2'],
                'beat' => ['059669', '0D9488', '0891B2', '0284C7'],
                'feature' => ['7C3AED', '8B5CF6', '6366F1', '4F46E5'],
                'default' => ['6366F1', '8B5CF6', '06B6D4', '10B981'],
            ];

            $colorSet = 'default';
            if (str_contains($role, 'hero')) $colorSet = 'hero';
            elseif (str_contains($role, 'beat')) $colorSet = 'beat';
            elseif (str_contains($role, 'feature')) $colorSet = 'feature';

            $colors = $colorSets[$colorSet];
            $colorIndex = $itemIndex % count($colors);
            $color = $colors[$colorIndex];

            $width = str_contains($role, 'hero') ? 1200 : 600;
            $height = str_contains($role, 'hero') ? 600 : 400;
            $imageUrl = "https://placehold.co/{$width}x{$height}/{$color}/ffffff?text=" . urlencode($alt);
        }

        return [
            'src' => $imageUrl,
            'alt' => $alt,
            'title' => '',
        ];
    }

    /**
     * Generate CTA section content
     */
    public static function generateCtaContent(string $industry, array $context = []): array
    {
        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET CONTRACT: Use colors from context if available
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);
        $preset = JTB_AI_Styles::getStylePreset($style, $context);
        $typography = $preset['typography'] ?? [];
        $shadows = $preset['shadows'] ?? [];

        $pool = self::$headlinePool[$industry] ?? self::$headlinePool['default'];
        $subPool = self::$subheadlinePool[$industry] ?? self::$subheadlinePool['default'];

        $headlines = $pool['cta'] ?? ['Ready to Get Started?'];
        $sectionIndex = $context['section_index'] ?? 0;
        $elementIndex = $context['element_index'] ?? 0;
        $uniqueIndex = $sectionIndex * 20 + $elementIndex * 5 + ($context['iteration'] ?? 0);

        $headline = $headlines[$uniqueIndex % count($headlines)];

        // Handle subheadline pool (now arrays)
        $subheadlinePool = $subPool['cta'] ?? ['Take the first step today.'];
        if (!is_array($subheadlinePool)) {
            $subheadlinePool = [$subheadlinePool];
        }
        $subheadline = $subheadlinePool[$uniqueIndex % count($subheadlinePool)];

        $buttonPrimary = self::$buttonTextPool['primary'][$uniqueIndex % count(self::$buttonTextPool['primary'])];
        $buttonSecondary = self::$buttonTextPool['secondary'][$uniqueIndex % count(self::$buttonTextPool['secondary'])];

        return [
            // Content
            'title' => $headline,
            'content' => '<p>' . $subheadline . '</p>',
            'button_text' => $buttonPrimary,
            'link_url' => '#contact',

            // Background - use solid color (CTA module uses promo_color, not background_gradient)
            'use_background_color' => true,
            'promo_color' => $colors['primary'] ?? '#3B82F6',

            // Title styling
            'title_font_size' => 36,
            'title_font_size__tablet' => 32,
            'title_font_size__phone' => 28,
            'title_color' => '#FFFFFF',

            // Content styling
            'content_color' => 'rgba(255,255,255,0.9)',

            // Button styling (CTA module uses button_bg_color, button_text_color)
            'button_bg_color' => '#FFFFFF',
            'button_text_color' => $colors['primary'] ?? '#3B82F6',
            'button_border_radius' => 8,
            'button_bg_color__hover' => '#F3F4F6',

            // Alignment
            'text_orientation' => 'center',
        ];
    }

    /**
     * Generate contact form content
     */
    public static function generateContactFormContent(array $context = []): array
    {
        return [
            'title' => 'Get in Touch',
            'success_message' => 'Thank you for your message! We\'ll get back to you within 24 hours.',
            'submit_button_text' => 'Send Message',
            'use_spam_protection' => true,
            'fields' => [
                ['type' => 'text', 'label' => 'Name', 'required' => true],
                ['type' => 'email', 'label' => 'Email', 'required' => true],
                ['type' => 'text', 'label' => 'Subject', 'required' => false],
                ['type' => 'textarea', 'label' => 'Message', 'required' => true],
            ],
        ];
    }

    /**
     * Regenerate a specific field for a module
     */
    public static function regenerateField(string $moduleType, string $fieldName, $currentValue, array $context = []): mixed
    {
        // For simple regeneration, generate new content and return just the field
        $newContent = self::generateModuleContent($moduleType, $context);

        return $newContent[$fieldName] ?? $currentValue;
    }

    /**
     * Generate headline text with AI (when AI is available)
     */
    public static function generateHeadline(string $topic, string $style = 'professional', array $context = []): string
    {
        // Try AI first
        if (class_exists(__NAMESPACE__ . '\\JTB_AI_Core')) {
            try {
                $ai = JTB_AI_Core::getInstance();
                $prompt = "Generate a compelling headline for a {$topic} section. Style: {$style}. Return ONLY the headline text, nothing else.";
                $result = $ai->query($prompt, ['system_prompt' => 'You are a professional copywriter. Generate concise, impactful headlines.']);
                if (!empty($result) && strlen($result) < 200) {
                    return trim($result, '"\'');
                }
            } catch (\Exception $e) {
                // Fall through to pool
            }
        }

        // Fallback to pool
        $industry = $context['industry'] ?? 'default';
        $pool = self::$headlinePool[$industry] ?? self::$headlinePool['default'];
        $sectionPool = $pool['hero'] ?? ['Welcome'];
        return $sectionPool[array_rand($sectionPool)];
    }

    /**
     * Generate subheadline text with AI
     */
    public static function generateSubheadline(string $topic, string $style = 'professional', array $context = []): string
    {
        // Try AI first
        if (class_exists(__NAMESPACE__ . '\\JTB_AI_Core')) {
            try {
                $ai = JTB_AI_Core::getInstance();
                $prompt = "Generate a supporting subheadline for: {$topic}. Style: {$style}. Max 2 sentences. Return ONLY the text.";
                $result = $ai->query($prompt, ['system_prompt' => 'You are a professional copywriter.']);
                if (!empty($result) && strlen($result) < 500) {
                    return trim($result, '"\'');
                }
            } catch (\Exception $e) {
                // Fall through to pool
            }
        }

        // Fallback
        $industry = $context['industry'] ?? 'default';
        $pool = self::$subheadlinePool[$industry] ?? self::$subheadlinePool['default'];
        $heroPool = $pool['hero'] ?? ['Discover what makes us different.'];
        if (!is_array($heroPool)) {
            $heroPool = [$heroPool];
        }
        return $heroPool[array_rand($heroPool)];
    }

    /**
     * Generate paragraph text with AI
     */
    public static function generateParagraph(string $topic, int $length = 2, string $style = 'professional'): string
    {
        // Try AI first
        if (class_exists(__NAMESPACE__ . '\\JTB_AI_Core')) {
            try {
                $ai = JTB_AI_Core::getInstance();
                $prompt = "Write {$length} sentences about: {$topic}. Style: {$style}. Return ONLY the paragraph text.";
                $result = $ai->query($prompt, ['system_prompt' => 'You are a professional copywriter.']);
                if (!empty($result)) {
                    return trim($result, '"\'');
                }
            } catch (\Exception $e) {
                // Fall through
            }
        }

        return "Discover the power of our solution. We're committed to helping you achieve your goals with innovative tools and dedicated support.";
    }

    /**
     * Generate bullet points with AI
     */
    public static function generateBulletPoints(string $topic, int $count = 5): array
    {
        // Try AI first
        if (class_exists(__NAMESPACE__ . '\\JTB_AI_Core')) {
            try {
                $ai = JTB_AI_Core::getInstance();
                $prompt = "Generate {$count} bullet points for: {$topic}. Return as JSON array of strings.";
                $result = $ai->query($prompt, ['system_prompt' => 'You are a professional copywriter. Return valid JSON only.']);
                $parsed = json_decode($result, true);
                if (is_array($parsed) && count($parsed) >= $count) {
                    return array_slice($parsed, 0, $count);
                }
            } catch (\Exception $e) {
                // Fall through
            }
        }

        // Fallback
        return [
            'Powerful features that save time',
            'Easy to use interface',
            'Reliable performance',
            'Excellent customer support',
            'Regular updates and improvements',
        ];
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get appropriate heading size based on level and style
     */
    private static function getHeadingSize(string $level, string $style): int
    {
        $sizes = [
            'modern' => ['h1' => 56, 'h2' => 42, 'h3' => 32, 'h4' => 24, 'h5' => 20, 'h6' => 16],
            'minimal' => ['h1' => 48, 'h2' => 36, 'h3' => 28, 'h4' => 22, 'h5' => 18, 'h6' => 14],
            'bold' => ['h1' => 64, 'h2' => 48, 'h3' => 36, 'h4' => 28, 'h5' => 22, 'h6' => 18],
            'elegant' => ['h1' => 52, 'h2' => 40, 'h3' => 30, 'h4' => 24, 'h5' => 20, 'h6' => 16],
            'playful' => ['h1' => 58, 'h2' => 44, 'h3' => 34, 'h4' => 26, 'h5' => 20, 'h6' => 16],
            'corporate' => ['h1' => 50, 'h2' => 38, 'h3' => 28, 'h4' => 22, 'h5' => 18, 'h6' => 14],
        ];

        $styleSizes = $sizes[$style] ?? $sizes['modern'];
        return $styleSizes[$level] ?? 28;
    }

    /**
     * Dynamic content generator for ANY module type not in the match statement.
     * Reads field definitions from Registry and generates sensible defaults.
     */
    private static function generateDynamicContent(string $moduleType, array $context = []): array
    {
        try {
            $instance = JTB_Registry::get($moduleType);
            if (!$instance) {
                return [];
            }

            $fields = $instance->getFields();
            if (empty($fields)) {
                return [];
            }

            $industry = $context['industry'] ?? 'general';
            $content = [];

            foreach ($fields as $fieldName => $fieldDef) {
                $type = $fieldDef['type'] ?? 'text';
                $default = $fieldDef['default'] ?? null;

                // Use default if available
                if ($default !== null && $default !== '') {
                    $content[$fieldName] = $default;
                    continue;
                }

                // Generate appropriate content based on field type
                $content[$fieldName] = match ($type) {
                    'text' => self::generateSmartText($fieldName, $industry),
                    'textarea' => self::generateSmartParagraph($fieldName, $industry),
                    'richtext' => '<p>' . self::generateSmartParagraph($fieldName, $industry) . '</p>',
                    'url' => '#',
                    'upload' => '',
                    'color' => '#333333',
                    'number' => $fieldDef['default'] ?? ($fieldDef['min'] ?? 0),
                    'range' => $fieldDef['default'] ?? ($fieldDef['min'] ?? 0),
                    'toggle' => $fieldDef['default'] ?? false,
                    'select' => !empty($fieldDef['options']) ? array_key_first($fieldDef['options']) : '',
                    'icon' => 'star',
                    'gallery' => [],
                    'repeater' => [],
                    default => '',
                };
            }

            return $content;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Generate smart text based on field name context
     */
    private static function generateSmartText(string $fieldName, string $industry): string
    {
        // Infer purpose from field name
        $name = strtolower($fieldName);

        if (str_contains($name, 'title') || str_contains($name, 'heading') || str_contains($name, 'name')) {
            return ucwords(str_replace('_', ' ', $fieldName));
        }
        if (str_contains($name, 'label')) {
            return ucfirst(str_replace('_', ' ', str_replace('_label', '', $fieldName)));
        }
        if (str_contains($name, 'button') || str_contains($name, 'submit') || str_contains($name, 'cta')) {
            return 'Learn More';
        }
        if (str_contains($name, 'url') || str_contains($name, 'link')) {
            return '#';
        }
        if (str_contains($name, 'alt')) {
            return 'Image description';
        }
        if (str_contains($name, 'placeholder')) {
            return 'Type here...';
        }

        return ucwords(str_replace('_', ' ', $fieldName));
    }

    /**
     * Generate smart paragraph based on field name context
     */
    private static function generateSmartParagraph(string $fieldName, string $industry): string
    {
        $name = strtolower($fieldName);

        if (str_contains($name, 'description') || str_contains($name, 'content') || str_contains($name, 'bio')) {
            return "Professional " . str_replace('_', ' ', $industry) . " services designed to meet your needs. We deliver quality results with attention to detail.";
        }
        if (str_contains($name, 'excerpt') || str_contains($name, 'summary')) {
            return "A brief overview of our " . str_replace('_', ' ', $industry) . " services and solutions.";
        }
        if (str_contains($name, 'message') || str_contains($name, 'success')) {
            return "Thank you for your interest. We will get back to you shortly.";
        }

        return "Quality " . str_replace('_', ' ', $industry) . " solutions tailored to your specific requirements.";
    }
}
