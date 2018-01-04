<?php

namespace Mado\QueryBundle\Queries\Objects;

/** @deprecated since version 2.2 will be removed in version 2.3 */
final class Operator
{
    public static function fromString(string $operatorName)
    {
        return new self($operatorName);
    }

    private function __construct(string $operator)
    {
        $this->operator = $operator;
    }

    public function isListOrNlist() : bool
    {
        return in_array($this->operator, ['list', 'nlist']);
    }
}
