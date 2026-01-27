<?php
/**
 * Golden Plate Layout - Part 8: Contact Page
 */

$contactPage = [
    'title' => 'Contact',
    'slug' => 'contact',
    'is_homepage' => false,
    'status' => 'draft',
    'content' => [
        'sections' => [
            // PAGE HERO
            [
                'id' => 'section_contact_hero',
                'name' => 'Page Hero',
                'design' => ['background_color' => 'var(--color-background)', 'background_image' => img('gp-interior.jpg'), 'background_overlay' => 'rgba(10,10,10,0.85)', 'padding_top' => '150px', 'padding_bottom' => '80px'],
                'rows' => [[
                    'id' => 'row_contact_hero',
                    'columns' => [[
                        'id' => 'col_contact_hero', 'width' => '100%',
                        'modules' => [
                            ['id' => 'mod_contact_hero_title', 'type' => 'heading', 'content' => ['text' => 'Contact Us', 'level' => 'h1'], 'design' => ['text_align' => 'center', 'font_size' => '52px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '15px']],
                            ['id' => 'mod_contact_hero_bread', 'type' => 'text', 'content' => ['text' => 'Home / Contact'], 'design' => ['text_align' => 'center', 'font_size' => '14px', 'text_color' => 'var(--color-text-muted)']]
                        ]
                    ]]
                ]]
            ],
            // CONTACT SECTION
            [
                'id' => 'section_contact_main',
                'name' => 'Contact Section',
                'design' => ['background_color' => 'var(--color-background)', 'padding_top' => '100px', 'padding_bottom' => '100px'],
                'rows' => [[
                    'id' => 'row_contact',
                    'design' => ['max_width' => '1200px', 'margin' => '0 auto', 'gap' => '60px'],
                    'columns' => [
                        [
                            'id' => 'col_contact_info', 'width' => '50%',
                            'modules' => [
                                ['id' => 'mod_ci_tag', 'type' => 'text', 'content' => ['text' => 'GET IN TOUCH'], 'design' => ['font_size' => '12px', 'letter_spacing' => '4px', 'text_color' => 'var(--color-accent)', 'margin_bottom' => '15px']],
                                ['id' => 'mod_ci_title', 'type' => 'heading', 'content' => ['text' => 'Visit Us Today', 'level' => 'h2'], 'design' => ['font_size' => '38px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '30px']],
                                ['id' => 'mod_ci_loc', 'type' => 'blurb', 'content' => ['icon' => 'fas fa-map-marker-alt', 'title' => 'Location', 'text' => '123 Gourmet Avenue, Manhattan\nNew York, NY 10001'], 'design' => ['layout' => 'left', 'icon_size' => '20px', 'icon_color' => 'var(--color-accent)', 'title_color' => 'var(--color-text)', 'text_color' => 'var(--color-text-muted)', 'margin_bottom' => '25px']],
                                ['id' => 'mod_ci_phone', 'type' => 'blurb', 'content' => ['icon' => 'fas fa-phone', 'title' => 'Reservations', 'text' => '+1 (212) 555-GOLD'], 'design' => ['layout' => 'left', 'icon_size' => '20px', 'icon_color' => 'var(--color-accent)', 'title_color' => 'var(--color-text)', 'text_color' => 'var(--color-text-muted)', 'margin_bottom' => '25px']],
                                ['id' => 'mod_ci_email', 'type' => 'blurb', 'content' => ['icon' => 'fas fa-envelope', 'title' => 'Email', 'text' => 'reservations@goldenplate.com'], 'design' => ['layout' => 'left', 'icon_size' => '20px', 'icon_color' => 'var(--color-accent)', 'title_color' => 'var(--color-text)', 'text_color' => 'var(--color-text-muted)', 'margin_bottom' => '30px']],
                                ['id' => 'mod_ci_hours_title', 'type' => 'heading', 'content' => ['text' => 'Opening Hours', 'level' => 'h4'], 'design' => ['font_size' => '18px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '15px']],
                                ['id' => 'mod_ci_hours', 'type' => 'text', 'content' => ['text' => '<div style="border-bottom:1px solid #333;padding:10px 0;display:flex;justify-content:space-between"><span style="color:#e8e8e8">Monday - Thursday</span><span style="color:#d4af37">5:00 PM - 10:00 PM</span></div><div style="border-bottom:1px solid #333;padding:10px 0;display:flex;justify-content:space-between"><span style="color:#e8e8e8">Friday - Saturday</span><span style="color:#d4af37">5:00 PM - 11:00 PM</span></div><div style="padding:10px 0;display:flex;justify-content:space-between"><span style="color:#e8e8e8">Sunday</span><span style="color:#d4af37">4:00 PM - 9:00 PM</span></div>'], 'design' => []]
                            ]
                        ],
                        [
                            'id' => 'col_contact_form', 'width' => '50%',
                            'design' => ['background_color' => 'var(--color-surface)', 'padding' => '45px', 'border' => '1px solid rgba(212,175,55,0.2)'],
                            'modules' => [
                                ['id' => 'mod_form_title', 'type' => 'heading', 'content' => ['text' => 'Make a Reservation', 'level' => 'h3'], 'design' => ['font_size' => '26px', 'text_color' => 'var(--color-text)', 'margin_bottom' => '25px']],
                                ['id' => 'mod_form_name', 'type' => 'text', 'content' => ['text' => '<input type="text" placeholder="Your Name" style="width:100%;background:#0a0a0a;border:1px solid #333;padding:14px 18px;color:#fff;font-size:14px;margin-bottom:18px">'], 'design' => []],
                                ['id' => 'mod_form_email', 'type' => 'text', 'content' => ['text' => '<input type="email" placeholder="Your Email" style="width:100%;background:#0a0a0a;border:1px solid #333;padding:14px 18px;color:#fff;font-size:14px;margin-bottom:18px">'], 'design' => []],
                                ['id' => 'mod_form_phone', 'type' => 'text', 'content' => ['text' => '<input type="tel" placeholder="Phone Number" style="width:100%;background:#0a0a0a;border:1px solid #333;padding:14px 18px;color:#fff;font-size:14px;margin-bottom:18px">'], 'design' => []],
                                ['id' => 'mod_form_guests', 'type' => 'text', 'content' => ['text' => '<input type="number" placeholder="Number of Guests" min="1" max="20" style="width:100%;background:#0a0a0a;border:1px solid #333;padding:14px 18px;color:#fff;font-size:14px;margin-bottom:18px">'], 'design' => []],
                                ['id' => 'mod_form_date', 'type' => 'text', 'content' => ['text' => '<input type="date" style="width:48%;background:#0a0a0a;border:1px solid #333;padding:14px 18px;color:#fff;font-size:14px;margin-bottom:18px;margin-right:4%"><input type="time" style="width:48%;background:#0a0a0a;border:1px solid #333;padding:14px 18px;color:#fff;font-size:14px;margin-bottom:18px">'], 'design' => []],
                                ['id' => 'mod_form_msg', 'type' => 'text', 'content' => ['text' => '<textarea placeholder="Special Requests or Dietary Requirements" style="width:100%;background:#0a0a0a;border:1px solid #333;padding:14px 18px;color:#fff;font-size:14px;margin-bottom:18px;min-height:100px;resize:vertical"></textarea>'], 'design' => []],
                                ['id' => 'mod_form_btn', 'type' => 'button', 'content' => ['text' => 'REQUEST RESERVATION', 'url' => '#'], 'design' => ['background_color' => 'var(--color-accent)', 'text_color' => 'var(--color-background)', 'padding' => '16px 40px', 'font_size' => '13px', 'letter_spacing' => '2px', 'width' => '100%']]
                            ]
                        ]
                    ]
                ]]
            ],
            // MAP PLACEHOLDER
            [
                'id' => 'section_contact_map',
                'name' => 'Map',
                'design' => ['background_color' => 'var(--color-surface)', 'padding_top' => '0', 'padding_bottom' => '0'],
                'rows' => [[
                    'id' => 'row_map',
                    'columns' => [[
                        'id' => 'col_map', 'width' => '100%',
                        'modules' => [[
                            'id' => 'mod_map',
                            'type' => 'map',
                            'content' => [
                                'address' => '123 Gourmet Avenue, Manhattan, New York, NY 10001',
                                'lat' => 40.7484,
                                'lng' => -73.9857,
                                'zoom' => 15
                            ],
                            'design' => ['height' => '400px']
                        ]]
                    ]]
                ]]
            ]
        ]
    ]
];
