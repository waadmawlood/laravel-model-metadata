<?php

namespace Waad\Metadata\Traits;

use Illuminate\Support\Collection;
use Waad\Metadata\Models\Metadata;

trait HasManyMetadata
{
    /**
     * Create a new metadata record for the model
     *
     * @param array|Collection $metadata
     */
    public function createMetadata(array|Collection $metadata): Metadata
    {
        return $this->metadata()->create([
            'metadata' => $metadata instanceof Collection ? $metadata->toArray() : $metadata
        ]);
    }

    /**
     * Update an existing metadata record
     *
     * @param string $id
     * @param array|Collection $metadata
     */
    public function updateMetadata(string $id, array|Collection $metadata): bool
    {
        return (bool) $this->metadata()->where('id', $id)->update([
            'metadata' => $metadata instanceof Collection ? $metadata->toArray() : $metadata
        ]);
    }

    /**
     * Delete a metadata record
     *
     * @param string $id
     */
    public function deleteMetadata(string $id): bool
    {
        return (bool) $this->metadata()->where('id', $id)->delete();
    }

    /**
     * Get a metadata record by ID
     *
     * @param string $id
     */
    public function getMetadataById(string $id): ?Metadata
    {
        return $this->metadata()->find($id);
    }

    /**
     * Search metadata records containing the given term
     *
     * @param mixed $searchTerm
     */
    public function searchMetadata($searchTerm): \Illuminate\Database\Eloquent\Collection
    {
        return $this->metadata()->whereJsonContains('metadata', $searchTerm)->get();
    }

    /**
     * Get metadata column as Array
     */
    public function getMetadata(): ?array
    {
        return $this->metadata()->pluck('metadata')->toArray() ?: null;
    }

    /**
     * Get metadata column as collection
     */
    public function getMetadataCollection(): ?Collection
    {
        $metadata = $this->getMetadata();
        return $metadata ? collect($metadata) : null;
    }

    /**
     * Get the metadata relationship
     */
    public function metadata(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Metadata::class, 'metadatable');
    }
}
