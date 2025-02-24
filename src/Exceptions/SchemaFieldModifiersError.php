<?php

namespace Glimmer\TypesenseSearchable\Exceptions;

use Exception;

class SchemaFieldModifiersError extends Exception
{
    public static function noDuplicatedModifiers($model, $field): static
    {
        return new static("$model: `$field` - field modifiers must not be repeated.");
    }

    public static function modifierMustBeAClosure($model, $field, $modifier): static
    {
        return new static("$model: `$field` - `$modifier` must be a closure.");
    }
}
