<?php

namespace Glimmer\TypesenseSearchable\Exceptions;

use Exception;

class TypesenseSchemaMustReturnAnArray extends Exception
{
    public static function noMethod($model): static
    {
        return new static("$model: `typesenseSchema` method must be defined in the model and return an array.");
    }

    public static function noArray($model): static
    {
        return new static("$model: `typesenseSchema` method must return an array.");
    }
}
