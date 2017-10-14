<?php

namespace Mado\QueryBundle\Tests\Queries;

use Mado\QueryBundle\Queries\QueryBuilderFactory;
use PHPUnit\Framework\TestCase;

class QueryBuilderFactoryTest extends TestCase
{
    public function testGetAvailableFilters()
    {
        $entityManager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilderFactory = new QueryBuilderFactory($entityManager);

        $expectedFilters = [
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

        $availableFilters = $queryBuilderFactory->getAvailableFilters();

        $this->assertEquals($expectedFilters, $availableFilters);
    }
}