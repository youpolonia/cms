<?php
$appointmentLabel = theme_get('appointment.label', 'Private Viewing');
$appointmentTitle = theme_get('appointment.title', 'Schedule a White‑Glove Viewing');
$appointmentDesc = theme_get('appointment.description', 'Experience our rare editions in person. Our specialists will guide you through a private, appointment‑only session in our viewing room.');
$appointmentBtnText = theme_get('appointment.btn_text', 'Request an Appointment');
$appointmentBtnLink = theme_get('appointment.btn_link', '#contact');
$appointmentPhone = theme_get('header.phone', '+1 (212) 555-0187');
$appointmentEmail = theme_get('header.email', 'viewing@vellumvault.com');

$appointmentImage = 'https://images.pexels.com/photos/19594239/pexels-photo-19594239.jpeg?auto=compress&cs=tinysrgb&h=650&w=940';
?>
<section class="section vvr-appointment-section" id="appointment" style="background-color: var(--surface-elevated);">
    <div class="container">
        <div class="vvr-appointment-card">
            <div class="vvr-appointment-content" data-animate>
                <span class="section-label" data-ts="appointment.label"><?= esc($appointmentLabel) ?></span>
                <div class="section-divider"></div>
                <h2 class="section-title" data-ts="appointment.title"><?= esc($appointmentTitle) ?></h2>
                <p class="section-desc" data-ts="appointment.description"><?= esc($appointmentDesc) ?></p>

                <div class="vvr-appointment-details">
                    <?php if ($appointmentPhone): ?>
                    <div class="vvr-appointment-detail">
                        <div class="vvr-appointment-detail-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="vvr-appointment-detail-text">
                            <h4>By Telephone</h4>
                            <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', $appointmentPhone)) ?>" data-ts="header.phone"><?= esc($appointmentPhone) ?></a>
                            <p>Monday–Friday, 9am–6pm EST</p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($appointmentEmail): ?>
                    <div class="vvr-appointment-detail">
                        <div class="vvr-appointment-detail-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="vvr-appointment-detail-text">
                            <h4>By Email</h4>
                            <a href="mailto:<?= esc($appointmentEmail) ?>" data-ts="header.email"><?= esc($appointmentEmail) ?></a>
                            <p>We respond within 24 hours.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <a href="<?= esc($appointmentBtnLink) ?>" class="vvr-btn vvr-btn-primary vvr-btn-large" data-ts="appointment.btn_text" data-ts-href="appointment.btn_link">
                    <?= esc($appointmentBtnText) ?> <i class="fas fa-calendar-check"></i>
                </a>
            </div>

            <div class="vvr-appointment-visual" data-animate>
                <div class="vvr-appointment-image">
                    <img src="<?= esc($appointmentImage) ?>" alt="Vibrant neon sign of The Last Bookstore with a backdrop of books in Los Angeles." loading="lazy">
                    <div class="vvr-appointment-image-overlay"></div>
                </div>
                <div class="vvr-appointment-note">
                    <i class="fas fa-quote-left"></i>
                    <p>Each viewing is a curated experience, designed to connect you with literary history.</p>
                </div>
            </div>
        </div>
    </div>
</section>
