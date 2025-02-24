<?php

namespace Glimmer\TypesenseSearchable\Enums;

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
}
