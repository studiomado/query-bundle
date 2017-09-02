<?php

namespace Mado\QueryBundle\Tests\Objects;

use Mado\QueryBundle\Objects\FilteringObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mado\QueryBundle\Objects\FilteringObject
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
}
