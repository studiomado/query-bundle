<?php

use Mado\QueryBundle\Services\IdsChecker;
use PHPUnit\Framework\TestCase;

final class IdsCheckerTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Oops! Missing GenericFilter object!!!
     */
    public function testValidationNeedsGenericFilterInjection()
    {
        $filter = new IdsChecker();
        $filter->validateIds();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Oops! Filtering is missing!!!
     */
    public function testEnsureThatFilteringIsConfigured()
    {
        $this->genericFilter = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\Filter')
            ->disableOriginalConstructor()
            ->getMock();

        $filter = new IdsChecker();
        $filter->setObjectFilter($this->genericFilter);
        $filter->validateIds();
    }

    public function testByDefaultFilterKeyWontBeAltered()
    {
        $this->genericFilter = \Mado\QueryBundle\Objects\Filter::box([
            'ids' => [ 'ciao' => [1, 23, 42], ],
            'path' => '_embedded.foo.bar',
        ]);

        $this->filtering = [
            'filtering' => 'bar',
        ];

        $filter = new IdsChecker();
        $filter->setObjectFilter($this->genericFilter);
        $filter->setFiltering($this->filtering);
        $filter->setFilterKey('foo');
        $filter->validateIds();

        $this->assertEquals('foo', $filter->getFilterKey());
    }

    public function testIdsAreIntersectionBetweenQueryStringAndAditionalFilterList()
    {
        $this->genericFilter = \Mado\QueryBundle\Objects\Filter::box([
            'ids' => [ 'ciao' => [1, 23, 42], ],
            'path' => '_embedded.foo.bar',
        ]);

        $this->filtering = [
            'filtering' => '1,23',
        ];

        $filter = new IdsChecker();
        $filter->setObjectFilter($this->genericFilter);
        $filter->setFiltering($this->filtering);
        $filter->setFilterKey('foo');
        $filter->setAdditionalFiltersIds('1,23');
        $filter->validateIds();

        $this->assertEquals('1,23', $filter->getFinalFilterIds());
        $this->assertSame(true, $filter->idsAreSubset());
    }

    /**
     * @expectedException \Mado\QueryBundle\Exceptions\ForbiddenContentException
     */
    public function testIdsAreIntersectionBetweenQueryStringAndAditionalFilterLiasdfadst()
    {
        $this->genericFilter = \Mado\QueryBundle\Objects\Filter::box([
            'ids' => [ 'list' => [1, 23, 42], ],
            'path' => '_embedded.foo.bar',
        ]);

        $this->filtering = [
            'filtering' => '1,23',
        ];

        $filter = new IdsChecker();
        $filter->setObjectFilter($this->genericFilter);
        $filter->setFiltering($this->filtering);
        $filter->setFilterKey('foo');
        $filter->setAdditionalFiltersIds('666');
        $filter->validateIds();
    }

    public function testNlist()
    {
        $this->genericFilter = \Mado\QueryBundle\Objects\Filter::box([
            'ids' => [ 'nlist' => [1, 23, 42], ],
            'path' => '_embedded.foo.bar',
        ]);

        $this->filtering = [
            'ciao|list' => '1,3,4',
        ];

        $filter = new IdsChecker();
        $filter->setObjectFilter($this->genericFilter);
        $filter->setFiltering($this->filtering);
        $filter->setFilterKey('ciao');
        $filter->setAdditionalFiltersIds('23');
        $filter->validateIds();

        $this->assertEquals('1,3,4', $filter->getFinalFilterIds());
    }
}
