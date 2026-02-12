<?php
/**
 * Starter Business Theme — Layout
 * Full HTML wrapper for all pages
 */

if (!defined('CMS_ROOT')) exit;

$themeConfig = get_theme_config();
$siteName    = theme_get('brand.site_name', get_site_name());
$siteLogo    = theme_get('brand.logo', get_site_logo());
$bodyClass   = function_exists('get_body_class') ? get_body_class() : '';

$options = $themeConfig['options'] ?? [];
$showCta   = $options['show_header_cta'] ?? true;
$ctaText   = $options['header_cta_text'] ?? 'Get Started';
$ctaUrl    = $options['header_cta_url'] ?? '/contact';

$footerAbout   = $options['footer_about_text'] ?? 'We deliver innovative business solutions that drive growth and transform organizations for the digital age.';
$footerEmail   = $options['footer_email'] ?? 'hello@company.com';
$footerPhone   = $options['footer_phone'] ?? '+1 (555) 123-4567';
$footerAddress = $options['footer_address'] ?? "123 Business Ave, Suite 100\nNew York, NY 10001";
$socialLinkedin = $options['social_linkedin'] ?? '#';
$socialTwitter  = $options['social_twitter'] ?? '#';
$socialFacebook = $options['social_facebook'] ?? '#';

$isTbPage = !empty($page['is_tb_page']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title><?php echo esc($title ?? $siteName); ?></title>

    <?php if (function_exists('render_seo_meta') && !empty($page)): ?>
        <?php echo render_seo_meta($page); ?>
    <?php endif; ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">

    <!-- Theme CSS Variables -->
    <style>
        :root {
            <?php echo generate_theme_css_variables($themeConfig); ?>
        }
<?= generate_studio_css_overrides() ?>
    </style>

    <!-- Theme Stylesheet -->
    <link rel="stylesheet" href="/themes/starter-business/assets/css/style.css">

    <?php
    // Jessie Theme Builder frontend boot
    $jtbBoot = (defined('CMS_ROOT') ? CMS_ROOT : '') . '/plugins/jessie-theme-builder/includes/jtb-frontend-boot.php';
    if (file_exists($jtbBoot)) {
        require_once $jtbBoot;
    }
    ?>
<?= function_exists("theme_render_favicon") ? theme_render_favicon() : "" ?>
<?= function_exists("theme_render_og_image") ? theme_render_og_image() : "" ?>
</head>
<body class="starter-business <?php echo esc($bodyClass); ?>">
<?= function_exists("theme_render_announcement_bar") ? theme_render_announcement_bar() : "" ?>

    <!-- ====== HEADER ====== -->
    <header class="site-header" id="site-header">
        <div class="header-container">
            <div class="header-inner">
                <!-- Logo -->
                <a href="/" class="site-logo" aria-label="<?php echo esc($siteName); ?> — Home" data-ts="brand.logo">
                    <?php if (!empty($siteLogo)): ?>
                        <img src="<?php echo esc($siteLogo); ?>" alt="<?php echo esc($siteName); ?>" class="logo-img">
                    <?php else: ?>
                        <span class="logo-text" data-ts="brand.site_name"><?php echo esc($siteName); ?></span>
                    <?php endif; ?>
                </a>

                <!-- Navigation -->
                <nav class="main-nav" id="main-nav" aria-label="Main navigation">
                    <?php echo render_menu('header', [
                        'class'      => 'nav-links',
                        'link_class' => 'nav-link',
                        'wrap'       => false
                    ]); ?>
                </nav>

                <!-- Header CTA -->
                <?php if ($showCta): ?>
                    <div class="header-actions">
                        <a href="<?php echo esc($ctaUrl); ?>" class="btn btn-primary btn-header-cta" data-ts="header.cta_text" data-ts-href="header.cta_link">
                            <?php echo esc($ctaText); ?>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Mobile Toggle -->
                <button class="mobile-toggle" id="mobile-toggle" aria-label="Toggle navigation" aria-expanded="false">
                    <span class="hamburger">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>
        </div>
    </header>

    <!-- ====== MAIN CONTENT ====== -->
    <main class="site-main" id="site-main">
        <?php if ($isTbPage): ?>
            <?php echo $content; ?>
        <?php else: ?>
            <?php echo $content; ?>
        <?php endif; ?>
    </main>

    <!-- ====== FOOTER ====== -->
    <footer class="site-footer">
        <div class="footer-main">
            <div class="container">
                <div class="footer-grid">
                    <!-- Col 1 — About -->
                    <div class="footer-col footer-about">
                        <a href="/" class="footer-logo" aria-label="<?php echo esc($siteName); ?>" data-ts="brand.logo">
                            <?php if (!empty($siteLogo)): ?>
                                <img src="<?php echo esc($siteLogo); ?>" alt="<?php echo esc($siteName); ?>" class="footer-logo-img">
                            <?php else: ?>
                                <span class="footer-logo-text" data-ts="brand.site_name"><?php echo esc($siteName); ?></span>
                            <?php endif; ?>
                        </a>
                        <p class="footer-desc" data-ts="footer.description"><?php echo esc($footerAbout); ?></p>
                        <div class="footer-social">
                            <?php if ($socialLinkedin && $socialLinkedin !== '#'): ?>
                                <a href="<?php echo esc($socialLinkedin); ?>" class="social-link" aria-label="LinkedIn" target="_blank" rel="noopener"><i class="fab fa-linkedin-in"></i></a>
                            <?php endif; ?>
                            <?php if ($socialTwitter && $socialTwitter !== '#'): ?>
                                <a href="<?php echo esc($socialTwitter); ?>" class="social-link" aria-label="Twitter" target="_blank" rel="noopener"><i class="fab fa-x-twitter"></i></a>
                            <?php endif; ?>
                            <?php if ($socialFacebook && $socialFacebook !== '#'): ?>
                                <a href="<?php echo esc($socialFacebook); ?>" class="social-link" aria-label="Facebook" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Col 2 — Services -->
                    <div class="footer-col">
                        <h4 class="footer-heading">Services</h4>
                        <?php echo render_menu('footer', [
                            'class'      => 'footer-menu',
                            'link_class' => 'footer-link',
                            'wrap'       => false
                        ]); ?>
                    </div>

                    <!-- Col 3 — Company -->
                    <div class="footer-col">
                        <h4 class="footer-heading">Company</h4>
                        <ul class="footer-menu">
                            <li><a href="/about" class="footer-link">About Us</a></li>
                            <li><a href="/careers" class="footer-link">Careers</a></li>
                            <li><a href="/blog" class="footer-link">Blog</a></li>
                            <li><a href="/contact" class="footer-link">Contact</a></li>
                        </ul>
                    </div>

                    <!-- Col 4 — Contact -->
                    <div class="footer-col">
                        <h4 class="footer-heading">Contact</h4>
                        <ul class="footer-contact">
                            <li>
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:<?php echo esc($footerEmail); ?>"><?php echo esc($footerEmail); ?></a>
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                <a href="tel:<?php echo esc(preg_replace('/[^+0-9]/', '', $footerPhone)); ?>"><?php echo esc($footerPhone); ?></a>
                            </li>
                            <li>
                                <i class="fas fa-location-dot"></i>
                                <span><?php echo nl2br(esc($footerAddress)); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-inner">
                    <p class="copyright" data-ts="footer.copyright">&copy; <?php echo date('Y'); ?> <?php echo esc($siteName); ?>. All rights reserved.</p>
                    <ul class="legal-links">
                        <li><a href="/privacy-policy">Privacy Policy</a></li>
                        <li><a href="/terms-of-service">Terms of Service</a></li>
                        <li><a href="/cookies">Cookies</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Theme JS -->
    <script src="/themes/starter-business/assets/js/main.js"></script>
</body>
</html>
