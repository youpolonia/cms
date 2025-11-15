<?php
namespace Includes\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Includes\Http\Uri;

class Request implements ServerRequestInterface
{
    protected array $serverParams;
    protected array $attributes = [];
    protected array $queryParams = [];
    protected array $parsedBody = [];
    protected array $cookies = [];
    protected array $uploadedFiles = [];

    public function __construct(array $serverParams)
    {
        $this->serverParams = $serverParams;
        $this->queryParams = $_GET;
        $this->parsedBody = $_POST;
        $this->cookies = $_COOKIE;
    }

    public function getServerParams(): array { return $this->serverParams; }
    public function getCookieParams(): array { return $this->cookies; }
    public function withCookieParams(array $cookies): self { return $this; }
    public function getQueryParams(): array { return $this->queryParams; }
    public function withQueryParams(array $query): self { return $this; }
    public function getUploadedFiles(): array { return $this->uploadedFiles; }
    public function withUploadedFiles(array $uploadedFiles): self { return $this; }
    public function getParsedBody() { return $this->parsedBody; }
    public function withParsedBody($data): self { return $this; }
    public function getAttributes(): array { return $this->attributes; }
    public function getAttribute($name, $default = null) { return $this->attributes[$name] ?? $default; }
    public function withAttribute($name, $value): self { $this->attributes[$name] = $value; return $this; }
    public function withoutAttribute($name): self { unset($this->attributes[$name]); return $this; }

    // Implement other required PSR-7 methods with minimal functionality
    public function getProtocolVersion(): string { return '1.1'; }
    public function withProtocolVersion($version): self { return $this; }
    public function getHeaders(): array { return []; }
    public function hasHeader($name): bool { return false; }
    public function getHeader($name): array { return []; }
    public function getHeaderLine($name): string { return ''; }
    public function withHeader($name, $value): self { return $this; }
    public function withAddedHeader($name, $value): self { return $this; }
    public function withoutHeader($name): self { return $this; }
    public function getBody(): StreamInterface { return new Stream(''); }
    public function withBody(StreamInterface $body): self { return $this; }
    public function getRequestTarget(): string { return $this->serverParams['REQUEST_URI'] ?? '/'; }
    public function withRequestTarget($requestTarget): self { return $this; }
    public function getMethod(): string { return $this->serverParams['REQUEST_METHOD'] ?? 'GET'; }
    public function withMethod($method): self { return $this; }
    public function getUri(): UriInterface { return new Uri(''); }
    public function withUri(UriInterface $uri, $preserveHost = false): self { return $this; }
}
