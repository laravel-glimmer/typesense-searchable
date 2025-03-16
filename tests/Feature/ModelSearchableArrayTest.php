<?php

use Glimmer\TypesenseSearchable\Tests\Stubs\Models\User;

it('generates a correct searchable array', function () {
    $now = now();
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@doe.com',
        'password' => 'password',
        'email_verified_at' => now(),
    ]);

    expect($user->toSearchableArray())
        ->toMatchArray([
            'id' => '1',
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'custom_name' => 'John Doe',
            'email_verified_at' => "$now->timestamp",
            'created_at' => $user->created_at->timestamp,
        ])
        ->not
        ->toHaveKey('updated_at');
});
