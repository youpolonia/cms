<?php

namespace Includes\Database;

interface MigrationInterface {
    /**
     * Applies the migration.
     *
     * @return void
     */
    public function apply(): void;

    /**
     * Reverts the migration.
     *
     * @return void
     */
    public function revert(): void;
}
