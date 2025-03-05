<?php

namespace Waad\Metadata\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Waad\Metadata\Helpers\Helper;
use Waad\Metadata\Models\Metadata;

trait HasManyMetadata
{
    public $metadataNameIdEnabled = true;

    public $metadataNameId = 'id';

    public function setMetadataNameIdEnabled(bool $metadataNameIdEnabled): self
    {
        $this->metadataNameIdEnabled = $metadataNameIdEnabled;

        return $this;
    }

    public function getMetadataNameIdEnabled(): bool
    {
        return $this->metadataNameIdEnabled;
    }

    public function setMetadataNameId(string $metadataNameId): self
    {
        $this->metadataNameId = $metadataNameId;

        return $this;
    }

    public function getMetadataNameId(): string
    {
        return $this->metadataNameId;
    }

    /**
     * Create a new metadata record for the model
     */
    public function createMetadata(array|Collection $metadata): Metadata
    {
        return $this->metadata()->create([
            'metadata' => $metadata instanceof Collection ? $metadata->toArray() : $metadata,
        ]);
    }

    /**
     * Create multiple metadata records for the model
     */
    public function createManyMetadata(array|Collection $metadatas): Collection|false
    {
        if (blank($metadatas) || ! app(Helper::class)->isNestedMetadata($metadatas)) {
            return false;
        }

        $metadatas = is_array($metadatas) ? collect($metadatas) : $metadatas;

        return $metadatas->map(fn ($data) => $this->metadata()->create(['metadata' => $data]));
    }

    /**
     * Add specific values by keys to metadata field by ID
     */
    public function addKeysMetadataById(string $id, array|Collection|string|int|null $keys, array|Collection|string|int|float|bool|null $value = null): bool
    {
        $helper = app(Helper::class);

        if ($helper->isNullOrStringEmptyOrWhitespaceOrEmptyArray($keys)) {
            return false;
        }

        $metadata = $this->getMetadataById($id);
        $keys = $keys instanceof Collection ? $keys->toArray() : (! is_array($keys) ? [$keys => $value] : $keys);

        return $this->updateMetadataById($id, array_merge($metadata, $keys));
    }

    /**
     * Add one specific value by key to metadata field by ID
     */
    public function addKeyMetadataById(string $id, string|int|null $key, array|Collection|string|int|float|bool|null $value = null): bool
    {
        return $this->addKeysMetadataById($id, $key, $value);
    }

    /**
     * Update an existing metadata record
     */
    public function updateMetadataById(string $id, array|Collection $metadata): bool
    {
        return (bool) $this->queryById($id)->update([
            'metadata' => app(Helper::class)->pipMetadataToClearKeyNameId($metadata, $this->getMetadataNameId()),
        ]);
    }

    /**
     * Update specific values by keys in metadata field by ID
     */
    public function updateKeysMetadataById(string $id, array|Collection|string|int|null $keys, array|Collection|string|int|float|bool|null $value = null): bool
    {
        return $this->addKeysMetadataById($id, $keys, $value);
    }

    /**
     * Update one specific value by key in metadata field by ID
     */
    public function updateKeyMetadataById(string $id, string|int|null $key, array|Collection|string|int|float|bool|null $value = null): bool
    {
        return $this->updateKeysMetadataById($id, $key, $value);
    }

    /**
     * Sync metadata records by deleting existing ones and creating new ones
     */
    public function syncMetadata(array|Collection $metadata): bool
    {
        if (! app(Helper::class)->isNestedMetadata($metadata) && filled($metadata)) {
            return false;
        } elseif (! app(Helper::class)->isNestedMetadata($metadata) && blank($metadata)) {
            return $this->deleteMetadata() || true;
        }

        if ($this->deleteMetadata()) {
            return (bool) $this->createManyMetadata(
                app(Helper::class)->pipMetadataToClearKeyNameId($metadata, $this->getMetadataNameId())
            );
        }

        return false;
    }

    /**
     * Delete a metadata record By ID
     */
    public function deleteMetadataById(string $id): bool
    {
        return (bool) $this->queryById($id)->delete();
    }

    /**
     * Delete all metadata records
     */
    public function deleteMetadata(): bool
    {
        return (bool) $this->metadata()->delete();
    }

    /**
     * Forget content of metadata by ID
     */
    public function forgetMetadataById(string $id): bool
    {
        return (bool) $this->queryById($id)->update(['metadata' => null]);
    }

    /**
     * Forget content of Keys for metadata by ID
     */
    public function forgetKeysMetadataById(string $id, array|Collection|string|int|null $keys = null): bool
    {
        if (app(Helper::class)->isNullOrStringEmptyOrWhitespaceOrEmptyArray($keys)) {
            return false;
        }

        $metadata = $this->getMetadataById($id);
        if (is_null($metadata)) {
            return false;
        }

        if ($keys instanceof Collection) {
            $keys = $keys->toArray();
        }

        $keys = Arr::wrap($keys);

        return $this->updateMetadataById($id, Arr::except($metadata, $keys));
    }

    /**
     * Forget content of Key for metadata by ID
     */
    public function forgetKeyMetadataById(string $id, string|int|null $key = null): bool
    {
        return $this->forgetKeysMetadataById($id, $key);
    }

    /**
     * Get a content of metadata as array by ID
     */
    public function getMetadataById(string $id, array|Collection|string|int|null $keys = null): array
    {
        $metadata = $this->metadata()->find($id)?->metadata ?? [];

        if (app(Helper::class)->isNullOrStringEmptyOrWhitespaceOrEmptyArray($keys)) {
            return $metadata;
        }

        return Arr::only($metadata, Arr::wrap($keys));
    }

    public function getKeyMetadataById(string $id, string|int $key): string|int|float|bool|array|null
    {
        return $this->getMetadataById($id, $key)[$key] ?? null;
    }

    /**
     * Search metadata records by exact value match or partial string match
     *
     * @param  mixed  $searchTerm
     */
    public function searchMetadataCollection($searchTerm): Collection
    {
        try {
            // Try using JSON contains (works in MySQL, PostgreSQL)
            $collection = $this->metadata()->whereJsonContains('metadata', $searchTerm)->get();
        } catch (\RuntimeException $e) {
            // Fallback for SQLite and other DBs that don't support JSON contains
            $collection = $this->metadata()->get()->filter(function ($item) use ($searchTerm) {
                $metadata = $item->metadata;
                // Search in all values
                foreach ($metadata as $value) {
                    if (is_string($value) && is_string($searchTerm) && str_contains($value, $searchTerm)) {
                        return true;
                    } elseif ($value === $searchTerm) {
                        return true;
                    }
                }

                return false;
            });
        }

        return $collection->map(fn ($item) => $this->getMetadataNameIdEnabled() ?
            $item->mergeIdToMetadata($this->getMetadataNameId())->metadata :
            $item->metadata
        );
    }

    /**
     * Search metadata records by exact value match or partial string match
     *
     * @param  mixed  $searchTerm
     */
    public function searchMetadata($searchTerm): array
    {
        return $this->searchMetadataCollection($searchTerm)->toArray();
    }

    /**
     * Get metadata column as collection
     */
    public function getMetadataCollection(): Collection
    {
        return $this->metadata()->get()
            ->map(fn ($item) => $this->getMetadataNameIdEnabled() ?
                $item->mergeIdToMetadata($this->getMetadataNameId())->metadata :
                $item->metadata
            );
    }

    /**
     * Get metadata column as Array
     */
    public function getMetadata(): array
    {
        return $this->getMetadataCollection()->toArray();
    }

    /**
     * Check if metadata exists and is not empty by ID
     */
    public function hasFilledMetadataById(string $id): bool
    {
        return $this->hasMetadataById($id) && filled($this->getMetadataById($id));
    }

    /**
     * Query metadata by ID
     */
    public function queryById(string $id): Builder|MorphMany
    {
        return $this->metadata()->whereKey($id);
    }

    /**
     * Check if model has any metadata
     */
    public function hasMetadata(): bool
    {
        return $this->metadata()->exists();
    }

    /**
     * Check if model has metadata by ID
     */
    public function hasMetadataById(string $id): bool
    {
        return $this->queryById($id)->exists();
    }

    /**
     * Get the metadata relationship
     */
    public function metadata(): MorphMany
    {
        return $this->morphMany(Metadata::class, 'metadatable');
    }
}
