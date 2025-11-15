<?php
/**
 * Shift model for CMS scheduling system
 *
 * @package CMS
 * @subpackage Models
 */

// Prevent direct access
defined('CMS_ROOT') or die('No direct script access allowed');

class Shift
{

    /**
     * Create a new shift
     * 
     * @param array $data Shift data
     * @return bool True on success
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO shifts (worker_id, start_time, end_time, status, location, notes)
                VALUES (:worker_id, :start_time, :end_time, :status, :location, :notes)";
        
        return Connection::execute($sql, [
            ':worker_id' => $data['worker_id'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':status' => $data['status'] ?? 'scheduled',
            ':location' => $data['location'] ?? null,
            ':notes' => $data['notes'] ?? null
        ]);
    }

    /**
     * Get shift by ID
     * 
     * @param int $shiftId Shift ID
     * @return array|null Shift data or null if not found
     */
    public function get(int $shiftId): ?array
    {
        $results = Connection::query("SELECT * FROM shifts WHERE shift_id = :shift_id LIMIT 1", [':shift_id' => $shiftId]);
        return $results[0] ?? null;
    }

    /**
     * Get all shifts
     *
     * @return array List of shifts
     */
    public function getAll(): array
    {
        return Connection::query("SELECT * FROM shifts");
    }

    /**
     * Update shift
     * 
     * @param int $shiftId Shift ID
     * @param array $data Shift data
     * @return bool True on success
     */
    public function update(int $shiftId, array $data): bool
    {
        $sql = "UPDATE shifts SET
                worker_id = :worker_id,
                start_time = :start_time,
                end_time = :end_time,
                status = :status,
                location = :location,
                notes = :notes
                WHERE shift_id = :shift_id";
        
        return Connection::execute($sql, [
            ':worker_id' => $data['worker_id'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':status' => $data['status'] ?? 'scheduled',
            ':location' => $data['location'] ?? null,
            ':notes' => $data['notes'] ?? null,
            ':shift_id' => $shiftId
        ]);
    }

    /**
     * Delete shift
     * 
     * @param int $shiftId Shift ID
     * @return bool True on success
     */
    public function delete(int $shiftId): bool
    {
        return Connection::execute(
            "DELETE FROM shifts WHERE shift_id = :shift_id",
            [':shift_id' => $shiftId]
        );
    }

    /**
     * Get shifts by worker ID
     * 
     * @param string $workerId Worker ID
     * @return array List of shifts
     */
    public function getShiftsByWorker(string $workerId): array
    {
        return Connection::query(
            "SELECT * FROM shifts WHERE worker_id = :worker_id",
            [':worker_id' => $workerId]
        );
    }

    /**
     * Get shifts within date range
     *
     * @param string $start Start date (Y-m-d)
     * @param string $end End date (Y-m-d)
     * @return array List of shifts
     */
    public static function getShiftsByDateRange(string $start, string $end): array
    {
        return Connection::query(
            "SELECT * FROM shifts
             WHERE DATE(start_time) BETWEEN :start AND :end
             ORDER BY start_time",
            [':start' => $start, ':end' => $end]
        );
    }

    /**
     * Check for shift conflicts
     * 
     * @param string $workerId Worker ID
     * @param string $start Start datetime
     * @param string $end End datetime
     * @param int|null $excludeShiftId Shift ID to exclude (for updates)
     * @return bool True if conflict exists
     */
    public function checkShiftConflicts(
        string $workerId,
        string $start,
        string $end,
        ?int $excludeShiftId = null
    ): bool {
        $sql = "SELECT COUNT(*) as conflict_count FROM shifts
                WHERE worker_id = :worker_id
                AND (
                    (start_time < :end AND end_time > :start)
                    OR (start_time >= :start AND start_time < :end)
                )
                AND status != 'cancelled'";
        
        $params = [
            ':worker_id' => $workerId,
            ':start' => $start,
            ':end' => $end
        ];
        
        if ($excludeShiftId) {
            $sql .= " AND shift_id != :exclude_shift_id";
            $params[':exclude_shift_id'] = $excludeShiftId;
        }

        $results = Connection::query($sql, $params);
        return isset($results[0]) ? (int)$results[0]['conflict_count'] > 0 : false;
    }
}
