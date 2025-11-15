-- GDPR Retention Stored Procedures
-- MSSQL (T-SQL) Syntax

-- =============================================================================
-- Helper Function: GetRetentionPolicy
-- Description: Retrieves the retention period in days for a given content type.
-- Arguments:
--   @content_type_param NVARCHAR(255): The type of content (e.g., 'article', 'user_comment', 'audit_log')
-- Returns:
--   INT: Retention period in days, or NULL if no policy found.
-- =============================================================================
CREATE OR ALTER FUNCTION dbo.GetRetentionPolicy(@content_type_param NVARCHAR(255))
RETURNS INT
AS
BEGIN
    DECLARE @retention_days INT;

    -- This is a simplified example. In a real system, this might query a dedicated
    -- 'retention_policies' table:
    -- CREATE TABLE retention_policies (
    --   id INT IDENTITY(1,1) PRIMARY KEY,
    --   content_type NVARCHAR(255) UNIQUE NOT NULL,
    --   retention_days INT NOT NULL,
    --   description NVARCHAR(MAX)
    -- );
    SELECT @retention_days = CASE @content_type_param
        WHEN 'article' THEN 365 * 5 -- 5 years
        WHEN 'user_comment' THEN 365 * 2 -- 2 years
        WHEN 'user_activity_log' THEN 180     -- 6 months
        WHEN 'system_audit_log' THEN 365 * 7 -- 7 years for critical audit logs
        ELSE NULL    -- Default: no specific retention, handle carefully
    END;

    RETURN @retention_days;
END;
GO
-- =============================================================================
-- Stored Procedure: PseudonymizeExpiredContent
-- Description: Identifies and pseudonymizes PII in content that has expired
--              based on its retention policy.
-- Arguments: None
-- Assumes:
--   - A table 'content_items' with (id, content_type, pii_field_1, pii_field_2, created_at, last_modified_at)
--   - An 'audit_log' table (event_type, entity_id, entity_type, details, performed_by, performed_at)
-- =============================================================================
CREATE OR ALTER PROCEDURE dbo.PseudonymizeExpiredContent
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @item_id INT;
    DECLARE @item_content_type NVARCHAR(255);
    DECLARE @item_created_at DATETIMEOFFSET;
    -- Declare variables for PII fields if you fetch them in the cursor
    -- e.g., DECLARE @pii_field_1_value NVARCHAR(MAX);

    DECLARE @retention_days INT;
    DECLARE @pseudonymized_value_prefix NVARCHAR(50) = '[PSEUDONYMIZED]';
    DECLARE @current_timestamp DATETIMEOFFSET = SYSDATETIMEOFFSET();
    DECLARE @error_message NVARCHAR(MAX);

    -- Declare cursor for items to check
    DECLARE item_cursor CURSOR FOR
        SELECT id, content_type, created_at -- Add PII fields here if needed for complex pseudonymization logic
        FROM dbo.content_items; -- Replace with your actual content table

    OPEN item_cursor;
    FETCH NEXT FROM item_cursor INTO @item_id, @item_content_type, @item_created_at;

    WHILE @@FETCH_STATUS = 0
    BEGIN
        BEGIN TRY
            SET @retention_days = dbo.GetRetentionPolicy(@item_content_type);

            IF @retention_days IS NOT NULL AND (DATEADD(day, @retention_days, @item_created_at) < @current_timestamp)
            BEGIN
                -- Example: Pseudonymize fields in 'content_items'
                -- This is a placeholder. Actual PII fields and tables need to be identified.
                -- UPDATE dbo.content_items
                -- SET
                --     pii_field_1 = @pseudonymized_value_prefix + '_' + CAST(@item_id AS NVARCHAR(10)), -- e.g., for email
                --     pii_field_2 = 'User_' + SUBSTRING(CONVERT(NVARCHAR(32), HASHBYTES('MD5', CAST(RAND() AS VARCHAR)), 2), 1, 8) -- e.g., for username
                -- WHERE id = @item_id;

                -- Example: Pseudonymize user data if content_type is 'user_profile'
                -- IF @item_content_type = 'user_profile'
                -- BEGIN
                --    UPDATE dbo.users -- Assuming 'users' table and '@item_id' refers to user_id
                --    SET email = 'user_' + CAST(@item_id AS NVARCHAR(10)) + '@example.com',
                --        full_name = 'Pseudonymized User ' + CAST(@item_id AS NVARCHAR(10)),
                --        address = NULL, -- Or some generic placeholder
                --        phone_number = NULL
                --    WHERE id = @item_id; -- Or appropriate foreign key
                -- END;

                PRINT 'Pseudonymizing content_item ID ' + CAST(@item_id AS NVARCHAR(10)) + ' of type ' + @item_content_type;

                -- Log the pseudonymization event
                INSERT INTO dbo.audit_log (event_type, entity_id, entity_type, details, performed_by, performed_at)
                VALUES ('PSEUDONYMIZATION', CAST(@item_id AS NVARCHAR(255)), @item_content_type, 'Content automatically pseudonymized due to retention policy.', 'SYSTEM_GDPR_PROC', @current_timestamp);

                -- IMPORTANT: Adapt the UPDATE statement above to your specific schema.
                -- Identify all PII fields and apply appropriate masking techniques.
            END;
        END TRY
        BEGIN CATCH
            SET @error_message = ERROR_MESSAGE();
            RAISERROR('Error processing item ID %d: %s', 10, 1, @item_id, @error_message) WITH NOWAIT;
            -- Optionally, log this error to a dedicated error log table.
        END CATCH;

        FETCH NEXT FROM item_cursor INTO @item_id, @item_content_type, @item_created_at;
    END;

    CLOSE item_cursor;
    DEALLOCATE item_cursor;

    -- No explicit COMMIT/ROLLBACK here as this procedure might be part of a larger transaction
    -- or called individually. If called individually, wrap the main loop in BEGIN TRAN / COMMIT / ROLLBACK.
    -- For simplicity and to match the original PostgreSQL procedure's structure (which had COMMIT inside loop's scope,
    -- which is unusual), this T-SQL version assumes external transaction management or auto-commit per statement.
    -- If explicit transaction for the whole procedure is needed:
    -- BEGIN TRANSACTION;
    -- ... cursor loop ...
    -- IF @@ERROR = 0 COMMIT TRANSACTION; ELSE ROLLBACK TRANSACTION;
END;
GO

-- Placeholder for the rest of the converted procedures
-- PseudonymizeExpiredContent will be converted next.
-- PurgeObsoleteVersions will follow.
-- ApplyRetentionPolicies will be last.

/*
Original PostgreSQL audit_log table structure for reference:
CREATE TABLE audit_log (
    log_id SERIAL PRIMARY KEY,
    event_type VARCHAR(100) NOT NULL,
    entity_id VARCHAR(255),
    entity_type VARCHAR(100),
    details TEXT,
    performed_by VARCHAR(100) DEFAULT 'SYSTEM',
    performed_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    status VARCHAR(50) DEFAULT 'SUCCESS',
    error_message TEXT
);

Equivalent MSSQL audit_log table structure:
CREATE TABLE audit_log (
    log_id INT IDENTITY(1,1) PRIMARY KEY,
    event_type NVARCHAR(100) NOT NULL,
    entity_id NVARCHAR(255),
    entity_type NVARCHAR(100),
    details NVARCHAR(MAX),
    performed_by NVARCHAR(100) DEFAULT 'SYSTEM',
    performed_at DATETIMEOFFSET DEFAULT SYSDATETIMEOFFSET(),
    ip_address VARCHAR(45),
    status NVARCHAR(50) DEFAULT 'SUCCESS',
    error_message NVARCHAR(MAX)
);

CREATE INDEX idx_audit_log_event_type ON audit_log(event_type);
CREATE INDEX idx_audit_log_entity ON audit_log(entity_id, entity_type);
CREATE INDEX idx_audit_log_performed_at ON audit_log(performed_at);
*/

/*
Original PostgreSQL content_items table structure for reference:
CREATE TABLE content_items (
    id SERIAL PRIMARY KEY,
    content_type TEXT NOT NULL,
    title TEXT,
    body TEXT,
    author_id INTEGER,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    last_modified_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'ACTIVE'
);

Equivalent MSSQL content_items table structure:
CREATE TABLE content_items (
    id INT IDENTITY(1,1) PRIMARY KEY,
    content_type NVARCHAR(MAX) NOT NULL,
    title NVARCHAR(MAX),
    body NVARCHAR(MAX),
    author_id INT, -- Foreign key to a users table
    created_at DATETIMEOFFSET DEFAULT SYSDATETIMEOFFSET(),
    last_modified_at DATETIMEOFFSET DEFAULT SYSDATETIMEOFFSET(),
    status VARCHAR(50) DEFAULT 'ACTIVE'
);
*/

/*
Original PostgreSQL content_versions table structure for reference:
CREATE TABLE content_versions (
    id SERIAL PRIMARY KEY,
    main_content_id INTEGER NOT NULL REFERENCES content_items(id) ON DELETE CASCADE,
    version_number INTEGER NOT NULL,
    content_data JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(100),
    reason_for_change TEXT,
    UNIQUE (main_content_id, version_number)
);

Equivalent MSSQL content_versions table structure:
CREATE TABLE content_versions (
    id INT IDENTITY(1,1) PRIMARY KEY,
    main_content_id INT NOT NULL FOREIGN KEY REFERENCES content_items(id) ON DELETE CASCADE,
    version_number INT NOT NULL,
    content_data NVARCHAR(MAX), -- For JSON, use NVARCHAR(MAX) and SQL Server JSON functions
    created_at DATETIMEOFFSET DEFAULT SYSDATETIMEOFFSET(),
    created_by VARCHAR(100),
    reason_for_change NVARCHAR(MAX),
    CONSTRAINT UK_content_versions_main_version UNIQUE (main_content_id, version_number)
);
CREATE INDEX idx_content_versions_main_id ON content_versions(main_content_id);
CREATE INDEX idx_content_versions_created_at ON content_versions(created_at);
*/
-- =============================================================================
-- Stored Procedure: PurgeObsoleteVersions
-- Description: Purges old versions of content items, keeping a specified number
--              of recent versions, while preserving an audit trail.
-- Arguments:
--   @content_id_param INT: The ID of the main content item.
--   @versions_to_keep_param INT: The number of recent versions to retain.
-- Assumes:
--   - A 'content_versions' table (id, main_content_id, version_number, content_data, created_at)
--   - An 'audit_log' table (as described above)
-- =============================================================================
CREATE OR ALTER PROCEDURE dbo.PurgeObsoleteVersions
    @content_id_param INT,
    @versions_to_keep_param INT = 3 -- Default to keeping 3 recent versions
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @version_id INT;
    DECLARE @version_number INT;
    DECLARE @version_created_at DATETIMEOFFSET;
    DECLARE @versions_count INT;
    DECLARE @main_content_type NVARCHAR(255);
    DECLARE @error_message NVARCHAR(MAX);
    DECLARE @current_timestamp DATETIMEOFFSET = SYSDATETIMEOFFSET();

    BEGIN TRY
        -- Check if the main content item exists and get its type for logging
        SELECT @main_content_type = content_type
        FROM dbo.content_items
        WHERE id = @content_id_param;

        IF @main_content_type IS NULL
        BEGIN
            PRINT 'Main content item ID ' + CAST(@content_id_param AS NVARCHAR(10)) + ' not found. Skipping purge.';
            RETURN;
        END;

        -- Count current versions
        SELECT @versions_count = COUNT(*)
        FROM dbo.content_versions
        WHERE main_content_id = @content_id_param;

        IF @versions_count &lt;= @versions_to_keep_param
        BEGIN
            PRINT 'Content item ID ' + CAST(@content_id_param AS NVARCHAR(10)) +
                  ' has ' + CAST(@versions_count AS NVARCHAR(10)) +
                  ' versions, which is not more than ' + CAST(@versions_to_keep_param AS NVARCHAR(10)) +
                  ' to keep. No purge needed.';
            RETURN;
        END;

        -- Identify and delete obsolete versions, keeping the newest ones
        -- Using a temporary table to store IDs to be deleted to avoid cursor complexities with DELETE OUTPUT
        DECLARE @versions_to_delete TABLE (id INT, version_number INT, created_at DATETIMEOFFSET);

        INSERT INTO @versions_to_delete (id, version_number, created_at)
        SELECT id, version_number, created_at
        FROM (
            SELECT id, version_number, created_at,
                   ROW_NUMBER() OVER (ORDER BY created_at DESC, version_number DESC) as rn
            FROM dbo.content_versions
            WHERE main_content_id = @content_id_param
        ) AS RankedVersions
        WHERE rn > @versions_to_keep_param;

        -- Declare cursor for versions to delete
        DECLARE version_cursor CURSOR FOR
            SELECT id, version_number, created_at FROM @versions_to_delete;

        OPEN version_cursor;
        FETCH NEXT FROM version_cursor INTO @version_id, @version_number, @version_created_at;

        WHILE @@FETCH_STATUS = 0
        BEGIN
            BEGIN TRANSACTION;

            DELETE FROM dbo.content_versions WHERE id = @version_id;

            PRINT 'Purging version ID ' + CAST(@version_id AS NVARCHAR(10)) +
                  ' (version_number: ' + CAST(@version_number AS NVARCHAR(10)) +
                  ') for content_item ID ' + CAST(@content_id_param AS NVARCHAR(10));

            -- Log the purge event
            INSERT INTO dbo.audit_log (event_type, entity_id, entity_type, details, performed_by, performed_at)
            VALUES (
                'VERSION_PURGE',
                CAST(@version_id AS NVARCHAR(255)), -- The ID of the version record itself
                @main_content_type + '_version', -- e.g., 'article_version'
                'Version ' + CAST(@version_number AS NVARCHAR(10)) + ' for content_item ' + CAST(@content_id_param AS NVARCHAR(10)) +
                ' purged. Kept ' + CAST(@versions_to_keep_param AS NVARCHAR(10)) + ' versions.',
                'SYSTEM_GDPR_PROC',
                @current_timestamp
            );
            COMMIT TRANSACTION;

            FETCH NEXT FROM version_cursor INTO @version_id, @version_number, @version_created_at;
        END;

        CLOSE version_cursor;
        DEALLOCATE version_cursor;

    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        SET @error_message = ERROR_MESSAGE();
        RAISERROR('Error in PurgeObsoleteVersions for content_id %d: %s', 10, 1, @content_id_param, @error_message) WITH NOWAIT;
        -- Optionally, log this error to a dedicated error log table.
    END CATCH;
END;
GO
-- =============================================================================
-- Stored Procedure: ApplyRetentionPolicies
-- Description: Iterates through content and applies retention policies,
--              triggering pseudonymization or marking for deletion/archival.
-- Arguments: None
-- Assumes:
--   - 'content_items' table with (id, content_type, created_at, status)
--   - 'audit_log' table
-- =============================================================================
CREATE OR ALTER PROCEDURE dbo.ApplyRetentionPolicies
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @item_id INT;
    DECLARE @item_content_type NVARCHAR(255);
    DECLARE @item_created_at DATETIMEOFFSET;
    DECLARE @item_status VARCHAR(50);

    DECLARE @retention_days INT;
    DECLARE @action_taken NVARCHAR(255);
    DECLARE @current_timestamp DATETIMEOFFSET = SYSDATETIMEOFFSET();
    DECLARE @error_message NVARCHAR(MAX);

    -- Declare cursor for items to check
    DECLARE item_cursor CURSOR FOR
        SELECT id, content_type, created_at, status -- Assuming a 'status' field
        FROM dbo.content_items
        -- WHERE status NOT IN ('ARCHIVED', 'LEGAL_HOLD'); -- Example: Exclude certain statuses

    OPEN item_cursor;
    FETCH NEXT FROM item_cursor INTO @item_id, @item_content_type, @item_created_at, @item_status;

    WHILE @@FETCH_STATUS = 0
    BEGIN
        BEGIN TRY
            SET @retention_days = dbo.GetRetentionPolicy(@item_content_type);
            SET @action_taken = 'NO_ACTION';

            IF @retention_days IS NOT NULL
            BEGIN
                IF (DATEADD(day, @retention_days, @item_created_at) < @current_timestamp)
                BEGIN
                    -- Policy: Pseudonymize first, then consider further actions
                    IF @item_content_type = 'temporary_file'
                    BEGIN
                        -- In a real scenario, you might delete or update status
                        -- DELETE FROM dbo.content_items WHERE id = @item_id;
                        -- SET @action_taken = 'DELETED';
                        PRINT 'Content item ID ' + CAST(@item_id AS NVARCHAR(10)) + ' (type: ' + @item_content_type + ') marked for deletion (simulated).';
                        SET @action_taken = 'MARKED_FOR_DELETION'; -- Simulate marking
                    END
                    ELSE
                    BEGIN
                        -- For other types, rely on PseudonymizeExpiredContent to handle PII.
                        -- Or update status here:
                        -- UPDATE dbo.content_items SET status = 'PENDING_PSEUDONYMIZATION' WHERE id = @item_id;
                        PRINT 'Content item ID ' + CAST(@item_id AS NVARCHAR(10)) + ' (type: ' + @item_content_type + ') is past retention. Pseudonymization should apply.';
                        SET @action_taken = 'PENDING_PSEUDONYMIZATION';
                    END;

                    -- Log the action determined by retention policy
                    INSERT INTO dbo.audit_log (event_type, entity_id, entity_type, details, performed_by, performed_at)
                    VALUES (
                        'RETENTION_POLICY_APPLIED',
                        CAST(@item_id AS NVARCHAR(255)),
                        @item_content_type,
                        'Retention policy triggered: ' + @action_taken + '. Retention period: ' + CAST(@retention_days AS NVARCHAR(10)) + ' days.',
                        'SYSTEM_GDPR_PROC',
                        @current_timestamp
                    );
                END
                ELSE
                BEGIN
                    SET @action_taken = 'WITHIN_RETENTION';
                END;
            END
            ELSE
            BEGIN
                SET @action_taken = 'NO_POLICY_DEFINED';
                PRINT 'No retention policy defined for content_type: ' + @item_content_type + ' (ID: ' + CAST(@item_id AS NVARCHAR(10)) + ')';
            END;
        END TRY
        BEGIN CATCH
            SET @error_message = ERROR_MESSAGE();
            RAISERROR('Error processing item ID %d for retention: %s', 10, 1, @item_id, @error_message) WITH NOWAIT;
        END CATCH;

        FETCH NEXT FROM item_cursor INTO @item_id, @item_content_type, @item_created_at, @item_status;
    END;

    CLOSE item_cursor;
    DEALLOCATE item_cursor;

    -- Call PseudonymizeExpiredContent to process items marked or identified
    -- This is a broad call; in a more refined system, ApplyRetentionPolicies might
    -- queue items for PseudonymizeExpiredContent to process in batches.
    EXEC dbo.PseudonymizeExpiredContent;

    -- Example: Call PurgeObsoleteVersions for relevant content types
    -- This would typically be more selective or based on specific triggers/schedules.
    -- DECLARE @article_id INT;
    -- DECLARE article_cursor CURSOR FOR SELECT id FROM dbo.content_items WHERE content_type = 'article';
    -- OPEN article_cursor;
    -- FETCH NEXT FROM article_cursor INTO @article_id;
    -- WHILE @@FETCH_STATUS = 0
    -- BEGIN
    --     EXEC dbo.PurgeObsoleteVersions @content_id_param = @article_id, @versions_to_keep_param = 5; -- Keep 5 versions for articles
    --     FETCH NEXT FROM article_cursor INTO @article_id;
    -- END;
    -- CLOSE article_cursor;
    -- DEALLOCATE article_cursor;

    PRINT 'ApplyRetentionPolicies completed.';
    -- No explicit COMMIT/ROLLBACK here as it's an orchestrator.
    -- Called procedures should handle their own transactions if necessary.
END;
GO