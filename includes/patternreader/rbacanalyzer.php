<?php
// RBAC Analysis with Token Limit Management
class RbacAnalyzer {
    const CORE_TABLES = ['roles', 'permissions'];
    const ASSIGNMENT_TABLES = ['role_permission', 'user_role'];
    
    public static function analyzeRbacMigrations(array $migrations): array {
        $results = [];
        $chunks = chunk_migrations($migrations);
        
        foreach ($chunks as $chunk) {
            $summary = generate_schema_summary($chunk);
            $results[] = [
                'chunk' => array_column($chunk, 'name'),
                'summary' => $summary,
                'tokens' => estimate_token_usage($summary)
            ];
            
            if (estimate_token_usage($results) > 50000) {
                file_put_contents('memory-bank/rbac_analysis_part.json', json_encode($results));
                $results = [];
            }
        }
        
        return $results;
    }
    
    public static function processCoreTablesFirst(array $migrations): array {
        $core = array_filter($migrations, fn($m) => in_array(extract_table_name($m), self::CORE_TABLES));
        $assignments = array_filter($migrations, fn($m) => in_array(extract_table_name($m), self::ASSIGNMENT_TABLES));
        
        return array_merge(
            self::analyzeRbacMigrations($core),
            self::analyzeRbacMigrations($assignments)
        );
    }
}
