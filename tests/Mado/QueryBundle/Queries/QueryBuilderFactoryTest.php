<?php

namespace Mado\QueryBundle\Tests\Queries;

use Mado\QueryBundle\Queries\QueryBuilderFactory;
use PHPUnit\Framework\TestCase;
use Mado\QueryBundle\Dictionary;

class QueryBuilderFactoryTest extends TestCase
{
    public function testGetAvailableFilters()
    {
        $entityManager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilderFactory = new QueryBuilderFactory($entityManager);

        $expectedFilters = Dictionary::getOperators();

        $availableFilters = $queryBuilderFactory->getValueAvailableFilters();

        $this->assertEquals($expectedFilters, $availableFilters);
    }
}
