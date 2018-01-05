<?php

namespace Mado\QueryBundle\Tests\Queries;

use Mado\QueryBundle\Queries\Objects\FilterObject;
use PHPUnit\Framework\TestCase;
use Mado\QueryBundle\Dictionary;

class FilterObjectTest extends TestCase
{
    public function testDetectOperator()
    {
        $filter = FilterObject::fromRawFilter('foo|bar');
        $operator = $filter->getOperator();
        $this->assertEquals('bar', $operator);
    }

    public function testWheneverOperatorIsntDefinedUseDefaultOperator()
    {
        $filter = FilterObject::fromRawFilter('foo');
        $operator = $filter->getOperator();
        $this->assertEquals(Dictionary::DEFAULT_OPERATOR, $operator);
    }
}
