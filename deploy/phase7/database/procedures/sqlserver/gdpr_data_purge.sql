CREATE PROCEDURE purge_orphaned_user_data
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @cutoff_date DATE = DATEADD(DAY, -30, GETDATE());
    
    BEGIN TRANSACTION;
    DELETE FROM user_sessions 
    WHERE last_activity < @cutoff_date;
    
    DELETE FROM audit_logs 
    WHERE event_date < @cutoff_date 
    AND user_id NOT IN (SELECT id FROM users WHERE status = 'active');
    COMMIT TRANSACTION;
END