<?php

namespace Mado\QueryBundle\Tests\Queries;

use Mado\QueryBundle\Queries\QueryBuilderOptions;
use PHPUnit\Framework\TestCase;

class QueryBuilderOptionsTest extends TestCase
{
    public function testConvertNegativeLimitToInfinite()
    {
        $options = QueryBuilderOptions::fromArray([
            'limit' => -1,
        ]);
        $this->assertEquals(PHP_INT_MAX, $options->get('limit'));
    }
}
