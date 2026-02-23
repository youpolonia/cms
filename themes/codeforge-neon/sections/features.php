<?php
$featuresLabel = theme_get('features.label', 'Capabilities');
$featuresTitle = theme_get('features.title', 'Everything You Need to Ship');
$featuresDesc = theme_get('features.description', 'A complete toolkit for turning your ideas into real software—without the complexity.');
?>
<section class="section features-section" id="features">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="features.label"><?= esc($featuresLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="features.title"><?= esc($featuresTitle) ?></h2>
            <p class="section-desc" data-ts="features.description"><?= esc($featuresDesc) ?></p>
        </div>
        
        <div class="features-bento" data-animate>
            <div class="bento-item large">
                <div class="bento-glow"></div>
                <div class="bento-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <h3>Natural Language Input</h3>
                <p>Describe what you want in plain English. "Create a dashboard with sales charts and user analytics." Our AI understands context, intent, and best practices.</p>
                <div class="bento-visual">
                    <div class="input-demo">
                        <span class="cursor"></span>
                        <span class="demo-text">Build a landing page with pricing tiers...</span>
                    </div>
                </div>
            </div>
            
            <div class="bento-item">
                <div class="bento-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h3>Full-Stack Output</h3>
                <p>Get complete frontend, backend, and database code—not just snippets.</p>
            </div>
            
            <div class="bento-item">
                <div class="bento-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3>Instant Preview</h3>
                <p>See your app running in real-time as you describe changes.</p>
            </div>
            
            <div class="bento-item wide">
                <div class="bento-icon">
                    <i class="fas fa-code-branch"></i>
                </div>
                <h3>Export & Own Your Code</h3>
                <p>Download clean, documented source code. Deploy anywhere—Vercel, Netlify, your own servers. No vendor lock-in.</p>
                <div class="tech-badges">
                    <span class="tech-badge">React</span>
                    <span class="tech-badge">Next.js</span>
                    <span class="tech-badge">Node.js</span>
                    <span class="tech-badge">PostgreSQL</span>
                    <span class="tech-badge">Tailwind</span>
                </div>
            </div>
            
            <div class="bento-item">
                <div class="bento-icon">
                    <i class="fas fa-palette"></i>
                </div>
                <h3>Custom Styling</h3>
                <p>Choose your design system or let AI generate a unique look.</p>
            </div>
            
            <div class="bento-item">
                <div class="bento-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Built-in Security</h3>
                <p>Auth, encryption, and security best practices included by default.</p>
            </div>
        </div>
    </div>
</section>
