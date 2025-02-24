<?php

namespace Glimmer\TypesenseSearchable\Enums;

use Glimmer\TypesenseSearchable\Traits\EnumCasesAsArray;

/**
 * Some field parameters supported and extracted from Typesense documentation.
 *
 * These field parameters are not boolean as the ones in FieldParameter enum. So they are not suffixed with `:true` or `:false`.
 * So they are treated differently by the Trait, and that's why they are separated in a different Enum.
 *
 * @see https://typesense.org/docs/28.0/api/collections.html#field-parameters
 */
enum FieldModifier: string
{
    use EnumCasesAsArray;

    /** Modifies the name of the field that is used when is saved in Typesense. Default: model column/field name */
    case Name = 'name';
    /** Modifies the locale that Typesense uses to index this field. Default: `en` */
    case Locale = 'locale';
    /**
     * Modifies how the field data/values are separated/split when is being indexed.
     *
     * Typesense included support for field-level control on v28.0.
     */
    case TokenSeparators = 'token_separators';
    /**
     * Modifies which field symbols should be indexed and not be ignored by Typesense by default.
     *
     * Typesense included support for field-level control on v28.0.
     *
     * For more information about what symbols are ignored by default, see: https://typesense.org/docs/28.0/api/collections.html#schema-parameters at `symbols_to_index`
     */
    case SymbolsToIndex = 'symbols_to_index';
    /** Transforms the original value into any other thing specified in a Closure before saving it on Typesense. */
    case TransformTo = 'transformTo';
    /** Overrides/Gets the value of the field to be saved on Typesense from the specified value or Closure. */
    case ValueFrom = 'valueFrom';
}
