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

    public function testAllowPathChange()
    {
        $old = Filter::box([
            'ids' => ['list' => [2, 3, 4]],
            'path' => 'path.to',
        ]);

        $new = $old->withPath('new.path');

        $this->assertEquals('new.path',$new->getPath());
        $this->assertEquals('new.path.id|list',$new->getRawFilter());
    }

    public function testAllowFullPathChange()
    {
        $old = Filter::box([
            'ids' => ['list' => [2, 3, 4]],
            'path' => 'path.to',
        ]);

        $new = $old->withFullPath('new.path|foo');

        $this->assertEquals('new.path',$new->getPath());
        $this->assertEquals('new.path|foo',$new->getRawFilter());
    }

    public function testPathChangeEmpty()
    {
        $old = Filter::box([
            'ids' => ['list' => [2, 3, 4]],
            'path' => 'path.to',
        ]);

        $new = $old->withPath('');

        $this->assertEquals('',$new->getPath());
        $this->assertEquals('id|list',$new->getRawFilter());
    }
}
