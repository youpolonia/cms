<?php
/**
 * FTP/SFTP Deployment Manager
 * Handles secure file transfers for deployment
 */
namespace CMS\Deployment;

class FTPManager {
    private $connection;
    private $host;
    private $username;
    private $password;
    private $port;
    private $timeout = 30;
    private $passive = true;
    private $ssl = false;
    private $rootPath = '';

    /**
     * Connect to FTP/SFTP server
     */
    public function connect(string $host, string $username, string $password, int $port = 21, bool $ssl = false): bool {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->ssl = $ssl;

        if ($ssl) {
            $this->connection = ftp_ssl_connect($host, $port, $this->timeout);
        } else {
            $this->connection = ftp_connect($host, $port, $this->timeout);
        }

        if (!$this->connection) {
            throw new \RuntimeException("Failed to connect to FTP server");
        }

        if (!ftp_login($this->connection, $username, $password)) {
            throw new \RuntimeException("FTP login failed");
        }

        ftp_pasv($this->connection, $this->passive);
        return true;
    }

    /**
     * Set root path for deployment
     */
    public function setRootPath(string $path): void {
        $this->rootPath = rtrim($path, '/') . '/';
    }

    /**
     * Upload file to server
     */
    public function uploadFile(string $localPath, string $remotePath): bool {
        $remotePath = $this->rootPath . ltrim($remotePath, '/');
        $remoteDir = dirname($remotePath);

        if (!$this->directoryExists($remoteDir)) {
            $this->createDirectory($remoteDir);
        }

        return ftp_put($this->connection, $remotePath, $localPath, FTP_BINARY);
    }

    /**
     * Check if directory exists on server
     */
    public function directoryExists(string $path): bool {
        $current = ftp_pwd($this->connection);
        
        if (@ftp_chdir($this->connection, $path)) {
            ftp_chdir($this->connection, $current);
            return true;
        }
        
        return false;
    }

    /**
     * Create directory on server
     */
    public function createDirectory(string $path): bool {
        $path = $this->rootPath . ltrim($path, '/');
        $parts = explode('/', $path);
        $currentPath = '';

        foreach ($parts as $part) {
            $currentPath .= '/' . $part;
            if (!$this->directoryExists($currentPath)) {
                if (!ftp_mkdir($this->connection, $currentPath)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Close connection
     */
    public function disconnect(): void {
        if ($this->connection) {
            ftp_close($this->connection);
            $this->connection = null;
        }
    }

    /**
     * Synchronize directory
     */
    public function syncDirectory(string $localDir, string $remoteDir): bool {
        $localDir = rtrim($localDir, '/') . '/';
        $remoteDir = $this->rootPath . ltrim($remoteDir, '/') . '/';

        if (!is_dir($localDir)) {
            throw new \InvalidArgumentException("Local directory does not exist");
        }

        $this->createDirectory($remoteDir);
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($localDir),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                $relativePath = substr($file->getPathname(), strlen($localDir));
                $this->createDirectory($remoteDir . $relativePath);
            } else {
                $relativePath = substr($file->getPathname(), strlen($localDir));
                $this->uploadFile($file->getPathname(), $remoteDir . $relativePath);
            }
        }

        return true;
    }
}
