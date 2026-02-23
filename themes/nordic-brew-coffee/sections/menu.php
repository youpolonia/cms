<?php
$menuLabel = theme_get('menu.label', 'The Menu');
$menuTitle = theme_get('menu.title', 'Simple Pleasures');
$menuDesc = theme_get('menu.description', 'Carefully curated drinks and pastries. Nothing more, nothing less.');

$menuCat1Title = theme_get('menu.cat1_title', 'Pour-Over Bar');
$menuItem1Name = theme_get('menu.item1_name', 'Ethiopian Yirgacheffe');
$menuItem1Desc = theme_get('menu.item1_desc', 'Floral, citrus, tea-like');
$menuItem1Price = theme_get('menu.item1_price', '5.50');
$menuItem2Name = theme_get('menu.item2_name', 'Colombian Huila');
$menuItem2Desc = theme_get('menu.item2_desc', 'Caramel, red apple, balanced');
$menuItem2Price = theme_get('menu.item2_price', '5.00');
$menuItem3Name = theme_get('menu.item3_name', 'Guatemalan Antigua');
$menuItem3Desc = theme_get('menu.item3_desc', 'Chocolate, nuts, smoky');
$menuItem3Price = theme_get('menu.item3_price', '5.25');

$menuCat2Title = theme_get('menu.cat2_title', 'Espresso');
$menuItem4Name = theme_get('menu.item4_name', 'Espresso');
$menuItem4Desc = theme_get('menu.item4_desc', 'Double shot, house blend');
$menuItem4Price = theme_get('menu.item4_price', '3.50');
$menuItem5Name = theme_get('menu.item5_name', 'Flat White');
$menuItem5Desc = theme_get('menu.item5_desc', 'Velvety microfoam, double ristretto');
$menuItem5Price = theme_get('menu.item5_price', '4.50');
$menuItem6Name = theme_get('menu.item6_name', 'Cortado');
$menuItem6Desc = theme_get('menu.item6_desc', 'Equal parts espresso and steamed milk');
$menuItem6Price = theme_get('menu.item6_price', '4.00');

$menuCat3Title = theme_get('menu.cat3_title', 'Pastries');
$menuItem7Name = theme_get('menu.item7_name', 'Cardamom Bun');
$menuItem7Desc = theme_get('menu.item7_desc', 'Swedish classic, house-made');
$menuItem7Price = theme_get('menu.item7_price', '4.50');
$menuItem8Name = theme_get('menu.item8_name', 'Almond Croissant');
$menuItem8Desc = theme_get('menu.item8_desc', 'Frangipane, toasted almonds');
$menuItem8Price = theme_get('menu.item8_price', '5.00');
$menuItem9Name = theme_get('menu.item9_name', 'Rye Toast');
$menuItem9Desc = theme_get('menu.item9_desc', 'Butter, sea salt, honey');
$menuItem9Price = theme_get('menu.item9_price', '3.50');
?>
<section class="section menu-section" id="menu">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="menu.label"><?= esc($menuLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="menu.title"><?= esc($menuTitle) ?></h2>
            <p class="section-desc" data-ts="menu.description"><?= esc($menuDesc) ?></p>
        </div>
        <div class="menu-grid">
            <div class="menu-category" data-animate>
                <h3 class="menu-category-title" data-ts="menu.cat1_title"><?= esc($menuCat1Title) ?></h3>
                <div class="menu-items">
                    <div class="menu-item">
                        <div class="menu-item-header">
                            <span class="menu-item-name" data-ts="menu.item1_name"><?= esc($menuItem1Name) ?></span>
                            <span class="menu-item-dots"></span>
                            <span class="menu-item-price" data-ts="menu.item1_price">$<?= esc($menuItem1Price) ?></span>
                        </div>
                        <p class="menu-item-desc" data-ts="menu.item1_desc"><?= esc($menuItem1Desc) ?></p>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-header">
                            <span class="menu-item-name" data-ts="menu.item2_name"><?= esc($menuItem2Name) ?></span>
                            <span class="menu-item-dots"></span>
                            <span class="menu-item-price" data-ts="menu.item2_price">$<?= esc($menuItem2Price) ?></span>
                        </div>
                        <p class="menu-item-desc" data-ts="menu.item2_desc"><?= esc($menuItem2Desc) ?></p>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-header">
                            <span class="menu-item-name" data-ts="menu.item3_name"><?= esc($menuItem3Name) ?></span>
                            <span class="menu-item-dots"></span>
                            <span class="menu-item-price" data-ts="menu.item3_price">$<?= esc($menuItem3Price) ?></span>
                        </div>
                        <p class="menu-item-desc" data-ts="menu.item3_desc"><?= esc($menuItem3Desc) ?></p>
                    </div>
                </div>
            </div>
            <div class="menu-category" data-animate>
                <h3 class="menu-category-title" data-ts="menu.cat2_title"><?= esc($menuCat2Title) ?></h3>
                <div class="menu-items">
                    <div class="menu-item">
                        <div class="menu-item-header">
                            <span class="menu-item-name" data-ts="menu.item4_name"><?= esc($menuItem4Name) ?></span>
                            <span class="menu-item-dots"></span>
                            <span class="menu-item-price" data-ts="menu.item4_price">$<?= esc($menuItem4Price) ?></span>
                        </div>
                        <p class="menu-item-desc" data-ts="menu.item4_desc"><?= esc($menuItem4Desc) ?></p>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-header">
                            <span class="menu-item-name" data-ts="menu.item5_name"><?= esc($menuItem5Name) ?></span>
                            <span class="menu-item-dots"></span>
                            <span class="menu-item-price" data-ts="menu.item5_price">$<?= esc($menuItem5Price) ?></span>
                        </div>
                        <p class="menu-item-desc" data-ts="menu.item5_desc"><?= esc($menuItem5Desc) ?></p>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-header">
                            <span class="menu-item-name" data-ts="menu.item6_name"><?= esc($menuItem6Name) ?></span>
                            <span class="menu-item-dots"></span>
                            <span class="menu-item-price" data-ts="menu.item6_price">$<?= esc($menuItem6Price) ?></span>
                        </div>
                        <p class="menu-item-desc" data-ts="menu.item6_desc"><?= esc($menuItem6Desc) ?></p>
                    </div>
                </div>
            </div>
            <div class="menu-category" data-animate>
                <h3 class="menu-category-title" data-ts="menu.cat3_title"><?= esc($menuCat3Title) ?></h3>
                <div class="menu-items">
                    <div class="menu-item">
                        <div class="menu-item-header">
                            <span class="menu-item-name" data-ts="menu.item7_name"><?= esc($menuItem7Name) ?></span>
                            <span class="menu-item-dots"></span>
                            <span class="menu-item-price" data-ts="menu.item7_price">$<?= esc($menuItem7Price) ?></span>
                        </div>
                        <p class="menu-item-desc" data-ts="menu.item7_desc"><?= esc($menuItem7Desc) ?></p>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-header">
                            <span class="menu-item-name" data-ts="menu.item8_name"><?= esc($menuItem8Name) ?></span>
                            <span class="menu-item-dots"></span>
                            <span class="menu-item-price" data-ts="menu.item8_price">$<?= esc($menuItem8Price) ?></span>
                        </div>
                        <p class="menu-item-desc" data-ts="menu.item8_desc"><?= esc($menuItem8Desc) ?></p>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-header">
                            <span class="menu-item-name" data-ts="menu.item9_name"><?= esc($menuItem9Name) ?></span>
                            <span class="menu-item-dots"></span>
                            <span class="menu-item-price" data-ts="menu.item9_price">$<?= esc($menuItem9Price) ?></span>
                        </div>
                        <p class="menu-item-desc" data-ts="menu.item9_desc"><?= esc($menuItem9Desc) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
