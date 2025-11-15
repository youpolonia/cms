<?php
declare(strict_types=1);

namespace Includes\Validation;

final class ValidationHelper
{
    public static function validateOrFail(array $data, array $rules): void
    {
        $validator = new ValidationService();
        $result = $validator->validate($data, $rules);

        if (!$result->passed()) {
            throw new ValidationException($result->errors());
        }
    }

    public static function getValidator(): ValidationService
    {
        return new ValidationService();
    }
}
