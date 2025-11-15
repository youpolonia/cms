<?php
namespace Includes\Http;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    private $stream;
    private $seekable;
    private $readable;
    private $writable;
    private $size;

    public function __construct($stream = '')
    {
        $this->stream = fopen('php://memory', 'r+');
        fwrite($this->stream, $stream);
        rewind($this->stream);
        
        $this->seekable = true;
        $this->readable = true;
        $this->writable = true;
    }

    public function __toString(): string
    {
        if (!$this->isReadable()) {
            return '';
        }
        try {
            $this->seek(0);
            return $this->getContents();
        } catch (\RuntimeException $e) {
            return '';
        }
    }

    public function close(): void
    {
        if (isset($this->stream)) {
            fclose($this->stream);
        }
        $this->detach();
    }

    public function detach()
    {
        $result = $this->stream;
        $this->stream = null;
        $this->size = null;
        $this->seekable = false;
        $this->readable = false;
        $this->writable = false;
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

        $stats = fstat($this->stream);
        $this->size = $stats['size'] ?? null;
        return $this->size;
    }

    public function tell(): int
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        $result = ftell($this->stream);
        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
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
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->seekable) {
            throw new \RuntimeException('Stream is not seekable');
        }
        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek to stream position');
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
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->writable) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }
        $result = fwrite($this->stream, $string);
        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }
        $this->size = null;
        return $result;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function read($length): string
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }
        $result = fread($this->stream, $length);
        if ($result === false) {
            throw new \RuntimeException('Unable to read from stream');
        }
        return $result;
    }

    public function getContents(): string
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
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
