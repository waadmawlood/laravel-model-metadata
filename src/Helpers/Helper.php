<?php

namespace Waad\Metadata\Helpers;

use Illuminate\Support\Arr;
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

    public function pipMetadataToClearKeyNameId(array|Collection $metadata, string $keyNameId = 'id'): array
    {
        $metadata = $metadata instanceof Collection ? $metadata->toArray() : $metadata;
        $firstItem = Arr::first($metadata);

        // Check if metadata is nested (array of arrays)
        if (filled($firstItem) && is_array($firstItem)) {
            return array_map(fn ($item) => Arr::except($item, $keyNameId), $metadata);
        }

        return Arr::except($metadata, $keyNameId);
    }

    public function isNestedMetadata(array|Collection $metadata): bool
    {
        $metadata = $metadata instanceof Collection ? $metadata->toArray() : $metadata;
        $firstItem = Arr::first($metadata);

        return filled($firstItem) && is_array($firstItem);
    }
}
