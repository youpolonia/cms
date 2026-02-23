<?php
$pricingBadge = theme_get('pricing.badge', 'Pricing');
$pricingTitle = theme_get('pricing.title', 'Simple, Transparent Pricing');
$pricingSubtitle = theme_get('pricing.subtitle', 'Choose the plan that works best for you and your team.');

$plan1Name = theme_get('pricing.plan1_name', 'Starter');
$plan1Price = theme_get('pricing.plan1_price', '$29');
$plan1Period = theme_get('pricing.plan1_period', '/month');
$plan1Desc = theme_get('pricing.plan1_desc', 'Perfect for individuals and small projects.');
$plan1Features = theme_get('pricing.plan1_features', 'Up to 5 projects|10 GB storage|Basic analytics|Email support');
$plan1BtnText = theme_get('pricing.plan1_btn_text', 'Get Started');
$plan1BtnLink = theme_get('pricing.plan1_btn_link', '/contact');
$plan1Featured = theme_get('pricing.plan1_featured', '');

$plan2Name = theme_get('pricing.plan2_name', 'Professional');
$plan2Price = theme_get('pricing.plan2_price', '$79');
$plan2Period = theme_get('pricing.plan2_period', '/month');
$plan2Desc = theme_get('pricing.plan2_desc', 'Best for growing teams and businesses.');
$plan2Features = theme_get('pricing.plan2_features', 'Unlimited projects|100 GB storage|Advanced analytics|Priority support|Team collaboration');
$plan2BtnText = theme_get('pricing.plan2_btn_text', 'Get Started');
$plan2BtnLink = theme_get('pricing.plan2_btn_link', '/contact');
$plan2Featured = theme_get('pricing.plan2_featured', '1');

$plan3Name = theme_get('pricing.plan3_name', 'Enterprise');
$plan3Price = theme_get('pricing.plan3_price', '$199');
$plan3Period = theme_get('pricing.plan3_period', '/month');
$plan3Desc = theme_get('pricing.plan3_desc', 'For large organizations with custom needs.');
$plan3Features = theme_get('pricing.plan3_features', 'Everything in Pro|Unlimited storage|Custom integrations|Dedicated manager|SLA guarantee|SSO & audit logs');
$plan3BtnText = theme_get('pricing.plan3_btn_text', 'Contact Sales');
$plan3BtnLink = theme_get('pricing.plan3_btn_link', '/contact');
$plan3Featured = theme_get('pricing.plan3_featured', '');
?>
<section class="fs-pricing fs-pricing--columns-3" id="pricing">
  <div class="container">
    <div class="fs-pricing-header" data-animate="fade-up">
      <?php if ($pricingBadge): ?><span class="fs-pricing-badge" data-ts="pricing.badge"><?= esc($pricingBadge) ?></span><?php endif; ?>
      <h2 class="fs-pricing-title" data-ts="pricing.title"><?= esc($pricingTitle) ?></h2>
      <p class="fs-pricing-subtitle" data-ts="pricing.subtitle"><?= esc($pricingSubtitle) ?></p>
    </div>
    <div class="fs-pricing-grid" data-animate="fade-up">
      <?php foreach ([
        ['name'=>$plan1Name,'price'=>$plan1Price,'period'=>$plan1Period,'desc'=>$plan1Desc,'features'=>$plan1Features,'btn'=>$plan1BtnText,'link'=>$plan1BtnLink,'featured'=>$plan1Featured,'n'=>'1'],
        ['name'=>$plan2Name,'price'=>$plan2Price,'period'=>$plan2Period,'desc'=>$plan2Desc,'features'=>$plan2Features,'btn'=>$plan2BtnText,'link'=>$plan2BtnLink,'featured'=>$plan2Featured,'n'=>'2'],
        ['name'=>$plan3Name,'price'=>$plan3Price,'period'=>$plan3Period,'desc'=>$plan3Desc,'features'=>$plan3Features,'btn'=>$plan3BtnText,'link'=>$plan3BtnLink,'featured'=>$plan3Featured,'n'=>'3'],
      ] as $plan): ?>
      <div class="fs-pricing-plan<?= ($plan['featured'] === '1' || $plan['featured'] === 'true') ? ' fs-pricing-plan--featured' : '' ?>">
        <div class="fs-pricing-plan-header">
          <h3 class="fs-pricing-plan-name" data-ts="pricing.plan<?= $plan['n'] ?>_name"><?= esc($plan['name']) ?></h3>
          <p class="fs-pricing-plan-desc" data-ts="pricing.plan<?= $plan['n'] ?>_desc"><?= esc($plan['desc']) ?></p>
        </div>
        <div class="fs-pricing-plan-price">
          <span class="fs-pricing-amount" data-ts="pricing.plan<?= $plan['n'] ?>_price"><?= esc($plan['price']) ?></span>
          <span class="fs-pricing-period" data-ts="pricing.plan<?= $plan['n'] ?>_period"><?= esc($plan['period']) ?></span>
        </div>
        <ul class="fs-pricing-features">
          <?php foreach (explode('|', $plan['features']) as $feat): ?>
          <li><i class="fas fa-check"></i> <?= esc(trim($feat)) ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= esc($plan['link']) ?>" class="fs-btn <?= ($plan['featured'] === '1' || $plan['featured'] === 'true') ? 'fs-btn-primary' : 'fs-btn-outline-pricing' ?>" data-ts="pricing.plan<?= $plan['n'] ?>_btn_text" data-ts-href="pricing.plan<?= $plan['n'] ?>_btn_link"><?= esc($plan['btn']) ?></a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
