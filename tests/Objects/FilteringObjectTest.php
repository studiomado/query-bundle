<?php

namespace Mado\QueryBundle\Tests\Objects;

use Mado\QueryBundle\Objects\FilteringObject;
use PHPUnit\Framework\TestCase;

class FilteringObjectTest extends TestCase
{
    public function testContainsOperatorInSecondPartOfFilter()
    {
        $fo = FilteringObject::fromFilter('foo|op');

        $this->assertSame(true, $fo->hasOperator());
    }

    public function testHasNoOperatorIfFilterDoesNotContainThePipe()
    {
        $fo = FilteringObject::fromFilter('foo');

        $this->assertSame(false, $fo->hasOperator());
    }

    public function testProvideOperatorsSign()
    {
        $fo = FilteringObject::fromFilter('foo');

        $this->assertSame('=', $fo->getOperatorSign());
    }

    public function testProvideOperators()
    {
        $fo = FilteringObject::fromFilter('foo|eq');

        $this->assertSame('eq', $fo->getOperator());
    }

    public function testProvideFilterName()
    {
        $fo = FilteringObject::fromFilter('foo');

        $this->assertSame('foo', $fo->getFieldName());
    }

    public function testKnowsIfOperatorIsListOneOrNot()
    {
        $fo = FilteringObject::fromFilter('foo|list');

        $this->assertSame(true, $fo->isListOperator());
    }

    public function testKnowsIfOperatorIsFieldEqualsOrNot()
    {
        $fo = FilteringObject::fromFilter('foo|field_eq');

        $this->assertSame(true, $fo->isFieldEqualsOperator());
    }

    public function testKnowsIfHaveNotAnOperator()
    {
        $fo = FilteringObject::fromFilter('foo');

        $this->assertSame(false, $fo->hasOperator());
    }
}
