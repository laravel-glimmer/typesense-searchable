<?php

namespace Glimmer\TypesenseSearchable\Traits;

trait EnumCasesAsArray
{
    public static function toArray(): array
    {
        return array_reduce(self::cases(), function ($carry, $item) {
            $carry[$item->name] = $item->value ?? $item->name;

            return $carry;
        }, []);
    }
}
