<?php

namespace Spatie\OpeningHours\Helpers;

class Arr
{
    /**
     * @param array    $array
     * @param callable $callback
     * @return array
     */
    public static function map(array $array, callable $callback)
    {
        $keys = array_keys($array);

        $items = array_map($callback, $array, $keys);

        return array_combine($keys, $items);
    }

    /**
     * @param      $array
     * @param      $key
     * @param null $default
     * @return null
     */
    public static function pull(&$array, $key, $default = null)
    {
        $value = isset($array[$key]) ? $array[$key] : $default;

        unset($array[$key]);

        return $value;
    }

    /**
     * @param array $array
     * @return array
     */
    public static function mirror(array $array)
    {
        return array_combine($array, $array);
    }

    /**
     * @param array $array
     * @return array
     */
    public static function createUniquePairs(array $array)
    {
        $pairs = [];

        while ($a = array_shift($array)) {
            foreach ($array as $b) {
                $pairs[] = [$a, $b];
            }
        }

        return $pairs;
    }
}
