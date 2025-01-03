<?php

use Waad\Metadata\Tests\App\Models\Post;
use Waad\Metadata\Models\Metadata;

// Test to ensure a post can be created
it('can create a post', function () {
    $post = createPost();

    // Check that the created post is an instance of Post
    expect($post)
        ->toBeInstanceOf(Post::class)
        ->and($post->title)->not->toBeEmpty() // Ensure the title is not empty
        ->and($post->content)->not->toBeEmpty() // Ensure the content is not empty
        ->and($post->status)->toBeBool(); // Ensure the status is a boolean
});

// Test to ensure metadata can be attached to a post
it('can attach metadata to post', function () {
    $post = createPost();
    expect($post)->toBeInstanceOf(Post::class);

    // Create metadata for the post
    $metadata = $post->createMetadata([
        'language' => 'English',
        'is_visible' => true,
        'author' => '',
        'slug' => null,
        'theme' => 'dark',
    ]);

    // Check that the created metadata is an instance of Metadata
    expect($metadata)->toBeInstanceOf(Metadata::class);
});

// Test to ensure post metadata can be updated
it('can update post metadata', function () {
    $post = createPost();
    expect($post)->toBeInstanceOf(Post::class);

    // Create initial metadata
    $metadata = $post->createMetadata([
        'theme' => 'dark',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Update the metadata
    $status = $post->updateMetadata($metadata->id, [
        'theme' => 'light',
    ]);
    expect($status)->toBeTrue(); // Ensure the update was successful
    expect($post->getMetadata())->toBeArray()->toContain(['theme' => 'light']); // Verify the updated metadata
});

// Test to ensure existing post metadata can be updated
it('can update existing post metadata', function () {
    $post = createPost();
    expect($post)->toBeInstanceOf(Post::class);

    // Create initial metadata
    $metadata = $post->createMetadata([
        'theme' => 'light',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Update the metadata
    $status = $post->updateMetadata($metadata->id, [
        'theme' => 'dark',
        'language' => 'French',
    ]);
    expect($status)->toBeTrue(); // Ensure the update was successful

    // Verify the updated metadata
    $updatedMetadata = $post->getMetadata();
    expect($updatedMetadata)->toBeArray()
        ->and($updatedMetadata[0]['theme'])->toBeString()->toBe('dark') // Check theme
        ->and($updatedMetadata[0]['language'])->toBeString()->toBe('French'); // Check language
});

// Test to ensure post metadata can be deleted
it('can delete post metadata', function () {
    $post = createPost();
    expect($post)->toBeInstanceOf(Post::class);

    // Create metadata for the post
    $metadata = $post->createMetadata([
        'theme' => 'dark',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Delete the metadata
    $status = $post->deleteMetadata($metadata->id);
    expect($status)->toBeTrue(); // Ensure the deletion was successful
    expect($post->getMetadata())->toBeEmpty(); // Verify that metadata is now empty
});

// Test to ensure multiple types of metadata can be attached to a post
it('can attach multiple types of metadata to post', function () {
    $post = createPost();
    expect($post)->toBeInstanceOf(Post::class);

    // Create multiple types of metadata for the post
    $metadata = $post->createMetadata([
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
    $attachedMetadata = $post->getMetadata();

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

// Test to ensure metadata can be retrieved as a collection
it('can retrieve metadata as a collection', function () {
    $post = createPost();
    expect($post)->toBeInstanceOf(Post::class);

    // Create metadata for the post
    $metadata = $post->createMetadata([
        'theme' => 'dark',
        'language' => 'French',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Retrieve the metadata collection
    $metadataCollection = $post->getMetadataCollection();
    expect($metadataCollection)->toBeInstanceOf(\Illuminate\Support\Collection::class)
        ->and($metadataCollection->get(0)['theme'])->toBeString()->toBe('dark') // Check theme
        ->and($metadataCollection->get(0)['language'])->toBeString()->toBe('French'); // Check language
});

it('can retrieve metadata by ID', function () {
    $post = createPost();
    expect($post)->toBeInstanceOf(Post::class);

    // Create metadata for the post
    $metadata = $post->createMetadata([
        'language' => 'Spanish',
        'is_visible' => true,
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Retrieve the metadata by ID
    $retrievedMetadata = $post->getMetadataById($metadata->id);
    expect($retrievedMetadata)->toBeInstanceOf(Metadata::class)
        ->and($retrievedMetadata->metadata['language'])->toBeString()->toBe('Spanish')
        ->and($retrievedMetadata->metadata['is_visible'])->toBeTrue();
});

it('can search metadata by language', function () {
    $post = createPost();
    expect($post)->toBeInstanceOf(Post::class);

    // Create metadata for the post
    $post->createMetadata([
        'language' => 'German',
        'is_visible' => true,
    ]);
    $post->createMetadata([
        'language' => 'Italian',
        'is_visible' => false,
    ]);

    // Search for metadata by language
    $searchResults = $post->searchMetadata('German');
    expect($searchResults)->toBeInstanceOf(\Illuminate\Support\Collection::class)
        ->and($searchResults->count())->toBe(1)
        ->and($searchResults->first()->metadata['language'])->toBeString()->toBe('German')
        ->and($searchResults->first()->metadata['is_visible'])->toBeTrue();
});