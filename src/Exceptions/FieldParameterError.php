<?php

namespace Glimmer\TypesenseSearchable\Exceptions;

use Exception;

class FieldParameterError extends Exception
{
    public static function mustBeABoolean($model, $field, $parameter): static
    {
        return new static("$model: `$field` - `$parameter` must be a boolean.\nExamples:\n\t- $parameter \n\t- $parameter:true\n\t- $parameter:false\n");
    }
}
