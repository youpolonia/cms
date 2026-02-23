<?php
$teamLabel = theme_get('team.label', 'Our Advocates');
$teamTitle = theme_get('team.title', 'Meet Our Legal Experts');
$teamDesc = theme_get('team.description', 'Our attorneys combine decades of experience with innovative thinking to deliver exceptional results for our clients.');
?>
<section class="llg-section llg-team" id="team">
    <div class="container">
        <div class="llg-section-header" data-animate>
            <span class="llg-section-label" data-ts="team.label"><?= esc($teamLabel) ?></span>
            <div class="llg-section-divider"></div>
            <h2 class="llg-section-title" data-ts="team.title"><?= esc($teamTitle) ?></h2>
            <p class="llg-section-desc" data-ts="team.description"><?= esc($teamDesc) ?></p>
        </div>
        <div class="llg-team-grid">
            <div class="llg-team-member" data-animate>
                <div class="llg-member-image">
                    <div class="llg-member-placeholder">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
                <div class="llg-member-info">
                    <h3 class="llg-member-name">Robert Lexington</h3>
                    <p class="llg-member-title">Senior Partner</p>
                    <p class="llg-member-bio">30+ years in corporate law and M&A. Former general counsel of a Fortune 500 company.</p>
                    <div class="llg-member-specialties">
                        <span>Corporate Law</span>
                        <span>M&A</span>
                    </div>
                </div>
            </div>
            <div class="llg-team-member" data-animate>
                <div class="llg-member-image">
                    <div class="llg-member-placeholder">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
                <div class="llg-member-info">
                    <h3 class="llg-member-name">Amanda Chen</h3>
                    <p class="llg-member-title">IP Partner</p>
                    <p class="llg-member-bio">Patent attorney with background in engineering. Named "IP Star" for five consecutive years.</p>
                    <div class="llg-member-specialties">
                        <span>Intellectual Property</span>
                        <span>Patents</span>
                    </div>
                </div>
            </div>
            <div class="llg-team-member" data-animate>
                <div class="llg-member-image">
                    <div class="llg-member-placeholder">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
                <div class="llg-member-info">
                    <h3 class="llg-member-name">David Sterling</h3>
                    <p class="llg-member-title">Litigation Partner</p>
                    <p class="llg-member-bio">Former federal prosecutor with exceptional trial record in complex commercial disputes.</p>
                    <div class="llg-member-specialties">
                        <span>Commercial Litigation</span>
                        <span>Regulatory</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="llg-team-cta" data-animate>
            <p>Our collaborative approach ensures you benefit from our collective expertise.</p>
            <a href="/team" class="llg-btn llg-btn--secondary">View Full Team</a>
        </div>
    </div>
</section>
