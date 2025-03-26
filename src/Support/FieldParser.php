<?php

namespace Glimmer\TypesenseSearchable\Support;

use Illuminate\Support\Arr;

class FieldParser
{
    public static function toArray(string|array|bool $input): array|bool
    {
        if (is_bool($input)) {
            return $input;
        }

        if (is_string($input)) {
            $input = explode('|', $input);
        }

        return Arr::collapse(Arr::map($input, fn ($item, $key) => self::colonExplode($item, $key)));
    }

    private static function colonExplode($input, $key): array
    {
        if (is_string($input) && str_contains($input, ':')) {
            [$parameter, $value] = explode(':', $input);

            return [$parameter => $value];
        }

        return [$key => $input];
    }

    public static function charsToArray(string|array $input): array
    {
        if (is_string($input)) {
            return mb_str_split($input);
        }

        return array_merge(...array_map(fn ($item) => is_string($item) ? mb_str_split($item) : [$item], $input));
    }

    public static function paramName($key, $value): string
    {
        return is_numeric($key) ? $value : $key;
    }
}
