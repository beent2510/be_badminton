<?php

namespace App\Core;

use Illuminate\Support\Str;

class Util
{
    public static function convertKeysToSnakeCase(array $array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $snakeKey = is_int($key) ? $key : Str::snake($key);
            if (is_array($value)) {
                $value = self::convertKeysToSnakeCase($value);
            }
            $result[$snakeKey] = $value;
        }
        return $result;
    }

    public static function convertKeysToCamelCase(array $array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $camelKey = is_int($key) ? $key : Str::camel($key);
            if (is_array($value)) {
                $value = self::convertKeysToCamelCase($value);
            }
            $result[$camelKey] = $value;
        }
        return $result;
    }
}
