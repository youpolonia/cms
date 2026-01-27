<?php
$pageTitle = $page['title'] ?? 'Contact';
require_once __DIR__ . '/layouts/header.php';
?>
<article class="contact-page">
    <header class="contact-header">
        <h1><?= esc($page['title']) ?></h1>
        <?php if (!empty($page['excerpt'])): ?>
        <p class="contact-subtitle"><?= esc($page['excerpt']) ?></p>
        <?php endif; ?>
    </header>
    
    <div class="contact-container">
        <div class="contact-content">
            <?= $page['content'] ?? '' ?>
        </div>
        
        <div class="contact-form-wrapper">
            <h2>Send us a message</h2>
            <form class="contact-form" method="POST" action="/contact-submit">
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>
    </div>
</article>

<style>
.contact-page { padding: 140px 24px 80px; }
.contact-header { text-align: center; max-width: 700px; margin: 0 auto 60px; }
.contact-header h1 { font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 16px; }
.contact-subtitle { font-size: 1.15rem; color: var(--text-secondary); }
.contact-container { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: start; }
.contact-content { line-height: 1.8; font-size: 1.05rem; }
.contact-content h2, .contact-content h3 { margin: 30px 0 15px; }
.contact-content p { margin-bottom: 20px; }
.contact-content a { color: var(--accent-primary); }
.contact-form-wrapper { background: var(--surface-secondary); border-radius: var(--radius-lg); padding: 40px; }
.contact-form-wrapper h2 { font-size: 1.5rem; margin-bottom: 30px; }
.contact-form .form-group { margin-bottom: 20px; }
.contact-form label { display: block; font-size: 0.9rem; font-weight: 500; margin-bottom: 8px; color: var(--text-secondary); }
.contact-form input, .contact-form textarea { width: 100%; padding: 14px 16px; border: 1px solid var(--border-primary); border-radius: var(--radius-md); background: var(--surface-primary); color: var(--text-primary); font-size: 1rem; transition: border-color 0.2s; }
.contact-form input:focus, .contact-form textarea:focus { outline: none; border-color: var(--accent-primary); }
.contact-form textarea { resize: vertical; min-height: 120px; }
.btn-submit { width: 100%; padding: 16px; background: var(--accent-primary); color: white; border: none; border-radius: var(--radius-md); font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-submit:hover { background: var(--accent-secondary); transform: translateY(-2px); }
@media (max-width: 900px) { .contact-container { grid-template-columns: 1fr; } }
</style>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
