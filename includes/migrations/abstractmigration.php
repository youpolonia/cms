<?php
// Minimal, framework-free base. No namespaces, no Composer, no autoloaders.
// ONE public method only. No up()/down(), no Schema, no Illuminate, no Artisan.

class AbstractMigration
{
    /**
     * Execute the migration logic using the provided PDO connection.
     * Implementations should return true on success, false on failure.
     * In this step, this method will NOT be called.
     */
    public function execute(PDO $db): bool
    {
        return true;
    }
}
