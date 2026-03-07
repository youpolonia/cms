<?php
/**
 * Contact page — Jessie CMS Demo
 */
?>
<section class="jd-section" style="padding-top: 120px;">
    <div class="jd-section-header jd-fade-up">
        <span class="jd-section-badge"><i class="fas fa-envelope"></i> Get in Touch</span>
        <h2 class="jd-section-title">Contact Us</h2>
        <p class="jd-section-desc">Have questions about Jessie CMS? We'd love to hear from you.</p>
    </div>

    <div style="max-width: 800px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 48px;">
        <!-- Contact Form -->
        <div class="jd-fade-up">
            <form method="POST" action="/api/contact" style="display: flex; flex-direction: column; gap: 20px;">
                <?php if (function_exists('csrf_field')) csrf_field(); ?>
                <div>
                    <label style="display: block; margin-bottom: 6px; color: var(--jd-text); font-weight: 500;">Name</label>
                    <input type="text" name="name" required
                           style="width: 100%; padding: 12px 16px; background: var(--jd-surface); border: 1px solid var(--jd-border); border-radius: 8px; color: var(--jd-text); font-size: 1rem;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; color: var(--jd-text); font-weight: 500;">Email</label>
                    <input type="email" name="email" required
                           style="width: 100%; padding: 12px 16px; background: var(--jd-surface); border: 1px solid var(--jd-border); border-radius: 8px; color: var(--jd-text); font-size: 1rem;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; color: var(--jd-text); font-weight: 500;">Subject</label>
                    <input type="text" name="subject"
                           style="width: 100%; padding: 12px 16px; background: var(--jd-surface); border: 1px solid var(--jd-border); border-radius: 8px; color: var(--jd-text); font-size: 1rem;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; color: var(--jd-text); font-weight: 500;">Message</label>
                    <textarea name="message" rows="5" required
                              style="width: 100%; padding: 12px 16px; background: var(--jd-surface); border: 1px solid var(--jd-border); border-radius: 8px; color: var(--jd-text); font-size: 1rem; resize: vertical;"></textarea>
                </div>
                <div style="display: none;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                <button type="submit" class="jd-btn jd-btn-primary" style="align-self: flex-start;">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>

        <!-- Contact Info -->
        <div class="jd-fade-up" style="display: flex; flex-direction: column; gap: 32px; padding-top: 8px;">
            <div>
                <h3 style="color: var(--jd-text); margin-bottom: 12px;"><i class="fas fa-code" style="color: var(--jd-purple); margin-right: 8px;"></i> Open Source</h3>
                <p style="color: var(--jd-muted);">Jessie CMS is open source. Check out the code, report issues, or contribute on GitHub.</p>
                <a href="https://github.com/youpolonia/cms" target="_blank" style="color: var(--jd-cyan); text-decoration: none;">
                    <i class="fab fa-github"></i> github.com/youpolonia/cms
                </a>
            </div>
            <div>
                <h3 style="color: var(--jd-text); margin-bottom: 12px;"><i class="fas fa-comments" style="color: var(--jd-cyan); margin-right: 8px;"></i> Community</h3>
                <p style="color: var(--jd-muted);">Join the discussion, get help, and share your Jessie CMS projects.</p>
            </div>
            <div>
                <h3 style="color: var(--jd-text); margin-bottom: 12px;"><i class="fas fa-headset" style="color: var(--jd-amber); margin-right: 8px;"></i> Support</h3>
                <p style="color: var(--jd-muted);">Pro and Agency license holders get priority email support with &lt;24h response time.</p>
            </div>
        </div>
    </div>
</section>
