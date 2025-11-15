<?php
namespace Core;

require_once __DIR__ . '/stream.php';

use RuntimeException;
use InvalidArgumentException;

class UploadedFile
{
    public function __destruct()
    {
        if ($this->stream !== null) {
            $this->stream->close();
        }
    }
    private const ERRORS = [
        UPLOAD_ERR_OK => 'There is no error, the file uploaded with success',
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
    ];

    private ?Stream $stream = null;
    private ?string $file = null;
    private int $size;
    private int $error;
    private ?string $clientFilename;
    private ?string $clientMediaType;
    private bool $moved = false;

    /**
     * @param string|resource $streamOrFile Path to file or stream resource.
     * @param int $size Size in bytes.
     * @param int $errorStatus One of UPLOAD_ERR_* constants.
     * @param string|null $clientFilename Client filename, if available.
     * @param string|null $clientMediaType Client media type, if available.
     */
    public function __construct(
        $streamOrFile,
        int $size,
        int $errorStatus,
        ?string $clientFilename = null,
        ?string $clientMediaType = null
    ) {
        if (!isset(self::ERRORS[$errorStatus])) {
            throw new InvalidArgumentException('Invalid error status for UploadedFile');
        }
        $this->error = $errorStatus;
        $this->size = $size;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;

        if ($this->error === UPLOAD_ERR_OK) {
            if (is_string($streamOrFile)) {
                $this->file = $streamOrFile;
            } elseif (is_resource($streamOrFile)) {
                $this->stream = new Stream($streamOrFile);
            } elseif ($streamOrFile instanceof Stream) {
                $this->stream = $streamOrFile;
            } else {
                throw new InvalidArgumentException('Invalid stream or file provided for UploadedFile');
            }
        }
    }

    private function validateActive(): void
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(self::ERRORS[$this->error]);
        }
        if ($this->moved) {
            throw new RuntimeException('Cannot retrieve stream after it has been moved');
        }
    }

    public function getStream(): Stream
    {
        $this->validateActive();
        if ($this->stream instanceof Stream) {
            return $this->stream;
        }
        if ($this->file === null || !is_readable($this->file)) {
             throw new RuntimeException('No valid file or stream available');
        }
        $stream = null;
        try {
            $stream = fopen($this->file, 'r');
            $this->stream = new Stream($stream);
            $stream = null; // Ownership transferred to Stream
            return $this->stream;
        } catch (\Throwable $e) {
            if ($stream !== null) {
                @fclose($stream);
            }
            if (strpos($e->getMessage(), 'timeout') !== false) {
                throw new TimeoutException('File stream timeout: ' . $e->getMessage(), 0, $e);
            }
            throw new NetworkFailureException('Unable to create stream from file: ' . $e->getMessage(), 0, $e);
        }
    }

    public function moveTo($targetPath): void
    {
        $this->validateActive();

        if (!is_string($targetPath) || empty($targetPath)) {
            throw new InvalidArgumentException('Invalid path provided for move operation; must be a non-empty string');
        }

        $targetDirectory = dirname($targetPath);
        if (!is_dir($targetDirectory) || !is_writable($targetDirectory)) {
            throw new RuntimeException(sprintf('The target directory "%s" does not exist or is not writable.', $targetDirectory));
        }

        if ($this->file) {
            // SAPI (CLI or FPM)
            if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
                 // @codeCoverageIgnoreStart
                if (!rename($this->file, $targetPath)) {
                    throw new RuntimeException(sprintf('Uploaded file could not be moved to "%s"', $targetPath));
                }
                 // @codeCoverageIgnoreEnd
            } else {
                // Non-CLI SAPI (e.g., Apache, FPM)
                if (!is_uploaded_file($this->file) || !move_uploaded_file($this->file, $targetPath)) {
                    throw new RuntimeException(sprintf('Uploaded file could not be moved to "%s"', $targetPath));
                }
            }
        } else {
            // Stream
            $stream = $this->getStream();
            if ($stream->isSeekable()) {
                $stream->rewind();
            }
            $destResource = null;
            try {
                $destResource = fopen($targetPath, 'w');
                $destination = new Stream($destResource);
                $destResource = null; // Ownership transferred to Stream
                while (!$stream->eof()) {
                    if (!$destination->write($stream->read(1048576))) { // 1MB chunks
                        throw new NetworkFailureException(sprintf('Error moving uploaded file to "%s": Could not write to destination stream.', $targetPath));
                    }
                }
            } finally {
                if ($destResource !== null) {
                    @fclose($destResource);
                }
            }
            
            try {
            } catch (\Throwable $e) {
                if (strpos($e->getMessage(), 'timeout') !== false) {
                    throw new TimeoutException('File transfer timeout: ' . $e->getMessage(), 0, $e);
                }
                throw new NetworkFailureException('Network error during file transfer: ' . $e->getMessage(), 0, $e);
            }
            $stream->close();
            $destination->close();
        }
        $this->moved = true;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }
}
