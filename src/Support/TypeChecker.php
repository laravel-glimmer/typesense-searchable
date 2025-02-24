<?php

namespace Glimmer\TypesenseSearchable\Support;

use Closure;
use Glimmer\TypesenseSearchable\Exceptions\EnableNestedFieldsError;
use Glimmer\TypesenseSearchable\Exceptions\FieldParameterError;
use Glimmer\TypesenseSearchable\Exceptions\SchemaFieldModifiersError;

class TypeChecker
{
    /**
     * @throws FieldParameterError
     * @throws EnableNestedFieldsError
     */
    public static function boolean($value, $model, $field, $parameter = null): bool
    {
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if (! is_bool($value)) {
            $parameter ? throw FieldParameterError::mustBeABoolean($model, $field,
                $parameter) : throw EnableNestedFieldsError::mustBeABoolean($model);
        }

        return $value;
    }

    /**
     * @throws SchemaFieldModifiersError
     */
    public static function isNotACallable($value, $model, $field, $parameter): mixed
    {
        if (! $value instanceof Closure && is_callable($value)) {
            throw SchemaFieldModifiersError::modifierMustBeAClosure($model, $field, $parameter);
        }

        return $value;
    }

    /**
     * @throws SchemaFieldModifiersError
     */
    public static function isAClosure($value, $model, $field, $parameter): Closure
    {
        if ($value instanceof Closure) {
            return $value;
        }

        throw SchemaFieldModifiersError::modifierMustBeAClosure($model, $field, $parameter);
    }
}
