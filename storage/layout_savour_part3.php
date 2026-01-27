<?php
// ============================================================================
// PAGE 4: GALLERY
// ============================================================================
$galleryPage = [
    'title' => 'Gallery',
    'slug' => 'gallery',
    'is_homepage' => false,
    'status' => 'draft',
    'content' => [
        'sections' => [
            // PAGE HEADER
            [
                'id' => 'section_gallery_hero',
                'name' => 'Page Header',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'background_image' => 'https://images.pexels.com/photos/941861/pexels-photo-941861.jpeg?auto=compress&cs=tinysrgb&w=1920',
                    'background_overlay' => 'rgba(15,15,15,0.8)',
                    'padding_top' => '160px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'row_gallery_hero_1',
                        'columns' => [
                            [
                                'id' => 'col_gallery_hero_1',
                                'width' => '100%',
                                'modules' => [
                                    [
                                        'id' => 'mod_gallery_hero_tagline',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'VISUAL JOURNEY'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '12px',
                                            'letter_spacing' => '4px',
                                            'text_color' => '#d4af37',
                                            'margin_bottom' => '20px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_gallery_hero_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Our Gallery',
                                            'level' => 'h1'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '64px',
                                            'font_weight' => '400',
                                            'text_color' => '#ffffff',
                                            'font_family' => 'Playfair Display, serif'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // GALLERY GRID
            [
                'id' => 'section_gallery_grid',
                'name' => 'Gallery Grid',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'padding_top' => '80px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'row_gallery_1',
                        'design' => [
                            'max_width' => '1400px',
                            'margin' => '0 auto'
                        ],
                        'columns' => [
                            [
                                'id' => 'col_gallery_1',
                                'width' => '100%',
                                'modules' => [
                                    [
                                        'id' => 'mod_gallery_grid',
                                        'type' => 'gallery',
                                        'content' => [
                                            'images' => [
                                                [
                                                    'src' => 'https://images.pexels.com/photos/262978/pexels-photo-262978.jpeg?auto=compress&cs=tinysrgb&w=600',
                                                    'alt' => 'Restaurant Interior'
                                                ],
                                                [
                                                    'src' => 'https://images.pexels.com/photos/1579739/pexels-photo-1579739.jpeg?auto=compress&cs=tinysrgb&w=600',
                                                    'alt' => 'Fine Dining Setting'
                                                ],
                                                [
                                                    'src' => 'https://images.pexels.com/photos/3535383/pexels-photo-3535383.jpeg?auto=compress&cs=tinysrgb&w=600',
                                                    'alt' => 'Signature Dish'
                                                ],
                                                [
                                                    'src' => 'https://images.pexels.com/photos/699953/pexels-photo-699953.jpeg?auto=compress&cs=tinysrgb&w=600',
                                                    'alt' => 'Seafood'
                                                ],
                                                [
                                                    'src' => 'https://images.pexels.com/photos/1435904/pexels-photo-1435904.jpeg?auto=compress&cs=tinysrgb&w=600',
                                                    'alt' => 'Dessert'
                                                ],
                                                [
                                                    'src' => 'https://images.pexels.com/photos/260922/pexels-photo-260922.jpeg?auto=compress&cs=tinysrgb&w=600',
                                                    'alt' => 'Dining Room'
                                                ],
                                                [
                                                    'src' => 'https://images.pexels.com/photos/2403391/pexels-photo-2403391.jpeg?auto=compress&cs=tinysrgb&w=600',
                                                    'alt' => 'Chef at Work'
                                                ],
                                                [
                                                    'src' => 'https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg?auto=compress&cs=tinysrgb&w=600',
                                                    'alt' => 'Fresh Ingredients'
                                                ],
                                                [
                                                    'src' => 'https://images.pexels.com/photos/3338497/pexels-photo-3338497.jpeg?auto=compress&cs=tinysrgb&w=600',
                                                    'alt' => 'Kitchen'
                                                ]
                                            ],
                                            'columns' => 3
                                        ],
                                        'design' => [
                                            'gap' => '20px',
                                            'border_radius' => '0'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];

// ============================================================================
// PAGE 5: CONTACT
// ============================================================================
$contactPage = [
    'title' => 'Contact',
    'slug' => 'contact',
    'is_homepage' => false,
    'status' => 'draft',
    'content' => [
        'sections' => [
            // PAGE HEADER
            [
                'id' => 'section_contact_hero',
                'name' => 'Page Header',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'background_image' => 'https://images.pexels.com/photos/67468/pexels-photo-67468.jpeg?auto=compress&cs=tinysrgb&w=1920',
                    'background_overlay' => 'rgba(15,15,15,0.85)',
                    'padding_top' => '160px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'row_contact_hero_1',
                        'columns' => [
                            [
                                'id' => 'col_contact_hero_1',
                                'width' => '100%',
                                'modules' => [
                                    [
                                        'id' => 'mod_contact_hero_tagline',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => 'GET IN TOUCH'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '12px',
                                            'letter_spacing' => '4px',
                                            'text_color' => '#d4af37',
                                            'margin_bottom' => '20px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_contact_hero_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Contact & Reservations',
                                            'level' => 'h1'
                                        ],
                                        'design' => [
                                            'text_align' => 'center',
                                            'font_size' => '56px',
                                            'font_weight' => '400',
                                            'text_color' => '#ffffff',
                                            'font_family' => 'Playfair Display, serif'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // CONTACT INFO + FORM
            [
                'id' => 'section_contact_main',
                'name' => 'Contact Information',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'padding_top' => '100px',
                    'padding_bottom' => '100px'
                ],
                'rows' => [
                    [
                        'id' => 'row_contact_main_1',
                        'design' => [
                            'max_width' => '1200px',
                            'margin' => '0 auto',
                            'gap' => '80px'
                        ],
                        'columns' => [
                            // Contact Info
                            [
                                'id' => 'col_contact_info',
                                'width' => '40%',
                                'modules' => [
                                    [
                                        'id' => 'mod_contact_info_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Visit Us',
                                            'level' => 'h2'
                                        ],
                                        'design' => [
                                            'font_size' => '32px',
                                            'font_weight' => '400',
                                            'text_color' => '#ffffff',
                                            'margin_bottom' => '32px',
                                            'font_family' => 'Playfair Display, serif'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_contact_address',
                                        'type' => 'blurb',
                                        'content' => [
                                            'icon' => 'fas fa-map-marker-alt',
                                            'use_image' => false,
                                            'title' => 'Address',
                                            'text' => '1234 Fine Dining Avenue\nManhattan, NY 10001',
                                            'url' => ''
                                        ],
                                        'design' => [
                                            'alignment' => 'left',
                                            'layout' => 'left',
                                            'icon_size' => '24px',
                                            'icon_color' => '#d4af37',
                                            'title_font_size' => '16px',
                                            'title_color' => '#d4af37',
                                            'text_font_size' => '15px',
                                            'text_color' => '#999999',
                                            'margin_bottom' => '28px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_contact_phone',
                                        'type' => 'blurb',
                                        'content' => [
                                            'icon' => 'fas fa-phone',
                                            'use_image' => false,
                                            'title' => 'Phone',
                                            'text' => '+1 (212) 555-0123',
                                            'url' => ''
                                        ],
                                        'design' => [
                                            'alignment' => 'left',
                                            'layout' => 'left',
                                            'icon_size' => '24px',
                                            'icon_color' => '#d4af37',
                                            'title_font_size' => '16px',
                                            'title_color' => '#d4af37',
                                            'text_font_size' => '15px',
                                            'text_color' => '#999999',
                                            'margin_bottom' => '28px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_contact_email',
                                        'type' => 'blurb',
                                        'content' => [
                                            'icon' => 'fas fa-envelope',
                                            'use_image' => false,
                                            'title' => 'Email',
                                            'text' => 'reservations@savour.com',
                                            'url' => ''
                                        ],
                                        'design' => [
                                            'alignment' => 'left',
                                            'layout' => 'left',
                                            'icon_size' => '24px',
                                            'icon_color' => '#d4af37',
                                            'title_font_size' => '16px',
                                            'title_color' => '#d4af37',
                                            'text_font_size' => '15px',
                                            'text_color' => '#999999',
                                            'margin_bottom' => '40px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_contact_hours_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Opening Hours',
                                            'level' => 'h3'
                                        ],
                                        'design' => [
                                            'font_size' => '20px',
                                            'font_weight' => '400',
                                            'text_color' => '#d4af37',
                                            'margin_bottom' => '20px'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_contact_hours',
                                        'type' => 'text',
                                        'content' => [
                                            'text' => "Monday – Thursday: 5:30 PM – 10:00 PM\nFriday – Saturday: 5:30 PM – 11:00 PM\nSunday: 5:00 PM – 9:00 PM"
                                        ],
                                        'design' => [
                                            'font_size' => '15px',
                                            'line_height' => '2',
                                            'text_color' => '#999999'
                                        ]
                                    ]
                                ]
                            ],
                            // Reservation Form
                            [
                                'id' => 'col_contact_form',
                                'width' => '60%',
                                'design' => [
                                    'background_color' => '#1a1a1a',
                                    'padding' => '50px'
                                ],
                                'modules' => [
                                    [
                                        'id' => 'mod_contact_form_heading',
                                        'type' => 'heading',
                                        'content' => [
                                            'text' => 'Make a Reservation',
                                            'level' => 'h2'
                                        ],
                                        'design' => [
                                            'font_size' => '32px',
                                            'font_weight' => '400',
                                            'text_color' => '#ffffff',
                                            'margin_bottom' => '32px',
                                            'font_family' => 'Playfair Display, serif'
                                        ]
                                    ],
                                    [
                                        'id' => 'mod_contact_form',
                                        'type' => 'form',
                                        'content' => [
                                            'title' => '',
                                            'fields' => [
                                                ['type' => 'text', 'label' => 'Full Name', 'placeholder' => 'Your name', 'required' => true],
                                                ['type' => 'email', 'label' => 'Email', 'placeholder' => 'your@email.com', 'required' => true],
                                                ['type' => 'text', 'label' => 'Phone', 'placeholder' => 'Phone number', 'required' => true],
                                                ['type' => 'date', 'label' => 'Preferred Date', 'placeholder' => '', 'required' => true],
                                                ['type' => 'select', 'label' => 'Party Size', 'placeholder' => 'Number of guests', 'required' => true, 'options' => ['2 Guests', '3 Guests', '4 Guests', '5 Guests', '6+ Guests']],
                                                ['type' => 'textarea', 'label' => 'Special Requests', 'placeholder' => 'Dietary restrictions, occasion, seating preferences...', 'required' => false]
                                            ],
                                            'submit_text' => 'REQUEST RESERVATION',
                                            'success_message' => 'Thank you! We will confirm your reservation shortly.',
                                            'recipient_email' => ''
                                        ],
                                        'design' => [
                                            'style' => 'stacked',
                                            'button_style' => 'primary',
                                            'button_full_width' => true,
                                            'label_position' => 'top',
                                            'input_background' => '#0f0f0f',
                                            'input_border' => '1px solid #333',
                                            'input_text_color' => '#ffffff',
                                            'label_color' => '#999999',
                                            'button_background' => '#d4af37',
                                            'button_text_color' => '#0f0f0f'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // MAP
            [
                'id' => 'section_contact_map',
                'name' => 'Location Map',
                'design' => [
                    'background_color' => '#0f0f0f',
                    'padding_top' => '0',
                    'padding_bottom' => '0'
                ],
                'rows' => [
                    [
                        'id' => 'row_contact_map_1',
                        'columns' => [
                            [
                                'id' => 'col_contact_map_1',
                                'width' => '100%',
                                'modules' => [
                                    [
                                        'id' => 'mod_contact_map',
                                        'type' => 'map',
                                        'content' => [
                                            'address' => '1234 Fine Dining Avenue, Manhattan, NY 10001',
                                            'lat' => 40.7128,
                                            'lng' => -74.0060,
                                            'zoom' => 15
                                        ],
                                        'design' => [
                                            'height' => '400px'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
