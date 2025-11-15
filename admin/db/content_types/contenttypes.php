<?php
class ContentTypes {
    protected $connection;

    public function __construct(PDO $connection) {
        $this->connection = $connection;
    }

    public function getByMachineNames(array $machineNames): array {
        $placeholders = implode(',', array_fill(0, count($machineNames), '?'));
        $query = "SELECT * FROM content_types WHERE machine_name IN ($placeholders)";
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute($machineNames);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
