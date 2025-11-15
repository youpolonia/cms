<?php
/**
 * Debug Version of VersionMetadata with proper namespace
 *
 * This file demonstrates how the VersionMetadata.php file should be modified
 * to work with the TransactionVerificationTest.php file.
 */

if (!defined('DEV_MODE')) {
    http_response_code(500);
    echo 'Configuration error';
    return;
}
if (!DEV_MODE) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Forbidden in production";
    return;
}

namespace Includes\Versioning;

// Use absolute path for includes
require_once __DIR__.'/core/database.php';

class VersionMetadata {
    private $db;
    private $inTransaction = false;

    public function __construct($db = null) {
        // Accept database dependency injection with fallback
        $this->db = $db ?: new \Database();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): void
    {
        $this->db->beginTransaction();
        $this->inTransaction = true;
    }

    /**
     * Commit transaction
     */
    public function commit(): void
    {
        $this->db->commit();
        $this->inTransaction = false;
    }

    /**
     * Rollback transaction
     */
    public function rollback(): void
    {
        $this->db->rollback();
        $this->inTransaction = false;
    }

    /**
     * Check if in transaction
     */
    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    // Other methods would go here...
}
