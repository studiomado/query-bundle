<?php

namespace Mado\QueryBundle\Tests\Objects;

use Mado\QueryBundle\Queries\QueryBuilderOptions;
use PHPUnit\Framework\TestCase;

class QueryBuilderOptionsTest extends TestCase
{
    public function testInitialize()
    {
        $op = QueryBuilderOptions::fromArray([
            'filters' => 'andFilterValue',
            'orFilters' => 'orFiltersValue',
            'sorting' => 'asc',
            'rel' => 'azione',
            'printing' => 'pppp',
            'select' => 'a,b,c',
        ]);

        $this->assertEquals('andFilterValue', $op->getAndFilters());
        $this->assertEquals('orFiltersValue', $op->getOrFilters());
        $this->assertEquals('asc', $op->getSorting());
        $this->assertEquals('azione', $op->getRel());
        $this->assertEquals('pppp', $op->getPrinting());
        $this->assertEquals('a,b,c', $op->getSelect());

        $this->assertEquals(
            $op->get('filters'),
            $op->getAndFilters()
        );

        $this->assertNull($op->get('fake'));
    }
}
