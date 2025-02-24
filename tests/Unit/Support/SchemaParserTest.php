<?php

use Glimmer\TypesenseSearchable\Exceptions\DefaultSortingFieldError;
use Glimmer\TypesenseSearchable\Support\SchemaParser;

it('validates only contains one field', function () {
    expect(SchemaParser::onlyOneField(['name'], 'App\Models\User'))
        ->toBe('name')
        ->and(SchemaParser::onlyOneField('name', 'App\Models\User'))
        ->toBe('name');
});

it('throws exception when there is more than one field', function () {
    expect(fn () => SchemaParser::onlyOneField(['name', 'email'], 'App\Models\User'))
        ->toThrow(DefaultSortingFieldError::class)
        ->and(fn () => SchemaParser::onlyOneField('name,email', 'App\Models\User'))
        ->toThrow(DefaultSortingFieldError::class);
});

it('validates field type is sortable', function () {
    expect(SchemaParser::typeIsSortable('name', 'int32', 'App\Models\User'))
        ->toBe('name');
});

it('throws exception when field is not sortable', function () {
    expect(fn () => SchemaParser::typeIsSortable('name', 'string', 'App\Models\User'))
        ->toThrow(DefaultSortingFieldError::class);
});
