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
        $this->assertEquals('path.to.id|list',$f->getFieldAndOperator());
        $this->assertEquals('path.to.id',$f->getField());
    }

    public function testAllowPathChange()
    {
        $old = Filter::box([
            'ids' => ['list' => [2, 3, 4]],
            'path' => 'path.to',
        ]);

        $new = $old->withPath('new.path');

        $this->assertEquals('new.path',$new->getPath());
        $this->assertEquals('new.path.id|list',$new->getFieldAndOperator());
        $this->assertEquals('new.path.id',$new->getField());
    }

    public function testAllowFullPathChange()
    {
        $old = Filter::box([
            'ids' => ['list' => [2, 3, 4]],
            'path' => 'path.to',
        ]);

        $new = $old->withFullPath('new.path|foo');

        $this->assertEquals('new.path',$new->getPath());
        $this->assertEquals('new.path|foo',$new->getFieldAndOperator());
        $this->assertEquals('new.path',$new->getField());
    }

    public function testPathChangeEmpty()
    {
        $old = Filter::box([
            'ids' => ['list' => [2, 3, 4]],
            'path' => 'path.to',
        ]);

        $new = $old->withPath('');

        $this->assertEquals('',$new->getPath());
        $this->assertEquals('id|list',$new->getFieldAndOperator());
        $this->assertEquals('id',$new->getField());
    }
    
    public function testBuildFromQueryString()
    {
        $queryStringFilter = Filter::fromQueryStringFilter([
            '_embedded.attributes.alfanumerico12|eq' => 'GTK'
        ]);

        $this->assertEquals('_embedded.attributes.alfanumerico12|eq',$queryStringFilter->getFieldAndOperator());
        $this->assertEquals('_embedded.attributes.alfanumerico12',$queryStringFilter->getField());
        $this->assertEquals('GTK',$queryStringFilter->getValue());
    }    
}
