<?php

namespace Glimmer\TypesenseSearchable\Exceptions;

use Exception;

class SearchableSchemaMustReturnAnArray extends Exception
{
    public static function noMethod($model): static
    {
        return new static("$model: `searchableSchema` method must be defined in the model and return an array.");
    }

    public static function noArray($model): static
    {
        return new static("$model: `searchableSchema` method must return an array.");
    }
}
