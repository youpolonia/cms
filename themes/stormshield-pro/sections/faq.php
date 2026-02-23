<?php
$faqLabel = theme_get('faq.label', 'Common Questions');
$faqTitle = theme_get('faq.title', 'Emergency Roofing FAQ');
$faqDesc = theme_get('faq.description', 'Quick answers to help you make informed decisions during a roofing emergency.');

$faqs = [
    [
        'question' => theme_get('faq.q1', 'How quickly can you respond to an emergency?'),
        'answer' => theme_get('faq.a1', 'We aim to arrive within 30-60 minutes for most emergencies in our service area. Our 24/7 dispatch team prioritizes calls based on severity, and active water intrusion always gets immediate attention.'),
        'ts_q' => 'faq.q1',
        'ts_a' => 'faq.a1'
    ],
    [
        'question' => theme_get('faq.q2', 'Do you work with insurance companies?'),
        'answer' => theme_get('faq.a2', 'Absolutely! We document all damage thoroughly and work directly with your insurance adjuster. Many of our repairs are fully covered by homeowner\'s insurance for storm damage.'),
        'ts_q' => 'faq.q2',
        'ts_a' => 'faq.a2'
    ],
    [
        'question' => theme_get('faq.q3', 'What does temporary weatherproofing include?'),
        'answer' => theme_get('faq.a3', 'We use heavy-duty tarps, emergency sealants, and board-up services to prevent further water damage until permanent repairs can be scheduled. This protection typically lasts 30-90 days.'),
        'ts_q' => 'faq.q3',
        'ts_a' => 'faq.a3'
    ],
    [
        'question' => theme_get('faq.q4', 'Are your technicians licensed and insured?'),
        'answer' => theme_get('faq.a4', 'Yes, all our technicians are fully licensed, bonded, and insured. We carry comprehensive liability coverage and workers\' compensation to protect both you and our team.'),
        'ts_q' => 'faq.q4',
        'ts_a' => 'faq.a4'
    ]
];
?>
<section class="ssp-faq" id="faq">
    <div class="ssp-faq-container">
        <div class="ssp-faq-layout">
            <div class="ssp-faq-header" data-animate>
                <span class="ssp-section-label" data-ts="faq.label"><?= esc($faqLabel) ?></span>
                <h2 class="ssp-faq-title" data-ts="faq.title"><?= esc($faqTitle) ?></h2>
                <p class="ssp-faq-desc" data-ts="faq.description"><?= esc($faqDesc) ?></p>
                <a href="/faq" class="ssp-btn ssp-btn-outline ssp-btn-sm">
                    View All FAQs <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="ssp-faq-list" data-animate>
                <?php foreach ($faqs as $index => $faq): ?>
                <div class="ssp-faq-item" style="--delay: <?= $index * 0.1 ?>s;">
                    <button class="ssp-faq-trigger" aria-expanded="false">
                        <span class="ssp-faq-question" data-ts="<?= $faq['ts_q'] ?>"><?= esc($faq['question']) ?></span>
                        <span class="ssp-faq-icon">
                            <i class="fas fa-plus"></i>
                        </span>
                    </button>
                    <div class="ssp-faq-answer">
                        <p data-ts="<?= $faq['ts_a'] ?>"><?= esc($faq['answer']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
