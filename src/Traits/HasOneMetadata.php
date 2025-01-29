<?php

namespace Waad\Metadata\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Waad\Metadata\Helpers\Helper;
use Waad\Metadata\Models\Metadata;

trait HasOneMetadata
{
    /**
     * Create a new metadata record for the model
     */
    public function createMetadata(array|Collection $metadata): ?Metadata
    {
        if ($this->hasMetadata()) {
            return null;
        }

        return $this->metadata()->create([
            'metadata' => $metadata instanceof Collection ? $metadata->toArray() : $metadata,
        ]);
    }

    /**
     * Add specific values by keys from metadata field
     */
    public function addKeysMetadata(array|Collection|string|int|null $keys, array|Collection|string|int|float|bool|null $value = null): bool
    {
        $helper = app(Helper::class);

        if ($helper->isNullOrStringEmptyOrWhitespaceOrEmptyArray($keys)) {
            return false;
        }

        $metadata = $this->getMetadata() ?? [];
        $keys = $keys instanceof Collection ? $keys->toArray() : (! is_array($keys) ? [$keys => $value] : $keys);

        return $this->syncMetadata(array_merge($metadata, $keys));
    }

    /**
     * Add one specific value by key from metadata field
     */
    public function addKeyMetadata(string|int|null $key, array|Collection|string|int|float|bool|null $value = null): bool
    {
        return $this->addKeysMetadata($key, $value);
    }

    /**
     * Sync metadata
     */
    public function syncMetadata(array|Collection $metadata): bool
    {
        $metadataArray = $metadata instanceof Collection ? $metadata->toArray() : $metadata;

        if ($this->hasMetadata()) {
            return $this->updateMetadata($metadataArray);
        }

        return $this->createMetadata($metadataArray) !== null;
    }

    /**
     * Update an existing metadata record
     */
    public function updateMetadata(array|Collection $metadata): bool
    {
        $metadataArray = $metadata instanceof Collection ? $metadata->toArray() : $metadata;

        return (bool) $this->metadata()->first()?->update(['metadata' => $metadataArray]);
    }

    /**
     * Update specific values by keys from metadata field
     */
    public function updateKeysMetadata(array|Collection|string|int|null $keys, array|Collection|string|int|float|bool|null $value = null): bool
    {
        $helper = app(Helper::class);

        if ($helper->isNullOrStringEmptyOrWhitespaceOrEmptyArray($keys)) {
            return false;
        }

        $metadata = $this->getMetadata() ?? [];
        $keys = $keys instanceof Collection ? $keys->toArray() : (! is_array($keys) ? [$keys => $value] : $keys);

        return $this->syncMetadata(array_merge($metadata, $keys));
    }

    /**
     * Update one specific value by key from metadata field
     */
    public function updateKeyMetadata(string|int|null $key, array|Collection|string|int|float|bool|null $value = null): bool
    {
        return $this->updateKeysMetadata($key, $value);
    }

    /**
     * Delete a metadata record
     */
    public function deleteMetadata(): bool
    {
        return (bool) $this->metadata()->first()?->delete();
    }

    /**
     * Emptying metadata field make it null
     */
    public function forgetMetadata(): bool
    {
        return (bool) $this->metadata()->first()?->update(['metadata' => null]);
    }

    /**
     * delete specific value by key from metadata field
     */
    public function forgetKeysMetadata(array|Collection|string|int|null $keys = null): bool
    {
        if (app(Helper::class)->isNullOrStringEmptyOrWhitespaceOrEmptyArray($keys)) {
            return false;
        }

        $metadata = $this->getMetadata();
        if (is_null($metadata)) {
            return false;
        }

        if ($keys instanceof Collection) {
            $keys = $keys->toArray();
        }

        $keys = Arr::wrap($keys);

        return $this->syncMetadata(Arr::except($metadata, $keys));
    }

    /**
     * delete specific value by key from metadata field
     */
    public function forgetKeyMetadata(string|int|null $key = null): bool
    {
        return $this->forgetKeysMetadata($key);
    }

    /**
     * Check if metadata exists
     */
    public function hasMetadata(): bool
    {
        return $this->metadata()->exists();
    }

    /**
     * Check if metadata exists all keys
     */
    public function hasAllKeysMetadata(array|Collection|string|int|null $keys): bool
    {
        if (app(Helper::class)->isNullOrStringEmptyOrWhitespaceOrEmptyArray($keys)) {
            return false;
        }

        $keys = $keys instanceof Collection ? $keys->toArray() : (! is_array($keys) ? [$keys] : $keys);
        $metadata = $this->getMetadata() ?? [];

        return Arr::has($metadata, $keys);
    }

    /**
     * Check if metadata exists by key
     */
    public function hasKeyMetadata(string|int|null $key): bool
    {
        return $this->hasAllKeysMetadata($key);
    }

    /**
     * Check if metadata exists any keys
     */
    public function hasAnyKeysMetadata(array|Collection|string|int|null $keys): bool
    {
        if (is_array($keys) || $keys instanceof Collection) {
            foreach ($keys as $key) {
                if ($this->hasKeyMetadata($key)) {
                    return true;
                }
            }

            return false;
        }

        return $this->hasKeyMetadata($keys);
    }

    /**
     * Check if metadata exists and is not empty
     */
    public function hasFilledMetadata(): bool
    {
        return $this->hasMetadata() && filled($this->getMetadata());
    }

    /**
     * Get metadata column as Array
     */
    public function getMetadata(array|Collection|string|int|null $keys = null): array
    {
        $metadata = $this->metadata()->first()?->metadata;

        if (app(Helper::class)->isNullOrStringEmptyOrWhitespaceOrEmptyArray($keys)) {
            return $metadata ?? [];
        }

        $keys = Arr::wrap($keys);

        return Arr::only($metadata, $keys);
    }

    /**
     * Get individual metadata
     */
    public function getKeyMetadata(string|int $key): string|int|float|bool|array|null
    {
        return $this->getMetadata($key)[$key] ?? null;
    }

    /**
     * Get metadata column as collection
     */
    public function getMetadataCollection(array|Collection|string|int|null $keys = null): Collection
    {
        return collect($this->getMetadata($keys));
    }

    /**
     * Get the metadata relationship
     */
    public function metadata(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Metadata::class, 'metadatable');
    }
}
