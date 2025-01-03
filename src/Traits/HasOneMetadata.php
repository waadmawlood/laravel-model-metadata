<?php

namespace Waad\Metadata\Traits;

use Illuminate\Support\Collection;
use Waad\Metadata\Models\Metadata;

trait HasOneMetadata
{
    /**
     * Create a new metadata record for the model
     *
     * @param array|Collection $metadata
     */
    public function createMetadata(array|Collection $metadata): ?Metadata
    {
        if ($this->metadata()->exists()) {
            return null;
        }

        return $this->metadata()->create([
            'metadata' => $metadata instanceof Collection ? $metadata->toArray() : $metadata
        ]);
    }

    /**
     * Update an existing metadata record
     *
     * @param array|Collection $metadata
     */
    public function updateMetadata(array|Collection $metadata): bool
    {
        $metadataArray = $metadata instanceof Collection ? $metadata->toArray() : $metadata;

        return (bool) $this->metadata()->first()?->update(['metadata' => $metadataArray]);
    }

    /**
     * Delete a metadata record
     */
    public function deleteMetadata(): bool
    {
        return (bool) $this->metadata()->first()?->delete();
    }

    /**
     * Get metadata column as Array
     */
    public function getMetadata(): ?array
    {
        return $this->metadata()->first()?->metadata;
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
    public function metadata(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Metadata::class, 'metadatable');
    }
}
