<?php
/**
 * Client model for CMS client management system
 * 
 * @package CMS
 * @subpackage Models
 */

declare(strict_types=1);

namespace CMS\Models;

use CMS\Includes\Database\Connection;

class Client
{
    protected Connection $db;
    protected ClientActivity $activityTracker;

    public function __construct(Connection $connection)
    {
        $this->db = $connection;
        $this->activityTracker = new ClientActivity();
    }

    /**
     * Create a new client record
     */
    public function create(array $data): bool
    {
        $this->validateClientData($data);

        $sql = "INSERT INTO clients (
            name,
            email,
            phone,
            address,
            status,
            created_at,
            updated_at
        ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['name'],
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['status'] ?? 'active'
            ]);

            if ($result) {
                $clientId = $this->db->lastInsertId();
                $userId = $_SESSION['user_id'] ?? null;
                $this->activityTracker->logActivity(
                    $clientId,
                    'client_create',
                    $data,
                    $userId
                );
            }

            return $result;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to create client: " . $e->getMessage());
        }
    }

    /**
     * Get client by ID
     */
    public function get(int $clientId): ?array
    {
        $sql = "SELECT * FROM clients WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$clientId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to get client: " . $e->getMessage());
        }
    }

    /**
     * Get all clients
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM clients ORDER BY name";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to get clients: " . $e->getMessage());
        }
    }

    /**
     * Update client data
     */
    public function update(int $clientId, array $data): bool
    {
        $this->validateClientData($data, false);

        // Get current client data before update
        $currentData = $this->get($clientId);
        if (!$currentData) {
            throw new \RuntimeException("Client not found");
        }

        $sql = "UPDATE clients SET
            name = ?,
            email = ?,
            phone = ?,
            address = ?,
            status = ?,
            updated_at = NOW()
        WHERE id = ?";

        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['name'],
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['status'] ?? 'active',
                $clientId
            ]);

            if ($result) {
                // Find changed fields
                $changes = [];
                foreach ($data as $key => $value) {
                    if (array_key_exists($key, $currentData) && $currentData[$key] != $value) {
                        $changes[$key] = [
                            'old' => $currentData[$key],
                            'new' => $value
                        ];
                    }
                }

                if (!empty($changes)) {
                    $userId = $_SESSION['user_id'] ?? null;
                    $this->activityTracker->logActivity(
                        $clientId,
                        'client_update',
                        $changes,
                        $userId
                    );
                }
            }

            return $result;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to update client: " . $e->getMessage());
        }
    }

    /**
     * Delete client record
     */
    public function delete(int $clientId): bool
    {
        // Get client data before deletion
        $clientData = $this->get($clientId);
        if (!$clientData) {
            throw new \RuntimeException("Client not found");
        }

        $sql = "DELETE FROM clients WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$clientId]);

            if ($result) {
                $userId = $_SESSION['user_id'] ?? null;
                $this->activityTracker->logActivity(
                    $clientId,
                    'client_delete',
                    ['name' => $clientData['name']],
                    $userId
                );
            }

            return $result;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to delete client: " . $e->getMessage());
        }
    }

    /**
     * Validate client data
     */
    /**
     * Search clients by name, email, phone or address
     */
    public function search(string $term): array
    {
        $sql = "SELECT * FROM clients
                WHERE name LIKE :term
                OR email LIKE :term
                OR phone LIKE :term
                OR address LIKE :term
                ORDER BY name";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['term' => "%$term%"]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Search failed: " . $e->getMessage());
        }
    }

    protected function validateClientData(array $data, bool $requireName = true): void
    {
        if ($requireName && empty($data['name'])) {
            throw new \InvalidArgumentException("Client name is required");
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format");
        }

        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive', 'pending'])) {
            throw new \InvalidArgumentException("Invalid status value");
        }
    }
}
