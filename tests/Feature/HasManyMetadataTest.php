<?php

use Illuminate\Support\Collection;
use Waad\Metadata\Models\Metadata;
use Waad\Metadata\Tests\App\Models\Post;

beforeEach(function () {
    $this->post = createPost();
    expect($this->post)->toBeInstanceOf(Post::class);
});

// Test to ensure a post can be created
it('can create a post', function () {
    // Check that the created post is an instance of Post
    expect($this->post)
        ->toBeInstanceOf(Post::class)
        ->and($this->post->title)->not->toBeEmpty() // Ensure the title is not empty
        ->and($this->post->content)->not->toBeEmpty() // Ensure the content is not empty
        ->and($this->post->status)->toBeBool(); // Ensure the status is a boolean
});

// Test to ensure metadata can be attached to a post
it('can create metadata to post using createMetadata', function () {
    // Create metadata for the post
    $metadata = $this->post->createMetadata([
        'language' => 'English',
        'is_visible' => true,
        'author' => '',
        'slug' => null,
        'theme' => 'dark',
    ]);

    // Check that the created metadata is an instance of Metadata
    expect($metadata)->toBeInstanceOf(Metadata::class);
});

// Test to ensure multiple metadata records can be created for a post
it('can create multiple metadata records using createManyMetadata', function () {
    // Create multiple metadata records
    $metadataRecords = $this->post->createManyMetadata([
        [
            'language' => 'English',
            'theme' => 'light',
        ],
        [
            'language' => 'French',
            'theme' => 'dark',
        ],
        [
            'language' => 'Spanish',
            'theme' => 'auto',
        ],
    ]);

    // Check that we got back a collection of Metadata instances
    expect($metadataRecords)
        ->toBeCollection()
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Metadata::class);

    // Verify the metadata values were stored correctly
    $storedMetadata = $this->post->getMetadata();
    expect($storedMetadata)->toBeArray()->toHaveCount(3);

    expect($storedMetadata[0])
        ->toMatchArray(['language' => 'English', 'theme' => 'light']);
    expect($storedMetadata[1])
        ->toMatchArray(['language' => 'French', 'theme' => 'dark']);
    expect($storedMetadata[2])
        ->toMatchArray(['language' => 'Spanish', 'theme' => 'auto']);
});

// Test metadata supports multiple data types (string, bool, null, int, float)
it('can add one metadata with multiple types', function () {
    // Create multiple types of metadata for the post
    $metadata = $this->post->createMetadata([
        'language' => 'English',
        'is_visible' => true,
        'author' => '',
        'slug' => null,
        'theme' => 'dark',
        'views' => 100,
        'rating' => 4.5,
    ]);

    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Retrieve the attached metadata
    $attachedMetadata = $this->post->getMetadata();

    // Verify the attached metadata
    expect($attachedMetadata)->toBeArray()
        ->and($attachedMetadata[0]['language'])->toBeString()->toBe('English')
        ->and($attachedMetadata[0]['is_visible'])->toBeTrue()
        ->and($attachedMetadata[0]['author'])->toBeString()->toBe('')
        ->and($attachedMetadata[0]['slug'])->toBeNull()
        ->and($attachedMetadata[0]['theme'])->toBeString()->toBe('dark')
        ->and($attachedMetadata[0]['views'])->toBeInt()->toBe(100)
        ->and($attachedMetadata[0]['rating'])->toBeFloat()->toBe(4.5);
});

// Test adding multiple keys to metadata using addKeysMetadataById
it('can add multiple keys to metadata using addKeysMetadataById', function () {
    // Create initial metadata
    $metadata = $this->post->createMetadata([
        'language' => 'English',
        'theme' => 'light',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Add multiple keys to metadata
    $status = $this->post->addKeysMetadataById($metadata->id, [
        'is_visible' => true,
        'views' => 100,
    ]);
    expect($status)->toBeTrue();

    // Verify the metadata was updated correctly
    $updatedMetadata = $this->post->getMetadataById($metadata->id);
    expect($updatedMetadata)
        ->toMatchArray([
            'language' => 'English',
            'theme' => 'light',
            'is_visible' => true,
            'views' => 100,
        ]);
});

// Test adding a single key to metadata using addKeyMetadataById
it('can add single key to metadata using addKeyMetadataById', function () {
    // Create initial metadata
    $metadata = $this->post->createMetadata([
        'language' => 'English',
    ]);

    // Add single key to metadata
    $status = $this->post->addKeyMetadataById($metadata->id, 'is_visible', true);
    expect($status)->toBeTrue();

    // Verify the metadata was updated correctly
    $updatedMetadata = $this->post->getMetadataById($metadata->id);
    expect($updatedMetadata)
        ->toMatchArray([
            'language' => 'English',
            'is_visible' => true,
        ]);
});

// Test adding keys to metadata with invalid inputs
it('handles invalid inputs when adding metadata keys', function () {
    // Create initial metadata
    $metadata = $this->post->createMetadata([
        'language' => 'English',
    ]);

    // Test with null key
    expect($this->post->addKeysMetadataById($metadata->id, null))->toBeFalse();

    // Test with empty string key
    expect($this->post->addKeyMetadataById($metadata->id, ''))->toBeFalse();

    // Test with empty array
    expect($this->post->addKeysMetadataById($metadata->id, []))->toBeFalse();

    // Verify original metadata remains unchanged
    $unchangedMetadata = $this->post->getMetadataById($metadata->id);
    expect($unchangedMetadata)->toMatchArray(['language' => 'English']);
});

// Test to ensure post metadata can be updated
it('can update post metadata using updateMetadataById', function () {
    // Create initial metadata
    $metadata = $this->post->createMetadata([
        'theme' => 'dark',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Update the metadata
    $status = $this->post->updateMetadataById($metadata->id, [
        'theme' => 'light',
    ]);
    expect($status)->toBeTrue();

    // Get updated metadata and verify changes
    $updatedMetadata = $this->post->getMetadataById($metadata->id);
    expect($updatedMetadata)->toMatchArray(['id' => $metadata->id, 'theme' => 'light']);
});

// Test to ensure post metadata can be synced
it('can sync post metadata using syncMetadata', function () {
    // Create initial metadata
    $this->post->createManyMetadata([
        [
            'language' => 'English',
            'theme' => 'light',
        ],
        [
            'language' => 'Arabic',
            'theme' => 'dark',
        ],
    ]);

    expect($this->post->getMetadata())
        ->toBeArray()
        ->toHaveCount(2);

    // Sync with new metadata
    $status = $this->post->syncMetadata([
        [
            'language' => 'Spanish',
            'theme' => 'auto',
        ],
    ]);
    expect($status)->toBeTrue();

    // Verify old metadata was deleted and new metadata was created
    $syncedMetadata = $this->post->getMetadata();
    expect($syncedMetadata)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($syncedMetadata[0])
        ->toMatchArray([
            'language' => 'Spanish',
            'theme' => 'auto',
        ]);
});

// Test to ensure post metadata can be deleted
it('can delete post metadata using deleteMetadataById and deleteMetadata', function () {
    // Create metadata for the post
    $metadata = $this->post->createMetadata([
        'theme' => 'dark',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Delete the metadata
    $status = $this->post->deleteMetadataById($metadata->id);
    expect($status)->toBeTrue(); // Ensure the deletion was successful
    expect($this->post->getMetadata())->toBeEmpty(); // Verify that metadata is now empty

    // Create multiple metadata records
    $this->post->createManyMetadata([
        [
            'theme' => 'light',
            'language' => 'English',
        ],
        [
            'theme' => 'dark',
            'language' => 'Spanish',
        ],
    ]);
    expect($this->post->getMetadata())->toHaveCount(2);

    // Delete all metadata
    $status = $this->post->deleteMetadata();
    expect($status)->toBeTrue();
    expect($this->post->getMetadata())->toBeEmpty();
});

// Test to ensure metadata can be retrieved as a collection
it('can retrieve metadata as a collection using getMetadataCollection', function () {
    // Create metadata for the post
    $metadata = $this->post->createMetadata([
        'theme' => 'dark',
        'language' => 'Arabic',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Retrieve the metadata collection
    $metadataCollection = $this->post->getMetadataCollection();
    expect($metadataCollection)->toBeInstanceOf(Collection::class)
        ->and($metadataCollection->get(0)['theme'])->toBeString()->toBe('dark')
        ->and($metadataCollection->get(0)['language'])->toBeString()->toBe('Arabic');
});

it('can retrieve metadata by ID using getMetadataById', function () {
    // Create metadata for the post
    $metadata = $this->post->createMetadata([
        'language' => 'Arabic',
        'is_visible' => true,
    ]);

    // Retrieve metadata by ID and verify contents
    $retrievedMetadata = $this->post->getMetadataById($metadata->id);
    expect($retrievedMetadata)
        ->toBeArray()
        ->toHaveCount(3)
        ->and($retrievedMetadata)->toMatchArray([
            'language' => 'Arabic',
            'is_visible' => true,
        ])
        ->and($retrievedMetadata['id'])->toBeString();
});

it('can search metadata using searchMetadataCollection, searchMetadata', function () {
    // Create metadata for the post
    $this->post->createMetadata([
        'language' => 'Arabic',
        'is_visible' => true,
    ]);
    $this->post->createMetadata([
        'language' => 'English',
        'is_visible' => false,
    ]);

    // Search for metadata by language to result collection
    $searchResults = $this->post->searchMetadataCollection('Arabic');
    expect($searchResults)->toBeInstanceOf(Collection::class)
        ->and($searchResults->count())->toBe(1)
        ->and($searchResults->first()['language'])->toBeString()->toBe('Arabic')
        ->and($searchResults->first()['is_visible'])->toBeTrue();

    // Search for metadata by language to result array
    $searchResults = $this->post->searchMetadata('Arabic');
    expect($searchResults)->toBeArray()->toHaveCount(1)
        ->and($searchResults[0]['language'])->toBeString()->toBe('Arabic')
        ->and($searchResults[0]['is_visible'])->toBeTrue();
});

// Test to ensure model has metadata using hasMetadata, hasMetadataById
it('can check if model has metadata using hasMetadata, hasMetadataById', function () {
    // check if model has metadata using hasMetadata
    $this->post->createMetadata([
        'language' => 'Arabic',
        'is_visible' => true,
    ]);
    expect($this->post->hasMetadata())->toBeTrue();

    // check if model has metadata using hasMetadataById
    $metadata = $this->post->getMetadata();
    expect($metadata)->toBeArray()->toHaveCount(1);
    expect($this->post->hasMetadataById($metadata[0]['id']))->toBeTrue();
});
