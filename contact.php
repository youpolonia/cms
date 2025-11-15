<?php
// Contact page
require_once __DIR__ . '/includes/header.php';
?>
<main class="container">
    <h1>Contact Us</h1>
    <form class="contact-form" method="post">
<?= csrf_field(); ?>
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" required></textarea>
        </div>
        <button type="submit">Send Message</button>
    </form>
</main>
<?php
require_once __DIR__ . '/includes/footer.php';