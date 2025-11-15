<?php
namespace Core;

use RuntimeException;
use InvalidArgumentException;

class Stream
{
    /** @var resource|null A resource reference */
    private $stream;

    /** @var bool */
    private bool $seekable;

    /** @var bool */
    private bool $readable;

    /** @var bool */
    private bool $writable;

    /** @var array|mixed|null|false */
    private $uri;

    /** @var int|null */
    private ?int $size = null;

    /**
     * @param string|resource $body
     * @param string $mode
     */
    public function __construct($body = '', string $mode = 'r+b')
    {
        if (is_string($body)) {
            $resource = fopen('php://temp', $mode);
            if ($body !== '') {
                fwrite($resource, $body);
                fseek($resource, 0);
            }
            $this->stream = $resource;
        } elseif (is_resource($body)) {
            $this->stream = $body;
        } else {
            throw new InvalidArgumentException('Invalid stream provided; must be a string or resource');
        }

        $meta = stream_get_meta_data($this->stream);
        $this->seekable = $meta['seekable'];
        $this->readable = preg_match('/r|a\+?|w\+?|c\+?|x\+?/', $meta['mode']) > 0;
        $this->writable = preg_match('/a|w|x|c/', $meta['mode']) > 0;
        $this->uri = $this->getMetadata('uri');
    }

    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }
            return $this->getContents();
        } catch (\Throwable $e) {
            // According to PSR-7, __toString must not throw an exception.
            // It should return an empty string if an error occurs.
            return '';
        }
    }

    public function close(): void
    {
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
            $this->detach();
        }
    }

    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }
        $result = $this->stream;
        unset($this->stream);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;
        return $result;
    }

    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }
        if (!isset($this->stream)) {
            return null;
        }
        // Clear stat cache
        clearstatcache(true, $this->uri);
        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }
        return null;
    }

    public function tell(): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }
        $result = ftell($this->stream);
        if ($result === false) {
            throw new RuntimeException('Unable to determine stream position');
        }
        return $result;
    }

    public function eof(): bool
    {
        return !isset($this->stream) || feof($this->stream);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }
        if (!$this->seekable) {
            throw new RuntimeException('Stream is not seekable');
        }
        $this->cachedMetadata = []; // Invalidate metadata cache on seek

        // Type validation with backward compatibility
        $validWhence = [SEEK_SET, SEEK_CUR, SEEK_END];
        if (!in_array($whence, $validWhence, true)) {
            throw new InvalidArgumentException('Invalid whence value');
        }

        // Convert offset to integer if possible
        $intOffset = is_numeric($offset) ? (int)$offset : $offset;
        if (!is_int($intOffset)) {
            throw new InvalidArgumentException('Offset must be an integer');
        }

        if (fseek($this->stream, $intOffset, $whence) === -1) {
            throw new RuntimeException('Unable to seek to stream position ' . $intOffset . ' with whence ' . var_export($whence, true));
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function write($string): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }
        if (!$this->writable) {
            throw new RuntimeException('Cannot write to a non-writable stream');
        }
        // We can't know the size after writing anything
        $this->size = null;
        $result = fwrite($this->stream, $string);
        if ($result === false) {
            throw new RuntimeException('Unable to write to stream');
        }
        return $result;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function read($length): string
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }
        if (!$this->readable) {
            throw new RuntimeException('Cannot read from non-readable stream');
        }
        if ($length < 0) {
            throw new InvalidArgumentException('Length parameter cannot be negative');
        }
        if (0 === $length) {
            return '';
        }
        $string = fread($this->stream, $length);
        if (false === $string) {
            throw new RuntimeException('Unable to read from stream');
        }
        return $string;
    }

    public function getContents(): string
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }
        if (!$this->readable) {
             throw new RuntimeException('Cannot read from non-readable stream');
        }
        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new RuntimeException('Unable to read stream contents');
        }
        return $contents;
    }

    public function getMetadata($key = null)
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        }
        $meta = stream_get_meta_data($this->stream);
        if ($key === null) {
            return $meta;
        }
        return $meta[$key] ?? null;
    }
}
