<?php
$featuresLabel = theme_get('features.label', 'Our Technology');
$featuresTitle = theme_get('features.title', 'Advanced Genomic Analysis Platform');
$featuresDesc = theme_get('features.description', 'Our integrated platform combines state-of-the-art sequencing, AI analytics, and clinical interpretation to deliver actionable health intelligence.');
?>
<section class="gp-features">
    <div class="container">
        <div class="gp-features__header" data-animate>
            <span class="gp-features__label" data-ts="features.label"><?= esc($featuresLabel) ?></span>
            <div class="gp-features__divider"></div>
            <h2 class="gp-features__title" data-ts="features.title"><?= esc($featuresTitle) ?></h2>
            <p class="gp-features__desc" data-ts="features.description"><?= esc($featuresDesc) ?></p>
        </div>

        <div class="gp-features__grid">
            <div class="gp-features__card" data-animate>
                <div class="gp-features__card-icon">
                    <i class="fas fa-sequencing"></i>
                </div>
                <h3 class="gp-features__card-title">High-Throughput Sequencing</h3>
                <p class="gp-features__card-desc">Industry-leading Illumina and Oxford Nanopore technology for comprehensive genomic coverage.</p>
                <ul class="gp-features__card-list">
                    <li>Whole Genome & Exome Sequencing</li>
                    <li>RNA & Epigenetic Analysis</li>
                    <li>Microbiome Profiling</li>
                </ul>
            </div>

            <div class="gp-features__card" data-animate>
                <div class="gp-features__card-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <h3 class="gp-features__card-title">AI-Driven Interpretation</h3>
                <p class="gp-features__card-desc">Machine learning algorithms identify clinically relevant variants and predict disease risk with 99.8% accuracy.</p>
                <ul class="gp-features__card-list">
                    <li>Variant Pathogenicity Scoring</li>
                    <li>Polygenic Risk Assessment</li>
                    <li>Drug Response Prediction</li>
                </ul>
            </div>

            <div class="gp-features__card" data-animate>
                <div class="gp-features__card-icon">
                    <i class="fas fa-chart-network"></i>
                </div>
                <h3 class="gp-features__card-title">Clinical Decision Support</h3>
                <p class="gp-features__card-desc">Integrated tools for healthcare providers to translate genomic data into personalized treatment plans.</p>
                <ul class="gp-features__card-list">
                    <li>Electronic Health Record Integration</li>
                    <li>Treatment Recommendation Engine</li>
                    <li>Clinical Trial Matching</li>
                </ul>
            </div>

            <div class="gp-features__card" data-animate>
                <div class="gp-features__card-icon">
                    <i class="fas fa-database"></i>
                </div>
                <h3 class="gp-features__card-title">Longitudinal Data Tracking</h3>
                <p class="gp-features__card-desc">Secure platform for monitoring health outcomes and updating recommendations as new research emerges.</p>
                <ul class="gp-features__card-list">
                    <li>Lifetime Genetic Record</li>
                    <li>Health Trend Analysis</li>
                    <li>Research Participation Portal</li>
                </ul>
            </div>

            <div class="gp-features__card" data-animate>
                <div class="gp-features__card-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3 class="gp-features__card-title">Privacy & Security</h3>
                <p class="gp-features__card-desc">Enterprise-grade encryption and compliance with global data protection regulations (GDPR, HIPAA, CLIA).</p>
                <ul class="gp-features__card-list">
                    <li>End-to-End Encryption</li>
                    <li>Consent Management</li>
                    <li>Data Anonymization</li>
                </ul>
            </div>

            <div class="gp-features__card" data-animate>
                <div class="gp-features__card-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3 class="gp-features__card-title">Collaborative Ecosystem</h3>
                <p class="gp-features__card-desc">Partnerships with leading research institutions, pharmaceutical companies, and healthcare systems.</p>
                <ul class="gp-features__card-list">
                    <li>Research Consortium Access</li>
                    <li>Physician Network</li>
                    <li>Patient Advocacy Groups</li>
                </ul>
            </div>
        </div>
    </div>
</section>
