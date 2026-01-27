<?php

class PathResolver {
    public static function resolve(string $path): string|false {
        $fullPath = __DIR__ . '/../' . $path;
        return file_exists($fullPath) ? $fullPath : false;
    }

    public static function core(string $path): string|false {
        return self::resolve("core/{$path}");
    }

    public static function includes(string $path): string|false {
        return self::resolve("includes/{$path}");
    }

    public static function templates(string $path): string|false {
        return self::resolve("templates/{$path}");
    }

    public static function adminViews(string $path): string|false {
        return self::resolve("admin/views/{$path}");
    }

    public static function publicViews(string $path): string|false {
        return self::resolve("public/views/{$path}");
    }

    public static function vendor(string $path): string|false {
        return self::resolve("vendor/{$path}");
    }

    // public static function test(string $path): string|false {
    //     return self::resolve("tests/{$path}");
    // }
}
