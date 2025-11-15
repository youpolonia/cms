<?php

interface ContentTypeInterface {
    public static function getTypeName(): string;
    public static function getFieldDefinitions(): array;
}
