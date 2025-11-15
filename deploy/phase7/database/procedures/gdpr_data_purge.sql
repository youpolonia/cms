DELIMITER //
CREATE PROCEDURE purge_orphaned_user_data()
BEGIN
    DECLARE cutoff_date DATE;
    SET cutoff_date = DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY);
    
    START TRANSACTION;
    DELETE FROM user_sessions
    WHERE last_activity < cutoff_date;
    
    DELETE FROM audit_logs
    WHERE event_date < cutoff_date
    AND user_id NOT IN (SELECT id FROM users WHERE status = 'active');
    COMMIT;
END //
DELIMITER ;