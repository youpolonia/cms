<?php
// Example NO-OP migration. Does not touch the database.
require_once __DIR__ . '/abstractmigration.php';

class Migration_0000_example_noop extends AbstractMigration
{
    public function execute(PDO $db): bool
    {
        // Dry example: nothing happens here.
        $note = 'This is a no-op migration used to validate the scaffold.';
        return true;
    }
}
