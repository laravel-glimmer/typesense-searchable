<?php

use Glimmer\TypesenseSearchable\Exceptions\EnableNestedFieldsError;
use Glimmer\TypesenseSearchable\Exceptions\FieldParameterError;
use Glimmer\TypesenseSearchable\Exceptions\SchemaFieldModifiersError;
use Glimmer\TypesenseSearchable\Support\TypeChecker;

it('validates values is a boolean', function () {
    expect(TypeChecker::boolean(true, 'model', 'field'))
        ->toBeTrue()
        ->and(TypeChecker::boolean(1, 'model', 'field'))
        ->toBeTrue()
        ->and(TypeChecker::boolean('true', 'model', 'field'))
        ->toBeTrue();
});

it('throws an exception when is not a boolean', function () {
    expect(fn () => TypeChecker::boolean('string', 'model', 'field'))
        ->toThrow(EnableNestedFieldsError::class)
        ->and(fn () => TypeChecker::boolean('string', 'model', 'field', 'parameter'))
        ->toThrow(FieldParameterError::class);
});

it('validates value is not a callable', function () {
    $closure = fn () => 1;
    expect(TypeChecker::isNotACallable('string', 'model', 'field', 'parameter'))
        ->toBe('string')
        ->and(TypeChecker::isNotACallable($closure, 'model', 'field', 'parameter'))
        ->toBe($closure);
});

it('throws an exception when is a callable', function () {
    function callableTest()
    {
        return 1;
    }

    expect(fn () => TypeChecker::isNotACallable('callableTest', 'model', 'field', 'parameter'))
        ->toThrow(SchemaFieldModifiersError::class);
});

it('validates value is a closure', function () {
    expect(TypeChecker::isAClosure(fn () => 1, 'model', 'field', 'parameter'))
        ->toBeInstanceOf(Closure::class);
});

it('throws an exception when is not a closure', function () {
    expect(fn () => TypeChecker::isAClosure('string', 'model', 'field', 'parameter'))
        ->toThrow(SchemaFieldModifiersError::class);
});
