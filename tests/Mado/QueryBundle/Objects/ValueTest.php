<?php

namespace Mado\QueryBundle\Tests\Objects;

use Mado\QueryBundle\Queries\Objects\Value;
use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase
{
    public function testLeaveFilterAsIsIfCamesFromQueryString()
    {
        $value = Value::fromFilter('foo');

        $filter = $value->getFilter();

        $this->assertEquals('foo', $filter);
    }

    public function testCamesFromQuerystringWheneverIsComposedByAString()
    {
        $value = Value::fromFilter('foo');

        $this->assertSame(true, $value->camesFromQueryString());
    }

    public function testCamesFromAdditionalFiltersWheneverComposedByAnArray()
    {
        $value = Value::fromFilter(['op' => [1, 2, 3]]);

        $this->assertSame(false, $value->camesFromQueryString());
    }

    public function testDetectOperator()
    {
        $value = Value::fromFilter(['op' => [1, 2, 3]]);

        $this->assertEquals('op', $value->getOperator());
    }

    public function testDetectIds()
    {
        $value = Value::fromFilter(['op' => [1, 2, 3]]);

        $this->assertEquals([1, 2, 3], $value->getValues());
    }
}
