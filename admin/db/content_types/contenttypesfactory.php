<?php
/**
 * Content Types Factory
 */
class ContentTypesFactory {
    protected static $typesInstance;
    protected static $fieldsInstance;

    public static function getTypesInstance($db) {
        if (!self::$typesInstance) {
            self::$typesInstance = new ContentTypes($db);
        }
        return self::$typesInstance;
    }

    public static function getFieldsInstance($db) {
        if (!self::$fieldsInstance) {
            self::$fieldsInstance = new ContentFields($db);
        }
        return self::$fieldsInstance;
    }

    public static function clearInstances() {
        self::$typesInstance = null;
        self::$fieldsInstance = null;
    }
}
