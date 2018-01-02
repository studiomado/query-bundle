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

    public static function box(array $params) : Parameter
    {
        $fieldName          = $params['fieldName'];
        $filterAndOperator  = $params['filterAndOperator'];
        $op                 = $params['op'];
        $operator           = $params['operator'];
        $salt               = $params['salt'];
        $value              = $params['value'];

        if (isset($operator['substitution_pattern'])) {
            $isSingleValue = isset($filterAndOperator[1])
                && $op->isListOrNlist();

            if ($isSingleValue) {
                $value = str_replace(
                    '{string}',
                    $value,
                    $operator['substitution_pattern']
                );
            } else {
                $value = explode(',', $value);
            }
        }

        return new self(
            'field_' . $fieldName . $salt,
            $value
        );
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
