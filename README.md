# Typesense Searchable - Laravel Scout

[![Latest Version on Packagist](https://img.shields.io/packagist/v/glimmer/typesense-searchable?style=flat-square)](https://packagist.org/packages/glimmer/typesense-searchable)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/laravel-glimmer/typesense-searchable/tests.yml?branch=main&label=Tests&style=flat-square)](https://github.com/laravel-glimmer/typesense-searchable/actions/workflows/tests.yml?query=branch%3Amain)
[![Laravel Octane Compatibility](https://img.shields.io/badge/Laravel%20Octane-Compatible-success?style=flat&logo=laravel)](https://laravel.com/docs/12.x/octane#introduction)

This is a trait that enables Laravel Eloquent models to integrate with Typesense easily when using Laravel Scout,
allowing it to be indexed and searched using a schema generated directly from the model’s `searchableSchema()` method.

### Features

- **Easier Schema Generation**: Define parameters and field types directly within the model.
- **Field Transformation and Casting**: Automatic casting of fields to Typesense supported data types based on schema
  definitions.
- **Automatic ID and Carbon Instances Conversion**: Automatically adds the `id` field to the schema and converts it to a
  string type. If the `SoftDeletes` trait is used, the `__soft_deleted` field is also added and converted. Any field
  that is a Carbon instance is automatically converted to timestamp.
- **Model `toSearchableArray` Method Generation**: Automatically defines the required `toSearchableArray()` method in
  the model, converting the model instance to a Typesense-compatible array for indexing based on the schema definition.

> **Note:** In the examples below, the `id` field is not defined because it is added automatically to the schema.
> The `created_at` is converted to timestamp automatically because is a Carbon instance and defined as Int32 (or Int64).
> The `toSearchableArray` method is generated automatically based on the schema.

> These defaults can be overridden by manually defining the `id` or `soft_deletes` fields in the schema, using the
> `transformTo` modifier on Carbon instance fields, or redefining the `toSearchableArray` method.

---

### Installation

```bash
composer require glimmer/typesense-searchable
```

## Usage

### Schema Definition in the Model

Define the schema by implementing a static `searchableSchema()` method in your model.
This method should return an array, where each key is a field name and each value is a Typesense compatible field type
with optional fields parameters and modifiers.

Example usage in the `User` model:

```php
use Glimmer\TypesenseSearchable\TypesenseSearchable;
use Glimmer\TypesenseSearchable\Contracts\HasTypesenseSchema;

class User extends Authenticatable implements HasTypesenseSchema
{
    use TypesenseSearchable;

    public static function searchableSchema(): array
    {
        return self::schemaAutocompletion([
            'name' => 'string|searchable', // or ['string', 'searchable']
            'email' => ['string', 'searchable:true'], // or 'string|searchable:true'
            'email_verified_at' => [
                'string', 
                'transformTo' => fn($verified) => $verified->timestamp
            ],
            'created_at' => 'int32',
            'symbols_to_index' => '@#',
            'default_sorting_field' => 'created_at',
        ]);
    }
}

```

> Note: The `schemaAutocompletion()` method is a wrapper that returns the same array it receives but allows the Laravel
> Idea plugin to make code completion for easier schema creation. It is not required to use it, but it is recommended.

### 1. Field Types

| Field Type   | Description                                         |
|--------------|-----------------------------------------------------|
| `auto`       | Typesense automatically determines the type of data |
| `string`     | Text-based field                                    |
| `string[]`   | Array of text-based values                          |
| `string*`    | Wildcard string                                     |
| `int32`      | 32-bit integer                                      |
| `int32[]`    | Array of 32-bit integers                            |
| `int64`      | 64-bit integer                                      |
| `int64[]`    | Array of 64-bit integers                            |
| `float`      | Floating point number                               |
| `float[]`    | Array of floats                                     |
| `bool`       | Boolean (true/false)                                |
| `bool[]`     | Array of booleans                                   |
| `geopoint`   | Geolocation point                                   |
| `geopoint[]` | Array of geolocation points                         |
| `geopolygon` | Geolocation polygon                                 |
| `object`     | JSON object                                         |
| `object[]`   | Array of JSON objects                               |
| `image`      | Image data                                          |

> Note: For additional details, refer to
> [Typesense field types documentation](https://typesense.org/docs/28.0/api/collections.html#field-types).

### 2. Field Parameters

Each field can be further configured with the following parameters.

| Parameter    | Description                                                                   |
|--------------|-------------------------------------------------------------------------------|
| `searchable` | Adds the field to default search queries (default: `false`)                   |
| `excluded`   | Excludes the field from `toSearchableArray` method (default: `false`)         |
| `optional`   | Marks the field as optional (default: `false`)                                |
| `store`      | Stores the field in Typesense for retrieval (default: `true`)                 |
| `sort`       | Enables sorting on the field (default: `true` for numbers, otherwise `false`) |
| `facet`      | Allows the field to be used for faceting (default: `false`)                   |
| `index`      | Indexes the field in Typesense (default: `true`)                              |
| `infix`      | Enables infix search on the field (default: `false`)                          |
| `stem`       | Enables stemming (root word matching) (default: `false`)                      |

> Parameters are treated as booleans, so they can be defined as:
> - `parameter` (which is equivalent to `parameter:true`)
> - `parameter:true`
> - `parameter:false`

> Note: Refer to
> [Typesense field parameters documentation](https://typesense.org/docs/28.0/api/collections.html#field-parameters)
> for more information.

### Field Modifiers

Field modifiers allow transformations (before sending the data to Typesense) or custom handling for fields within
Typesense.

| Modifier           | Description                                                                                                                 |
|--------------------|-----------------------------------------------------------------------------------------------------------------------------|
| `name`             | Renames the field in Typesense.                                                                                             |
| `locale`           | Sets the locale for the field (default is `en` that supports UTF-8).                                                        |
| `token_separators` | Characters to separate tokens during indexing, e.g., `@#`.                                                                  |
| `symbols_to_index` | Symbols to retain in the index, e.g., `%&`.                                                                                 |
| `transformTo`      | Function that receives the current field (if it exists) and returns a transformed value. Useful for custom transformations. |
| `valueFrom`        | Provides a value for the field, even if it doesn't exist. Can be anything but if it is a function, receives `$this` model.  |

Example:

```php
public static function searchableSchema(): array
{
    return [
        'name' => [
            'string',
            'searchable',
            'locale' => 'ja',
            'transformTo' => fn($name) => strtoupper($name)
        ],
        'customField' => [
            'string',
            'index:false',
            'valueFrom' => 'customValue',
            'token_separators' => '@#', // or ['@', '#']
            'symbols_to_index' => ['%', '&'], // or '%&'
        ],
        'customField2' => [
            'string',
            'store:false',
            'valueFrom' => fn($model) => $model->customValue2
        ],
    ];
}
```

> Note: Refer to
> [Typesense schema parameters documentation](https://typesense.org/docs/28.0/api/collections.html#schema-parameters)
> for more information about `locale`

### Schema Parameters as Fields

The following are the schema-level parameters, which control schema-wide settings in Typesense. They can be included
directly in the `searchableSchema()` as fields but will be treated as schema parameters.

| Parameter               | Description                                                                     |
|-------------------------|---------------------------------------------------------------------------------|
| `token_separators`      | Characters to separate tokens used during indexing, e.g., `@#`.                 |
| `symbols_to_index`      | Symbols to retain in the index, e.g., `%&`.                                     |
| `default_sorting_field` | Field used for default sorting; Typesense requires it to be `int32` or `float`. |
| `enable_nested_fields`  | Boolean to enable or disable nested fields support.                             |

**Example:**

```php
public static function searchableSchema(): array
{
    return [
        'name' => 'string|searchable',
        'token_separators' => '@#', // or ['@', '#']
        'symbols_to_index' => ['%', '&'], // or '%&'
        'default_sorting_field' => 'created_at',
        'enable_nested_fields' => true,
    ];
}
```

### Configuring `config/scout.php`

To integrate with Laravel Scout, configure the model's Typesense settings in the `config/scout.php` file:

```php
'model-settings' => [
    User::class => [
        'collection-schema' => [
            'fields' => User::typesenseFieldsSchema(),
            ...User::typesenseExtraConfigurationsSchema(),
        ],
        'search-parameters' => [
            'query_by' => User::typesenseFieldsQueryBy(),
        ],
    ],
],
```

> `typesenseFieldsSchema()` Generates the Typesense `fields` schema array by parsing `searchableSchema()` and applying
> types, modifiers, and parameters.

> `typesenseExtraConfigurationsSchema()` Generates the Typesense schema parameters by parsing `searchableSchema()` and
> extracting schema-level parameters.

> `typesenseFieldsQueryBy()` Generates an array of searchable fields for `query_by` in Typesense, based on fields marked
> as `searchable`.

Or if you are not manually modifying anything else in the `collection-schema` you may just use this shorter option:

```php
'model-settings' => [
    User::class => [
        'collection-schema' => User::typesenseCollectionSchema(),
        'search-parameters' => [
            'query_by' => User::typesenseFieldsQueryBy(),
        ],
    ],
],
```

> `typesenseCollectionSchema()` Returns the entire collection schema for Typesense, including schema fields
> `typesenseSchemaFields()` and schema extra configurations `typesenseSchemaExtraConfigurations()` combined.
