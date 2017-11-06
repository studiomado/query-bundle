<?php

namespace Mado\QueryBundle\Tests\Queries;

use Mado\QueryBundle\Queries\QueryBuilderFactory;
use PHPUnit\Framework\TestCase;
use Mado\QueryBundle\Dictionary\Operators;

class QueryBuilderFactoryTest extends TestCase
{
    public function testGetAvailableFilters()
    {
        $entityManager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilderFactory = new QueryBuilderFactory($entityManager);

        $expectedFilters = Operators::getOperators();

        $availableFilters = $queryBuilderFactory->getValueAvailableFilters();

        $this->assertEquals($expectedFilters, $availableFilters);
    }
}