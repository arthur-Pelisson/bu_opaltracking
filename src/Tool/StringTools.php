<?php


namespace App\Tool;


class StringTools
{
    public static function is_null_or_empty($str): bool
    {
        return (!isset($str) || trim($str) === '');
    }

    public static function combine($paths): string
    {
        $all = array();
        foreach ($paths as $path) {

            foreach (explode(DIRECTORY_SEPARATOR, $path) as $segment) {
                if (empty($segment)) {
                    continue;
                }
                $all[] = $segment;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $all);
    }

}