<?php

class AuditManagerTask
{
    public static function run()
    {
        $path = __DIR__.'/../../logs/audit_manager.log';

        if (file_exists($path) && filesize($path) >= 1048576) {
            $rot = $path.'.1';
            if (file_exists($rot)) @unlink($rot);
            @rename($path, $rot);
        }

        $line = gmdate('Y-m-d\TH:i:s\Z').' AuditManagerTask executed'."\n";
        file_put_contents($path, $line, FILE_APPEND | LOCK_EX);

        return true;
    }
}
