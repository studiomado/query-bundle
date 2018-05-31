<?php

namespace Mado\QueryBundle;

class Dictionary
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
        'nlist' => [
            'meta' => 'NOT IN',
            'substitution_pattern' => '({string})',
        ],
        'field_eq' => [
            'meta' => '=',
        ],
        'isnull' => [
            'meta' => 'IS NULL',
        ],
        'isnotnull' => [
            'meta' => 'IS NOT NULL',
        ],
        'listcontains' => [
            'meta' => 'LIKE',
            'substitution_pattern' => '({string})',
        ],
    ];

    public static function getOperators()
    {
        return self::$operatorMap;
    }
}

