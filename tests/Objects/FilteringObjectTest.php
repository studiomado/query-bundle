<?php

namespace Mado\QueryBundle\Tests\Objects;

use Mado\QueryBundle\Objects\FilteringObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mado\QueryBundle\Objects\FilteringObject
 */
class FilteringObjectTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::fromFilter
     * @covers ::hasOperator
     */
    public function testContainsOperatorInSecondPartOfFilter()
    {
        $fo = FilteringObject::fromFilter('foo|op');

        $this->assertSame(true, $fo->hasOperator());
    }

    /**
     * @covers ::__construct
     * @covers ::fromFilter
     * @covers ::hasOperator
     */
    public function testHasNoOperatorIfFilterDoesNotContainThePipe()
    {
        $fo = FilteringObject::fromFilter('foo');

        $this->assertSame(false, $fo->hasOperator());
    }

    /**
     * @covers ::__construct
     * @covers ::fromFilter
     * @covers ::getOperatorSign
     * @covers ::getOperator
     * @covers ::getAll
     * @covers ::getDefaultOperator
     */
    public function testProvideOperatorsSign()
    {
        $fo = FilteringObject::fromFilter('foo');

        $this->assertSame('=', $fo->getOperatorSign());
    }

    /**
     * @covers ::__construct
     * @covers ::fromFilter
     * @covers ::getOperatorSign
     * @covers ::getOperator
     */
    public function testProvideOperators()
    {
        $fo = FilteringObject::fromFilter('foo|eq');

        $this->assertSame('eq', $fo->getOperator());
    }

    /**
     * @covers ::__construct
     * @covers ::fromFilter
     * @covers ::getFieldName
     */
    public function testProvideFilterName()
    {
        $fo = FilteringObject::fromFilter('foo');

        $this->assertSame('foo', $fo->getFieldName());
    }

    /**
     * @covers ::__construct
     * @covers ::fromFilter
     * @covers ::isListOperator
     * @covers ::getOperator
     * @covers ::hasOperator
     * @covers ::is
     */
    public function testKnowsIfOperatorIsListOneOrNot()
    {
        $fo = FilteringObject::fromFilter('foo|list');

        $this->assertSame(true, $fo->isListOperator());
    }

    /**
     * @covers ::__construct
     * @covers ::fromFilter
     * @covers ::isFieldEqualsOperator
     * @covers ::getOperator
     * @covers ::hasOperator
     * @covers ::is
     */
    public function testKnowsIfOperatorIsFieldEqualsOrNot()
    {
        $fo = FilteringObject::fromFilter('foo|field_eq');

        $this->assertSame(true, $fo->isFieldEqualsOperator());
    }

    /**
     * @covers ::__construct
     * @covers ::fromFilter
     * @covers ::isListOperator
     * @covers ::getOperator
     * @covers ::hasOperator
     * @covers ::is
     */
    public function testKnowsIfHaveNotAnOperator()
    {
        $fo = FilteringObject::fromFilter('foo');

        $this->assertSame(false, $fo->hasOperator());
    }
}
