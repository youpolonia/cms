<?php
namespace Includes\Database;

class SchemaVerifier {
    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function verifyTableStructure(string $table, array $expectedColumns): array {
        $results = [];
        
        // Check table exists
        $stmt = $this->pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() === 0) {
            return ['error' => "Table $table does not exist"];
        }

        // Get actual columns
        $stmt = $this->pdo->query("DESCRIBE $table");
        $actualColumns = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Compare columns
        foreach ($expectedColumns as $column => $expected) {
            $found = false;
            foreach ($actualColumns as $actual) {
                if ($actual['Field'] === $column) {
                    $found = true;
                    if ($actual['Type'] !== $expected['type']) {
                        $results['mismatches'][$column]['type'] = [
                            'expected' => $expected['type'],
                            'actual' => $actual['Type']
                        ];
                    }
                    if ($actual['Null'] !== $expected['nullable']) {
                        $results['mismatches'][$column]['nullable'] = [
                            'expected' => $expected['nullable'],
                            'actual' => $actual['Null']
                        ];
                    }
                    break;
                }
            }
            if (!$found) {
                $results['missing_columns'][] = $column;
            }
        }

        return $results;
    }

    public function verifyForeignKey(string $table, string $column, string $refTable, string $refColumn): bool {
        $stmt = $this->pdo->query("
            SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = '$table'
            AND COLUMN_NAME = '$column'
            AND REFERENCED_TABLE_NAME = '$refTable'
            AND REFERENCED_COLUMN_NAME = '$refColumn'
        ");
        return $stmt->rowCount() > 0;
    }
}
