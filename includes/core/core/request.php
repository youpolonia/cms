<?php
namespace Core;

require_once __DIR__ . '/uri.php';
require_once __DIR__ . '/stream.php';
require_once __DIR__ . '/uploadedfile.php';

use Core\Uri;
use Core\Stream;
use Core\UploadedFile;

class Request
{
    private array $attributes = [];
    private array $cookieParams;
    private $parsedBody; // mixed
    private array $queryParams;
    private array $serverParams;
    private array $uploadedFiles = []; // Array of Core\UploadedFile
    private string $method;
    private ?Uri $uri = null;
    private string $protocolVersion = '1.1';
    private array $headers = [];
    private ?Stream $body = null;

    // Keep session data internally, not part of PSR-7 request attributes directly
    private array $sessionData;

    public function __construct(
        array $serverParams,
        array $uploadedFiles, // Should be Core\UploadedFile[]
        array $cookieParams,
        array $queryParams,
        $parsedBody, // mixed
        string $method,
        Uri $uri,
        array $headers = [],
        ?Stream $body = null,
        string $protocolVersion = '1.1',
        array $attributes = [],
        array $sessionData = [] // For existing session compatibility
    ) {
        $this->serverParams = $serverParams;
        $this->uploadedFiles = $uploadedFiles;
        $this->cookieParams = $cookieParams;
        $this->queryParams = $queryParams;
        $this->parsedBody = $parsedBody;
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->headers = $this->normalizeHeaders($headers);
        $this->body = $body;
        $this->protocolVersion = $protocolVersion;
        $this->attributes = $attributes;
        $this->sessionData = $sessionData;
    }

    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $name))));
            $normalized[$name] = is_array($value) ? $value : [$value];
        }
        return $normalized;
    }

    public static function createFromGlobals(): self
    {
        $serverParams = $_SERVER;

        // Method
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // URI
        $uriString = '';
        $scheme = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) ? 'https' : 'http';
        $uriString .= $scheme . '://';

        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $uriString .= $_SERVER['PHP_AUTH_USER'];
            if (isset($_SERVER['PHP_AUTH_PW'])) {
                $uriString .= ':' . $_SERVER['PHP_AUTH_PW'];
            }
            $uriString .= '@';
        }

        $uriString .= $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        if (isset($_SERVER['SERVER_PORT']) && !Uri::isStandardPort($scheme, (int)$_SERVER['SERVER_PORT'])) {
             $uriString .= ':' . $_SERVER['SERVER_PORT'];
        }
        $uriString .= $_SERVER['REQUEST_URI'] ?? '/';
        $uri = new Uri($uriString);


        // Headers
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerName = substr($key, 5);
                $headers[$headerName] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $headers[$key] = $value;
            }
        }

        // Body
        $body = new Stream('php://input', 'r');


        // Protocol Version
        $protocolVersion = '1.1';
        if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.0') {
            $protocolVersion = '1.0';
        } elseif (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/2.0') {
            $protocolVersion = '2.0';
        }

        // Uploaded Files
        $uploadedFiles = self::normalizeFiles($_FILES);


        $cookieParams = $_COOKIE;
        $queryParams = $_GET;
        $parsedBody = $_POST ?: null; // Basic assumption, real parsing depends on Content-Type

        // Session data
        $sessionData = $_SESSION ?? [];


        return new self(
            $serverParams,
            $uploadedFiles,
            $cookieParams,
            $queryParams,
            $parsedBody,
            $method,
            $uri,
            $headers,
            $body,
            $protocolVersion,
            [], // attributes
            $sessionData
        );
    }

    // PSR-7 MessageInterface methods
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version): self
    {
        if ($this->protocolVersion === $version) {
            return $this;
        }
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        return isset($this->headers[$this->normalizeHeaderName($name)]);
    }

    public function getHeader($name): array
    {
        $normalizedName = $this->normalizeHeaderName($name);
        return $this->headers[$normalizedName] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader($name, $value): self
    {
        $normalizedName = $this->normalizeHeaderName($name);
        $new = clone $this;
        $new->headers[$normalizedName] = is_array($value) ? $value : [$value];
        return $new;
    }

    public function withAddedHeader($name, $value): self
    {
        $normalizedName = $this->normalizeHeaderName($name);
        $new = clone $this;
        if (!isset($new->headers[$normalizedName])) {
            $new->headers[$normalizedName] = [];
        }
        $new->headers[$normalizedName] = array_merge($new->headers[$normalizedName], is_array($value) ? $value : [$value]);
        return $new;
    }

    public function withoutHeader($name): self
    {
        $normalizedName = $this->normalizeHeaderName($name);
        if (!isset($this->headers[$normalizedName])) {
            return $this;
        }
        $new = clone $this;
        unset($new->headers[$normalizedName]);
        return $new;
    }

    public function getBody(): Stream
    {
        if ($this->body === null) {
            $this->body = new Stream(''); // Default empty stream
        }
        return $this->body;
    }

    public function withBody(Stream $body): self
    {
        if ($this->body === $body) {
            return $this;
        }
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    // PSR-7 RequestInterface methods
    public function getRequestTarget(): string
    {
        if ($this->uri === null) return '/'; // Should not happen if URI is always set
        $target = $this->uri->getPath();
        if ($target === '') {
            $target = '/';
        }
        if ($this->uri->getQuery() !== '') {
            $target .= '?' . $this->uri->getQuery();
        }
        return $target;
    }

    public function withRequestTarget($requestTarget): self
    {
        // This is complex as it might require re-parsing the URI.
        // For now, not deeply implemented.
        // PSR-7 states: "If the request target is not an absolute URI, the Host header MUST be present."
        // "If the request target is "/", the Host header MUST be present."
        // "If the request target is "*", the Host header MAY be omitted."
        // This method is usually for internal use or specific cases like OPTIONS *
        if ($this->getRequestTarget() === $requestTarget) {
            return $this;
        }
        $new = clone $this;
        // $new->requestTarget = $requestTarget; // Need to store this or reflect in URI
        // This implementation is simplified. A more robust one would parse $requestTarget
        // and update the URI object accordingly, or store $requestTarget separately if it's '*'.
        // For now, we assume $requestTarget is a path with an optional query.
        if (strpos($requestTarget, ' ') !== false) {
            // Invalid request target
            // Consider throwing an exception or handling as per specific needs
            return $this; // Or throw new \InvalidArgumentException('Invalid request target');
        }

        $parts = parse_url($requestTarget);
        $newUri = $this->uri;

        if (isset($parts['path'])) {
            $newUri = $newUri->withPath($parts['path']);
        }
        if (isset($parts['query'])) {
            $newUri = $newUri->withQuery($parts['query']);
        } else {
            $newUri = $newUri->withQuery(''); // Clear query if not present
        }
        $new->uri = $newUri;
        return $new;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod($method): self
    {
        $method = strtoupper($method);
        if ($this->method === $method) {
            return $this;
        }
        $new = clone $this;
        $new->method = $method;
        return $new;
    }

    public function getUri(): Uri
    {
        if ($this->uri === null) {
            // This should ideally not happen if constructor populates it.
            // Fallback to a default URI.
            $this->uri = new Uri();
        }
        return $this->uri;
    }

    public function withUri(Uri $uri, $preserveHost = false): self
    {
        if ($this->uri === $uri) {
            return $this;
        }
        $new = clone $this;
        $new->uri = $uri;

        if (!$preserveHost || !$this->hasHeader('Host')) {
            if ($uri->getHost() !== '') {
                $host = $uri->getHost();
                if ($uri->getPort() !== null) {
                    $host .= ':' . $uri->getPort();
                }
                // Remove existing Host header before setting the new one
                if (isset($new->headers['Host'])) unset($new->headers['Host']);
                $new->headers['Host'] = [$host]; // PSR-7: Host header is case-sensitive as 'Host'
            }
        }
        return $new;
    }

    // PSR-7 ServerRequestInterface methods
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies): self
    {
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): self
    {
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles; // Array of Core\UploadedFile
    }

    public function withUploadedFiles(array $uploadedFiles): self
    {
        // Validate that $uploadedFiles contains Core\UploadedFile instances
        foreach ($uploadedFiles as $file) {
            if (!$file instanceof UploadedFile) {
                throw new \InvalidArgumentException(
                    'Each value in $uploadedFiles must be an instance of Core\UploadedFile'
                );
            }
        }
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;
        return $new;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data): self
    {
        if (!is_array($data) && !is_object($data) && $data !== null) {
            throw new \InvalidArgumentException('Parsed body must be an array, object, or null.');
        }
        $new = clone $this;
        $new->parsedBody = $data;
        return $new;
    }

    /**
     * Marshals a normalized array of UploadedFileInterface instances from $_FILES.
     *
     * @param array $files The $_FILES superglobal.
     * @return array A normalized array of UploadedFileInterface instances.
     */
    private static function normalizeFiles(array $files): array
    {
        $normalized = [];
        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFile) {
                $normalized[$key] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                if (is_array($value['tmp_name'])) {
                    $normalized[$key] = self::createUploadedFileFromSpec($value);
                } else {
                    $normalized[$key] = new UploadedFile(
                        $value['tmp_name'],
                        (int) ($value['size'] ?? 0),
                        (int) ($value['error'] ?? UPLOAD_ERR_OK),
                        $value['name'] ?? null,
                        $value['type'] ?? null
                    );
                }
            } else {
                // Potentially invalid structure, log or throw? For now, skip.
                // Or throw new \InvalidArgumentException("Invalid value in files specification");
            }
        }
        return $normalized;
    }

    /**
     * Create an array of UploadedFile objects from a specification.
     *
     * @param array $value $_FILES struct
     * @return array
     */
    private static function createUploadedFileFromSpec(array $value): array
    {
        $normalized = [];
        foreach (array_keys($value['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $value['tmp_name'][$key],
                'size'     => $value['size'][$key] ?? 0,
                'error'    => $value['error'][$key] ?? UPLOAD_ERR_OK,
                'name'     => $value['name'][$key] ?? null,
                'type'     => $value['type'][$key] ?? null,
            ];
            $normalized[$key] = new UploadedFile(
                $spec['tmp_name'],
                (int)$spec['size'],
                (int)$spec['error'],
                $spec['name'],
                $spec['type']
            );
        }
        return $normalized;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    public function withAttribute($name, $value): self
    {
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    public function withoutAttribute($name): self
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $this;
        }
        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }

    // Helper for header normalization
    private function normalizeHeaderName(string $name): string
    {
        return str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $name))));
    }

    // Method to access session data (not part of PSR-7 but useful for the app)
    public function getSessionData(): array
    {
        return $this->sessionData;
    }

    public function getSessionValue(string $key, $default = null)
    {
        return $this->sessionData[$key] ?? $default;
    }
}
