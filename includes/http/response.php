<?php
namespace Includes\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    private $statusCode = 200;
    private $reasonPhrase = '';
    private $headers = [];
    private $body;

    public function __construct()
    {
        $this->body = new Stream('');
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        $new = clone $this;
        $new->statusCode = $code;
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    // MessageInterface methods
    public function getProtocolVersion(): string
    {
        return '1.1';
    }

    public function withProtocolVersion($version): ResponseInterface
    {
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function getHeader($name): array
    {
        return $this->headers[strtolower($name)] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(',', $this->getHeader($name));
    }

    public function withHeader($name, $value): ResponseInterface
    {
        $new = clone $this;
        $new->headers[strtolower($name)] = is_array($value) ? $value : [$value];
        return $new;
    }

    public function withAddedHeader($name, $value): ResponseInterface
    {
        $new = clone $this;
        $headerName = strtolower($name);
        $new->headers[$headerName] = array_merge(
            $this->getHeader($name),
            is_array($value) ? $value : [$value]
        );
        return $new;
    }

    public function withoutHeader($name): ResponseInterface
    {
        $new = clone $this;
        unset($new->headers[strtolower($name)]);
        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): ResponseInterface
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }
}
