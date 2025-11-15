<?php
namespace Includes\Http;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    private $scheme = '';
    private $user = '';
    private $pass = '';
    private $host = '';
    private $port = null;
    private $path = '';
    private $query = '';
    private $fragment = '';

    public function __construct(string $uri = '')
    {
        if ($uri !== '') {
            $parts = parse_url($uri);
            if ($parts === false) {
                throw new \InvalidArgumentException("Unable to parse URI: $uri");
            }

            $this->scheme = $parts['scheme'] ?? '';
            $this->user = $parts['user'] ?? '';
            $this->pass = $parts['pass'] ?? '';
            $this->host = $parts['host'] ?? '';
            $this->port = $parts['port'] ?? null;
            $this->path = $parts['path'] ?? '';
            $this->query = $parts['query'] ?? '';
            $this->fragment = $parts['fragment'] ?? '';
        }
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        $authority = $this->host;
        if ($this->user !== '') {
            $authority = $this->user . ($this->pass !== '' ? ':' . $this->pass : '') . '@' . $authority;
        }
        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }
        return $authority;
    }

    public function getUserInfo(): string
    {
        return $this->user . ($this->pass !== '' ? ':' . $this->pass : '');
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withScheme($scheme): UriInterface
    {
        $new = clone $this;
        $new->scheme = $scheme;
        return $new;
    }

    public function withUserInfo($user, $password = null): UriInterface
    {
        $new = clone $this;
        $new->user = $user;
        $new->pass = $password ?? '';
        return $new;
    }

    public function withHost($host): UriInterface
    {
        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    public function withPort($port): UriInterface
    {
        $new = clone $this;
        $new->port = $port;
        return $new;
    }

    public function withPath($path): UriInterface
    {
        $new = clone $this;
        $new->path = $path;
        return $new;
    }

    public function withQuery($query): UriInterface
    {
        $new = clone $this;
        $new->query = $query;
        return $new;
    }

    public function withFragment($fragment): UriInterface
    {
        $new = clone $this;
        $new->fragment = $fragment;
        return $new;
    }

    public function __toString(): string
    {
        $uri = '';
        if ($this->scheme !== '') {
            $uri .= $this->scheme . ':';
        }
        if ($this->getAuthority() !== '') {
            $uri .= '//' . $this->getAuthority();
        }
        $uri .= $this->path;
        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }
        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }
        return $uri;
    }
}
