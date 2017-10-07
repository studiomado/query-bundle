<?php

namespace Mado\QueryBundle\Tests\Services;

use Mado\QueryBundle\Dictionary\Operators;
use PHPUnit\Framework\TestCase;

class OpertatorTest extends TestCase
{
    public function testGetOperators()
    {
        $operators = Operators::getOperators();

        $expectedOperators = [
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

        $this->assertEquals($expectedOperators, $operators);
    }


}
