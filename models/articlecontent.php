<?php

class ArticleContent extends Content implements ContentTypeInterface {
    protected $fillable = [
        'title',
        'slug', 
        'body',
        'status',
        'author_id',
        'excerpt',
        'featured_image',
        'categories'
    ];

    public static function getTypeName(): string {
        return 'article';
    }

    public static function getFieldDefinitions(): array {
        return array_merge(parent::getFieldDefinitions(), [
            'excerpt' => [
                'type' => 'text',
                'required' => false,
                'maxLength' => 500
            ],
            'featured_image' => [
                'type' => 'string',
                'required' => false
            ],
            'categories' => [
                'type' => 'array',
                'required' => false
            ]
        ]);
    }
}
