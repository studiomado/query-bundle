<?php

use Mado\QueryBundle\Objects\HalResponse;
use PHPUnit\Framework\TestCase;

class HalResponseTest extends TestCase
{
    public function testExtractTotalFromArray()
    {
        $response = HalResponse::fromArray([
            'total' => 42,
        ]);

        $this->assertEquals(42, $response->total());
    }

    public function testExtractTotalFromRawJson()
    {
        $response = HalResponse::fromJson('{"total":"42"}');

        $this->assertEquals(42, $response->total());
    }
}
