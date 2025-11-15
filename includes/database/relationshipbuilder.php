<?php

namespace Includes\Database;

use Includes\Database\Connection;

class RelationshipBuilder
{
    protected $connection;
    protected $type;
    protected $localTable;
    protected $relatedTable;
    protected $foreignKey;
    protected $localKey;

    public function __construct(
        string $type,
        string $localTable,
        string $relatedTable,
        ?string $foreignKey = null,
        ?string $localKey = null
    ) {
        $this->type = $type;
        $this->localTable = $localTable;
        $this->relatedTable = $relatedTable;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    public function getResults(array $localIds): array
    {
        switch ($this->type) {
            case 'hasOne':
                return $this->getHasOneResults($localIds);
            case 'hasMany':
                return $this->getHasManyResults($localIds);
            case 'belongsTo':
                return $this->getBelongsToResults($localIds);
            default:
                throw new \InvalidArgumentException("Invalid relationship type: {$this->type}");
        }
    }

    protected function getHasOneResults(array $localIds): array
    {
        $foreignKey = $this->foreignKey ?? $this->localTable . '_id';
        $localKey = $this->localKey ?? 'id';

        $query = "SELECT * FROM {$this->relatedTable} 
                 WHERE {$foreignKey} IN (" . implode(',', array_fill(0, count($localIds), '?')) . ")";

        $stmt = Connection::getInstance()->getPdo()->prepare($query);
        $stmt->execute($localIds);
        
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[$row[$foreignKey]] = $row;
        }
        
        return $results;
    }

    protected function getHasManyResults(array $localIds): array
    {
        $foreignKey = $this->foreignKey ?? $this->localTable . '_id';
        $localKey = $this->localKey ?? 'id';

        $query = "SELECT * FROM {$this->relatedTable} 
                 WHERE {$foreignKey} IN (" . implode(',', array_fill(0, count($localIds), '?')) . ")";

        $stmt = Connection::getInstance()->getPdo()->prepare($query);
        $stmt->execute($localIds);
        
        $results = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $results[$row[$foreignKey]][] = $row;
        }
        
        return $results;
    }

    protected function getBelongsToResults(array $localIds): array
    {
        $foreignKey = $this->foreignKey ?? $this->relatedTable . '_id';
        $localKey = $this->localKey ?? 'id';

        $query = "SELECT * FROM {$this->relatedTable} 
                 WHERE {$localKey} IN (" . implode(',', array_fill(0, count($localIds), '?')) . ")";

        $stmt = Connection::getInstance()->getPdo()->prepare($query);
        $stmt->execute($localIds);
        
        $results = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $results[$row[$localKey]] = $row;
        }
        
        return $results;
    }
}
