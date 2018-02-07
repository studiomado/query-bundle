<?php

use Mado\QueryBundle\Objects\Filter;
use Mado\QueryBundle\Services\IdsChecker;
use PHPUnit\Framework\TestCase;

final class IdsCheckerTest extends TestCase
{
    /**
     * @expectedException \Mado\QueryBundle\Exceptions\ForbiddenContentException
     */
    public function testQueryStringCantContainsInvalidIds()
    {
        $querystringIds = '1';
        $additionalFiltersIds = '2,3';

        $filter = Filter::box([
            'ids'  => ['list' => []],
            'path' => '_embedded.foo.bar',
        ]);

        $cheker = new IdsChecker();
        $cheker->setObjectFilter($filter);
        $cheker->setFiltering(['filtering' => $querystringIds]);

        $cheker->validateIds();
    }

    public function testValidIdsOverwriteFilteringWithIntersection()
    {
        $querystringIds = '2';

        $filter = Filter::box([
            'ids'  => ['list' => [2,3]],
            'path' => '_embedded.foo.bar',
        ]);

        $cheker = new IdsChecker();
        $cheker->setObjectFilter($filter);
        $cheker->setFiltering(['filtering' => $querystringIds]);

        $cheker->validateIds();

        $this->assertEquals('2', $cheker->getFinalFilterIds());
    }

    public function testAdditionalFilterInBlacList()
    {
        $querystringIds = '2';
        $additionalFiltersIds = '66';

        $additionalFilterObject = Filter::box([
            'ids'  => ['nlist' => []],
            'path' => '_embedded.foo.bar',
        ]);

        $cheker = new IdsChecker();
        $cheker->setObjectFilter($additionalFilterObject);
        $cheker->setFiltering(['field' => $querystringIds]);

        $cheker->validateIds();

        $this->assertEquals('2', $cheker->getFinalFilterIds());
    }

    public function testProvideListOfRightIds()
    {
        $querystringIds = '2';
        $additionalFiltersIds = '66';

        $additionalFilterObject = Filter::box([
            'ids'  => ['nlist' => []],
            'path' => '_embedded.foo.bar',
        ]);

        $cheker = new IdsChecker();
        $cheker->setObjectFilter($additionalFilterObject);
        $cheker->setFiltering(['field' => $querystringIds]);

        $cheker->validateIds();

        $this->assertEquals('_embedded.foo.bar.id|list', $cheker->getFilterKey());
    }

    public function testOverwriteFilterKeyIfNecessary()
    {
        $querystringIds = '2';
        $additionalFiltersIds = '66';

        $additionalFilterObject = Filter::box([
            'ids'  => ['nlist' => []],
            'path' => '_embedded.foo.bar',
        ]);

        $cheker = new IdsChecker();
        $cheker->setObjectFilter($additionalFilterObject);
        $cheker->setFilterKey('foo');
        $cheker->setFiltering(['field' => $querystringIds]);

        $cheker->validateIds();

        $this->assertNotEquals('foo', $cheker->getFilterKey());
    }
}
