<?php

namespace Glimmer\TypesenseSearchable\Enums;

use Glimmer\TypesenseSearchable\Exceptions\DefaultSortingFieldError;
use Glimmer\TypesenseSearchable\Exceptions\EnableNestedFieldsError;
use Glimmer\TypesenseSearchable\Exceptions\FieldParameterError;
use Glimmer\TypesenseSearchable\Support\FieldParser;
use Glimmer\TypesenseSearchable\Support\SchemaParser;
use Glimmer\TypesenseSearchable\Support\TypeChecker;
use Glimmer\TypesenseSearchable\Traits\EnumCasesAsArray;

/**
 * Schema parameters supported and extracted from Typesense documentation.
 *
 * @see https://typesense.org/docs/28.0/api/collections.html#schema-parameters
 */
enum SchemaParameter: string
{
    use EnumCasesAsArray;

    /** Modifies how all the schema fields data/values are separated/split when they are being indexed. */
    case TokenSeparators = 'token_separators';
    /** Modifies which symbols should be indexed and not be ignored by Typesense by default on all schema fields. */
    case SymbolsToIndex = 'symbols_to_index';
    /** Defines by which field the results must be sorted. Only supports `int32` or `float` */
    case DefaultSortingField = 'default_sorting_field';
    /** Enables nested field support in the schema. */
    case EnableNestedFields = 'enable_nested_fields';

    /**
     * @throws DefaultSortingFieldError
     * @throws FieldParameterError
     * @throws EnableNestedFieldsError
     */
    public function parse($value, $model, $type = null): string|array|bool
    {
        return match ($this) {
            self::TokenSeparators, self::SymbolsToIndex => FieldParser::charsToArray($value),
            self::DefaultSortingField => SchemaParser::onlyOneField(SchemaParser::typeIsSortable($value, $type,
                $model), $model),
            self::EnableNestedFields => TypeChecker::boolean($value, $model, $this->value, ''),
        };
    }
}
