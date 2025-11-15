<?php
/**
 * Company information template
 * Extends layout.php
 */

declare(strict_types=1);

if (!isset($content)) {
    throw new RuntimeException('Content variable not set for company template');
}

ob_start();
?><article class="company-info">
    <header>
        <h1><?= htmlspecialchars($companyName ?? 'Our Company') ?></h1>
        <?php if (isset($tagline)): ?>
            <p class="tagline"><?= htmlspecialchars($tagline) ?></p>
        <?php endif; ?>
    </header>

<div class="company-content">
        <?php if (isset($missionStatement)): ?>
<section class="mission">
                <h2>Our Mission</h2>
                <p><?= htmlspecialchars($missionStatement) ?></p>
            </section>
        <?php endif; ?>
        <?php if (isset($teamMembers) && is_array($teamMembers)): ?>
<section class="team">
                <h2>Our Team</h2>
                <div class="team-members">
                    <?php foreach ($teamMembers as $member): ?>
<div class="team-member">
                            <h3><?= htmlspecialchars($member['name'] ?? '') ?></h3>
                            <p class="position"><?= htmlspecialchars($member['position'] ?? '') ?></p>
                            <?php if (isset($member['bio'])): ?>
                                <p class="bio"><?= htmlspecialchars($member['bio']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
        <?= $content ?>
    </div>
</article>
<?php
$content = ob_get_clean();
// Render within base layout template
require_once __DIR__ . '/layout.php';
