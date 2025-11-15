-- Migration: Create AI Prompts Table
-- Date: 2025-04-27
-- Description: Creates table for storing AI prompt templates

START TRANSACTION;

CREATE TABLE IF NOT EXISTS ai_prompts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    prompt TEXT NOT NULL,
    description TEXT NULL,
    model VARCHAR(255) NOT NULL DEFAULT 'gpt-3.5-turbo',
    parameters JSON NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;