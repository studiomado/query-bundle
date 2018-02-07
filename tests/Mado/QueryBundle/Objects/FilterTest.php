<?php

use Mado\QueryBundle\Objects\Filter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testExtractOperator()
    {
        $f = Filter::box([
            'ids' => ['list' => [2, 3, 4]],
            'path' => 'path.to',
        ]);

        $this->assertEquals('list',$f->getOperator());
        $this->assertEquals('path.to',$f->getPath());
        $this->assertEquals('2,3,4',$f->getIds());
        $this->assertEquals('path.to.id|list',$f->getRawFilter());
    }
}
