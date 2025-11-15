<?php

abstract class Content implements ContentTypeInterface {
    protected $fillable = [
        'title',
        'slug',
        'body',
        'status',
        'author_id',
        'tenant_id'
    ];

    public static function getFieldDefinitions(): array {
        return [
            'title' => [
                'type' => 'string',
                'required' => true,
                'maxLength' => 255
            ],
            'slug' => [
                'type' => 'string',
                'required' => true,
                'pattern' => '/^[a-z0-9-]+$/'
            ],
            'body' => [
                'type' => 'text',
                'required' => true
            ],
            'status' => [
                'type' => 'string',
                'required' => true,
                'enum' => ['draft', 'published', 'archived']
            ],
            'author_id' => [
                'type' => 'integer',
                'required' => true
            ],
            'tenant_id' => [
                'type' => 'integer',
                'required' => true
            ]
        ];
    }
}
