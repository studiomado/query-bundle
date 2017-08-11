<?php

namespace Mado\QueryBundle\Vocabulary;

class Operators
{
    const DEFAULT_OPERATOR = 'eq';

    private static $operatorMap = [
        'eq' => [
            'meta' => '=',
        ],
        'neq' => [
            'meta' => '!=',
        ],
        'gt' => [
            'meta' => '>',
        ],
        'gte' => [
            'meta' => '>=',
        ],
        'lt' => [
            'meta' => '<',
        ],
        'lte' => [
            'meta' => '<=',
        ],
        'startswith' => [
            'meta' => 'LIKE',
            'substitution_pattern' => '{string}%'
        ],
        'contains' => [
            'meta' => 'LIKE',
            'substitution_pattern' => '%{string}%'
        ],
        'notcontains' => [
            'meta' => 'NOT LIKE',
            'substitution_pattern' => '%{string}%'
        ],
        'endswith' => [
            'meta' => 'LIKE',
            'substitution_pattern' => '%{string}'
        ],
        'list' => [
            'meta' => 'IN',
            'substitution_pattern' => '({string})',
        ],
        'field_eq' => [
            'meta' => '=',
        ],
    ];

    public static function getAll()
    {
        return static::$operatorMap;
    }

    public static function getDefaultOperator()
    {
        return static::getAll()[static::DEFAULT_OPERATOR];
    }

    public static function get($operator)
    {
        return static::getAll()[$operator];
    }
}
