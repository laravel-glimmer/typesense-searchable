<?php

namespace Glimmer\TypesenseSearchable\Support;

use Glimmer\TypesenseSearchable\Enums\FieldType;
use Glimmer\TypesenseSearchable\Exceptions\DefaultSortingFieldError;

class SchemaParser
{
    /**
     * @throws DefaultSortingFieldError
     */
    public static function onlyOneField(array|string $input, $model): string
    {
        if ((is_array($input) && count($input) > 1) || (is_string($input) && count(explode(',', $input)) > 1)) {
            throw DefaultSortingFieldError::onlyOneField($model);
        }

        return is_array($input) ? (string) $input[0] : $input;
    }

    /**
     * @throws DefaultSortingFieldError
     */
    public static function typeIsSortable(string $input, string $type, $model): string
    {
        FieldType::from($type)->canBeSorted($model, $input);

        return $input;
    }
}
