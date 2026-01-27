<?php

function seo_get_settings(): array
{
    $path = CMS_ROOT . '/config/seo_settings.json';

    if (!file_exists($path)) {
        return [
            'site_name'         => '',
            'meta_description'  => '',
            'meta_keywords'     => '',
            'robots_index'      => 'index',
            'robots_follow'     => 'follow',
            'canonical_base_url'=> '',
            'og_image_url'      => ''
        ];
    }

    $json = @file_get_contents($path);
    if ($json === false) {
        return [
            'site_name'         => '',
            'meta_description'  => '',
            'meta_keywords'     => '',
            'robots_index'      => 'index',
            'robots_follow'     => 'follow',
            'canonical_base_url'=> '',
            'og_image_url'      => ''
        ];
    }

    $data = json_decode($json, true);
    if (!is_array($data)) {
        return [
            'site_name'         => '',
            'meta_description'  => '',
            'meta_keywords'     => '',
            'robots_index'      => 'index',
            'robots_follow'     => 'follow',
            'canonical_base_url'=> '',
            'og_image_url'      => ''
        ];
    }

    return array_merge([
        'site_name'         => '',
        'meta_description'  => '',
        'meta_keywords'     => '',
        'robots_index'      => 'index',
        'robots_follow'     => 'follow',
        'canonical_base_url'=> '',
        'og_image_url'      => ''
    ], $data);
}
