<?php
declare(strict_types=1);

namespace Core;

use Exception;
use Core\Logger\LoggerFactory;

/**
 * MCPService - Model Context Protocol Service
 * Handles MCP server connections, tool execution, and resource access
 */
final class MCPService
{
    private static ?self $instance = null;
    private array $connections = [];
    private array $servers = [];
    private ?\Psr\Log\LoggerInterface $logger = null;

    // Prevent direct instantiation
    private function __construct() {}

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->logger = LoggerFactory::getInstance();
        }
        return self::$instance;
    }

    /**
     * Register an MCP server
     */
    public function registerServer(string $name, array $config): void
    {
        $this->servers[$name] = $config;
        $this->logger?->info("Registered MCP server: $name", ['config' => $config]);
    }

    /**
     * Set logger instance (backward compatibility)
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Execute an MCP tool
     */
    public function useTool(string $serverName, string $toolName, array $arguments): array
    {
        $this->logger?->debug("Starting tool execution", [
            'server' => $serverName,
            'tool' => $toolName,
            'arguments' => $arguments
        ]);

        try {
            $this->validateServer($serverName);
            $connection = $this->getConnection($serverName);
            
            $this->logger?->info("Tool execution started", [
                'server' => $serverName,
                'tool' => $toolName
            ]);
            
            // Simulate tool execution (actual implementation would vary)
            $result = [
                'status' => 'success',
                'data' => [
                    'server' => $serverName,
                    'tool' => $toolName,
                    'arguments' => $arguments
                ]
            ];
            
            $this->logger?->info("Tool execution completed", [
                'server' => $serverName,
                'tool' => $toolName,
                'status' => 'success'
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->logError($e, [
                'server' => $serverName,
                'tool' => $toolName,
                'arguments' => $arguments
            ]);
            return $this->handleFallback($serverName, $toolName, $arguments, $e);
        }
    }

    /**
     * Access an MCP resource
     */
    public function accessResource(string $serverName, string $uri): mixed
    {
        $this->logger?->debug("Starting resource access", [
            'server' => $serverName,
            'uri' => $uri
        ]);

        try {
            $this->validateServer($serverName);
            $connection = $this->getConnection($serverName);
            
            $this->logger?->info("Resource access started", [
                'server' => $serverName,
                'uri' => $uri
            ]);
            
            // Simulate resource access (actual implementation would vary)
            $result = [
                'status' => 'success',
                'data' => [
                    'server' => $serverName,
                    'uri' => $uri
                ]
            ];
            
            $this->logger?->info("Resource access completed", [
                'server' => $serverName,
                'uri' => $uri,
                'status' => 'success'
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->logError($e, [
                'server' => $serverName,
                'uri' => $uri
            ]);
            return $this->handleResourceFallback($serverName, $uri, $e);
        }
    }

    private function validateServer(string $serverName): void
    {
        if (!isset($this->servers[$serverName])) {
            $this->logger?->warning("Server not registered", ['server' => $serverName]);
            throw new Exception("Server $serverName not registered");
        }
    }

    private function getConnection(string $serverName): mixed
    {
        if (!isset($this->connections[$serverName])) {
            $this->logger?->debug("Creating new connection", ['server' => $serverName]);
            $this->connections[$serverName] = $this->createConnection($serverName);
        }
        return $this->connections[$serverName];
    }

    private function createConnection(string $serverName): mixed
    {
        $config = $this->servers[$serverName];
        $this->logger?->info("Creating connection", ['server' => $serverName]);
        // Actual connection logic would go here
        return ['status' => 'connected'];
    }

    private function logError(Exception $e, array $context = []): void
    {
        $this->logger?->error($e->getMessage(), array_merge($context, ['exception' => $e]));
    }

    private function handleFallback(
        string $serverName,
        string $toolName,
        array $arguments,
        Exception $originalError
    ): array {
        $this->logger?->warning("Fallback triggered", [
            'server' => $serverName,
            'tool' => $toolName,
            'error' => $originalError->getMessage()
        ]);
        
        return [
            'status' => 'error',
            'error' => $originalError->getMessage(),
            'fallback_attempted' => true
        ];
    }

    private function handleResourceFallback(
        string $serverName,
        string $uri,
        Exception $originalError
    ): mixed {
        $this->logger?->warning("Resource fallback triggered", [
            'server' => $serverName,
            'uri' => $uri,
            'error' => $originalError->getMessage()
        ]);
        
        return [
            'status' => 'error',
            'error' => $originalError->getMessage(),
            'fallback_attempted' => true
        ];
    }
}
