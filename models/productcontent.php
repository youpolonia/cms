<?php

class ProductContent extends Content implements ContentTypeInterface {
    protected $fillable = [
        'title',
        'slug',
        'body',
        'status',
        'author_id',
        'price',
        'sku',
        'in_stock'
    ];

    public static function getTypeName(): string {
        return 'product';
    }

    public static function getFieldDefinitions(): array {
        return array_merge(parent::getFieldDefinitions(), [
            'price' => [
                'type' => 'float',
                'required' => true,
                'min' => 0
            ],
            'sku' => [
                'type' => 'string',
                'required' => true,
                'pattern' => '/^[A-Z0-9-]+$/'
            ],
            'in_stock' => [
                'type' => 'boolean',
                'required' => true,
                'default' => true
            ]
        ]);
    }
}
