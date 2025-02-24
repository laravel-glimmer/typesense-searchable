<?php

namespace Glimmer\TypesenseSearchable\Exceptions;

use Exception;

class SchemaFieldTypeError extends Exception
{
    public static function onlyOneType($model, $field): static
    {
        return new static("$model: `$field` - must exactly have one type of field.");
    }

    public static function cannotBeQueried($model, $field, $type): static
    {
        return new static("$model: `$field` - field type `$type` is not supported by Typesense to be queried.");
    }

    public static function typeNotSupported($model, $field, $type): static
    {
        return new static("$model: `$field` - field type `$type` is not supported by Typesense.");
    }
}
