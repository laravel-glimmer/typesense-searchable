<?php

namespace Glimmer\TypesenseSearchable;

use Carbon\Carbon;
use Closure;
use Exception;
use Glimmer\TypesenseSearchable\Enums\FieldModifier;
use Glimmer\TypesenseSearchable\Enums\FieldParameter;
use Glimmer\TypesenseSearchable\Enums\FieldType;
use Glimmer\TypesenseSearchable\Enums\SchemaParameter;
use Glimmer\TypesenseSearchable\Exceptions\DefaultSortingFieldError;
use Glimmer\TypesenseSearchable\Exceptions\EnableNestedFieldsError;
use Glimmer\TypesenseSearchable\Exceptions\FieldParameterError;
use Glimmer\TypesenseSearchable\Exceptions\SchemaFieldModifiersError;
use Glimmer\TypesenseSearchable\Exceptions\SchemaFieldTypeError;
use Glimmer\TypesenseSearchable\Exceptions\TypesenseSchemaMustReturnAnArray;
use Glimmer\TypesenseSearchable\Support\FieldParser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;
use ReflectionClass;

/**
 * Trait TypesenseSearchable
 *
 * Enables a model to be searchable with Typesense by providing methods to generate the necessary schema
 * and configuration options directly from the model's defined `typesenseSchema()` method, rather than from a separate configuration.
 * This trait also attempts to convert values to their specified Typesense types as defined in the schema.
 *
 * @mixin Model
 */
trait TypesenseSearchable
{
    use Searchable;

    /*
     * These static arrays serve as a cache to avoid redundant processing within the same request each time a model is saved to Typesense.
     * This approach is particularly effective in a Laravel Octane environment, where the application only boots once.
     */
    protected static ?array $parsedSchema = null;

    protected static array $fieldTypes = [];

    protected static array $fieldModifiers = [];

    /**
     * Combines `typesenseFields()` and `typesenseExtraConfigurationsSchema()` to generate the full collection schema.
     */
    public static function typesenseCollectionSchema(): array
    {
        return ['fields' => self::typesenseFieldsSchema(), ...self::typesenseExtraConfigurationsSchema()];
    }

    /**
     * Generates the Typesense schema fields based on the model's defined `typesenseSchema()`.
     */
    public static function typesenseFieldsSchema(): array
    {
        return array_values(Arr::map(self::getSchemaFields(), function ($options, $field) {
            $modifiers = self::getFieldModifiers($field, $options);

            return [
                'name' => $modifiers['name'] ?? $field,
                'type' => self::getFieldType($field, $options),
                ...Arr::whereNotNull([
                    'locale' => $modifiers['locale'] ?? null,
                    'token_separators' => $modifiers['token_separators'] ?? null,
                    'symbols_to_index' => $modifiers['symbols_to_index'] ?? null,
                ]),
                ...array_diff_key(self::getFieldParameters($field, $options), array_flip(['searchable'])),
            ];
        }));
    }

    /**
     * Returns the model's schema without the schema parameters that are not fields.
     */
    protected static function getSchemaFields(): array
    {
        return Arr::except(self::parsedSchema(), SchemaParameter::toArray());
    }

    /**
     * Ensures the schema is an array and converts the schema options to an array.
     * Additionally, adds the primary key and soft delete fields if they are not defined in the schema.
     */
    protected static function parsedSchema(): array
    {
        if (self::$parsedSchema == null) {
            self::$parsedSchema = self::typesenseSchema();
            $model = (new ReflectionClass(self::class))->newInstanceWithoutConstructor();

            if ( ! Arr::exists(self::$parsedSchema, $model->getScoutKeyName())) {
                self::$parsedSchema[$model->getScoutKeyName()] = [
                    'string', 'valueFrom' => fn() => $this->getScoutKey(),
                ];
            }

            if (self::usesSoftDelete() && ! Arr::exists(self::$parsedSchema, $model->getDeletedAtColumn())) {
                self::$parsedSchema[$model->getDeletedAtColumn()] = [
                    'int32', 'name:__soft_deleted', 'optional',
                    'transformTo' => fn($deleted_at) => $deleted_at?->timestamp,
                ];
            }

            self::$parsedSchema = Arr::map(self::$parsedSchema, fn($field) => FieldParser::toArray($field));
        }

        return self::$parsedSchema;
    }

    /**
     * Gets the field modifiers for the specified field and validates them.
     *
     * @throws SchemaFieldModifiersError
     */
    protected static function getFieldModifiers(string $field, array $options): array
    {
        if ( ! Arr::exists(self::$fieldModifiers, $field)) {
            $modifiers = Arr::only($options, FieldModifier::toArray());

            if (count($modifiers) > count(array_unique($modifiers, SORT_REGULAR))) {
                throw SchemaFieldModifiersError::noDuplicatedModifiers(self::class, $field);
            }

            self::$fieldModifiers[$field] = Arr::map($modifiers,
                fn($value, $modifier) => FieldModifier::from($modifier)->parse($value, self::class, $field));
        }

        return self::$fieldModifiers[$field];
    }

    /**
     * Gets the field type for the specified field and validates it is exactly one valid field type.
     *
     * @throws SchemaFieldTypeError
     */
    protected static function getFieldType(string $field, array $options): string
    {
        if ( ! Arr::exists(self::$fieldTypes, $field)) {
            $types = array_intersect(
                Arr::except($options, array_merge(FieldModifier::toArray(), FieldParameter::toArray())),
                FieldType::toArray()
            );

            if (count($types) !== 1) {
                throw SchemaFieldTypeError::onlyOneType(self::class, $field);
            }

            self::$fieldTypes[$field] = $types[0];
        }

        return self::$fieldTypes[$field];
    }

    /**
     * Gets the field parameters for the specified field and validates them.
     *
     * @throws FieldParameterError
     * @throws EnableNestedFieldsError
     */
    protected static function getFieldParameters(string $field, array $options): array
    {
        $parameters = Arr::where($options, fn($value, $key) => in_array($value, FieldParameter::toArray()) ||
            in_array($key, FieldParameter::toArray()));

        return Arr::mapWithKeys($parameters, fn($parameterOrBoolean, $keyOrParameter) => [
            FieldParser::paramName($keyOrParameter, $parameterOrBoolean) => FieldParameter::tryFrom($keyOrParameter)
                    ?->parse($parameterOrBoolean, self::class, $field) ?? true,
        ]);
    }

    /**
     * Generates the extra configurations for the collection schema based on the model's defined `typesenseSchema()`.
     */
    public static function typesenseExtraConfigurationsSchema(): array
    {
        return Arr::map(self::getAdditionalSchema(), function ($value, $config) {
            if ($config == 'default_sorting_field') {
                try {
                    $value = $value[0];
                    $fieldType = self::getFieldType($value, self::getSchemaFields()[$value]);
                } catch (Exception) {
                    throw DefaultSortingFieldError::fieldNotInSchema(self::class, $value);
                }
            }

            return SchemaParameter::from($config)->parse($value, self::class, $fieldType ?? null);
        });
    }

    /**
     * Returns the additional schema parameters that are not fields.
     */
    protected static function getAdditionalSchema(): array
    {
        return array_intersect_key(self::parsedSchema(), array_flip(SchemaParameter::toArray()));
    }

    /**
     * Generates the default query_by parameters based on the model's defined `typesenseSchema()`.
     *
     * @throws SchemaFieldTypeError
     */
    public static function typesenseFieldsQueryBy(): string
    {
        return collect(self::getSchemaFields())->filter(function ($options, $field) {
            return self::getFieldParameters($field, $options)['searchable'] ?? false;
        })->each(function ($options, $field) {
            return FieldType::from(self::getFieldType($field, $options))->canBeQueried(self::class, $field);
        })->keys()->implode(',');
    }

    /**
     * Initialize the trait and validate model `typesenseSchema()` is defined.
     * Laravel calls this method automatically when the trait is used.
     * If running on Octane, it will call `typesenseSchemaFields()`
     * so the schema is cached before the first request.
     *
     * @throws TypesenseSchemaMustReturnAnArray
     */
    public static function bootTypesenseSearchable(): void
    {
        if ( ! method_exists(self::class, 'typesenseSchema')) {
            throw TypesenseSchemaMustReturnAnArray::noMethod(self::class);
        }

        if ( ! is_array(self::typesenseSchema())) {
            throw TypesenseSchemaMustReturnAnArray::noArray(self::class);
        }

        if (isset($_SERVER['LARAVEL_OCTANE']) && ((int) $_SERVER['LARAVEL_OCTANE'] === 1)) {
            self::typesenseFieldsSchema();
        }
    }

    /**
     * Returns the same array provided as a parameter. This method is just used to autocomplete the schema method as
     * I couldn't make Laravel Idea to autocomplete the schema method as it does with casts.
     *
     * @param  array<string, string|array>  $array
     */
    protected static function schemaAutocompletion(array $array): array
    {
        return $array;
    }

    /**
     * Converts the model to an array that Typesense can index.
     */
    public function toSearchableArray(): array
    {
        return array_merge(
            Arr::except($this->toArray(), array_keys(self::getSchemaFields())),
            $this->transformFieldsBasedOnSchema(),
        );
    }

    /**
     * Transforms the fields based on the model's defined `typesenseSchema()`.
     */
    protected function transformFieldsBasedOnSchema(): array
    {
        return Arr::mapWithKeys(self::getSchemaFields(), function ($options, $field) {
            $modifiers = self::getFieldModifiers($field, $options);

            return [
                    $modifiers['name'] ?? $field => self::castFieldToType(
                    $field,
                    self::getFieldType($field, $options),
                    $modifiers['transformTo'] ?? null,
                    $modifiers['valueFrom'] ?? null
                ),
            ];
        });
    }

    /**
     * Transforms and gets the value of the field before casting it to the specified Typesense type.
     * Additionally, handles Carbon instances.
     */
    protected function castFieldToType(
        string $fieldName,
        string $type,
        ?Closure $transformTo,
        mixed $valueFrom
    ): mixed {
        // $field = $valueFrom instanceof Closure ? $valueFrom->bindTo($this)($this) : ($valueFrom ?? $this->$fieldName);
        // $field = $transformTo ? $transformTo->bindTo($this)($field) : $field;

        // If the valueFrom is a Closure, bind it to the model instance and call it.
        // Else-if the valueFrom is set, use it as the field value.
        // Else, use the field value from the model.
        if ($valueFrom instanceof Closure) {
            $field = $valueFrom->bindTo($this)($this);
        } else {
            $field = $valueFrom ?? $this->$fieldName;
        }

        // If transformTo is set, bind it to the model instance and call it.
        if ($transformTo) {
            $field = $transformTo->bindTo($this)($field);
        }

        if ($transformTo == null && ($type == 'int32' || $type == 'int64' || $type == 'auto') && $field instanceof Carbon) {
            $field = $field->timestamp;
        }

        return FieldType::from($type)->cast($field);
    }
}
