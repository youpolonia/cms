<?php
$faqBadge = theme_get('faq.badge', '');
$faqTitle = theme_get('faq.title', 'How can we help?');
$faqSubtitle = theme_get('faq.subtitle', 'Search our knowledge base or browse the frequently asked questions below.');
$faqSearchPlaceholder = theme_get('faq.search_placeholder', 'Type your question...');
$q1 = theme_get('faq.q1', 'What services do you offer?');
$a1 = theme_get('faq.a1', '<p>We offer a comprehensive range of services tailored to meet your needs. From initial consultation to final delivery, our team ensures quality at every step.</p>');
$q2 = theme_get('faq.q2', 'How do I get started?');
$a2 = theme_get('faq.a2', '<p>Getting started is easy. Simply reach out to us through our contact form or give us a call. We\'ll schedule a free consultation to discuss your requirements.</p>');
$q3 = theme_get('faq.q3', 'What are your pricing options?');
$a3 = theme_get('faq.a3', '<p>We offer flexible pricing plans to suit different budgets and project scopes. Contact us for a detailed quote tailored to your specific needs.</p>');
$q4 = theme_get('faq.q4', 'Do you offer support after delivery?');
$a4 = theme_get('faq.a4', '<p>Absolutely. We provide ongoing support and maintenance to ensure everything continues running smoothly after project completion.</p>');
$q5 = theme_get('faq.q5', 'What is your typical turnaround time?');
$a5 = theme_get('faq.a5', '<p>Turnaround times vary depending on project complexity. Most projects are completed within 2-4 weeks. We\'ll provide a detailed timeline during consultation.</p>');
$q6 = theme_get('faq.q6', 'Can I request custom solutions?');
$a6 = theme_get('faq.a6', '<p>Yes! We specialize in custom solutions. Every project is unique, and we work closely with you to deliver exactly what you need.</p>');
?>
<section class="fs-faq fs-faq--search" id="faq">
  <div class="container">
    <div class="fs-faq-header" data-animate="fade-up">
      <?php if ($faqBadge): ?><span class="fs-faq-badge" data-ts="faq.badge"><?= esc($faqBadge) ?></span><?php endif; ?>
      <h2 class="fs-faq-title" data-ts="faq.title"><?= esc($faqTitle) ?></h2>
      <?php if ($faqSubtitle): ?><p class="fs-faq-subtitle" data-ts="faq.subtitle"><?= esc($faqSubtitle) ?></p><?php endif; ?>
      <div class="fs-faq-search-wrap">
        <i class="fas fa-search fs-faq-search-icon"></i>
        <input type="text" class="fs-faq-search" placeholder="<?= esc($faqSearchPlaceholder) ?>" data-ts="faq.search_placeholder" id="fs-faq-search-input">
      </div>
    </div>
    <div class="fs-faq-list" data-animate="fade-up">
      <?php if ($q1): ?>
      <details class="fs-faq-item" data-search="<?= esc(strtolower($q1)) ?>">
        <summary class="fs-faq-question" data-ts="faq.q1"><?= esc($q1) ?><span class="fs-faq-icon"></span></summary>
        <div class="fs-faq-answer" data-ts="faq.a1"><?= $a1 ?></div>
      </details>
      <?php endif; ?>
      <?php if ($q2): ?>
      <details class="fs-faq-item" data-search="<?= esc(strtolower($q2)) ?>">
        <summary class="fs-faq-question" data-ts="faq.q2"><?= esc($q2) ?><span class="fs-faq-icon"></span></summary>
        <div class="fs-faq-answer" data-ts="faq.a2"><?= $a2 ?></div>
      </details>
      <?php endif; ?>
      <?php if ($q3): ?>
      <details class="fs-faq-item" data-search="<?= esc(strtolower($q3)) ?>">
        <summary class="fs-faq-question" data-ts="faq.q3"><?= esc($q3) ?><span class="fs-faq-icon"></span></summary>
        <div class="fs-faq-answer" data-ts="faq.a3"><?= $a3 ?></div>
      </details>
      <?php endif; ?>
      <?php if ($q4): ?>
      <details class="fs-faq-item" data-search="<?= esc(strtolower($q4)) ?>">
        <summary class="fs-faq-question" data-ts="faq.q4"><?= esc($q4) ?><span class="fs-faq-icon"></span></summary>
        <div class="fs-faq-answer" data-ts="faq.a4"><?= $a4 ?></div>
      </details>
      <?php endif; ?>
      <?php if ($q5): ?>
      <details class="fs-faq-item" data-search="<?= esc(strtolower($q5)) ?>">
        <summary class="fs-faq-question" data-ts="faq.q5"><?= esc($q5) ?><span class="fs-faq-icon"></span></summary>
        <div class="fs-faq-answer" data-ts="faq.a5"><?= $a5 ?></div>
      </details>
      <?php endif; ?>
      <?php if ($q6): ?>
      <details class="fs-faq-item" data-search="<?= esc(strtolower($q6)) ?>">
        <summary class="fs-faq-question" data-ts="faq.q6"><?= esc($q6) ?><span class="fs-faq-icon"></span></summary>
        <div class="fs-faq-answer" data-ts="faq.a6"><?= $a6 ?></div>
      </details>
      <?php endif; ?>
    </div>
  </div>
  <script>
  (function(){
    var input = document.getElementById('fs-faq-search-input');
    if (!input) return;
    input.addEventListener('input', function() {
      var term = this.value.toLowerCase();
      var items = this.closest('.fs-faq--search').querySelectorAll('.fs-faq-item');
      items.forEach(function(item) {
        var text = (item.getAttribute('data-search') || '') + ' ' + item.textContent.toLowerCase();
        item.style.display = (!term || text.indexOf(term) !== -1) ? '' : 'none';
      });
    });
  })();
  </script>
</section>
