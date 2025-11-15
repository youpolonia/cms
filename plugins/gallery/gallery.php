<?php

require_once __DIR__ . '/galleryblock.php';

class GalleryPlugin {
    public static function init() {
        GalleryBlock::register();
        
        add_action('admin_enqueue_scripts', function() {
            wp_enqueue_script(
                'gallery-block', 
                plugins_url('gallery/dist/gallery-block.js', __FILE__),
                ['vue', 'plugin-blocks'],
                filemtime(plugin_dir_path(__FILE__) . 'dist/gallery-block.js')
            );
        });
    }
    
    public static function getBlockDefinition() {
        return [
            'type' => 'gallery',
            'label' => 'Image Gallery',
            'category' => 'media',
            'icon' => 'images',
            'component' => 'GalleryBlock',
            'configSchema' => [
                'images' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'number'],
                            'url' => ['type' => 'string', 'format' => 'uri']
                        ]
                    ]
                ]
            ],
            'defaultConfig' => [
                'images' => []
            ]
        ];
    }
}

return GalleryPlugin::class;
