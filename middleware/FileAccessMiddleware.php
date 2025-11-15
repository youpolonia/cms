<?php
declare(strict_types=1);

namespace CMS\Middleware;

/**
 * Middleware to prevent direct file access
 * 
 * This middleware checks if the requested URL is trying to access files directly
 * and blocks access to sensitive file types and directories.
 */
class FileAccessMiddleware
{
    /**
     * List of file extensions that should be protected from direct access
     */
    private array $protectedExtensions = [
        'php', 'inc', 'config', 'env', 'log', 'sql', 'json', 'lock', 'gitignore',
        'yml', 'yaml', 'xml', 'md', 'sh', 'bat', 'ini'
    ];

    /**
     * List of directories that should be protected from direct access
     */
    private array $protectedDirectories = [
        'includes', 'core', 'config', 'database', 'middleware', 'migrations',
        'models', 'services', 'controllers', 'phases', 'plans'
    ];

    /**
     * Handle the request
     * 
     * @param array $request The request data
     * @return array|bool The request data if allowed, false if blocked
     */
    public function handle(array $request)
    {
        $uri = $request['uri'] ?? '';
        
        // Skip for API endpoints and assets
        if (str_starts_with($uri, '/api/') || 
            str_starts_with($uri, '/assets/') || 
            str_starts_with($uri, '/public/')) {
            return $request;
        }
        
        // Check for direct file access attempts
        if ($this->isDirectFileAccess($uri)) {
            return $this->blockAccess();
        }
        
        return $request;
    }
    
    /**
     * Check if the URI is attempting direct file access
     * 
     * @param string $uri The request URI
     * @return bool True if direct file access is detected
     */
    private function isDirectFileAccess(string $uri): bool
    {
        // Check for file extensions
        $extension = pathinfo($uri, PATHINFO_EXTENSION);
        if (in_array(strtolower($extension), $this->protectedExtensions)) {
            return true;
        }
        
        // Check for protected directories
        foreach ($this->protectedDirectories as $dir) {
            if (preg_match('~^/' . $dir . '/~i', $uri)) {
                return true;
            }
        }
        
        // Check for path traversal attempts
        if (strpos($uri, '../') !== false || strpos($uri, '..\\') !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Block access and return 403 Forbidden response
     * 
     * @return bool Always returns false to indicate blocked access
     */
    private function blockAccess(): bool
    {
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: text/html; charset=UTF-8');
        echo '
<h1>403 Forbidden</h1>';
        echo '
<p>Direct access to this resource is not allowed.</p>';
        exit;
    }
}
