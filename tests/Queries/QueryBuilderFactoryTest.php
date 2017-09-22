<?php

namespace Mado\QueryBundle\Tests\Objects;

use Mado\QueryBundle\Queries\QueryBuilderFactory;
use Mado\QueryBundle\Vocabulary\Operators;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mado\QueryBundle\Queries\QueryBuilderFactory
 */
class QueryBuilderFactoryTest extends TestCase
{
    /**
     * @covers \Mado\QueryBundle\Queries\QueryBuilderFactory::__construct
     * @covers \Mado\QueryBundle\Queries\QueryBuilderFactory::getAvailableFilters
     */
    public function test()
    {
        $this->manager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilderFactory = new QueryBuilderFactory($this->manager);

        $this->assertEquals(
            array_keys(Operators::getAll()),
            $queryBuilderFactory->getAvailableFilters()
        );
    }
}
