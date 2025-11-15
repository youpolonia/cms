<?php
// Core system roles
class Roles {
    const ADMIN = 'admin';
    const EDITOR = 'editor';
    const AUTHOR = 'author';
    const VIEWER = 'viewer';
    const SENIOR_EDITOR = 'senior_editor';
    
    // Worker roles
    const WORKER = 'worker';
    
    // Get all valid roles
    public static function all(): array {
        return [
            self::ADMIN,
            self::EDITOR,
            self::AUTHOR,
            self::VIEWER,
            self::SENIOR_EDITOR,
            self::WORKER
        ];
    }
}
