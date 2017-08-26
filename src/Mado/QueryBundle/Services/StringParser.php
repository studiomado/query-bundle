<?php

namespace Mado\QueryBundle\Services;

class StringParser
{
    public static function numberOfTokens(string $string)
    {
        return count(self::exploded($string));
    }

    private static function exploded(string $string)
    {
        return explode('_', $string);
    }

    public static function tokenize(string $string, int $position)
    {
        return self::exploded($string)[$position];
    }

    public static function camelize($string)
    {
        $camelized = self::tokenize($string, 0);

        for ($i = 1; $i < self::numberOfTokens($string); $i++) {
            $camelized .= ucfirst(self::tokenize($string, $i));
        }

        return $camelized;
    }
}
