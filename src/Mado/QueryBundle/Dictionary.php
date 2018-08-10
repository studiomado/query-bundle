<?php

namespace Mado\QueryBundle;

class Dictionary
{
    const DEFAULT_OPERATOR = 'eq';

    const NUMBER_EQUAL         = 'eq';
    const NUMBER_NOT_EQUAL     = 'neq';
    const NUMBER_GREATER       = 'gt';
    const NUMBER_GREATER_EQUAL = 'gte';
    const NUMBER_LITTLE        = 'lt';
    const NUMBER_LITTLE_EQUAL  = 'lte';

    const STRING_STARTS_WITH  = 'startswith';
    const STRING_CONTAINS     = 'contains';
    const STRING_NOT_CONTAINS = 'notcontains';
    const STRING_ENDS_WITH    = 'endswith';

    const FIELD_LIST        = 'list';
    const FIELD_NOT_IN_LIST = 'nlist';
    const FIELD_EQUALITY    = 'field_eq';

    private static $doctrineTypeToOperatorsMap = [

        'default' => [
            self::FIELD_LIST,
            self::FIELD_NOT_IN_LIST,
            self::FIELD_EQUALITY,
            self::NUMBER_EQUAL,
            self::NUMBER_NOT_EQUAL,
            self::NUMBER_GREATER,
            self::NUMBER_GREATER_EQUAL,
            self::NUMBER_LITTLE,
            self::NUMBER_LITTLE_EQUAL,
            self::STRING_STARTS_WITH,
            self::STRING_CONTAINS,
            self::STRING_NOT_CONTAINS,
            self::STRING_ENDS_WITH,
            'isnull',
            'isnotnull',
            'listcontains',
        ],

        'fields' => [
            self::FIELD_LIST,
            self::FIELD_NOT_IN_LIST,
            self::FIELD_EQUALITY,
        ],

        'integer' => [
            self::NUMBER_EQUAL,
            self::NUMBER_NOT_EQUAL,
            self::NUMBER_GREATER,
            self::NUMBER_GREATER_EQUAL,
            self::NUMBER_LITTLE,
            self::NUMBER_LITTLE_EQUAL,
        ],

        'string' => [
            self::STRING_STARTS_WITH,
            self::STRING_CONTAINS,
            self::STRING_NOT_CONTAINS,
            self::STRING_ENDS_WITH,
        ],

    ];

    private static $operatorMap = [

        self::NUMBER_EQUAL         => [ 'meta' => ' =' ],
        self::NUMBER_NOT_EQUAL     => [ 'meta' => '!=' ],
        self::NUMBER_GREATER       => [ 'meta' => '>'  ],
        self::NUMBER_GREATER_EQUAL => [ 'meta' => '>=' ],
        self::NUMBER_LITTLE        => [ 'meta' => '<'  ],
        self::NUMBER_LITTLE_EQUAL  => [ 'meta' => '<=' ],

        self::STRING_STARTS_WITH  => [ 'meta' => 'LIKE', 'substitution_pattern' => '{string}%' ],
        self::STRING_CONTAINS     => [ 'meta' => 'LIKE', 'substitution_pattern'     => '%{string}%' ],
        self::STRING_NOT_CONTAINS => [ 'meta' => 'NOT LIKE', 'substitution_pattern' => '%{string}%' ],
        self::STRING_ENDS_WITH    => [ 'meta' => 'LIKE', 'substitution_pattern' => '%{string}' ],

        self::FIELD_LIST        => [ 'meta' => 'IN', 'substitution_pattern'     => '({string})' ],
        self::FIELD_NOT_IN_LIST => [ 'meta' => 'NOT IN', 'substitution_pattern' => '({string})' ],
        self::FIELD_EQUALITY    => [ 'meta' => '=' ],

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

    public static function getPublicOperators()
    {
        return self::$doctrineTypeToOperatorsMap;
    }

    public static function getOperatorsFromDoctrineType(string $type)
    {
        try {
            self::ensureTypeIsDefined($type);
            return self::$doctrineTypeToOperatorsMap[$type];
        } catch (\Exception $e) {
            return self::$doctrineTypeToOperatorsMap['default'];
        }
    }

    public static function ensureTypeIsDefined($type)
    {
        if (!isset(self::$doctrineTypeToOperatorsMap[$type])) {
            throw new \RuntimeException(
                'Oops! Type "'.$type.'" is not yet defined.'
            );
        }
    }

    public static function isValidOperator($operator)
    {
        if (!isset(self::$operatorMap[$operator])) {
            throw new \RuntimeException(
                'Oops! Operator "' . $operator . '" is not yet defined.'
            );
        }
    }
}

