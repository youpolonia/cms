<?php
/**
 * FTP Sync Utility for CMS Deployment
 * 
 * @package Automation
 * @version 1.0.0
 */

class FTPSync {
    private $conn;
    private $config;

    public function __construct(array $config) {
        $this->config = $config;
    }

    public function connect(): bool {
        $this->conn = ftp_connect($this->config['host']);
        if (!$this->conn) return false;
        
        return ftp_login(
            $this->conn,
            $this->config['username'],
            $this->config['password']
        );
    }

    public function syncDirectory(string $localPath, string $remotePath): void {
        foreach (new DirectoryIterator($localPath) as $file) {
            if ($file->isDot()) continue;
            
            $localFile = $file->getPathname();
            $remoteFile = $remotePath . '/' . $file->getFilename();
            
            if ($file->isDir()) {
                $this->createRemoteDir($remoteFile);
                $this->syncDirectory($localFile, $remoteFile);
            } else {
                ftp_put($this->conn, $remoteFile, $localFile, FTP_BINARY);
            }
        }
    }

    private function createRemoteDir(string $path): void {
        @ftp_mkdir($this->conn, $path);
    }

    public function __destruct() {
        if ($this->conn) {
            ftp_close($this->conn);
        }
    }
}
