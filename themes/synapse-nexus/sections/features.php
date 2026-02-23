<?php
$featuresLabel = theme_get('features.label', 'CORE FEATURES');
$featuresTitle = theme_get('features.title', 'Built for How Modern Teams Work');
$featuresDesc = theme_get('features.description', 'Everything you need to collaborate seamlessly across time zones, codebases, and tools.');
?>
<section class="sn-section sn-section-features" id="features">
    <div class="container">
        <div class="sn-section-header" data-animate>
            <span class="sn-section-label" data-ts="features.label"><?= esc($featuresLabel) ?></span>
            <div class="sn-section-divider"></div>
            <h2 class="sn-section-title" data-ts="features.title"><?= esc($featuresTitle) ?></h2>
            <p class="sn-section-desc" data-ts="features.description"><?= esc($featuresDesc) ?></p>
        </div>
        <div class="sn-features-grid">
            <div class="sn-feature-card" data-animate>
                <div class="sn-feature-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3 class="sn-feature-title">Channels & Threads</h3>
                <p class="sn-feature-desc">Organize conversations by project, team, or topic. Keep discussions focused and searchable.</p>
                <ul class="sn-feature-list">
                    <li>Public & private channels</li>
                    <li>Threaded replies</li>
                    <li>@mentions & reactions</li>
                </ul>
            </div>
            <div class="sn-feature-card" data-animate>
                <div class="sn-feature-icon">
                    <i class="fas fa-video"></i>
                </div>
                <h3 class="sn-feature-title">HD Video Calls</h3>
                <p class="sn-feature-desc">Crystal‑clear video with screen sharing, collaborative whiteboards, and recording.</p>
                <ul class="sn-feature-list">
                    <li>Up to 100 participants</li>
                    <li>Virtual backgrounds</li>
                    <li>Live captions</li>
                </ul>
            </div>
            <div class="sn-feature-card" data-animate>
                <div class="sn-feature-icon">
                    <i class="fas fa-plug"></i>
                </div>
                <h3 class="sn-feature-title">500+ Integrations</h3>
                <p class="sn-feature-desc">Connect GitHub, Jira, Figma, Linear, and your entire dev stack directly into conversations.</p>
                <ul class="sn-feature-list">
                    <li>Real‑time notifications</li>
                    <li>Custom webhooks</li>
                    <li>API‑first design</li>
                </ul>
            </div>
            <div class="sn-feature-card" data-animate>
                <div class="sn-feature-icon">
                    <i class="fas fa-code"></i>
                </div>
                <h3 class="sn-feature-title">Collaborative Coding</h3>
                <p class="sn-feature-desc">Pair program in real‑time with shared terminals, code editors, and live previews.</p>
                <ul class="sn-feature-list">
                    <li>VS Code integration</li>
                    <li>Multi‑cursor editing</li>
                    <li>Terminal sharing</li>
                </ul>
            </div>
            <div class="sn-feature-card" data-animate>
                <div class="sn-feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="sn-feature-title">Enterprise Security</h3>
                <p class="sn-feature-desc">SOC2, GDPR, end‑to‑end encryption, and granular permissions for peace of mind.</p>
                <ul class="sn-feature-list">
                    <li>Single sign‑on (SSO)</li>
                    <li>Audit logs</li>
                    <li>Data residency</li>
                </ul>
            </div>
            <div class="sn-feature-card" data-animate>
                <div class="sn-feature-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <h3 class="sn-feature-title">AI Assistant</h3>
                <p class="sn-feature-desc">Get answers, summarize threads, generate code snippets, and automate workflows.</p>
                <ul class="sn-feature-list">
                    <li>Context‑aware chat</li>
                    <li>Meeting summaries</li>
                    <li>Custom AI workflows</li>
                </ul>
            </div>
        </div>
        <div class="sn-features-cta" data-animate>
            <a href="#features-all" class="sn-btn sn-btn-outline">
                <i class="fas fa-list-ul"></i> View All Features
            </a>
        </div>
    </div>
</section>
