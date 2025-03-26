<?php

use Glimmer\TypesenseSearchable\Tests\Stubs\Models\User;

$expectedFieldSchema = [
    ['name' => 'name', 'type' => 'string', 'infix' => false],
    ['name' => 'email', 'type' => 'string', 'locale' => 'ja'],
    ['name' => 'email_verified_at', 'type' => 'string', 'symbols_to_index' => ['/', '?']],
    ['name' => 'created_at', 'type' => 'int32'],
    ['name' => 'custom_name', 'type' => 'string', 'token_separators' => ['@', '#']],
    ['name' => 'id', 'type' => 'string'],
    ['name' => '__soft_deleted', 'type' => 'int32', 'optional' => true],
];

$expectedExtraConfigurationsSchema = [
    'token_separators' => ['@', '#'],
    'symbols_to_index' => ['%', '&'],
    'enable_nested_fields' => false,
    'default_sorting_field' => 'created_at',
];

$expectedDefaultQueryBy = 'name,email';

it('generates a correct typesense fields schema', function () use ($expectedFieldSchema) {
    expect(User::typesenseFieldsSchema())->toBe($expectedFieldSchema);
});

it('generates a correct typesense extra configurations schema', function () use ($expectedExtraConfigurationsSchema) {
    expect(User::typesenseExtraConfigurationsSchema())->toBe($expectedExtraConfigurationsSchema);
});

it('generates a correct typesense collection schema', function () use (
    $expectedExtraConfigurationsSchema,
    $expectedFieldSchema
) {
    expect(User::typesenseCollectionSchema())->toBe([
        'fields' => $expectedFieldSchema,
        ...$expectedExtraConfigurationsSchema,
    ]);
});

it('generates a correct default query_by string', function () use ($expectedDefaultQueryBy) {
    expect(User::typesenseFieldsQueryBy())->toBe($expectedDefaultQueryBy);
});

it('excludes update_at from collection schema', function () {
    expect(collect(User::typesenseFieldsSchema())->contains(fn ($item) => $item['name'] === 'updated_at'))->toBeFalse();
});
