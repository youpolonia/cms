<?php
$featuresLabel = theme_get('features.label', 'Our Methodology');
$featuresTitle = theme_get('features.title', 'Why Conversation-First Works');
$featuresDesc = theme_get('features.description', 'Our immersive approach combines live practice, cultural context, and personalized feedback to build real-world language skills faster.');
?>
<section class="lsa-section lsa-features" id="features">
    <div class="container">
        <div class="lsa-section-header" data-animate>
            <span class="lsa-section-label" data-ts="features.label"><?= esc($featuresLabel) ?></span>
            <div class="lsa-section-divider"></div>
            <h2 class="lsa-section-title" data-ts="features.title"><?= esc($featuresTitle) ?></h2>
            <p class="lsa-section-desc" data-ts="features.description"><?= esc($featuresDesc) ?></p>
        </div>

        <div class="lsa-features-grid">
            <div class="lsa-feature-card" data-animate>
                <div class="lsa-feature-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3 class="lsa-feature-title">Live Conversation Practice</h3>
                <p class="lsa-feature-desc">Daily small-group sessions with native speakers build confidence and fluency through real dialogue, not textbook exercises.</p>
                <div class="lsa-feature-stats">
                    <span><strong>40+</strong> weekly sessions</span>
                    <span><strong>6</strong> max per group</span>
                </div>
            </div>

            <div class="lsa-feature-card" data-animate>
                <div class="lsa-feature-icon">
                    <i class="fas fa-globe-americas"></i>
                </div>
                <h3 class="lsa-feature-title">Cultural Immersion Modules</h3>
                <p class="lsa-feature-desc">Learn language within its living context through virtual cultural exchanges, holiday celebrations, and authentic media analysis.</p>
                <div class="lsa-feature-stats">
                    <span><strong>12</strong> cultural themes</span>
                    <span><strong>50+</strong> native partners</span>
                </div>
            </div>

            <div class="lsa-feature-card" data-animate>
                <div class="lsa-feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="lsa-feature-title">Personalized Progress Tracking</h3>
                <p class="lsa-feature-desc">AI-powered feedback on pronunciation, vocabulary growth, and conversation fluency with weekly personalized improvement plans.</p>
                <div class="lsa-feature-stats">
                    <span><strong>24/7</strong> progress dashboard</span>
                    <span><strong>98%</strong> accuracy rate</span>
                </div>
            </div>

            <div class="lsa-feature-card" data-animate>
                <div class="lsa-feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="lsa-feature-title">Community Learning</h3>
                <p class="lsa-feature-desc">Join language-specific clubs, conversation partners, and global events that turn learning into a social, motivating experience.</p>
                <div class="lsa-feature-stats">
                    <span><strong>500+</strong> active members</span>
                    <span><strong>4</strong> language communities</span>
                </div>
            </div>

            <div class="lsa-feature-card" data-animate>
                <div class="lsa-feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3 class="lsa-feature-title">Flexible Mobile Learning</h3>
                <p class="lsa-feature-desc">Access lessons, practice exercises, and conversation tools on any device. Learn during your commute, lunch break, or travel.</p>
                <div class="lsa-feature-stats">
                    <span><strong>100%</strong> mobile optimized</span>
                    <span><strong>Offline</strong> practice mode</span>
                </div>
            </div>

            <div class="lsa-feature-card" data-animate>
                <div class="lsa-feature-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <h3 class="lsa-feature-title">Certification Pathways</h3>
                <p class="lsa-feature-desc">Prepare for recognized language proficiency exams (DELE, DELF, HSK, TOEFL) with targeted practice and mock tests.</p>
                <div class="lsa-feature-stats">
                    <span><strong>4</strong> exam preparations</span>
                    <span><strong>95%</strong> pass rate</span>
                </div>
            </div>
        </div>

        <div class="lsa-features-illustration" data-animate>
            <div class="lsa-illustration-content">
                <h3>Experience the Difference</h3>
                <p>Traditional methods focus on grammar rules first. Our conversation-first approach mirrors how we naturally acquire language—through communication.</p>
                <a href="/methodology" class="lsa-btn lsa-btn-secondary">
                    Dive Deeper Into Our Method
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="lsa-illustration-image">
                <img src="https://images.pexels.com/photos/5905709/pexels-photo-5905709.jpeg?auto=compress&cs=tinysrgb&h=650&w=940" alt="Student engaged in online language conversation" loading="lazy">
            </div>
        </div>
    </div>
</section>
