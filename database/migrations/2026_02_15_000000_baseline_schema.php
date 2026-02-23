<?php
/**
 * Migration: Baseline schema snapshot
 * 
 * This migration represents the existing schema as of 2026-02-15.
 * Running it is a no-op — it just marks the baseline as "executed".
 * 
 * All future schema changes should be in subsequent migration files.
 */

return new class {
    public string $name = '2026_02_15_000000_baseline_schema';

    public function up(\PDO $pdo): void
    {
        // Baseline — all 64 tables already exist.
        // This migration is a marker only.
    }

    public function down(\PDO $pdo): void
    {
        // Cannot roll back baseline
        throw new \RuntimeException('Cannot rollback baseline migration');
    }
};
