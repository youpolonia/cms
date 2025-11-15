/**
 * Page template for the corporate theme
 * This template extends the base template and overrides specific sections
 */

// Extend the base template
$this->extend('base');

// Set the title
$this->section('title');
echo $page['title'] . ' | Corporate Theme';
$this->endSection();

// Add custom CSS to the head
$this->section('head');
?><link rel="stylesheet" href="<?php echo $this->asset('css/corporate.css'); ?>">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap">
$this->endSection();

// Main content section
$this->section('content');
?><div class="page-header">
    <h1><?php echo $page['title']; ?></h1>
    <?php if (!empty($page['subtitle'])): ?>
        <h2 class="subtitle"><?php echo $page['subtitle']; ?></h2>
    <?php endif;  ?>
</div>

<div class="page-content">
    <?php echo $page['content'];  ?>
</div>

<?php if (!empty($page['sidebar'])): ?>
<div class="sidebar">
    <?php echo $page['sidebar'];  ?>
</div>
<?php endif;  ?><?php
$this->endSection();

// Footer section
$this->section('footer');
?><div class="footer-links">
    <ul>
        <li><a href="/about">About Us</a></li>
        <li><a href="/contact">Contact</a></li>
        <li><a href="/privacy">Privacy Policy</a></li>
        <li><a href="/terms">Terms of Service</a></li>
    </ul>
</div>
<div class="social-links">
    <a href="#" class="social-icon facebook">Facebook</a>
    <a href="#" class="social-icon twitter">Twitter</a>
    <a href="#" class="social-icon linkedin">LinkedIn</a>
</div>
$this->endSection();

// Custom scripts
$this->section('scripts');
?><script src="<?php echo $this->asset('js/corporate.js'); ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Corporate theme specific JavaScript
        console.log('Corporate theme initialized');
    });
?></script>
$this->endSection();
