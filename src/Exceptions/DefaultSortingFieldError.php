<?php

namespace Glimmer\TypesenseSearchable\Exceptions;

use Exception;

class DefaultSortingFieldError extends Exception
{
    public static function onlyOneField($model): static
    {
        return new static("$model: Only one field can be used for default sorting.");
    }

    public static function fieldNotInSchema($model, $field): static
    {
        return new static("$model: `$field` - field must defined in the schema.");
    }

    public static function fieldNotSortable($model, $field): static
    {
        return new static("$model: `$field` - field must be int32 or float type.");
    }
}
