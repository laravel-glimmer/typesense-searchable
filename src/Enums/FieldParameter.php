<?php

namespace Glimmer\TypesenseSearchable\Enums;

use Glimmer\TypesenseSearchable\Exceptions\EnableNestedFieldsError;
use Glimmer\TypesenseSearchable\Exceptions\FieldParameterError;
use Glimmer\TypesenseSearchable\Support\TypeChecker;
use Glimmer\TypesenseSearchable\Traits\EnumCasesAsArray;

/**
 * Field parameters supported and extracted from Typesense documentation.
 *
 * All these parameters can be suffixed with `:true` or `:false` to set the value in the schema.
 *
 * @see https://typesense.org/docs/28.0/api/collections.html#field-parameters
 */
enum FieldParameter: string
{
    use EnumCasesAsArray;

    /**
     * Enables the field to be in the default `query_by` list.
     *
     * Actually, this parameter is not an official Typesense field parameter, but it's used here as a parameter for convenience.
     * As all these parameters are resolved to a boolean, it will be excluded when creating the schema.
     */
    case Searchable = 'searchable';
    /**
     * Marks the field to be excluded from the schema on Typesense.
     *
     * Actually, this parameter is not an official Typesense field parameter, but it's used here as a parameter for convenience.
     */
    case Excluded = 'excluded';
    /** Marks the field as optional (nullable) on Typesense. Default: `false` */
    case Optional = 'optional';
    /** Marks the field to be stored on Typesense. Default: `true` */
    case Store = 'store';
    /** Enables sorting on the field on Typesense. Default: `true` for numbers, otherwise `false` */
    case Sort = 'sort';
    /** Enables the field to be used for faceting. Default: `false` */
    case Facet = 'facet';
    /** Marks the field to be indexed on Typesense. Default: `true` */
    case Index = 'index';
    /** Enables infix search on the field on Typesense. Default: `false` */
    case Infix = 'infix';
    /** Enables stemming the field words by Typesense. Default: `false` */
    case Stem = 'stem';

    /**
     * @throws FieldParameterError
     * @throws EnableNestedFieldsError
     */
    public function parse($value, $model, $field): bool
    {
        return match ($this) {
            default => TypeChecker::boolean($value, $model, $field, $this->value),
        };
    }
}
