<?php

use PHPUnit\Framework\TestCase;
use Mado\QueryBundle\Services\Pager;

class PagerTest extends TestCase
{
    private $pagerfantaFactoryMock;

    private $router;

    private $pager;

    private $queryBuilder;

    private $queryBuilderOptions;

    private $pagerfantaBuilder;

    private $doctrineOrmAdapter;

    private $query;

    public function setUp()
    {
        $this->pagerfantaFactoryMock = $this
            ->getMockBuilder(\Hateoas\Representation\Factory\PagerfantaFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->router = $this
            ->getMockBuilder(\Mado\QueryBundle\Services\Router::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryBuilder = $this
            ->getMockBuilder(\Doctrine\ORM\QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryBuilderOptions = $this
            ->getMockBuilder(\Mado\QueryBundle\Queries\QueryBuilderOptions::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pagerfantaBuilder = $this
            ->getMockBuilder(\Mado\QueryBundle\Objects\PagerfantaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineOrmAdapter = $this
            ->getMockBuilder(\Pagerfanta\Adapter\DoctrineORMAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->query = $this
            ->getMockBuilder(\Doctrine\ORM\AbstractQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pagerfanta = $this
            ->getMockBuilder(\Pagerfanta\Pagerfanta::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineOrmAdapter
            ->method('getQuery')
            ->willReturn($this->query);

        $this->queryBuilderOptions
            ->expects($this->at(0))
            ->method('get')
            ->with('limit')
            ->willReturn(1);

        $this->queryBuilderOptions
            ->expects($this->at(1))
            ->method('get')
            ->with('page')
            ->willReturn(1);

        $this->pagerfantaBuilder
            ->method('create')
            ->willReturn($this->pagerfanta);

        $this->pagerfantaBuilder
            ->method('createRepresentation')
            ->willReturn(true);

        $this->pager = new Pager();
    }

    public function testPaginateWithoutCache()
    {
        $this->query
            ->expects($this->never())
            ->method('useResultCache');

        $routeName = 'foo';
        $useCache = null;

        $this->pager->setRouter($this->router);

        $this->pager->paginateResults(
            $this->queryBuilderOptions,
            $this->doctrineOrmAdapter,
            $this->pagerfantaBuilder,
            $routeName,
            $useCache
        );
    }

    public function testPaginateWithCache()
    {
        $this->query
            ->expects($this->once())
            ->method('useResultCache');

        $routeName = 'foo';
        $useCache = true;

        $this->pager->setRouter($this->router);

        $this->pager->paginateResults(
            $this->queryBuilderOptions,
            $this->doctrineOrmAdapter,
            $this->pagerfantaBuilder,
            $routeName,
            $useCache
        );
    }
}
