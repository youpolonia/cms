<?php
if (!class_exists('SystemAlert', false)) {
    class SystemAlert {
        public static function info(string $msg): void { error_log('[INFO] '.$msg); }
        public static function warn(string $msg): void { error_log('[WARN] '.$msg); }
        public static function error(string $msg): void { error_log('[ERROR] '.$msg); }
    }
}
