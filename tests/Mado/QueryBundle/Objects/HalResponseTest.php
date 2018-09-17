<?php

use Mado\QueryBundle\Objects\HalResponse;
use PHPUnit\Framework\TestCase;

class HalResponseTest extends TestCase
{
    public function testExtractTotal()
    {
        $response = HalResponse::fromArray([
            'total' => 42,
        ]);

        $this->assertEquals(42, $response->total());
    }
}
