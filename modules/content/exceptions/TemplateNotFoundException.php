<?php
declare(strict_types=1);

namespace Modules\Content\Exceptions;

use Exception;

class TemplateNotFoundException extends Exception {
    public function __construct(string $template) {
        parent::__construct("Template '{$template}' not found in theme hierarchy");
    }
}
