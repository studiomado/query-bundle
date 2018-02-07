<?php

namespace Mado\QueryBundle\Tests\Queries;

use Mado\QueryBundle\Queries\QueryBuilderOptions;
use PHPUnit\Framework\TestCase;

class QueryBuilderOptionsTest extends TestCase
{
    public function testConvertNegativeLimitToInfinite()
    {
        $options = QueryBuilderOptions::fromArray(['limit' => -1]);
        $this->assertEquals(PHP_INT_MAX, $options->get('limit'));
    }

    public function testUndefinedLimitMeansInfiniteByDefault()
    {
        $options = QueryBuilderOptions::fromArray([]);
        $this->assertEquals(PHP_INT_MAX, $options->get('limit'));
    }

    public function testGetDefaultValueIfNotSpecified()
    {
        $options = QueryBuilderOptions::fromArray([]);
        $this->assertEquals(42, $options->get('limit', 42));
    }
}
