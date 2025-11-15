<?php

class PageContent extends Content implements ContentTypeInterface {
    protected $fillable = [
        'title',
        'slug',
        'body',
        'status',
        'author_id',
        'show_in_nav',
        'parent_id'
    ];

    public static function getTypeName(): string {
        return 'page';
    }

    public static function getFieldDefinitions(): array {
        return array_merge(parent::getFieldDefinitions(), [
            'show_in_nav' => [
                'type' => 'boolean',
                'required' => false,
                'default' => false
            ],
            'parent_id' => [
                'type' => 'integer',
                'required' => false
            ]
        ]);
    }
}
