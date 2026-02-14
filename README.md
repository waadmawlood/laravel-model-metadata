![Laravel Model Metadata](lmm.jpg)

# Laravel Model Metadata

Laravel Model Metadata is a package designed to manage metadata with JSON support for multiple data types. It allows you to easily attach, manage, and query metadata on your Laravel models using the `HasManyMetadata` or `HasOneMetadata` traits.

# 📚 Documentation

For detailed documentation, including usage examples and best practices, please refer to the [Documentation](https://waad-mawlood.gitbook.io/model-metadata).

# ✨ Requirements

- PHP 8.0 or higher
- Laravel framework 9.30.1 or higher
- JSON extension enabled

# 💼 Installation

1. Install the package using Composer:
   ```bash
   composer require waad/laravel-model-metadata
   ```

2. (Optional) Publish the config file first:
   ```bash
   php artisan vendor:publish --tag=metadata-config
   ```

3. (Recommended) Clear the configuration cache to ensure new config is loaded:
   ```bash
   php artisan config:clear
   ```

4. Publish the migration files:
   ```bash
   php artisan vendor:publish --tag=metadata-migrations
   ```

5. Run the migrations:
   ```bash
   php artisan migrate
   ```

# ⚙️ Configuration

You can customize the metadata table name, model, and caching behavior by editing the published config file at `config/model-metadata.php`:

```php
return [
    'table' => 'model_metadata',
    'model' => Waad\Metadata\Models\Metadata::class,

    'cache' => [
        'enabled' => false,
        'ttl'     => 3600,    // seconds (1 hour)
        'store'   => null,    // null = default cache driver
        'prefix'  => 'model_metadata',
    ],
];
```

# 🎈 Usage

## 🔥 HasOneMetadata Trait

Add the HasOneMetadata trait to your model to enable a single metadata record:

```php
use Waad\Metadata\Traits\HasOneMetadata;

class Company extends Model
{
    use HasOneMetadata;  // <--- Add this trait to your model
}
```

#### Some methods:

```php
// Create metadata with array (only works if no metadata exists)
$company->createMetadata(['key' => 'value', 'another_key' => 'another_value']);

// Create metadata with collection
$company->createMetadata(collect(['key' => 'value']));

// Update existing metadata
$company->updateMetadata(['new_key' => 'new_value']);

// Delete the metadata
$company->deleteMetadata();

// Get metadata as array
$metadata = $company->getMetadata();

// Get metadata as collection
$metadataCollection = $company->getMetadataCollection();
```

-------------

## 🔥 HasManyMetadata Trait

Add the HasManyMetadata trait to your model to enable multiple metadata records:

```php
use Waad\Metadata\Traits\HasManyMetadata;

class Post extends Model
{
    use HasManyMetadata;  // <--- Add this trait to your model

    // Enabled Append id with content metadata (default)
    public $metadataNameIdEnabled = true;

    // Custom Append key of id with metadata (default)
    public $metadataNameId = 'id';
}
```
see [Configuration Append Id](https://waad-mawlood.gitbook.io/model-metadata/basics/markdown-1/use-in-model) for more details

#### Some methods:

```php
// Create metadata with array or collection
$post->createMetadata(['key1' => 'value1', 'key2' => 'value2']);
$post->createMetadata(collect(['key1' => 'value1', 'key2' => 'value2']));

// Update metadata by ID
$post->updateMetadata('{metadata_id}', ['new_key' => 'new_value']);

// Delete metadata by ID
$post->deleteMetadata('{metadata_id}');

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

-------------

### 🗄️ Cache

The package includes an optional caching layer to reduce database queries. Cache is **disabled by default** and can be enabled in the config:

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `cache.enabled` | `bool` | `false` | Enable or disable metadata caching |
| `cache.ttl` | `int` | `3600` | Cache time-to-live in seconds |
| `cache.store` | `string\|null` | `null` | Cache store to use (`null` = default driver) |
| `cache.prefix` | `string` | `model_metadata` | Prefix for all cache keys |

When caching is enabled:
- **Read** operations (`getMetadata`, `getMetadataById`, `getMetadataCollection`) are automatically cached.
- **Write** operations (`create`, `update`, `delete`, `forget`, `sync`) automatically invalidate the cache.
- You can manually clear the cache for a specific model using `clearMetadataCache()`:

```php
$company->clearMetadataCache();
$post->clearMetadataCache();
```

- You can check if caching is active:

```php
$company->metadataCacheIsEnabled(); // bool
```

-------------

# 🧪 Testing

To run the tests for development, use the following command:

```bash
composer test
```

# 👨‍💻 Contributors

- **Waad Mawlood**
  - Email: waad_mawlood@outlook.com
  - Role: Developer

# 📝 License

This package is open-sourced software licensed under the [MIT license](LICENSE).
