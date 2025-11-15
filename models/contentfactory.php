<?php

class ContentFactory {
    public static function create(string $type, array $attributes = []): Content {
        $className = ucfirst($type) . 'Content';
        
        if (!class_exists($className)) {
            throw new InvalidArgumentException("Content type $type does not exist");
        }

        $content = new $className();
        
        foreach ($attributes as $key => $value) {
            if (in_array($key, $content->fillable)) {
                $content->$key = $value;
            }
        }

        return $content;
    }

    public static function getAvailableTypes(): array {
        return [
            'article' => 'Article',
            'page' => 'Page',
            'product' => 'Product'
        ];
    }
}
