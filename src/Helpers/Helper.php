<?php

namespace Waad\Metadata\Helpers;

use Illuminate\Support\Collection;

class Helper
{
    public function isNullOrStringEmptyOrWhitespaceOrEmptyArray(mixed $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        return is_array($value) && empty($value);
    }
}
