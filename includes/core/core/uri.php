<?php
namespace Core;

use InvalidArgumentException;

class Uri
{
    private string $scheme = '';
    private string $userInfo = '';
    private string $host = '';
    private ?int $port = null;
    private string $path = '';
    private string $query = '';
    private string $fragment = '';

    /**
     * Standard ports for schemes.
     * @var array<string, int>
     */
    private const STANDARD_PORTS = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
        'gopher' => 70,
        'nntp' => 119,
        'news' => 119,
        'telnet' => 23,
        'tn3270' => 23,
        'imap' => 143,
        'pop' => 110,
        'ldap' => 389,
    ];

    public function __construct(string $uri = '')
    {
        if ($uri !== '') {
            $parts = parse_url($uri);
            if ($parts === false) {
                throw new InvalidArgumentException("Unable to parse URI: " . $uri);
            }
            $this->applyParts($parts);
        }
    }

    private function applyParts(array $parts): void
    {
        $this->scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
        $this->userInfo = $parts['user'] ?? '';
        if (isset($parts['pass'])) {
            $this->userInfo .= ':' . $parts['pass'];
        }
        $this->host = isset($parts['host']) ? strtolower($parts['host']) : '';
        $this->port = isset($parts['port']) ? $this->filterPort($parts['port']) : null;
        $this->path = isset($parts['path']) ? $this->filterPath($parts['path']) : '';
        $this->query = isset($parts['query']) ? $this->filterQueryAndFragment($parts['query']) : '';
        $this->fragment = isset($parts['fragment']) ? $this->filterQueryAndFragment($parts['fragment']) : '';

        if ($this->scheme && $this->port && self::isStandardPort($this->scheme, $this->port)) {
            $this->port = null;
        }
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        if ($this->host === '') {
            return '';
        }
        $authority = $this->host;
        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }
        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }
        return $authority;
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
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

    public function withScheme($scheme): self
    {
        if (!is_string($scheme)) {
            throw new InvalidArgumentException('Scheme must be a string');
        }
        $scheme = strtolower($scheme);
        if ($this->scheme === $scheme) {
            return $this;
        }
        $new = clone $this;
        $new->scheme = $scheme;
        $new->port = $new->filterPort($new->port); // Re-filter port for new scheme
        if ($new->scheme && $new->port && self::isStandardPort($new->scheme, $new->port)) {
            $new->port = null;
        }
        return $new;
    }

    public function withUserInfo($user, $password = null): self
    {
        if (!is_string($user)) {
            throw new InvalidArgumentException('User must be a string');
        }
        $info = $user;
        if ($password !== null) {
            if (!is_string($password)) {
                throw new InvalidArgumentException('Password must be a string or null');
            }
            $info .= ':' . $password;
        }
        if ($this->userInfo === $info) {
            return $this;
        }
        $new = clone $this;
        $new->userInfo = $info;
        return $new;
    }

    public function withHost($host): self
    {
        if (!is_string($host)) {
            throw new InvalidArgumentException('Host must be a string');
        }
        $host = strtolower($host);
        if ($this->host === $host) {
            return $this;
        }
        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    public function withPort($port): self
    {
        $port = $this->filterPort($port);
        if ($this->port === $port) {
            return $this;
        }
        $new = clone $this;
        $new->port = $port;
        if ($new->scheme && $new->port && self::isStandardPort($new->scheme, $new->port)) {
            $new->port = null;
        }
        return $new;
    }

    public function withPath($path): self
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('Path must be a string');
        }
        $path = $this->filterPath($path);
        if ($this->path === $path) {
            return $this;
        }
        $new = clone $this;
        $new->path = $path;
        return $new;
    }

    public function withQuery($query): self
    {
        if (!is_string($query)) {
            throw new InvalidArgumentException('Query must be a string');
        }
        $query = $this->filterQueryAndFragment($query);
        if ($this->query === $query) {
            return $this;
        }
        $new = clone $this;
        $new->query = $query;
        return $new;
    }

    public function withFragment($fragment): self
    {
        if (!is_string($fragment)) {
            throw new InvalidArgumentException('Fragment must be a string');
        }
        $fragment = $this->filterQueryAndFragment($fragment);
        if ($this->fragment === $fragment) {
            return $this;
        }
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
        $authority = $this->getAuthority();
        if ($authority !== '' || $this->scheme === 'file') {
            $uri .= '//' . $authority;
        }
        $path = $this->path;
        if ($authority !== '' && $path !== '' && $path[0] !== '/') {
            $path = '/' . $path;
        } elseif ($authority === '' && isset($path[1]) && $path[0] === '/' && $path[1] === '/') {
            // Avoids "//" for paths like "//example.com/path" when authority is empty
            $path = '/' . ltrim($path, '/');
        }
        $uri .= $path;
        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }
        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }
        return $uri;
    }

    private function filterPort($port): ?int
    {
        if ($port === null) {
            return null;
        }
        $port = (int) $port;
        if (0 > $port || 0xffff < $port) {
            throw new InvalidArgumentException(sprintf('Invalid port: %d. Must be between 0 and 65535.', $port));
        }
        return $port;
    }

    public static function isStandardPort(string $scheme, int $port): bool
    {
        return isset(self::STANDARD_PORTS[$scheme]) && self::STANDARD_PORTS[$scheme] === $port;
    }

    private function filterPath(string $path): string
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/]++|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawurlencodeMatchZero'],
            $path
        );
    }

    private function filterQueryAndFragment(string $str): string
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawurlencodeMatchZero'],
            $str
        );
    }

    private function rawurlencodeMatchZero(array $match): string
    {
        return rawurlencode($match[0]);
    }
}
