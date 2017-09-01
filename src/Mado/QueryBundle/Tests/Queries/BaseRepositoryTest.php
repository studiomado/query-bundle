<?php

namespace Mado\QueryBundle\Tests\Objects;

use Mado\QueryBundle\Queries\QueryBuilderOptions;
use Mado\QueryBundle\Repositories\BaseRepository;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mado\QueryBundle\Repositories\BaseRepository
 */
class BaseRepositoryTest extends TestCase
{
    public function setUp()
    {
        $this->entityManager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->classMetaData = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $this->classMetaData->fieldMappings = [
            'foo' => 'bar',
        ];

        $this->classMetaData->name = 'fooo';

        $this->repository = new BaseRepository(
            $this->entityManager,
            $this->classMetaData
        );
    }

    /**
     * @covers Mado\QueryBundle\Queries\AbstractQuery::__construct
     * @covers Mado\QueryBundle\Repositories\BaseRepository::__construct
     * @covers Mado\QueryBundle\Repositories\BaseRepository::getEntityAlias
     */
    public function testProvideEntityAliasByFQCN()
    {
        $this->assertEquals(
            'classname',
            $this->repository->getEntityAlias('Mado\\QueryBundle\\Entity\\ClassName')
        );
    }

    /**
     * @covers Mado\QueryBundle\Queries\AbstractQuery::__construct
     * @covers Mado\QueryBundle\Queries\QueryBuilderOptions::__construct
     * @covers Mado\QueryBundle\Queries\QueryBuilderOptions::fromArray
     * @covers Mado\QueryBundle\Repositories\BaseRepository::__construct
     * @covers Mado\QueryBundle\Repositories\BaseRepository::getQueryBuilderOptions
     * @covers Mado\QueryBundle\Repositories\BaseRepository::getRequestAttributes
     * @covers Mado\QueryBundle\Repositories\BaseRepository::setQueryOptionsFromRequest
     * @covers Mado\QueryBundle\Repositories\BaseRepository::setRequest
     */
    public function testBuildOptionsViaRequest()
    {
        $this->attributeParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->setMethods(['all'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeParameterBag->expects($this->once())
            ->method('all')
            ->will($this->returnValue([
                // the collection of attributesd
            ]));

        $this->queryParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryParameterBag->method('get')
            ->will($this->returnValue([
                // the collection of attributesd
            ]));

        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes = $this->attributeParameterBag;
        $this->request->query = $this->queryParameterBag;

        $this->repository->setRequest($this->request);

        $this->assertEquals(
            QueryBuilderOptions::fromArray([
                'filtering' => [],
                'orFiltering' => [],
                'limit' => [],
                'page' => [],
                'filters' => [],
                'sorting' => [],
                'rel' => [],
                'printing' => [],
                'select' => [],
            ]),
            $this->repository->getQueryBuilderOptions()
        );
    }

    /**
     * @covers Mado\QueryBundle\Queries\AbstractQuery::__construct
     * @covers Mado\QueryBundle\Queries\QueryBuilderOptions::__construct
     * @covers Mado\QueryBundle\Queries\QueryBuilderOptions::fromArray
     * @covers Mado\QueryBundle\Repositories\BaseRepository::__construct
     * @covers Mado\QueryBundle\Repositories\BaseRepository::getRequestAttributes
     * @covers Mado\QueryBundle\Repositories\BaseRepository::setQueryOptionsFromRequest
     * @covers Mado\QueryBundle\Repositories\BaseRepository::getQueryBuilderOptions
     */
    public function testBuildQueryOptionsFromRequest()
    {
        $this->attributeParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->setMethods(['all'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeParameterBag->expects($this->once())
            ->method('all')
            ->will($this->returnValue([
                // the collection of attributesd
            ]));

        $this->queryParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->setMethods([
                'get',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryParameterBag->method('get')
            ->will($this->returnValue([
                // the collection of attributesd
            ]));

        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes = $this->attributeParameterBag;
        $this->request->query = $this->queryParameterBag;

        $this->repository->setQueryOptionsFromRequest($this->request);

        $this->assertEquals(
            QueryBuilderOptions::fromArray([
                'filtering' => [],
                'orFiltering' => [],
                'limit' => [],
                'page' => [],
                'filters' => [],
                'orFilters' => [],
                'sorting' => [],
                'rel' => [],
                'printing' => [],
                'select' => [],
            ]),
            $this->repository->getQueryBuilderOptions()
        );
    }

    /**
     * @covers Mado\QueryBundle\Queries\AbstractQuery::__construct
     * @covers Mado\QueryBundle\Queries\QueryBuilderOptions::__construct
     * @covers Mado\QueryBundle\Queries\QueryBuilderOptions::fromArray
     * @covers Mado\QueryBundle\Repositories\BaseRepository::__construct
     * @covers Mado\QueryBundle\Repositories\BaseRepository::getRequestAttributes
     * @covers Mado\QueryBundle\Repositories\BaseRepository::setQueryOptionsFromRequest
     * @covers Mado\QueryBundle\Repositories\BaseRepository::getQueryBuilderOptions
     */
    public function testBuildQueryOptionsFromRequestWithCustomFilter()
    {
        $this->attributeParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->setMethods(['all'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeParameterBag->expects($this->once())
            ->method('all')
            ->will($this->returnValue([
                // the collection of attributesd
            ]));

        $this->queryParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->setMethods([
                'get',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryParameterBag->method('get')
            ->will($this->returnValue([
                // the collection of attributesd
            ]));

        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes = $this->attributeParameterBag;
        $this->request->query = $this->queryParameterBag;

        $this->repository->setQueryOptionsFromRequest($this->request);

        $this->assertEquals(
            QueryBuilderOptions::fromArray([
                'filtering' => [],
                'orFiltering' => [],
                'limit' => [],
                'page' => [],
                'filters' => [],
                'sorting' => [],
                'rel' => [],
                'printing' => [],
                'select' => [],
            ]),
            $this->repository->getQueryBuilderOptions()
        );
    }

    /**
     * @covers Mado\QueryBundle\Queries\AbstractQuery::__construct
     * @covers Mado\QueryBundle\Queries\QueryBuilderOptions::__construct
     * @covers Mado\QueryBundle\Queries\QueryBuilderOptions::fromArray
     * @covers Mado\QueryBundle\Repositories\BaseRepository::__construct
     * @covers Mado\QueryBundle\Repositories\BaseRepository::getRequestAttributes
     * @covers Mado\QueryBundle\Repositories\BaseRepository::setQueryOptionsFromRequestWithCustomOrFilter
     * @covers Mado\QueryBundle\Repositories\BaseRepository::getQueryBuilderOptions
     */
    public function testBuildQueryOptionsFromRequestWithCustomOrFilter()
    {
        $this->attributeParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->setMethods([
                'all',
                'get',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeParameterBag->expects($this->once())
            ->method('all')
            ->will($this->returnValue([
                // the collection of attributesd
            ]));
        $this->attributeParameterBag->method('get')
            ->will($this->returnValue([
                // the collection of attributesd
            ]));

        $this->queryParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryParameterBag->method('get')
            ->will($this->returnValue([
                // the collection of attributesd
            ]));

        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes = $this->attributeParameterBag;
        $this->request->query = $this->queryParameterBag;

        $this->repository->setQueryOptionsFromRequestWithCustomOrFilter(
            $this->request,
            $orFilter = []
        );

        $this->assertEquals(
            QueryBuilderOptions::fromArray([
                '_route' => [],
                'customer_id' => [],
                'id' => [],
                '_route' => [],
                'filtering' => [],
                'limit' => [],
                'page' => [],
                'filters' => [],
                'orFilters' => [],
                'sorting' => [],
                'rel' => [],
                'printing' => [],
                'select' => [],
            ]),
            $this->repository->getQueryBuilderOptions()
        );
    }
}
