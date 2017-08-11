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
}
