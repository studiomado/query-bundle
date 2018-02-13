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
        $filter = Filter::box([
            'ids'  => ['list' => [1, 2, 3]],
            'path' => '_embedded.foo.bar',
        ]);

        $cheker = new IdsChecker();
        $cheker->setObjectFilter($filter);
        $cheker->setFiltering(['ciao|list' => '4']);
        $cheker->validateIds();
    }

    public function testValidIdsOverwriteFilteringWithIntersection()
    {
        $filter = Filter::box([
            'ids'  => ['list' => [2,3]],
            'path' => '_embedded.foo.bar',
        ]);

        $cheker = new IdsChecker();
        $cheker->setObjectFilter($filter);
        $cheker->setFiltering(['ciao|list' => '2']);

        $cheker->validateIds();

        $this->assertEquals('2', $cheker->getFinalFilterIds());
    }

    public function testAdditionalFilterInBlacList()
    {
        $filter = Filter::box([
            'ids'  => ['nlist' => [666]],
            'path' => '_embedded.foo.bar',
        ]);

        $cheker = new IdsChecker();
        $cheker->setObjectFilter($filter);
        $cheker->setFiltering(['ciao|list' => '2']);

        $cheker->validateIds();

        $this->assertEquals('2', $cheker->getFinalFilterIds());
    }

    public function testProvideListOfRightIds()
    {
        $additionalFilterObject = Filter::box([
            'ids'  => ['nlist' => [666]],
            'path' => '_embedded.foo.bar',
        ]);

        $cheker = new IdsChecker();
        $cheker->setObjectFilter($additionalFilterObject);
        $cheker->setFiltering(['ciao|list' => '2']);

        $cheker->validateIds();

        $this->assertEquals('_embedded.foo.bar.id|list', $cheker->getFilterKey());
    }

    public function testOverwriteFilterKeyIfNecessary()
    {
        $additionalFilterObject = Filter::box([
            'ids'  => ['nlist' => [666]],
            'path' => '_embedded.foo.bar',
        ]);

        $cheker = new IdsChecker();
        $cheker->setObjectFilter($additionalFilterObject);
        $cheker->setFilterKey('foo');
        $cheker->setFiltering(['ciao|list' => '2']);

        $cheker->validateIds();

        $this->assertNotEquals('foo', $cheker->getFilterKey());
    }
}
