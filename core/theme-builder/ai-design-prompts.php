<?php
/**
 * AI Theme Builder 4.0 - Design-First Prompts
 */
defined('CMS_ROOT') || define('CMS_ROOT', dirname(__DIR__, 2));

function tb_get_style_philosophy(string $style): array
{
    $styles = [
        'modern' => [
            'philosophy' => 'Clean lines meet bold statements. Every element has purpose. Whitespace is a design element. Think Apple meets Stripe.',
            'visual_identity' => 'Asymmetric layouts, floating elements, subtle depth. Cards that hover. Sections that breathe.',
            'colors' => ['primary' => '#8b5cf6', 'secondary' => '#06b6d4', 'dark' => '#0f172a', 'light' => '#f8fafc', 'accent' => '#f59e0b', 'gradients' => ['linear-gradient(135deg, #667eea 0%, #764ba2 100%)']],
            'typography' => ['heading_font' => 'Inter', 'body_font' => 'Inter, system-ui, sans-serif', 'accent_font' => 'JetBrains Mono, monospace', 'hero_size' => '72px', 'hero_weight' => '800', 'section_title' => '48px', 'body' => '18px', 'line_height' => '1.7'],
            'characteristics' => ['Subtle gradients (premium, not flashy)', 'Glassmorphism on cards', 'Generous padding (120-160px)', 'One dominant accent', 'Expansive shadows', 'Rounded corners (16-24px)'],
            'layout_patterns' => ['hero' => 'Split hero - heading left, mockup right. Or centered with floating elements.', 'features' => 'Bento grid - mixed size cards, not uniform', 'about' => 'Overlapping image and text, breaking grid', 'cta' => 'Full-width gradient with glassmorphism card'],
            'avoid' => 'Cluttered layouts, competing focal points, harsh transitions'
        ],
        'corporate' => [
            'philosophy' => 'Trust through structure. Professional, reliable, established. Think IBM, Deloitte - authority without arrogance.',
            'visual_identity' => 'Strong grid alignment, balanced compositions, structured hierarchy.',
            'colors' => ['primary' => '#1e40af', 'secondary' => '#0369a1', 'dark' => '#0f172a', 'light' => '#f8fafc', 'accent' => '#0891b2', 'gradients' => ['linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%)']],
            'typography' => ['heading_font' => 'Source Sans Pro', 'body_font' => 'Source Sans Pro, system-ui, sans-serif', 'accent_font' => 'Source Code Pro, monospace', 'hero_size' => '56px', 'hero_weight' => '700', 'section_title' => '42px', 'body' => '17px', 'line_height' => '1.6'],
            'characteristics' => ['Strong vertical rhythm', 'Precise grid alignment', 'Subtle professional shadows', 'Conservative radius (8-12px)', 'Navy/blue palette', 'Clear hierarchy'],
            'layout_patterns' => ['hero' => 'Centered headline, dual CTAs (primary + ghost)', 'features' => 'Clean 3 or 4-column uniform grid', 'about' => 'Side by side image + content', 'cta' => 'Dark section, white text, single CTA'],
            'avoid' => 'Playful elements, bright colors, casual tone, chaotic asymmetry'
        ],
        'creative' => [
            'philosophy' => 'Break rules beautifully. Memorable, bold, unique. Think design agencies - stand out.',
            'visual_identity' => 'Unexpected layouts, bold colors, artistic compositions. Each section a statement.',
            'colors' => ['primary' => '#ec4899', 'secondary' => '#8b5cf6', 'dark' => '#18181b', 'light' => '#fafafa', 'accent' => '#f97316', 'gradients' => ['linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%)']],
            'typography' => ['heading_font' => 'Clash Display', 'body_font' => 'Poppins, sans-serif', 'accent_font' => 'Space Mono, monospace', 'hero_size' => '80px', 'hero_weight' => '800', 'section_title' => '52px', 'body' => '18px', 'line_height' => '1.7'],
            'characteristics' => ['Bold gradients', 'Oversized typography', 'Unconventional grid breaks', 'Dramatic negative space', 'Animated elements implied', 'Strong personality'],
            'layout_patterns' => ['hero' => 'Full-screen gradient, massive typography, unexpected placement', 'features' => 'Masonry or scattered cards, varying sizes', 'about' => 'Full-bleed image with overlapping text', 'cta' => 'Vibrant gradient, playful copy, bold button'],
            'avoid' => 'Safe layouts, muted colors, corporate stiffness'
        ],
        'minimal' => [
            'philosophy' => 'Less is more. Every pixel earns its place. Think Muji, Dieter Rams - essence without losing soul.',
            'visual_identity' => 'Maximum whitespace, typography as design, invisible structure.',
            'colors' => ['primary' => '#18181b', 'secondary' => '#71717a', 'dark' => '#09090b', 'light' => '#ffffff', 'accent' => '#18181b', 'gradients' => []],
            'typography' => ['heading_font' => 'DM Sans', 'body_font' => 'DM Sans, Helvetica Neue, sans-serif', 'accent_font' => 'DM Mono, monospace', 'hero_size' => '64px', 'hero_weight' => '500', 'section_title' => '40px', 'body' => '17px', 'line_height' => '1.8'],
            'characteristics' => ['Extreme whitespace (180px+ padding)', 'Two colors max', 'No gradients, no/ultra-subtle shadows', 'Typography carries design', 'Invisible dividers', 'Content-first hierarchy'],
            'layout_patterns' => ['hero' => 'Simple centered headline, maximum breathing room', 'features' => 'Single column or spacious 2-column', 'about' => 'Large image, minimal text, lots of air', 'cta' => 'Text-only or single understated button'],
            'avoid' => 'Decoration, multiple colors, busy layouts'
        ],
        'elegant' => [
            'philosophy' => 'Timeless sophistication. Refined, luxurious. Think haute couture, fine dining - quality over everything.',
            'visual_identity' => 'Serif typography, balanced asymmetry, rich textures implied. Feels expensive.',
            'colors' => ['primary' => '#1c1917', 'secondary' => '#44403c', 'dark' => '#0c0a09', 'light' => '#fafaf9', 'accent' => '#d4af37', 'gradients' => ['linear-gradient(180deg, #1c1917 0%, #292524 100%)']],
            'typography' => ['heading_font' => 'Playfair Display', 'body_font' => 'Lato, Georgia, serif', 'accent_font' => 'Cormorant Garamond, serif', 'hero_size' => '68px', 'hero_weight' => '600', 'section_title' => '44px', 'body' => '18px', 'line_height' => '1.8'],
            'characteristics' => ['Serif headlines, sans-serif body', 'Gold/warm accents', 'Refined subtle animations', 'Generous letter-spacing', 'Classic proportions', 'Premium photography style'],
            'layout_patterns' => ['hero' => 'Dramatic full-screen image, elegant overlay text', 'features' => 'Refined cards with subtle borders', 'about' => 'Editorial layout - large image, refined typography', 'cta' => 'Dark sophisticated section, gold accent button'],
            'avoid' => 'Bright colors, playful elements, casual fonts, cheap effects'
        ],
        'vintage' => [
            'philosophy' => 'Nostalgia meets craft. Timeless authenticity, handmade feel. Think classic barbershops, artisan coffee, heritage brands.',
            'visual_identity' => 'Weathered textures, ornate details, classic typography. Feels like it has history and soul.',
            'colors' => ['primary' => '#8b4513', 'secondary' => '#d4a574', 'dark' => '#2c1810', 'light' => '#f5f0e8', 'accent' => '#c9a227', 'gradients' => []],
            'typography' => ['heading_font' => 'Playfair Display', 'body_font' => 'Lora, Georgia, serif', 'accent_font' => 'Great Vibes, cursive', 'hero_size' => '64px', 'hero_weight' => '700', 'section_title' => '42px', 'body' => '18px', 'line_height' => '1.7'],
            'characteristics' => ['Sepia and warm earth tones', 'Decorative borders and frames', 'Script accents for highlights', 'Textured backgrounds implied', 'Classic iconography', 'Generous letter-spacing'],
            'layout_patterns' => ['hero' => 'Centered vintage badge/logo, ornate framing, warm overlay on image', 'features' => 'Cards with decorative borders, icon illustrations', 'about' => 'Story-driven with historical imagery', 'cta' => 'Warm background, classic button with border'],
            'avoid' => 'Neon colors, ultra-modern elements, flat design, generic stock photos'
        ],
        'luxury' => [
            'philosophy' => 'Exclusive excellence. Ultimate refinement, prestigious presence. Think Rolls-Royce, five-star hotels, private banking.',
            'visual_identity' => 'Rich blacks, gold accents, dramatic imagery. Every detail whispers quality and exclusivity.',
            'colors' => ['primary' => '#0a0a0a', 'secondary' => '#1a1a1a', 'dark' => '#000000', 'light' => '#fafafa', 'accent' => '#c9a227', 'gradients' => ['linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%)']],
            'typography' => ['heading_font' => 'Cormorant Garamond', 'body_font' => 'Montserrat, sans-serif', 'accent_font' => 'Didot, serif', 'hero_size' => '72px', 'hero_weight' => '300', 'section_title' => '48px', 'body' => '17px', 'line_height' => '1.9'],
            'characteristics' => ['Ultra-thin elegant typography', 'Gold and champagne accents', 'Dramatic full-bleed imagery', 'Extreme whitespace as luxury signal', 'Subtle animations implied', 'Premium photography only'],
            'layout_patterns' => ['hero' => 'Full-screen cinematic image, minimal text, gold accent line', 'features' => 'Sparse grid, lots of breathing room, subtle borders', 'about' => 'Editorial spread style, large imagery dominates', 'cta' => 'Black background, gold text, understated button'],
            'avoid' => 'Bright colors, busy layouts, cheap imagery, playful fonts, cluttered design'
        ],
        'bold' => [
            'philosophy' => 'Make a statement. Unapologetic impact, impossible to ignore. Think Nike, Spotify, youth culture brands.',
            'visual_identity' => 'High contrast, oversized elements, dynamic energy. Demands attention and creates excitement.',
            'colors' => ['primary' => '#ff0050', 'secondary' => '#00d4ff', 'dark' => '#0d0d0d', 'light' => '#ffffff', 'accent' => '#ffdd00', 'gradients' => ['linear-gradient(135deg, #ff0050 0%, #ff6b00 100%)']],
            'typography' => ['heading_font' => 'Bebas Neue', 'body_font' => 'Oswald, sans-serif', 'accent_font' => 'Anton, sans-serif', 'hero_size' => '96px', 'hero_weight' => '900', 'section_title' => '56px', 'body' => '18px', 'line_height' => '1.6'],
            'characteristics' => ['Massive typography that dominates', 'High contrast color blocks', 'Diagonal lines and angles', 'Bold color overlays on images', 'All-caps headlines', 'Dynamic asymmetric layouts'],
            'layout_patterns' => ['hero' => 'Giant headline filling viewport, bold color block, strong CTA', 'features' => 'Oversized numbers, bold icons, high contrast cards', 'about' => 'Split design with strong diagonal or color block', 'cta' => 'Vibrant full-width banner, impossible to miss'],
            'avoid' => 'Subtle anything, muted colors, small typography, conventional layouts'
        ],
        'organic' => [
            'philosophy' => 'Nature-inspired harmony. Sustainable, authentic, connected to earth. Think eco brands, wellness, farm-to-table.',
            'visual_identity' => 'Flowing shapes, natural textures, earthy warmth. Feels grounded, healthy, and genuine.',
            'colors' => ['primary' => '#2d5016', 'secondary' => '#8fbc8f', 'dark' => '#1a2e0a', 'light' => '#f8f6f0', 'accent' => '#d4a574', 'gradients' => ['linear-gradient(180deg, #f8f6f0 0%, #e8e4d9 100%)']],
            'typography' => ['heading_font' => 'Nunito', 'body_font' => 'Quicksand, sans-serif', 'accent_font' => 'Caveat, cursive', 'hero_size' => '58px', 'hero_weight' => '600', 'section_title' => '40px', 'body' => '17px', 'line_height' => '1.8'],
            'characteristics' => ['Soft rounded corners everywhere', 'Natural/botanical imagery', 'Hand-drawn icon style', 'Cream and sage color palette', 'Organic flowing shapes', 'Warm and inviting feel'],
            'layout_patterns' => ['hero' => 'Nature imagery with soft overlay, rounded CTA button', 'features' => 'Cards with rounded corners, leaf/nature icons', 'about' => 'Story of sustainability, authentic photography', 'cta' => 'Soft green background, warm inviting copy'],
            'avoid' => 'Sharp corners, cold colors, synthetic feel, aggressive design'
        ],
        'industrial' => [
            'philosophy' => 'Raw urban edge. Exposed, authentic, metropolitan. Think loft spaces, craft breweries, urban studios.',
            'visual_identity' => 'Concrete textures, exposed elements, utilitarian beauty. Celebrates rawness and authenticity.',
            'colors' => ['primary' => '#374151', 'secondary' => '#6b7280', 'dark' => '#111827', 'light' => '#e5e7eb', 'accent' => '#f59e0b', 'gradients' => []],
            'typography' => ['heading_font' => 'Space Grotesk', 'body_font' => 'IBM Plex Sans, sans-serif', 'accent_font' => 'IBM Plex Mono, monospace', 'hero_size' => '64px', 'hero_weight' => '700', 'section_title' => '44px', 'body' => '17px', 'line_height' => '1.7'],
            'characteristics' => ['Monospace accents for labels', 'Grid lines visible as design element', 'Concrete/steel color palette', 'Sharp corners, no rounded edges', 'Exposed grid structure', 'Yellow/orange safety accents'],
            'layout_patterns' => ['hero' => 'Strong grid lines, industrial imagery, bold sans-serif', 'features' => 'Card grid with visible borders, utilitarian icons', 'about' => 'Behind-the-scenes, process-focused imagery', 'cta' => 'Dark concrete background, high-vis accent button'],
            'avoid' => 'Soft colors, decorative elements, playful fonts, polished corporate feel'
        ]
    ];
    return $styles[strtolower($style)] ?? $styles['modern'];
}

function tb_get_industry_knowledge(string $industry): array
{
    $industries = [
        'construction' => ['hero_focus' => 'Before/after transformations, completed projects', 'trust_signals' => 'Years in business, projects completed, 5-star reviews, licenses', 'key_sections' => ['Portfolio gallery', 'Service area map', 'Free quote CTA', 'Certifications'], 'imagery_style' => 'High-quality project photos, team at work', 'tone' => 'Reliable, skilled, local, established'],
        'restaurant' => ['hero_focus' => 'Signature dish or ambiance, appetizing imagery', 'trust_signals' => 'Awards, reviews, chef credentials', 'key_sections' => ['Menu highlights', 'Reservation CTA', 'Location/hours', 'Story'], 'imagery_style' => 'Food photography, interior, happy diners', 'tone' => 'Inviting, delicious, memorable'],
        'technology' => ['hero_focus' => 'Product in action, problem-solution narrative', 'trust_signals' => 'Client logos, metrics, security badges', 'key_sections' => ['Features grid', 'How it works', 'Integrations', 'Pricing'], 'imagery_style' => '3D mockups, abstract tech, clean interfaces', 'tone' => 'Innovative, reliable, cutting-edge'],
        'healthcare' => ['hero_focus' => 'Caring professionals, modern facilities', 'trust_signals' => 'Certifications, credentials, patient reviews', 'key_sections' => ['Services', 'Our team', 'Insurance', 'Book appointment'], 'imagery_style' => 'Friendly staff, clean facilities', 'tone' => 'Trustworthy, compassionate, professional'],
        'ecommerce' => ['hero_focus' => 'Best-selling products, current promotions', 'trust_signals' => 'Secure checkout, reviews, free shipping', 'key_sections' => ['Featured products', 'Categories', 'Testimonials', 'Newsletter'], 'imagery_style' => 'Product photography, clean backgrounds', 'tone' => 'Trustworthy, value-focused'],
        'professional_services' => ['hero_focus' => 'Clear value proposition, professional team', 'trust_signals' => 'Testimonials, case studies, certifications', 'key_sections' => ['Services', 'Process', 'Team', 'Case studies'], 'imagery_style' => 'Professional headshots, office environment', 'tone' => 'Expert, reliable, results-driven'],
        'barber' => ['hero_focus' => 'Craftsmanship in action - razor, hot towel, skilled hands. The masculine grooming experience.', 'trust_signals' => 'Years of experience, loyal clientele, master barber credentials, 5-star reviews', 'key_sections' => ['Services & Pricing', 'Our Barbers', 'Gallery of cuts', 'Book Appointment', 'Shop products'], 'imagery_style' => 'Moody lighting, leather chairs, vintage tools, beards, sharp fades, masculine atmosphere', 'tone' => 'Confident, skilled, masculine, welcoming brotherhood vibe'],
        'salon' => ['hero_focus' => 'Transformation and beauty - stunning hair reveals, glamorous results, pampering experience.', 'trust_signals' => 'Stylist portfolios, celebrity clients, product partnerships, awards, reviews', 'key_sections' => ['Services menu', 'Our Stylists', 'Transformations gallery', 'Book now', 'Bridal services'], 'imagery_style' => 'Bright airy space, beautiful hair transformations, stylish interior, happy clients', 'tone' => 'Glamorous, welcoming, confidence-boosting, trendy'],
        'spa' => ['hero_focus' => 'Ultimate relaxation - serene environments, healing hands, escape from stress.', 'trust_signals' => 'Therapist certifications, luxury product lines, wellness awards, tranquil reviews', 'key_sections' => ['Treatment menu', 'Packages', 'Facilities tour', 'Book retreat', 'Gift cards'], 'imagery_style' => 'Zen atmosphere, candles, stones, nature elements, peaceful faces, luxury amenities', 'tone' => 'Serene, luxurious, healing, rejuvenating escape'],
        'fitness' => ['hero_focus' => 'Transformation and power - before/after results, intense training, community energy.', 'trust_signals' => 'Trainer certifications, member transformations, equipment quality, class variety', 'key_sections' => ['Membership plans', 'Classes schedule', 'Personal training', 'Facilities', 'Free trial CTA'], 'imagery_style' => 'Action shots, sweat and determination, modern equipment, group energy, results photos', 'tone' => 'Motivating, energetic, no-excuses, supportive community'],
        'yoga' => ['hero_focus' => 'Inner peace and balance - mindful practice, serene spaces, spiritual journey.', 'trust_signals' => 'Instructor lineage, yoga alliance certification, retreat experience, testimonials', 'key_sections' => ['Class types', 'Schedule', 'Instructors', 'Workshops/Retreats', 'First class free'], 'imagery_style' => 'Natural light, peaceful poses, plants, minimalist studio, meditation spaces', 'tone' => 'Calm, welcoming, mindful, nurturing, non-judgmental'],
        'cafe' => ['hero_focus' => 'Cozy atmosphere, artisan coffee, perfect escape. The aroma, the warmth, the community.', 'trust_signals' => 'Bean sourcing story, barista expertise, local favorite awards, loyal regulars', 'key_sections' => ['Menu', 'Our Story', 'Location & Hours', 'Catering', 'Loyalty program'], 'imagery_style' => 'Warm lighting, latte art, cozy corners, pastries, people enjoying coffee moments', 'tone' => 'Warm, inviting, artisan, community-focused, cozy'],
        'bar' => ['hero_focus' => 'Craft cocktails, vibrant nightlife, unforgettable experiences. Where memories are made.', 'trust_signals' => 'Mixologist credentials, unique cocktail menu, atmosphere awards, event hosting', 'key_sections' => ['Cocktail menu', 'Events calendar', 'Reserve table', 'Private events', 'Happy hour'], 'imagery_style' => 'Moody lighting, craft cocktails, stylish interior, nightlife energy, bartender action', 'tone' => 'Sophisticated, vibrant, social, craft-focused, memorable nights'],
        'hotel' => ['hero_focus' => 'Luxurious escape, impeccable service, destination experience. Your home away from home.', 'trust_signals' => 'Star rating, guest reviews, awards, amenities quality, location prestige', 'key_sections' => ['Rooms & Suites', 'Amenities', 'Dining', 'Location/Attractions', 'Book direct CTA'], 'imagery_style' => 'Stunning rooms, lobby grandeur, pool/spa, city views, impeccable details', 'tone' => 'Luxurious, welcoming, attentive, escape-worthy, memorable stay'],
        'catering' => ['hero_focus' => 'Memorable events, exquisite cuisine, flawless execution. Making your occasion extraordinary.', 'trust_signals' => 'Events completed, client testimonials, venue partnerships, chef credentials', 'key_sections' => ['Services', 'Sample menus', 'Gallery of events', 'Request quote', 'Testimonials'], 'imagery_style' => 'Beautiful spreads, elegant table settings, event moments, chef preparation', 'tone' => 'Professional, creative, reliable, celebration-focused, stress-free planning'],
        'foodtruck' => ['hero_focus' => 'Street food revolution, bold flavors, find us today. Fresh, fast, unforgettable taste.', 'trust_signals' => 'Social media following, festival appearances, media features, loyal fans', 'key_sections' => ['Menu', 'Find us/Schedule', 'Book for events', 'Story', 'Social feed'], 'imagery_style' => 'Vibrant truck, sizzling food prep, happy customers, street atmosphere, bold colors', 'tone' => 'Fun, bold, authentic, mobile, cult-following energy'],
        'photography' => ['hero_focus' => 'Capturing moments that last forever. Artistic vision, emotional storytelling, timeless images.', 'trust_signals' => 'Portfolio quality, client testimonials, publications, awards, experience years', 'key_sections' => ['Portfolio galleries', 'Services/Packages', 'About the artist', 'Booking inquiry', 'Client stories'], 'imagery_style' => 'Stunning portfolio shots, behind-the-scenes, emotional moments, artistic compositions', 'tone' => 'Artistic, emotional, professional, storytelling, passionate'],
        'wedding' => ['hero_focus' => 'Your perfect day, flawlessly executed. Dreams become reality with expert planning.', 'trust_signals' => 'Weddings completed, venue partnerships, vendor network, real couple testimonials', 'key_sections' => ['Services', 'Real weddings gallery', 'Planning process', 'Consultation CTA', 'Vendor team'], 'imagery_style' => 'Romantic moments, stunning venues, happy couples, beautiful details, elegant setups', 'tone' => 'Romantic, reassuring, detail-oriented, dream-fulfilling, stress-free'],
        'music' => ['hero_focus' => 'Sound that moves souls. Live performances, studio mastery, unforgettable entertainment.', 'trust_signals' => 'Performance history, client events, streaming numbers, media features, testimonials', 'key_sections' => ['Music/Videos', 'Book for event', 'About/Bio', 'Tour dates', 'Contact/Booking'], 'imagery_style' => 'Live performance energy, studio shots, artistic portraits, crowd moments, equipment', 'tone' => 'Passionate, energetic, authentic, entertainment-focused, memorable'],
        'tattoo' => ['hero_focus' => 'Wearable art, permanent expression. Custom designs, master craftsmanship, your story on skin.', 'trust_signals' => 'Artist portfolios, years experience, hygiene certifications, healed photos, client reviews', 'key_sections' => ['Artist portfolios', 'Styles we do', 'Booking process', 'Aftercare', 'Studio tour'], 'imagery_style' => 'Detailed tattoo photos, artist at work, studio atmosphere, healed pieces, flash designs', 'tone' => 'Artistic, edgy, professional, custom-focused, expressive'],
        'art' => ['hero_focus' => 'Original creations that inspire. Unique vision, handcrafted beauty, collectible pieces.', 'trust_signals' => 'Exhibitions, collections, press features, collector testimonials, artistic credentials', 'key_sections' => ['Gallery/Portfolio', 'Available works', 'Commissions', 'Artist statement', 'Contact/Purchase'], 'imagery_style' => 'Artwork photography, studio environment, artist at work, exhibition shots, details', 'tone' => 'Artistic, thoughtful, unique, collectible, visionary'],
        'realestate' => ['hero_focus' => 'Find your dream home. Expert guidance, local knowledge, seamless transactions.', 'trust_signals' => 'Homes sold, years experience, client testimonials, local market expertise, certifications', 'key_sections' => ['Featured listings', 'Search properties', 'About agent/team', 'Sold properties', 'Free consultation'], 'imagery_style' => 'Stunning property photos, neighborhood shots, happy homeowners, agent professional photos', 'tone' => 'Trustworthy, knowledgeable, helpful, local expert, results-driven'],
        'finance' => ['hero_focus' => 'Secure your financial future. Expert advice, proven strategies, peace of mind.', 'trust_signals' => 'Credentials (CFP, CPA), assets managed, client retention rate, regulatory compliance', 'key_sections' => ['Services', 'Our approach', 'Team credentials', 'Resources/Blog', 'Schedule consultation'], 'imagery_style' => 'Professional office, confident advisors, charts/growth imagery, client meetings', 'tone' => 'Trustworthy, expert, secure, long-term focused, professional'],
        'education' => ['hero_focus' => 'Transform your future through learning. Expert instruction, proven results, supportive environment.', 'trust_signals' => 'Student success rates, accreditations, instructor credentials, alumni testimonials', 'key_sections' => ['Programs/Courses', 'Admissions', 'Faculty', 'Student success', 'Apply now/Enroll'], 'imagery_style' => 'Engaged students, modern facilities, graduation moments, classroom interaction', 'tone' => 'Inspiring, supportive, achievement-focused, welcoming, transformative'],
        'nonprofit' => ['hero_focus' => 'Making a difference together. Your support creates real change in real lives.', 'trust_signals' => 'Impact metrics, transparency reports, charity ratings, beneficiary stories', 'key_sections' => ['Our mission', 'Impact/Results', 'Ways to help', 'Stories', 'Donate CTA'], 'imagery_style' => 'Real people helped, volunteers in action, community impact, emotional moments', 'tone' => 'Compassionate, urgent, hopeful, transparent, community-driven'],
        'automotive' => ['hero_focus' => 'Your perfect vehicle awaits. Quality selection, fair deals, trusted service.', 'trust_signals' => 'Years in business, vehicles sold, customer reviews, certifications, warranties offered', 'key_sections' => ['Inventory', 'Financing options', 'Trade-in', 'Service center', 'Contact/Visit'], 'imagery_style' => 'Showroom quality car photos, happy customers, service team, dealership facility', 'tone' => 'Trustworthy, no-pressure, quality-focused, customer-first, reliable'],
        'default' => ['hero_focus' => 'Clear value proposition with strong visual', 'trust_signals' => 'Testimonials, experience, quality indicators', 'key_sections' => ['Services', 'About', 'Testimonials', 'Contact'], 'imagery_style' => 'Professional, relevant', 'tone' => 'Professional, trustworthy']
    ];
    $mapping = ['paving' => 'construction', 'landscaping' => 'construction', 'roofing' => 'construction', 'plumbing' => 'construction', 'saas' => 'technology', 'software' => 'technology', 'medical' => 'healthcare', 'dental' => 'healthcare', 'shop' => 'ecommerce', 'store' => 'ecommerce', 'consulting' => 'professional_services', 'legal' => 'professional_services', 'agency' => 'professional_services', 'barbershop' => 'barber', 'haircut' => 'barber', 'grooming' => 'barber', 'hairdresser' => 'salon', 'hairstylist' => 'salon', 'beauty' => 'salon', 'nails' => 'salon', 'wellness' => 'spa', 'massage' => 'spa', 'retreat' => 'spa', 'gym' => 'fitness', 'crossfit' => 'fitness', 'training' => 'fitness', 'pilates' => 'yoga', 'meditation' => 'yoga', 'mindfulness' => 'yoga', 'coffee' => 'cafe', 'coffeeshop' => 'cafe', 'bakery' => 'cafe', 'pub' => 'bar', 'cocktail' => 'bar', 'nightclub' => 'bar', 'lounge' => 'bar', 'motel' => 'hotel', 'resort' => 'hotel', 'hostel' => 'hotel', 'bnb' => 'hotel', 'events' => 'catering', 'wedding_catering' => 'catering', 'streefood' => 'foodtruck', 'mobile_food' => 'foodtruck', 'photographer' => 'photography', 'photo' => 'photography', 'videography' => 'photography', 'wedding_planner' => 'wedding', 'event_planner' => 'wedding', 'bridal' => 'wedding', 'band' => 'music', 'dj' => 'music', 'musician' => 'music', 'singer' => 'music', 'entertainment' => 'music', 'tattoo_studio' => 'tattoo', 'piercing' => 'tattoo', 'ink' => 'tattoo', 'artist' => 'art', 'gallery' => 'art', 'painter' => 'art', 'sculptor' => 'art', 'realtor' => 'realestate', 'property' => 'realestate', 'homes' => 'realestate', 'broker' => 'realestate', 'financial' => 'finance', 'accounting' => 'finance', 'insurance' => 'finance', 'investment' => 'finance', 'wealth' => 'finance', 'school' => 'education', 'university' => 'education', 'college' => 'education', 'tutoring' => 'education', 'courses' => 'education', 'training_center' => 'education', 'charity' => 'nonprofit', 'ngo' => 'nonprofit', 'foundation' => 'nonprofit', 'cause' => 'nonprofit', 'car_dealer' => 'automotive', 'auto' => 'automotive', 'mechanic' => 'automotive', 'car_service' => 'automotive', 'dealership' => 'automotive'];
    $key = strtolower($industry);
    return $industries[$mapping[$key] ?? $key] ?? $industries['default'];
}

/**
 * Get copywriting guidelines for each industry
 */
function tb_get_copywriting(string $industry): array
{
    $copy = [
        'construction' => ['headline_style' => 'Strong, confident, results-focused. Emphasize transformation and reliability.', 'cta_examples' => ['Get Free Quote', 'Start Your Project', 'See Our Work'], 'power_words' => ['transform', 'quality', 'trusted', 'expert', 'guaranteed', 'professional'], 'avoid_words' => ['cheap', 'budget', 'discount'], 'sample_headlines' => ['Transform Your Outdoor Space', 'Quality Craftsmanship, Guaranteed Results']],
        'restaurant' => ['headline_style' => 'Sensory, appetizing, experience-focused. Make them taste through words.', 'cta_examples' => ['Reserve a Table', 'View Menu', 'Order Online'], 'power_words' => ['savor', 'fresh', 'handcrafted', 'authentic', 'seasonal', 'indulge'], 'avoid_words' => ['cheap eats', 'fast food', 'filling'], 'sample_headlines' => ['Savor Every Moment', 'Where Flavor Meets Craft']],
        'technology' => ['headline_style' => 'Clear, benefit-driven, modern. Focus on solving problems.', 'cta_examples' => ['Start Free Trial', 'See Demo', 'Get Started'], 'power_words' => ['streamline', 'automate', 'powerful', 'seamless', 'intelligent', 'scalable'], 'avoid_words' => ['complicated', 'legacy', 'manual'], 'sample_headlines' => ['Work Smarter, Not Harder', 'Built for the Future']],
        'healthcare' => ['headline_style' => 'Caring, reassuring, professional. Build trust, reduce anxiety.', 'cta_examples' => ['Book Appointment', 'Schedule Consultation', 'Get Care Now'], 'power_words' => ['caring', 'trusted', 'experienced', 'compassionate', 'gentle', 'expert'], 'avoid_words' => ['pain', 'scary', 'invasive'], 'sample_headlines' => ['Caring for Your Family\'s Health', 'Expert Care, Gentle Touch']],
        'ecommerce' => ['headline_style' => 'Benefit-focused, urgent but not pushy. Highlight value.', 'cta_examples' => ['Shop Now', 'Add to Cart', 'Get Yours Today'], 'power_words' => ['exclusive', 'premium', 'handpicked', 'bestseller', 'limited', 'trending'], 'avoid_words' => ['cheap', 'knockoff', 'clearance'], 'sample_headlines' => ['Discover Your New Favorites', 'Curated Just for You']],
        'professional_services' => ['headline_style' => 'Authoritative, solution-focused. Establish expertise.', 'cta_examples' => ['Schedule Consultation', 'Get Expert Advice', 'Contact Our Team'], 'power_words' => ['expert', 'proven', 'strategic', 'results', 'trusted', 'dedicated'], 'avoid_words' => ['cheap', 'quick fix', 'amateur'], 'sample_headlines' => ['Expert Solutions for Complex Challenges', 'Results That Speak']],
        'barber' => ['headline_style' => 'Bold, confident, masculine. Brotherhood vibe, craft pride.', 'cta_examples' => ['Book Your Cut', 'Reserve Your Chair', 'Join the Brotherhood'], 'power_words' => ['craft', 'precision', 'tradition', 'master', 'sharp', 'classic', 'legendary'], 'avoid_words' => ['cute', 'lovely', 'pretty', 'sweet'], 'sample_headlines' => ['Where Legends Get Fresh', 'The Art of the Perfect Cut']],
        'salon' => ['headline_style' => 'Glamorous, empowering, transformative. Boost confidence.', 'cta_examples' => ['Book Your Transformation', 'Reveal Your Best Self', 'Get Gorgeous'], 'power_words' => ['stunning', 'transform', 'radiant', 'gorgeous', 'luxurious', 'glow'], 'avoid_words' => ['basic', 'cheap', 'quick'], 'sample_headlines' => ['Reveal Your Inner Radiance', 'Your Transformation Awaits']],
        'spa' => ['headline_style' => 'Serene, peaceful, escape-focused. Promise relaxation.', 'cta_examples' => ['Book Your Escape', 'Reserve Treatment', 'Find Your Peace'], 'power_words' => ['serene', 'rejuvenate', 'escape', 'tranquil', 'restore', 'bliss', 'sanctuary'], 'avoid_words' => ['busy', 'rush', 'quick'], 'sample_headlines' => ['Your Sanctuary Awaits', 'Escape. Relax. Renew.']],
        'fitness' => ['headline_style' => 'Motivating, powerful, no-excuses energy. Push to action.', 'cta_examples' => ['Start Your Journey', 'Join Now', 'Claim Free Trial'], 'power_words' => ['transform', 'crush', 'power', 'unstoppable', 'results', 'strong', 'elite'], 'avoid_words' => ['easy', 'lazy', 'someday'], 'sample_headlines' => ['Unleash Your Potential', 'No Excuses. Just Results.']],
        'yoga' => ['headline_style' => 'Calm, welcoming, mindful. Gentle invitation, not pressure.', 'cta_examples' => ['Begin Your Practice', 'Find Your Class', 'Join Our Community'], 'power_words' => ['peace', 'balance', 'mindful', 'flow', 'breath', 'harmony', 'center'], 'avoid_words' => ['intense', 'extreme', 'compete'], 'sample_headlines' => ['Find Your Center', 'Breathe. Flow. Transform.']],
        'cafe' => ['headline_style' => 'Warm, cozy, community-focused. Like a friend inviting you.', 'cta_examples' => ['Visit Us Today', 'View Menu', 'Find Your New Spot'], 'power_words' => ['artisan', 'fresh', 'cozy', 'handcrafted', 'local', 'warm', 'community'], 'avoid_words' => ['fast', 'instant', 'generic'], 'sample_headlines' => ['Your Perfect Cup Awaits', 'Crafted With Love']],
        'bar' => ['headline_style' => 'Sophisticated, social, memorable. Promise great nights.', 'cta_examples' => ['Reserve Table', 'See Tonight\'s Events', 'View Cocktails'], 'power_words' => ['craft', 'signature', 'handcrafted', 'curated', 'exclusive', 'unforgettable'], 'avoid_words' => ['cheap drinks', 'dive', 'basic'], 'sample_headlines' => ['Where Great Nights Begin', 'Elevate Your Evening']],
        'hotel' => ['headline_style' => 'Luxurious, welcoming, experience-focused. Promise escape.', 'cta_examples' => ['Book Your Stay', 'Check Availability', 'Plan Your Escape'], 'power_words' => ['luxurious', 'escape', 'exclusive', 'impeccable', 'stunning', 'unforgettable'], 'avoid_words' => ['cheap', 'budget', 'motel'], 'sample_headlines' => ['Your Escape Awaits', 'Experience Unforgettable']],
        'catering' => ['headline_style' => 'Elegant, stress-free, celebration-focused.', 'cta_examples' => ['Request Quote', 'Plan Your Event', 'Book Consultation'], 'power_words' => ['exquisite', 'seamless', 'memorable', 'customized', 'elegant', 'flawless'], 'avoid_words' => ['cheap', 'basic', 'standard'], 'sample_headlines' => ['Your Vision, Perfectly Executed', 'Every Detail, Handled']],
        'foodtruck' => ['headline_style' => 'Fun, bold, crave-worthy. Street food attitude.', 'cta_examples' => ['Find Us Today', 'See Menu', 'Book for Event'], 'power_words' => ['fresh', 'bold', 'crave', 'authentic', 'legendary', 'handmade'], 'avoid_words' => ['fast food', 'processed', 'frozen'], 'sample_headlines' => ['Street Food Revolution', 'Chase the Flavor']],
        'photography' => ['headline_style' => 'Emotional, artistic, storytelling-focused.', 'cta_examples' => ['Book Your Session', 'View Portfolio', 'Tell Your Story'], 'power_words' => ['capture', 'timeless', 'moments', 'storytelling', 'artistic', 'stunning'], 'avoid_words' => ['cheap', 'quick', 'snapshots'], 'sample_headlines' => ['Moments Frozen in Time', 'Capturing What Matters']],
        'wedding' => ['headline_style' => 'Romantic, dreamy, reassuring. Make dreams real.', 'cta_examples' => ['Start Planning', 'Book Consultation', 'See Real Weddings'], 'power_words' => ['dream', 'perfect', 'magical', 'unforgettable', 'forever', 'love'], 'avoid_words' => ['stress', 'budget', 'cheap'], 'sample_headlines' => ['Where Dreams Become Reality', 'Love, Celebrated Beautifully']],
        'music' => ['headline_style' => 'Energetic, passionate, memorable. Promise experience.', 'cta_examples' => ['Book for Event', 'Listen Now', 'Check Availability'], 'power_words' => ['unforgettable', 'electrifying', 'legendary', 'live', 'energy', 'soul'], 'avoid_words' => ['background music', 'quiet', 'amateur'], 'sample_headlines' => ['Music That Moves You', 'Unforgettable Performances']],
        'tattoo' => ['headline_style' => 'Artistic, bold, personal. Art that tells story.', 'cta_examples' => ['Book Consultation', 'View Portfolio', 'Get Inked'], 'power_words' => ['custom', 'art', 'permanent', 'expression', 'masterpiece', 'unique'], 'avoid_words' => ['cheap', 'flash only', 'quick'], 'sample_headlines' => ['Your Story, Our Canvas', 'Wearable Art for Life']],
        'art' => ['headline_style' => 'Thoughtful, unique, collectible. Investment in beauty.', 'cta_examples' => ['View Collection', 'Inquire About Piece', 'Commission Work'], 'power_words' => ['original', 'unique', 'collectible', 'vision', 'handcrafted', 'timeless'], 'avoid_words' => ['copy', 'print', 'mass-produced'], 'sample_headlines' => ['Original Works That Inspire', 'Collect the Extraordinary']],
        'realestate' => ['headline_style' => 'Trustworthy, local expert, dream-fulfilling.', 'cta_examples' => ['Find Your Home', 'Get Free Valuation', 'Schedule Showing'], 'power_words' => ['dream', 'perfect', 'home', 'invest', 'local', 'expert', 'trusted'], 'avoid_words' => ['cheap', 'fixer-upper', 'as-is'], 'sample_headlines' => ['Find Your Dream Home', 'Where Home Begins']],
        'finance' => ['headline_style' => 'Trustworthy, secure, expert. Build confidence.', 'cta_examples' => ['Schedule Consultation', 'Plan Your Future', 'Speak With Advisor'], 'power_words' => ['secure', 'growth', 'trusted', 'strategic', 'wealth', 'protect'], 'avoid_words' => ['risky', 'gamble', 'get rich quick'], 'sample_headlines' => ['Secure Your Financial Future', 'Building Wealth, Building Trust']],
        'education' => ['headline_style' => 'Inspiring, empowering, future-focused.', 'cta_examples' => ['Apply Now', 'Explore Programs', 'Start Learning'], 'power_words' => ['transform', 'discover', 'achieve', 'empower', 'future', 'excel'], 'avoid_words' => ['easy', 'shortcut', 'quick degree'], 'sample_headlines' => ['Shape Your Future', 'Learn Today, Lead Tomorrow']],
        'nonprofit' => ['headline_style' => 'Emotional, urgent, hopeful. Show impact, inspire.', 'cta_examples' => ['Donate Now', 'Join Our Mission', 'Make a Difference'], 'power_words' => ['impact', 'change', 'hope', 'together', 'community', 'transform'], 'avoid_words' => ['hopeless', 'desperate', 'guilt'], 'sample_headlines' => ['Together, We Change Lives', 'Your Impact Matters']],
        'automotive' => ['headline_style' => 'Trustworthy, no-pressure, quality-focused.', 'cta_examples' => ['Browse Inventory', 'Schedule Test Drive', 'Value Your Trade'], 'power_words' => ['quality', 'certified', 'trusted', 'reliable', 'warranty', 'value'], 'avoid_words' => ['pushy', 'pressure', 'as-is'], 'sample_headlines' => ['Quality Vehicles, Fair Prices', 'Drive With Confidence']],
        'default' => ['headline_style' => 'Clear, benefit-focused, professional.', 'cta_examples' => ['Get Started', 'Learn More', 'Contact Us'], 'power_words' => ['quality', 'trusted', 'professional', 'expert', 'reliable'], 'avoid_words' => ['cheap', 'basic'], 'sample_headlines' => ['Quality You Can Trust', 'Excellence in Every Detail']]
    ];
    $mapping = ['paving' => 'construction', 'barbershop' => 'barber', 'gym' => 'fitness', 'coffee' => 'cafe', 'photographer' => 'photography', 'realtor' => 'realestate'];
    $key = strtolower($industry);
    return $copy[$mapping[$key] ?? $key] ?? $copy['default'];
}

/**
 * Get style-industry synergy boosters - amplify design when style matches industry perfectly
 */
function tb_get_synergy(string $style, string $industry): ?array
{
    $synergies = [
        // VINTAGE synergies
        'vintage+barber' => ['match' => 'PERFECT', 'boost' => 'Amplify heritage feel. Use ornate borders, vintage badges, sepia tones. Classic barbershop pole imagery. Old-school typography with decorative serifs. Leather and wood textures implied.'],
        'vintage+cafe' => ['match' => 'PERFECT', 'boost' => 'Emphasize artisan craft heritage. Hand-drawn coffee illustrations, chalkboard menu style, warm Edison lighting feel. Community coffeehouse nostalgia.'],
        'vintage+bar' => ['match' => 'GREAT', 'boost' => 'Speakeasy sophistication. Art deco elements, prohibition-era elegance. Gold accents on dark backgrounds, ornate cocktail imagery.'],
        'vintage+restaurant' => ['match' => 'GREAT', 'boost' => 'Classic bistro charm. Handwritten menu feel, nostalgic food photography, heritage recipe storytelling.'],
        
        // LUXURY synergies
        'luxury+hotel' => ['match' => 'PERFECT', 'boost' => 'Maximum opulence. Cinematic full-bleed imagery, gold accent lines, ultra-thin typography. White glove service imagery. Dramatic room reveals.'],
        'luxury+spa' => ['match' => 'PERFECT', 'boost' => 'Exclusive retreat energy. Serene but prestigious. Marble textures implied, gold and cream palette, editorial photography of treatments.'],
        'luxury+realestate' => ['match' => 'GREAT', 'boost' => 'High-end property showcase. Architectural photography, prestigious neighborhood emphasis, exclusive listing feel.'],
        'luxury+wedding' => ['match' => 'GREAT', 'boost' => 'Haute couture wedding aesthetic. Editorial bridal imagery, gold calligraphy accents, dreamy soft focus.'],
        'luxury+salon' => ['match' => 'GREAT', 'boost' => 'High-fashion beauty. Vogue-style imagery, sleek sophistication, celebrity-worthy transformations.'],
        
        // BOLD synergies
        'bold+fitness' => ['match' => 'PERFECT', 'boost' => 'Maximum intensity. Aggressive typography, action shots with motion blur, high contrast blacks and neons. No-excuses energy in every element.'],
        'bold+music' => ['match' => 'PERFECT', 'boost' => 'Concert poster energy. Massive headlines, vibrant stage lighting colors, dynamic angles. Feel the bass in the design.'],
        'bold+tattoo' => ['match' => 'GREAT', 'boost' => 'Edgy and unapologetic. Dark backgrounds, neon accents, bold ink showcase. Street art influence.'],
        'bold+bar' => ['match' => 'GREAT', 'boost' => 'Nightlife energy. Club atmosphere, bold neon colors, vibrant nightlife photography.'],
        
        // ORGANIC synergies
        'organic+yoga' => ['match' => 'PERFECT', 'boost' => 'Nature-connected wellness. Botanical elements, earth tones, flowing organic shapes. Natural light photography, plant-filled studio spaces.'],
        'organic+spa' => ['match' => 'PERFECT', 'boost' => 'Natural healing sanctuary. Botanical ingredients showcase, stone and wood textures, green and cream palette. Eco-luxury feel.'],
        'organic+cafe' => ['match' => 'GREAT', 'boost' => 'Farm-to-cup authenticity. Sustainable sourcing story, natural materials, handcrafted feel. Local and eco-conscious.'],
        'organic+restaurant' => ['match' => 'GREAT', 'boost' => 'Farm-to-table story. Seasonal ingredients, garden imagery, sustainable dining narrative.'],
        'organic+nonprofit' => ['match' => 'GREAT', 'boost' => 'Environmental cause alignment. Nature photography, earth-friendly mission, community and planet focus.'],
        
        // INDUSTRIAL synergies
        'industrial+bar' => ['match' => 'PERFECT', 'boost' => 'Craft brewery aesthetic. Exposed brick, metal fixtures, concrete textures. Brewing process imagery, warehouse bar feel.'],
        'industrial+cafe' => ['match' => 'GREAT', 'boost' => 'Urban roastery vibe. Raw space, industrial coffee equipment, loft atmosphere. Third-wave coffee aesthetic.'],
        'industrial+tattoo' => ['match' => 'GREAT', 'boost' => 'Urban studio raw edge. Concrete walls, metal furniture, authentic street culture. Artist workshop feel.'],
        'industrial+photography' => ['match' => 'GREAT', 'boost' => 'Studio warehouse aesthetic. Raw creative space, exposed equipment, behind-scenes authenticity.'],
        'industrial+fitness' => ['match' => 'GREAT', 'boost' => 'Gritty gym aesthetic. Warehouse training space, raw iron, no-frills intensity. Functional fitness focus.'],
        
        // MINIMAL synergies
        'minimal+yoga' => ['match' => 'PERFECT', 'boost' => 'Zen simplicity. Maximum whitespace as meditation. Single accent color, typography-led, breathing room in every section.'],
        'minimal+art' => ['match' => 'PERFECT', 'boost' => 'Gallery-white aesthetic. Let artwork speak, invisible design framework, sophisticated restraint. Museum-quality presentation.'],
        'minimal+photography' => ['match' => 'GREAT', 'boost' => 'Portfolio-focused purity. Images dominate, minimal UI, elegant simplicity. Work speaks for itself.'],
        'minimal+technology' => ['match' => 'GREAT', 'boost' => 'Apple-style product focus. Clean interfaces, elegant simplicity, premium tech aesthetic.'],
        
        // ELEGANT synergies  
        'elegant+wedding' => ['match' => 'PERFECT', 'boost' => 'Bridal magazine sophistication. Serif romance, soft color palette, editorial layouts. Timeless love story aesthetic.'],
        'elegant+hotel' => ['match' => 'PERFECT', 'boost' => 'Boutique luxury. Refined service imagery, sophisticated color palette, editorial travel magazine feel.'],
        'elegant+restaurant' => ['match' => 'GREAT', 'boost' => 'Fine dining sophistication. Plated art photography, sommelier expertise, Michelin-star aesthetic.'],
        'elegant+salon' => ['match' => 'GREAT', 'boost' => 'Refined beauty. Fashion editorial style, sophisticated transformations, luxury product showcase.'],
        'elegant+finance' => ['match' => 'GREAT', 'boost' => 'Private banking prestige. Understated wealth, trustworthy sophistication, established firm aesthetic.'],
        
        // CREATIVE synergies
        'creative+art' => ['match' => 'PERFECT', 'boost' => 'Boundary-breaking gallery. Unexpected layouts, bold color statements, artistic expression in UI itself. Design as art.'],
        'creative+photography' => ['match' => 'GREAT', 'boost' => 'Editorial portfolio innovation. Unconventional image arrangements, bold typography overlay, artistic presentation.'],
        'creative+music' => ['match' => 'GREAT', 'boost' => 'Album artwork energy. Bold visual statements, artistic expression, genre-defining aesthetic.'],
        'creative+tattoo' => ['match' => 'GREAT', 'boost' => 'Artistic ink showcase. Portfolio as gallery, bold design statements, creative studio energy.'],
        
        // MODERN synergies
        'modern+technology' => ['match' => 'PERFECT', 'boost' => 'Cutting-edge SaaS aesthetic. Glassmorphism, gradient accents, floating UI elements. Product-led growth design.'],
        'modern+ecommerce' => ['match' => 'GREAT', 'boost' => 'Premium DTC brand feel. Clean product showcase, subtle animations implied, conversion-optimized elegance.'],
        'modern+professional_services' => ['match' => 'GREAT', 'boost' => 'Forward-thinking consultancy. Progressive but trustworthy, tech-savvy expertise, modern authority.'],
        
        // CORPORATE synergies
        'corporate+finance' => ['match' => 'PERFECT', 'boost' => 'Institutional trust. Navy authority, structured layouts, credential-focused. Established firm gravitas.'],
        'corporate+healthcare' => ['match' => 'GREAT', 'boost' => 'Medical institution authority. Professional trust, credential emphasis, established care provider.'],
        'corporate+education' => ['match' => 'GREAT', 'boost' => 'Academic institution prestige. Structured excellence, tradition meets progress, credential-focused.'],
        'corporate+professional_services' => ['match' => 'GREAT', 'boost' => 'Consulting firm authority. Blue-chip client aesthetic, case study focus, established expertise.']
    ];
    
    $key = strtolower($style) . '+' . strtolower($industry);
    return $synergies[$key] ?? null;
}

/**
 * Get section variety library - different layout options for each section type
 */
function tb_get_section_variants(): array
{
    return [
        'hero' => [
            'split_right' => 'Two columns: Bold headline + text + CTA on left (60%), striking image/mockup on right (40%). Image can overlap or have floating elements.',
            'split_left' => 'Two columns: Large image on left (40%), headline + text + CTA on right (60%). Great for visual-first industries.',
            'centered' => 'Full-width centered layout. Massive headline, supporting text below, CTA button, with subtle background image or gradient.',
            'video_bg' => 'Full-screen background video/image with dark overlay. Centered white text, minimal but impactful.',
            'minimal' => 'Maximum whitespace. Just headline and single CTA. Let the typography do all the work.',
            'cards_overlay' => 'Hero image with floating stat cards or feature highlights overlapping the bottom edge.',
            'diagonal' => 'Diagonal split background (two colors). Text on one side, creates dynamic energy.'
        ],
        'features' => [
            'grid_3col' => 'Classic 3-column grid of feature cards with icons. Equal spacing, clean alignment.',
            'grid_4col' => 'Compact 4-column grid for many features. Smaller cards, icon + title + short text.',
            'bento' => 'Bento box layout - mixed size cards (1 large + 2 small, or 2 medium + 2 small). Creates visual interest.',
            'alternating' => 'Alternating left-right layout. Each feature has image on one side, text on other, switching each row.',
            'icon_row' => 'Single row of icon + label pairs. Minimal, great for trust indicators or quick features.',
            'large_cards' => 'Big cards with large icons/images. 2 columns max. More breathing room, premium feel.',
            'numbered' => 'Numbered list style (01, 02, 03). Large numbers as design element, content beside each.'
        ],
        'about' => [
            'split_image' => 'Classic 50/50 split. Large image on one side, story text + stats on other.',
            'full_image_overlay' => 'Full-width image with text overlay box. Dramatic, editorial feel.',
            'timeline' => 'Vertical timeline showing company history/milestones. Great for established businesses.',
            'team_focus' => 'Lead with team photo or founder image. Personal story approach.',
            'stats_highlight' => 'Story text with large stat counters integrated. Numbers tell the story.',
            'video_story' => 'Embedded video with supporting text. Let them tell their own story.',
            'values_grid' => 'Mission statement + grid of company values with icons.'
        ],
        'services' => [
            'card_grid' => 'Grid of service cards (3 or 4 columns). Icon + title + description + learn more link.',
            'detailed_list' => 'Full-width service blocks. Each service gets more space, image + detailed description.',
            'tabbed' => 'Tab navigation at top, content changes below. Good for many services in limited space.',
            'accordion' => 'Expandable accordion style. Click to reveal details. Clean and organized.',
            'pricing_table' => 'Service cards with pricing prominently displayed. Comparison-friendly layout.',
            'icon_large' => 'Large icons/images for each service. Minimal text, visual-first approach.',
            'process_steps' => 'Numbered process steps showing how service works. 1-2-3-4 visual flow.'
        ],
        'testimonials' => [
            'carousel' => 'Single testimonial at a time, navigation arrows/dots. Focus on one story.',
            'grid' => 'Multiple testimonial cards in grid (2-3 columns). Show volume of happy clients.',
            'featured' => 'One large featured testimonial with photo, plus smaller supporting quotes.',
            'video' => 'Video testimonial embed with supporting text quote below.',
            'logo_bar' => 'Client logos in row + selected quotes. Good for B2B credibility.',
            'masonry' => 'Pinterest-style varying height cards. Dynamic, social-proof heavy.',
            'minimal' => 'Just the quote, author name, subtle styling. Let words speak.'
        ],
        'cta' => [
            'centered' => 'Centered headline + supporting text + prominent button. Classic and effective.',
            'split' => 'Two columns: Compelling copy on left, form or button on right.',
            'full_image' => 'Background image with overlay. Emotional appeal + clear action.',
            'gradient' => 'Bold gradient background. Eye-catching color transition with white text.',
            'minimal' => 'Simple text + button only. No background styling. Understated but clear.',
            'dual_action' => 'Two CTAs side by side - primary and secondary action options.',
            'urgency' => 'Include urgency element: limited time, spots remaining, countdown implied.'
        ],
        'contact' => [
            'split_form' => 'Form on one side (60%), contact info blurbs on other (40%).',
            'centered_form' => 'Centered narrow form with contact info above or below.',
            'map_split' => 'Large map on one side, form + info on other.',
            'cards_form' => 'Contact info in cards above, full-width form below.',
            'minimal' => 'Just essential: email, phone, form. Maximum simplicity.',
            'chat_focus' => 'Emphasize live chat/instant contact. Modern, immediate feel.',
            'multi_location' => 'Multiple location cards with individual contact details.'
        ],
        'gallery' => [
            'masonry' => 'Pinterest-style varying heights. Dynamic, visual-heavy.',
            'grid_uniform' => 'Clean uniform grid. Same size images, professional feel.',
            'featured_plus' => 'One large featured image + grid of smaller images.',
            'carousel' => 'Horizontal scrolling gallery. One section, swipe through.',
            'lightbox' => 'Grid with lightbox on click. Compact but expandable.',
            'before_after' => 'Side-by-side or slider comparison. Perfect for transformations.',
            'categories' => 'Filterable by category tabs. Organized portfolio display.'
        ]
    ];
}

function tb_build_design_prompt(string $style, string $industry, string $businessName, string $brief, array $availableModules): string
{
    $p = tb_get_style_philosophy($style);
    $i = tb_get_industry_knowledge($industry);
    $v = tb_get_section_variants();
    $c = tb_get_copywriting($industry);
    $synergy = tb_get_synergy($style, $industry);
    $moduleList = implode(', ', $availableModules);
    $chars = implode("\n- ", $p['characteristics']);
    $sections = implode(', ', $i['key_sections']);
    $ctaExamples = implode(', ', $c['cta_examples']);
    $powerWords = implode(', ', $c['power_words']);
    $avoidWords = implode(', ', $c['avoid_words']);
    $sampleHeadlines = implode(' | ', $c['sample_headlines']);
    
    // Build synergy boost if exists
    $synergyStr = '';
    if ($synergy) {
        $synergyStr = "\n\n🎯 STYLE-INDUSTRY SYNERGY: {$synergy['match']} MATCH!\n{$synergy['boost']}";
    }
    
    // Build section variants string
    $variantsStr = "SECTION LAYOUT OPTIONS (choose variety, don't repeat same layout):

HERO variants: " . implode(' | ', array_keys($v['hero'])) . "
FEATURES variants: " . implode(' | ', array_keys($v['features'])) . "
TESTIMONIALS variants: " . implode(' | ', array_keys($v['testimonials'])) . "
CTA variants: " . implode(' | ', array_keys($v['cta'])) . "
GALLERY variants: " . implode(' | ', array_keys($v['gallery']));

    // Build copywriting guidelines
    $copyStr = "COPYWRITING GUIDELINES:
Headline Style: {$c['headline_style']}
CTA Button Examples: {$ctaExamples}
Power Words to Use: {$powerWords}
Words to Avoid: {$avoidWords}
Sample Headlines for Inspiration: {$sampleHeadlines}";
    
    return "You are an ELITE website designer from Dribbble/Awwwards creating premium layouts.

DESIGN PHILOSOPHY: {$style} STYLE
{$p['philosophy']}{$synergyStr}

VISUAL IDENTITY: {$p['visual_identity']}

COLOR PALETTE:
- Primary: {$p['colors']['primary']}
- Secondary: {$p['colors']['secondary']}
- Dark: {$p['colors']['dark']}
- Light: {$p['colors']['light']}
- Accent: {$p['colors']['accent']}

TYPOGRAPHY:
- Headings: {$p['typography']['heading_font']}
- Body text: {$p['typography']['body_font']}
- Accents (labels, quotes): {$p['typography']['accent_font']}
- Hero: {$p['typography']['hero_size']} / weight {$p['typography']['hero_weight']}
- Section titles: {$p['typography']['section_title']}
- Body: {$p['typography']['body']} / line-height {$p['typography']['line_height']}

KEY CHARACTERISTICS:
- {$chars}

SECTION PATTERNS:
- Hero: {$p['layout_patterns']['hero']}
- Features: {$p['layout_patterns']['features']}
- About: {$p['layout_patterns']['about']}
- CTA: {$p['layout_patterns']['cta']}

AVOID: {$p['avoid']}

{$variantsStr}

INDUSTRY: {$industry}
Hero Focus: {$i['hero_focus']}
Trust Signals: {$i['trust_signals']}
Essential Sections: {$sections}
Imagery: {$i['imagery_style']}
Tone: {$i['tone']}

{$copyStr}

THEME BUILDER 3.0 STRUCTURE (CRITICAL - MUST FOLLOW)
AVAILABLE MODULES: {$moduleList}

OUTPUT FORMAT - VALID JSON ONLY:
{\"pages\": [{\"title\": \"Page Title\", \"slug\": \"page-slug\", \"is_homepage\": true, \"status\": \"draft\", \"content\": {\"sections\": [{\"id\": \"section_001\", \"name\": \"Section Name\", \"design\": {\"background_color\": \"#...\", \"padding_top\": \"120px\", \"padding_bottom\": \"120px\"}, \"rows\": [{\"id\": \"row_001\", \"columns\": [{\"id\": \"col_001\", \"width\": \"100%\", \"modules\": [{\"id\": \"mod_001\", \"type\": \"heading\", \"content\": {\"text\": \"...\", \"level\": \"h1\"}, \"design\": {\"text_color\": \"#fff\", \"font_size\": \"72px\"}}]}]}]}]}}]}

MODULE CONTENT:
- heading: {\"text\": \"...\", \"level\": \"h1|h2|h3\"}
- text: {\"text\": \"...\"}
- button: {\"text\": \"...\", \"url\": \"#\", \"target\": \"_self\"}
- blurb: {\"title\": \"...\", \"text\": \"...\", \"icon\": \"fas fa-...\"}
- image: {\"src\": \"descriptive-keywords\", \"alt\": \"...\"}
- counter: {\"number\": \"100\", \"suffix\": \"+\", \"label\": \"...\"}
- testimonial: {\"quote\": \"...\", \"author\": \"...\", \"role\": \"...\"}
- gallery: {\"keywords\": \"search terms\", \"image_count\": 6}

IMAGES ARE MANDATORY! You MUST include image modules:


- Services: Add image module above each service blurb
- Portfolio/Gallery: Use gallery module with keywords
Image src field contains SEARCH KEYWORDS (not URLs) - system fetches real images automatically.

EVERY MODULE NEEDS COMPLETE design OBJECT.
RESPOND WITH VALID JSON ONLY. No markdown, no explanations.";
}
