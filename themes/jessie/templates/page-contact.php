<?php
/**
 * Jessie Theme - Contact Page Template
 * Two-column layout: content on left, contact form on right
 *
 * @var array $page Page data array
 * @var string $content Raw content (fallback)
 */
?>
<section class="contact-page">
    <div class="container">
        <header class="contact-header">
            <?php if (!empty($page['title'])): ?>
            <h1><?= htmlspecialchars($page['title']) ?></h1>
            <?php endif; ?>
            <?php if (!empty($page['excerpt'])): ?>
            <p class="contact-subtitle"><?= htmlspecialchars($page['excerpt']) ?></p>
            <?php endif; ?>
        </header>

        <div class="contact-grid">
            <div class="contact-content">
                <div class="content-body">
                    <?= $page['content'] ?? $content ?? '' ?>
                </div>
            </div>

            <div class="contact-form-wrapper">
                <h2>Send us a message</h2>
                <form class="contact-form" method="POST" action="/contact-submit">
                    <div class="form-group">
                        <label for="contact-name">Your Name</label>
                        <input type="text" id="contact-name" name="name" required placeholder="John Doe">
                    </div>
                    <div class="form-group">
                        <label for="contact-email">Email Address</label>
                        <input type="email" id="contact-email" name="email" required placeholder="john@example.com">
                    </div>
                    <div class="form-group">
                        <label for="contact-subject">Subject</label>
                        <input type="text" id="contact-subject" name="subject" required placeholder="How can we help?">
                    </div>
                    <div class="form-group">
                        <label for="contact-message">Message</label>
                        <textarea id="contact-message" name="message" rows="5" required placeholder="Your message..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-submit">Send Message →</button>
                </form>
            </div>
        </div>
    </div>
</section>
