<?php

use Mado\QueryBundle\Queries\Options\QueryOptionsBuilder;
use Mado\QueryBundle\Queries\QueryBuilderOptions;
use PHPUnit\Framework\TestCase;

class QueryOptionsBuilderTest extends TestCase
{
    public function testShouldProvideSetterAndGettersForEntityAlias()
    {
        $this->builder = new QueryOptionsBuilder();
        $this->builder->setEntityAlias($alias = 'asdf');
        $this->assertEquals(
            $alias,
            $this->builder->getEntityAlias()
        );
    }

    /** @expectedException \RuntimeException */
    public function testRequireEntityAliasDefinition()
    {
        $this->builder = new QueryOptionsBuilder();
        $this->builder->ensureEntityAliasIsDefined();
    }

    public function testShouldBuildOptionsFromEmtpyRequest()
    {
        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes->expects($this->once())
            ->method('all')
            ->willReturn([]);
        $this->request->query = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->query->expects($this->at(0))->method('get')->with('filtering', [])->willReturn([]);
        $this->request->query->expects($this->at(1))->method('get')->with('filtering_or', [])->willReturn([]);
        $this->request->query->expects($this->at(2))->method('get')->with('sorting', [])->willReturn([]);
        $this->request->query->expects($this->at(3))->method('get')->with('printing', [])->willReturn([]);
        $this->request->query->expects($this->at(4))->method('get')->with('rel', '')->willReturn([]);
        $this->request->query->expects($this->at(5))->method('get')->with('page', '')->willReturn([]);
        $this->request->query->expects($this->at(6))->method('get')->with('select', 'asdf')->willReturn([]);
        $this->request->query->expects($this->at(7))->method('get')->with('filtering', '')->willReturn([]);
        $this->request->query->expects($this->at(8))->method('get')->with('limit', '')->willReturn([]);

        $this->builder = new QueryOptionsBuilder();
        $this->builder->setEntityAlias($alias = 'asdf');
        $options = $this->builder->builderFromRequest($this->request);

        $this->assertEquals(
            QueryBuilderOptions::fromArray([
                'filtering' => [],
                'orFilters' => [],
                'limit' => [],
                'page' => [],
                'filters' => [],
                'orFiltering' => [],
                'sorting' => [],
                'rel' => [],
                'printing' => [],
                'select' => [],
            ]),
            $options
        );
    }

    public function testFilteringOrIsConvertedInBothOriltersAndOrfiltering()
    {
        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes->expects($this->once())
            ->method('all')
            ->willReturn([]);
        $this->request->query = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->query->expects($this->at(0))->method('get')->with('filtering', [])->willReturn([]);
        $this->request->query->expects($this->at(1))->method('get')->with('filtering_or', [])->willReturn([
            'foo' => 'bar',
        ]);
        $this->request->query->expects($this->at(2))->method('get')->with('sorting', [])->willReturn([]);
        $this->request->query->expects($this->at(3))->method('get')->with('printing', [])->willReturn([]);
        $this->request->query->expects($this->at(4))->method('get')->with('rel', '')->willReturn([]);
        $this->request->query->expects($this->at(5))->method('get')->with('page', '')->willReturn([]);
        $this->request->query->expects($this->at(6))->method('get')->with('select', 'asdf')->willReturn([]);
        $this->request->query->expects($this->at(7))->method('get')->with('filtering', '')->willReturn([]);
        $this->request->query->expects($this->at(8))->method('get')->with('limit', '')->willReturn([]);

        $this->builder = new QueryOptionsBuilder();
        $this->builder->setEntityAlias($alias = 'asdf');
        $options = $this->builder->builderFromRequest($this->request);

        $this->assertEquals(
            QueryBuilderOptions::fromArray([
                'filtering' => [],
                'orFilters' => [
                    'foo' => 'bar',
                ],
                'limit' => [],
                'page' => [],
                'filters' => [],
                'orFiltering' => [
                    'foo' => 'bar',
                ],
                'sorting' => [],
                'rel' => [],
                'printing' => [],
                'select' => [],
            ]),
            $options
        );
    }

    public function testAdjustOrFilteringWheneverIsAnArray()
    {
        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes->expects($this->once())
            ->method('all')
            ->willReturn([]);
        $this->request->query = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->query->expects($this->at(0))->method('get')->with('filtering', [])->willReturn([]);
        $this->request->query->expects($this->at(1))->method('get')->with('filtering_or', [])->willReturn([
            'foo' => [
                'bar',
                'foo',
            ]
        ]);
        $this->request->query->expects($this->at(2))->method('get')->with('sorting', [])->willReturn([]);
        $this->request->query->expects($this->at(3))->method('get')->with('printing', [])->willReturn([]);
        $this->request->query->expects($this->at(4))->method('get')->with('rel', '')->willReturn([]);
        $this->request->query->expects($this->at(5))->method('get')->with('page', '')->willReturn([]);
        $this->request->query->expects($this->at(6))->method('get')->with('select', 'asdf')->willReturn([]);
        $this->request->query->expects($this->at(7))->method('get')->with('filtering', '')->willReturn([]);
        $this->request->query->expects($this->at(8))->method('get')->with('limit', '')->willReturn([]);

        $this->builder = new QueryOptionsBuilder();
        $this->builder->setEntityAlias($alias = 'asdf');
        $options = $this->builder->builderFromRequest($this->request);

        $this->assertEquals(
            QueryBuilderOptions::fromArray([
                'filtering' => [],
                'orFilters' => [
                    '0|0' => 'bar',
                    '1|1' => 'foo',
                ],
                'limit' => [],
                'page' => [],
                'filters' => [],
                'orFiltering' => [
                    '0|0' => 'bar',
                    '1|1' => 'foo',
                ],
                'sorting' => [],
                'rel' => [],
                'printing' => [],
                'select' => [],
            ]),
            $options
        );
    }

    public function testShouldBuildOptionsFromEmptyRequestAndCustomFilters()
    {
        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        //$this->request->attributes->expects($this->once())
            //->method('all')
            //->willReturn([]);
        $this->request->query = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->query->expects($this->at(0))->method('get')->with('filtering', [])->willReturn([]);
        $this->request->query->expects($this->at(1))->method('get')->with('filtering_or', [])->willReturn([]);
        $this->request->query->expects($this->at(2))->method('get')->with('sorting', [])->willReturn([]);
        $this->request->query->expects($this->at(3))->method('get')->with('printing', [])->willReturn([]);
        $this->request->query->expects($this->at(4))->method('get')->with('rel', '')->willReturn([]);
        $this->request->query->expects($this->at(5))->method('get')->with('page', '')->willReturn([]);
        $this->request->query->expects($this->at(6))->method('get')->with('select', 'asdf')->willReturn([]);
        $this->request->query->expects($this->at(7))->method('get')->with('filtering', '')->willReturn([]);
        $this->request->query->expects($this->at(8))->method('get')->with('limit', '')->willReturn([]);

        $this->builder = new QueryOptionsBuilder();
        $this->builder->setEntityAlias($alias = 'asdf');
        $options = $this->builder->buildFromRequestAndCustomFilter($this->request, [
            'custom' => 'filter',
        ]);

        $this->assertEquals(
            QueryBuilderOptions::fromArray([
                'filtering' => [],
                'orFilters' => [],
                'limit' => [],
                'page' => [],
                'filters' => [
                    'custom' => 'filter',
                ],
                '_route_params' => null,
                'sorting' => [],
                'rel' => [],
                'printing' => [],
                'select' => [],
                'justCount' => null,
                'id' => null,
                '_route' => null,
            ]),
            $options
        );
    }

    public function testShouldBuildQueryWithOrfiltersAndCustomFilters()
    {
        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        //$this->request->attributes->expects($this->once())
            //->method('all')
            //->willReturn([]);
        $this->request->query = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->query->expects($this->at(0))->method('get')->with('filtering', [])->willReturn([]);
        $this->request->query->expects($this->at(1))->method('get')->with('filtering_or', [])->willReturn([
            'foo' => 'bar',
        ]);
        $this->request->query->expects($this->at(2))->method('get')->with('sorting', [])->willReturn([]);
        $this->request->query->expects($this->at(3))->method('get')->with('printing', [])->willReturn([]);
        $this->request->query->expects($this->at(4))->method('get')->with('rel', '')->willReturn([]);
        $this->request->query->expects($this->at(5))->method('get')->with('page', '')->willReturn([]);
        $this->request->query->expects($this->at(6))->method('get')->with('select', 'asdf')->willReturn([]);
        $this->request->query->expects($this->at(7))->method('get')->with('filtering', '')->willReturn([]);
        $this->request->query->expects($this->at(8))->method('get')->with('limit', '')->willReturn([]);

        $this->builder = new QueryOptionsBuilder();
        $this->builder->setEntityAlias($alias = 'asdf');
        $options = $this->builder->buildFromRequestAndCustomFilter($this->request, [
            'custom' => 'filter',
        ]);

        $this->assertEquals(
            QueryBuilderOptions::fromArray([
                'filtering' => [],
                'orFilters' => [
                    'foo' => 'bar',
                ],
                'limit' => [],
                'page' => [],
                'filters' => [
                    'custom' => 'filter',
                ],
                '_route_params' => null,
                'sorting' => [],
                'rel' => [],
                'printing' => [],
                'select' => [],
                'justCount' => null,
                'id' => null,
                '_route' => null,
            ]),
            $options
        );
    }

    public function testBuildOptionsFromRequestWithOrFiltersAsArrayAndCustomFilters()
    {
        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        //$this->request->attributes->expects($this->once())
            //->method('all')
            //->willReturn([]);
        $this->request->query = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->query->expects($this->at(0))->method('get')->with('filtering', [])->willReturn([]);
        $this->request->query->expects($this->at(1))->method('get')->with('filtering_or', [])->willReturn([
            'foo' => [
                'bar',
                'atro',
            ]
        ]);
        $this->request->query->expects($this->at(2))->method('get')->with('sorting', [])->willReturn([]);
        $this->request->query->expects($this->at(3))->method('get')->with('printing', [])->willReturn([]);
        $this->request->query->expects($this->at(4))->method('get')->with('rel', '')->willReturn([]);
        $this->request->query->expects($this->at(5))->method('get')->with('page', '')->willReturn([]);
        $this->request->query->expects($this->at(6))->method('get')->with('select', 'asdf')->willReturn([]);
        $this->request->query->expects($this->at(7))->method('get')->with('filtering', '')->willReturn([]);
        $this->request->query->expects($this->at(8))->method('get')->with('limit', '')->willReturn([]);

        $this->builder = new QueryOptionsBuilder();
        $this->builder->setEntityAlias($alias = 'asdf');
        $options = $this->builder->buildFromRequestAndCustomFilter($this->request, [
            'custom' => 'filter',
        ]);

        $this->assertEquals(
            QueryBuilderOptions::fromArray([
                'filtering' => [],
                'orFilters' => [
                    '0|0' => 'bar',
                    '1|1' => 'atro',
                ],
                'limit' => [],
                'page' => [],
                'filters' => [
                    'custom' => 'filter',
                ],
                '_route_params' => null,
                'sorting' => [],
                'rel' => [],
                'printing' => [],
                'select' => [],
                'justCount' => null,
                'id' => null,
                '_route' => null,
            ]),
            $options
        );
    }

    public function testShouldBuildQueryWithOrfiltersAndCustomOrFilters()
    {
        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        //$this->request->attributes->expects($this->once())
            //->method('all')
            //->willReturn([]);
        $this->request->query = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->query->expects($this->at(0))->method('get')->with('filtering', [])->willReturn([]);
        $this->request->query->expects($this->at(1))->method('get')->with('filtering_or', [])->willReturn([]);
        $this->request->query->expects($this->at(2))->method('get')->with('sorting', [])->willReturn([]);
        $this->request->query->expects($this->at(3))->method('get')->with('printing', [])->willReturn([]);
        $this->request->query->expects($this->at(4))->method('get')->with('rel', '')->willReturn([]);
        $this->request->query->expects($this->at(5))->method('get')->with('page', '')->willReturn([]);
        $this->request->query->expects($this->at(6))->method('get')->with('select', 'asdf')->willReturn([]);
        $this->request->query->expects($this->at(7))->method('get')->with('filtering', '')->willReturn([]);
        $this->request->query->expects($this->at(8))->method('get')->with('limit', '')->willReturn([]);

        $this->builder = new QueryOptionsBuilder();
        $this->builder->setEntityAlias($alias = 'asdf');
        $options = $this->builder->buildForOrFilter($this->request, [
            'fizz' => 'buzz',
        ]);

        $this->assertEquals(
            QueryBuilderOptions::fromArray([
                'filtering' => [],
                'orFilters' => [
                    'fizz' => 'buzz',
                ],
                'limit' => [],
                'page' => [],
                'filters' => [],
                '_route_params' => null,
                'sorting' => [],
                'rel' => [],
                'printing' => [],
                'select' => [],
                'id' => null,
                '_route' => null,
            ]),
            $options
        );
    }

    public function testShouldBuildQueryWithOrfiltersAsArrayAndCustomOrFilters()
    {
        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        //$this->request->attributes->expects($this->once())
            //->method('all')
            //->willReturn([]);
        $this->request->query = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->query->expects($this->at(0))->method('get')->with('filtering', [])->willReturn([]);
        $this->request->query->expects($this->at(1))->method('get')->with('filtering_or', [])->willReturn([
            'a' => [
                'b',
                'c',
            ],
        ]);
        $this->request->query->expects($this->at(2))->method('get')->with('sorting', [])->willReturn([]);
        $this->request->query->expects($this->at(3))->method('get')->with('printing', [])->willReturn([]);
        $this->request->query->expects($this->at(4))->method('get')->with('rel', '')->willReturn([]);
        $this->request->query->expects($this->at(5))->method('get')->with('page', '')->willReturn([]);
        $this->request->query->expects($this->at(6))->method('get')->with('select', 'asdf')->willReturn([]);
        $this->request->query->expects($this->at(7))->method('get')->with('filtering', '')->willReturn([]);
        $this->request->query->expects($this->at(8))->method('get')->with('limit', '')->willReturn([]);

        $this->builder = new QueryOptionsBuilder();
        $this->builder->setEntityAlias($alias = 'asdf');
        $options = $this->builder->buildForOrFilter($this->request, [ ]);

        $this->assertEquals(
            QueryBuilderOptions::fromArray([
                'filtering' => [],
                'orFilters' => [
                    '0|0' => 'b',
                    '1|1' => 'c',
                ],
                'limit' => [],
                'page' => [],
                'filters' => [],
                '_route_params' => null,
                'sorting' => [],
                'rel' => [],
                'printing' => [],
                'select' => [],
                'id' => null,
                '_route' => null,
            ]),
            $options
        );
    }

    /** @expectedException Mado\QueryBundle\Exceptions\InvalidFiltersException */
    public function testInvalidFilterThrownException()
    {
        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->attributes = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->query = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->query->expects($this->at(0))->method('get')->with('filtering', [])->willReturn('');
        $this->request->query->expects($this->at(1))->method('get')->with('filtering_or', [])->willReturn([]);
        $this->request->query->expects($this->at(2))->method('get')->with('sorting', [])->willReturn([]);
        $this->request->query->expects($this->at(3))->method('get')->with('printing', [])->willReturn([]);
        $this->request->query->expects($this->at(4))->method('get')->with('rel', '')->willReturn([]);
        $this->request->query->expects($this->at(5))->method('get')->with('page', '')->willReturn([]);
        $this->request->query->expects($this->at(6))->method('get')->with('select', 'asdf')->willReturn([]);
        $this->request->query->expects($this->at(7))->method('get')->with('filtering', '')->willReturn([]);
        $this->request->query->expects($this->at(8))->method('get')->with('limit', '')->willReturn([]);

        $this->builder = new QueryOptionsBuilder();
        $this->builder->setEntityAlias($alias = 'asdf');
        $this->builder->buildFromRequestAndCustomFilter($this->request, []);
    }
}
