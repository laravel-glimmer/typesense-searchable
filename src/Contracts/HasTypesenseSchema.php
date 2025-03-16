<?php

namespace Glimmer\TypesenseSearchable\Contracts;

interface HasTypesenseSchema
{
    public static function searchableSchema(): array;
}
