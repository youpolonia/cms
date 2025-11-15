<?php
declare(strict_types=1);

namespace Modules\Content\Exceptions;

use Exception;

class InvalidThemeException extends Exception {
    public function __construct(string $theme) {
        parent::__construct("Theme '{$theme}' does not exist");
    }
}
