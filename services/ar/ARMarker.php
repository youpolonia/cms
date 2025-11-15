<?php
declare(strict_types=1);

class ARMarker {
    public const MARKER_SIZE = 8;
    private const DEFAULT_IMAGE_SIZE = 200;
    private const DEFAULT_BG_COLOR = [255, 255, 255]; // White
    private const DEFAULT_FG_COLOR = [0, 0, 0]; // Black

    public static function generate(int $markerId): string {
        if ($markerId < 1) {
            throw new InvalidArgumentException('Marker ID must be positive');
        }

        $hash = hash('sha256', (string)$markerId);
        return substr($hash, 0, self::MARKER_SIZE * self::MARKER_SIZE);
    }

    public static function validate(string $marker): bool {
        return strlen($marker) === self::MARKER_SIZE * self::MARKER_SIZE
            && preg_match('/^[0-9a-f]+$/', $marker);
    }

    public static function generateImage(
        string $marker,
        int $size = self::DEFAULT_IMAGE_SIZE,
        array $bgColor = self::DEFAULT_BG_COLOR,
        array $fgColor = self::DEFAULT_FG_COLOR
    ): string {
        if (!self::validate($marker)) {
            throw new InvalidArgumentException('Invalid marker pattern');
        }

        if (!extension_loaded('gd')) {
            throw new RuntimeException('GD extension not available');
        }

        $cellSize = (int)($size / self::MARKER_SIZE);
        $image = imagecreatetruecolor($size, $size);
        
        $bg = imagecolorallocate($image, ...$bgColor);
        $fg = imagecolorallocate($image, ...$fgColor);
        imagefill($image, 0, 0, $bg);

        for ($y = 0; $y < self::MARKER_SIZE; $y++) {
            for ($x = 0; $x < self::MARKER_SIZE; $x++) {
                $char = $marker[$y * self::MARKER_SIZE + $x];
                if (hexdec($char) % 2 === 0) { // Simple pattern based on hex value
                    imagefilledrectangle(
                        $image,
                        $x * $cellSize,
                        $y * $cellSize,
                        ($x + 1) * $cellSize,
                        ($y + 1) * $cellSize,
                        $fg
                    );
                }
            }
        }

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return $imageData;
    }
}
