<?php

namespace Waad\Metadata\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
    public function createManyMetadata(array|Collection $metadatas): Collection
    {
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
        $newMetadata = $helper->pipMetadataToClearKeyNameId(array_merge($metadata, $keys));

        return $this->updateMetadataById($id, $newMetadata);
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
     * Get a metadata array by ID
     */
    public function getMetadataById(string $id): array
    {
        return $this->getMetadataNameIdEnabled() ?
            $this->metadata()->find($id)?->mergeIdToMetadata($this->getMetadataNameId())->metadata ?? [] :
            $this->metadata()->find($id)?->metadata ?? [];
    }

    /**
     * Search metadata records by exact value match or partial string match
     *
     * @param  mixed  $searchTerm
     */
    public function searchMetadataCollection($searchTerm): Collection
    {
        $collection = $this->metadata()->whereJsonContains('metadata', $searchTerm)->get();

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
