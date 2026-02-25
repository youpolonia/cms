<?php
/**
 * About page — the story behind Jessie CMS
 */
?>
<section class="jd-section" style="padding-top: 120px;">
    <div style="max-width: 800px; margin: 0 auto;">
        <div class="jd-fade-up" style="text-align: center; margin-bottom: 64px;">
            <span class="jd-section-badge"><i class="fas fa-heart"></i> Our Story</span>
            <h1 class="jd-section-title">Built with Love, Named with Heart</h1>
        </div>

        <div class="jd-fade-up" style="background: var(--jd-surface); border: 1px solid var(--jd-border); border-radius: var(--jd-radius-lg); padding: 48px; margin-bottom: 48px;">
            <p style="font-size: 4rem; text-align: center; margin-bottom: 24px;">🐕</p>
            <p style="font-size: 1.15rem; line-height: 1.8; color: var(--jd-text-muted); text-align: center; max-width: 600px; margin: 0 auto;">
                Jessie CMS is named after a beloved dog who passed away in November 2025. 
                She was loyal, curious, and always eager to help — qualities we built into this CMS.
            </p>
        </div>

        <div class="jd-fade-up" style="margin-bottom: 48px;">
            <h2 style="font-size: 1.8rem; margin-bottom: 16px;">The Philosophy</h2>
            <p style="color: var(--jd-text-muted); font-size: 1.05rem; line-height: 1.8; margin-bottom: 16px;">
                <strong style="color: var(--jd-text);">"Build, don't patch."</strong> That's our guiding principle. 
                Every feature in Jessie CMS is built properly from the ground up — no quick fixes, no shortcuts, no duct tape.
            </p>
            <p style="color: var(--jd-text-muted); font-size: 1.05rem; line-height: 1.8;">
                We chose pure PHP with zero framework dependencies because we believe in simplicity and reliability. 
                If you can FTP files to a server, you can deploy Jessie CMS. No Composer, no npm, no build steps. 
                Just PHP files that work.
            </p>
        </div>

        <div class="jd-fade-up" style="margin-bottom: 48px;">
            <h2 style="font-size: 1.8rem; margin-bottom: 16px;">By the Numbers</h2>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <?php
                $nums = [
                    ['~1,500', 'PHP Files'],
                    ['~100+', 'Database Tables'],
                    ['18', 'Ready Plugins'],
                    ['6', 'SaaS Tools'],
                    ['49', 'AI Themes'],
                    ['79', 'JTB Modules'],
                    ['143', 'Unit Tests'],
                    ['129+', 'Supported Industries'],
                ];
                foreach ($nums as $n): ?>
                <div style="background: var(--jd-surface); border: 1px solid var(--jd-border); border-radius: var(--jd-radius); padding: 20px; text-align: center;">
                    <div style="font-size: 1.8rem; font-weight: 800; background: var(--jd-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?= $n[0] ?></div>
                    <div style="color: var(--jd-text-muted); font-size: 0.85rem; margin-top: 4px;"><?= $n[1] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="jd-fade-up" style="text-align: center; padding: 48px 0;">
            <h2 style="font-size: 1.8rem; margin-bottom: 16px;">Ready to Get Started?</h2>
            <p style="color: var(--jd-text-muted); font-size: 1.05rem; margin-bottom: 24px;">
                See what Jessie CMS can do for your business. Explore the admin panel or check out our plans.
            </p>
            <div class="jd-hero-buttons" style="justify-content: center;">
                <a href="/admin" class="jd-btn jd-btn-primary">
                    <i class="fas fa-rocket"></i> Try Admin Panel
                </a>
                <a href="/demo-pricing" class="jd-btn jd-btn-outline">
                    <i class="fas fa-tags"></i> View Pricing
                </a>
            </div>
        </div>
    </div>
</section>
