<?php
require_once __DIR__ . '/../../core/AIClient.php';
require_once __DIR__ . '/../../includes/usermanager.php';

class UserAISearch {
    /**
     * Perform semantic search on users
     * @param string $query Search query
     * @return array Matched users
     */
    public static function semanticSearch(string $query): array {
        // Sanitize input
        $query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
        
        // Get all users with their roles/permissions
        $users = UserManager::getAllUsersWithDetails();
        
        // Format user data for AI prompt
        $userData = array_map(function($user) {
            return [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'roles' => $user['roles'],
                'permissions' => $user['permissions']
            ];
        }, $users);

        // Create prompt
        $prompt = "Find users matching this query: '$query'\n\n";
        $prompt .= "Available users:\n" . json_encode($userData, JSON_PRETTY_PRINT);
        $prompt .= "\n\nReturn only user IDs that match, as JSON array";

        // Get AI response
        $response = AIClient::ask($prompt);
        $matchedIds = json_decode($response, true) ?? [];

        // Filter and return matched users
        return array_filter($users, function($user) use ($matchedIds) {
            return in_array($user['id'], $matchedIds);
        });
    }
}
