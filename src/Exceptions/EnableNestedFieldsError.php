<?php

namespace Glimmer\TypesenseSearchable\Exceptions;

use Exception;

class EnableNestedFieldsError extends Exception
{
    public static function mustBeABoolean($model): static
    {
        return new static("$model - `enable_nested_fields` must be a boolean.");
    }
}
