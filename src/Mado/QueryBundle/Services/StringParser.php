<?php

namespace Mado\QueryBundle\Services;

class StringParser
{
    public function numberOfTokens(string $string)
    {
        return count($this->exploded($string));
    }

    private function exploded(string $string)
    {
        return explode('_', $string);
    }

    public function tokenize(string $string, int $position)
    {
        return $this->exploded($string)[$position];
    }

    public function camelize($string)
    {
        $camelized = $this->tokenize($string, 0);

        for ($i = 1; $i < $this->numberOfTokens($string); $i++) {
            $camelized .= ucfirst($this->tokenize($string, $i));
        }

        return $camelized;
    }
}
