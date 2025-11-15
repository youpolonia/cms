-- GDPR Retention Stored Procedures
-- Assumes PostgreSQL syntax

-- =============================================================================
-- Helper Function: GetRetentionPolicy
-- Description: Retrieves the retention period in days for a given content type.
-- Arguments:
--   content_type_param TEXT: The type of content (e.g., 'article', 'user_comment', 'audit_log')
-- Returns:
--   INTEGER: Retention period in days, or NULL if no policy found.
-- =============================================================================
CREATE OR REPLACE FUNCTION GetRetentionPolicy(content_type_param TEXT)
RETURNS INTEGER AS $$
BEGIN
    -- This is a simplified example. In a real system, this might query a dedicated
    -- 'retention_policies' table:
    -- CREATE TABLE retention_policies (
    --   id SERIAL PRIMARY KEY,
    --   content_type TEXT UNIQUE NOT NULL,
    --   retention_days INTEGER NOT NULL,
    --   description TEXT
    -- );
    CASE content_type_param
        WHEN 'article' THEN
            RETURN 365 * 5; -- 5 years
        WHEN 'user_comment' THEN
            RETURN 365 * 2; -- 2 years
        WHEN 'user_activity_log' THEN
            RETURN 180;     -- 6 months
        WHEN 'system_audit_log' THEN
            RETURN 365 * 7; -- 7 years for critical audit logs
        ELSE
            RETURN NULL;    -- Default: no specific retention, handle carefully
    END CASE;
END;
$$ LANGUAGE plpgsql IMMUTABLE;

-- =============================================================================
-- Stored Procedure: PseudonymizeExpiredContent
-- Description: Identifies and pseudonymizes PII in content that has expired
--              based on its retention policy.
-- Arguments: None
-- Assumes:
--   - A table 'content_items' with (id, content_type, pii_field_1, pii_field_2, created_at, last_modified_at)
--   - An 'audit_log' table (event_type, entity_id, entity_type, details, performed_by, performed_at)
-- =============================================================================
CREATE OR REPLACE PROCEDURE PseudonymizeExpiredContent()
LANGUAGE plpgsql AS $$
DECLARE
    item RECORD;
    retention_days INTEGER;
    pseudonymized_value TEXT := '[PSEUDONYMIZED]';
BEGIN
    FOR item IN
        SELECT id, content_type, created_at -- Add PII fields here
        FROM content_items -- Replace with your actual content table
        -- Add more tables if PII is spread across multiple tables (e.g., users, profiles)
    LOOP
        retention_days := GetRetentionPolicy(item.content_type);

        IF retention_days IS NOT NULL AND (item.created_at + (retention_days || ' days')::INTERVAL) < CURRENT_TIMESTAMP THEN
            -- Example: Pseudonymize fields in 'content_items'
            -- UPDATE content_items
            -- SET
            --     pii_field_1 = pseudonymized_value || '_' || item.id, -- e.g., for email
            --     pii_field_2 = 'User ' || substr(md5(random()::text), 1, 8) -- e.g., for username
            -- WHERE id = item.id;

            -- Example: Pseudonymize user data if content_type is 'user_profile'
            -- IF item.content_type = 'user_profile' THEN
            --    UPDATE users -- Assuming 'users' table and 'item.id' refers to user_id
            --    SET email = 'user_' || item.id || '@example.com',
            --        full_name = 'Pseudonymized User ' || item.id,
            --        address = NULL, -- Or some generic placeholder
            --        phone_number = NULL
            --    WHERE id = item.id; -- Or appropriate foreign key
            -- END IF;

            RAISE NOTICE 'Pseudonymizing content_item ID % of type %', item.id, item.content_type;

            -- Log the pseudonymization event
            INSERT INTO audit_log (event_type, entity_id, entity_type, details, performed_by, performed_at)
            VALUES ('PSEUDONYMIZATION', item.id, item.content_type, 'Content automatically pseudonymized due to retention policy.', 'SYSTEM_GDPR_PROC', CURRENT_TIMESTAMP);

            -- IMPORTANT: Adapt the UPDATE statement above to your specific schema.
            -- Identify all PII fields and apply appropriate masking techniques.
            -- Techniques: hashing (with salt for non-reversible), nullifying, generalizing, randomizing.
        END IF;
    END LOOP;

    COMMIT; -- Commit changes if successful
EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        RAISE WARNING 'Error in PseudonymizeExpiredContent: %', SQLERRM;
        -- Optionally, log this error to a dedicated error log table.
END;
$$;

-- =============================================================================
-- Stored Procedure: PurgeObsoleteVersions
-- Description: Purges old versions of content items, keeping a specified number
--              of recent versions, while preserving an audit trail.
-- Arguments:
--   content_id_param INT: The ID of the main content item.
--   versions_to_keep_param INT: The number of recent versions to retain.
-- Assumes:
--   - A 'content_versions' table (id, main_content_id, version_number, content_data, created_at)
--   - An 'audit_log' table (as described above)
-- =============================================================================
CREATE OR REPLACE PROCEDURE PurgeObsoleteVersions(
    content_id_param INT,
    versions_to_keep_param INT DEFAULT 3 -- Default to keeping 3 recent versions
)
LANGUAGE plpgsql AS $$
DECLARE
    version_record RECORD;
    versions_count INTEGER;
    main_content_type TEXT;
BEGIN
    -- Check if the main content item exists and get its type for logging
    SELECT content_type INTO main_content_type FROM content_items WHERE id = content_id_param;
    IF NOT FOUND THEN
        RAISE WARNING 'Main content item ID % not found. Skipping purge.', content_id_param;
        RETURN;
    END IF;

    -- Count current versions
    SELECT count(*) INTO versions_count FROM content_versions WHERE main_content_id = content_id_param;

    IF versions_count <= versions_to_keep_param THEN
        RAISE NOTICE 'Content item ID % has % versions, which is not more than % to keep. No purge needed.',
                     content_id_param, versions_count, versions_to_keep_param;
        RETURN;
    END IF;

    -- Identify and delete obsolete versions, keeping the newest ones
    FOR version_record IN
        DELETE FROM content_versions
        WHERE id IN (
            SELECT id
            FROM content_versions
            WHERE main_content_id = content_id_param
            ORDER BY created_at DESC, version_number DESC -- Or your versioning scheme
            OFFSET versions_to_keep_param
        )
        RETURNING id, version_number, created_at -- Return info for logging
    LOOP
        RAISE NOTICE 'Purging version ID % (version_number: %) for content_item ID %',
                     version_record.id, version_record.version_number, content_id_param;

        -- Log the purge event
        INSERT INTO audit_log (event_type, entity_id, entity_type, details, performed_by, performed_at)
        VALUES (
            'VERSION_PURGE',
            version_record.id, -- The ID of the version record itself
            main_content_type || '_version', -- e.g., 'article_version'
            'Version ' || version_record.version_number || ' for content_item ' || content_id_param || ' purged. Kept ' || versions_to_keep_param || ' versions.',
            'SYSTEM_GDPR_PROC',
            CURRENT_TIMESTAMP
        );
    END LOOP;

    COMMIT;
EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        RAISE WARNING 'Error in PurgeObsoleteVersions for content_id %: %', content_id_param, SQLERRM;
END;
$$;

-- =============================================================================
-- Stored Procedure: ApplyRetentionPolicies
-- Description: Iterates through content and applies retention policies,
--              triggering pseudonymization or marking for deletion/archival.
-- Arguments: None
-- Assumes:
--   - 'content_items' table with (id, content_type, created_at, status)
--   - 'audit_log' table
-- =============================================================================
CREATE OR REPLACE PROCEDURE ApplyRetentionPolicies()
LANGUAGE plpgsql AS $$
DECLARE
    item RECORD;
    retention_days INTEGER;
    action_taken TEXT;
BEGIN
    FOR item IN
        SELECT id, content_type, created_at, status -- Assuming a 'status' field
        FROM content_items
        -- WHERE status != 'ARCHIVED' AND status != 'LEGAL_HOLD' -- Example: Exclude certain statuses
    LOOP
        retention_days := GetRetentionPolicy(item.content_type);
        action_taken := 'NO_ACTION';

        IF retention_days IS NOT NULL THEN
            IF (item.created_at + (retention_days || ' days')::INTERVAL) < CURRENT_TIMESTAMP THEN
                -- Policy: Pseudonymize first, then consider further actions
                -- This example calls PseudonymizeExpiredContent which iterates all content.
                -- A more targeted call could be:
                -- CALL PseudonymizeSpecificContent(item.id); -- (if such a procedure existed)
                -- For this example, we'll assume PseudonymizeExpiredContent handles it,
                -- or we could update status here.

                -- Example: If content type is 'temporary_file', delete it directly after retention
                IF item.content_type = 'temporary_file' THEN
                    -- DELETE FROM content_items WHERE id = item.id;
                    -- action_taken := 'DELETED';
                    RAISE NOTICE 'Content item ID % (type: %) marked for deletion (simulated).', item.id, item.content_type;
                    action_taken := 'MARKED_FOR_DELETION'; -- Simulate marking
                ELSE
                    -- For other types, rely on PseudonymizeExpiredContent to handle PII.
                    -- We might update a status here to 'PENDING_REVIEW_AFTER_RETENTION'
                    -- UPDATE content_items SET status = 'PENDING_PSEUDONYMIZATION' WHERE id = item.id;
                    RAISE NOTICE 'Content item ID % (type: %) is past retention. Pseudonymization should apply.', item.id, item.content_type;
                    action_taken := 'PENDING_PSEUDONYMIZATION';
                END IF;

                -- Log the action determined by retention policy
                INSERT INTO audit_log (event_type, entity_id, entity_type, details, performed_by, performed_at)
                VALUES (
                    'RETENTION_POLICY_APPLIED',
                    item.id,
                    item.content_type,
                    'Retention policy triggered: ' || action_taken || '. Retention period: ' || retention_days || ' days.',
                    'SYSTEM_GDPR_PROC',
                    CURRENT_TIMESTAMP
                );
            ELSE
                action_taken := 'WITHIN_RETENTION';
            END IF;
        ELSE
            action_taken := 'NO_POLICY_DEFINED';
            RAISE NOTICE 'No retention policy defined for content_type: % (ID: %)', item.content_type, item.id;
        END IF;

        -- If this procedure is part of a larger batch job, consider committing periodically
        -- or at the end. For simplicity, commit is handled by individual procedures called.
    END LOOP;

    -- Call PseudonymizeExpiredContent to process items marked or identified
    -- This is a broad call; in a more refined system, ApplyRetentionPolicies might
    -- queue items for PseudonymizeExpiredContent to process in batches.
    CALL PseudonymizeExpiredContent();

    -- Example: Call PurgeObsoleteVersions for relevant content types
    -- This would typically be more selective or based on specific triggers/schedules.
    -- FOR item IN SELECT id FROM content_items WHERE content_type = 'article' LOOP
    --    CALL PurgeObsoleteVersions(item.id, 5); -- Keep 5 versions for articles
    -- END LOOP;

    RAISE NOTICE 'ApplyRetentionPolicies completed.';
    -- No explicit COMMIT/ROLLBACK here as it's an orchestrator.
    -- Called procedures should handle their own transactions.
EXCEPTION
    WHEN OTHERS THEN
        RAISE WARNING 'Error in ApplyRetentionPolicies: %', SQLERRM;
END;
$$;

-- Example of an audit_log table structure (adjust as needed):
/*
CREATE TABLE audit_log (
    log_id SERIAL PRIMARY KEY,
    event_type VARCHAR(100) NOT NULL, -- e.g., 'PSEUDONYMIZATION', 'PURGE', 'RETENTION_APPLIED'
    entity_id VARCHAR(255),           -- ID of the entity affected (can be INT or TEXT based on your IDs)
    entity_type VARCHAR(100),         -- Type of the entity, e.g., 'user', 'content_item', 'content_version'
    details TEXT,                     -- Detailed description of the event
    performed_by VARCHAR(100) DEFAULT 'SYSTEM', -- User or system process performing the action
    performed_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),           -- Optional: IP address if user-initiated
    status VARCHAR(50) DEFAULT 'SUCCESS', -- e.g., 'SUCCESS', 'FAILURE'
    error_message TEXT                -- Optional: Error message if status is 'FAILURE'
);

CREATE INDEX idx_audit_log_event_type ON audit_log(event_type);
CREATE INDEX idx_audit_log_entity ON audit_log(entity_id, entity_type);
CREATE INDEX idx_audit_log_performed_at ON audit_log(performed_at);
*/

-- Example of content_items table (adjust as needed):
/*
CREATE TABLE content_items (
    id SERIAL PRIMARY KEY,
    content_type TEXT NOT NULL, -- e.g., 'article', 'user_comment', 'user_profile'
    -- PII fields (examples, adapt to your needs)
    title TEXT, -- May or may not be PII depending on context
    body TEXT,  -- May contain PII
    author_id INTEGER, -- Foreign key to a users table
    -- Other PII fields specific to content types
    -- e.g., if content_type is 'user_profile', it might have email, address directly or linked
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    last_modified_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'ACTIVE' -- e.g., 'ACTIVE', 'ARCHIVED', 'PENDING_DELETION', 'LEGAL_HOLD'
);
*/

-- Example of content_versions table (adjust as needed):
/*
CREATE TABLE content_versions (
    id SERIAL PRIMARY KEY,
    main_content_id INTEGER NOT NULL REFERENCES content_items(id) ON DELETE CASCADE,
    version_number INTEGER NOT NULL,
    content_data JSONB, -- Or TEXT, depending on how you store versioned content
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(100), -- User or system process that created the version
    reason_for_change TEXT,
    UNIQUE (main_content_id, version_number)
);
CREATE INDEX idx_content_versions_main_id ON content_versions(main_content_id);
CREATE INDEX idx_content_versions_created_at ON content_versions(created_at);
*/