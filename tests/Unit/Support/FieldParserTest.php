<?php

use Glimmer\TypesenseSearchable\Support\FieldParser;

it('parses field options to array', function () {
    expect(FieldParser::toArray('string|searchable'))->toBe(['string', 'searchable']);
});

it('explodes colon options to associative array', function () {
    expect(FieldParser::toArray('infix:true'))->toBe(['infix' => 'true']);
});

it('converts chars string/array to separated chars array', function () {
    expect(FieldParser::charsToArray('#&'))->toBe(['#', '&'])
        ->and(FieldParser::charsToArray(['#&', '/(']))->toBe(['#', '&', '/', '(']);
});

it('returns the correct parameter name', function () {
    expect(FieldParser::paramName(0, 'searchable'))->toBe('searchable')
        ->and(FieldParser::paramName('searchable', true))->toBe('searchable');
});
