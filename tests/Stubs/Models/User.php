<?php

namespace Glimmer\TypesenseSearchable\Tests\Stubs\Models;

use Glimmer\TypesenseSearchable\Contracts\HasTypesenseSchema;
use Glimmer\TypesenseSearchable\TypesenseSearchable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements HasTypesenseSchema
{
    use TypesenseSearchable;

    protected $guarded = ['id'];

    public static function typesenseSchema(): array
    {
        return self::schemaAutocompletion([
            'name' => 'string|searchable|infix:false',
            'email' => ['string', 'searchable:true', 'locale:ja'],
            'email_verified_at' => [
                'string',
                'transformTo' => fn ($verified) => $verified->timestamp,
                'symbols_to_index' => ['/?'],
            ],
            'created_at' => 'int32',
            'custom_field' => [
                'string',
                'name' => 'custom_name',
                'valueFrom' => fn ($user) => $user->name,
                'token_separators' => '@#',
            ],
            'token_separators' => '@#',
            'symbols_to_index' => ['%', '&'],
            'enable_nested_fields' => false,
            'default_sorting_field' => 'created_at',
        ]);
    }

    public function shouldBeSearchable(): false
    {
        return false;
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
