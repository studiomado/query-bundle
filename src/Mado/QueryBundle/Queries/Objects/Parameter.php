<?php

namespace Mado\QueryBundle\Queries\Objects;

final class Parameter
{
    private $key;

    private $value;

    public static function withKeyAndValue(
        string $key,
        string $value
    ) : Parameter {
        return new self($key, $value);
    }

    private function __construct($key, $value)
    {
        $this->key   = $key;
        $this->value = $value;
    }

    public function getKey() : string
    {
        return $this->key;
    }

    public function getValue() : string
    {
        return $this->value;
    }
}
