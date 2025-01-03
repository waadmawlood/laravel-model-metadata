![Laravel Model Metadata](lmm.jpg)


# Laravel Model Metadata
A Laravel package designed to manage metadata with JSON support multiple data types. This package allows you to easily attach, manage, and query metadata on your Laravel models.
can use `HasManyMetadata` or `HasOneMetadata` trait to manage metadata.

## ✨ Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- JSON extension enabled


## 💼 Installation
1. Install the package using Composer:
   ```bash
   composer require waad/laravel-model-metadata
   ```

2. Publish the migrations file:
   ```bash
   php artisan vendor:publish --provider="Waad\\Metadata\\Providers\\MetadataServiceProvider" --tag="migrations"
   ```

## 🎈 Usage

### 🔥 HasManyMetadata Trait

This trait allows a model to have multiple metadata records. Add the trait to your model:

```php
use Waad\Metadata\Traits\HasManyMetadata;

class Post extends Model
{
    use HasManyMetadata;
}
```

Available methods:

#### Creating Metadata
```php
// Create metadata with array
$post->createMetadata(['key' => 'value', 'another_key' => 'another_value']);

// Create metadata with collection
$post->createMetadata(collect(['key' => 'value']));
```

#### Updating Metadata
```php
// Update metadata by ID
$post->updateMetadata('metadata_id', ['new_key' => 'new_value']);
```

#### Deleting Metadata
```php
// Delete metadata by ID
$post->deleteMetadata('metadata_id');
```

#### Retrieving Metadata
```php
// Get all metadata objects
$metadata = $post->metadata;
// or
$metadata = $post->metadata()->get();

// Get metadata by ID
$metadata = $post->getMetadataById('metadata_id');

// Get all metadata column pluck as array
$allMetadata = $post->getMetadata();

// Get all metadata column pluck as collection
$metadataCollection = $post->getMetadataCollection();

// Search in metadata
$searchResults = $post->searchMetadata('search_term');
```

### 🔥 HasOneMetadata Trait

This trait allows a model to have a single metadata record. Add the trait to your model:

```php
use Waad\Metadata\Traits\HasOneMetadata;

class Company extends Model
{
    use HasOneMetadata;
}
```

Available methods:

#### Creating Metadata
```php
// Create metadata with array (only works if no metadata exists)
$company->createMetadata(['key' => 'value', 'another_key' => 'another_value']);

// Create metadata with collection
$company->createMetadata(collect(['key' => 'value']));
```

#### Updating Metadata
```php
// Update existing metadata
$company->updateMetadata(['new_key' => 'new_value']);
```

#### Deleting Metadata
```php
// Delete the metadata
$company->deleteMetadata();
```

#### Retrieving Metadata
```php
// Get metadata as array
$metadata = $company->getMetadata();

// Get metadata as collection
$metadataCollection = $company->getMetadataCollection();
```

Both traits use JSON casting for the metadata column, allowing you to store complex data structures. The metadata is stored in a polymorphic relationship, making it flexible and reusable across different models.


## 👨‍💻 Contributors

- **Waad Mawlood**
  - Email: waad_mawlood@outlook.com
  - Role: Developer



## 📝 License

This package is open-sourced software licensed under the [MIT license](LICENSE).
