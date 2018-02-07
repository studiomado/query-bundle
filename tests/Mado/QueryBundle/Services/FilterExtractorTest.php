<?php

use PHPUnit\Framework\TestCase;

final class FilterExtractorTest extends TestCase
{
    public function testExtractFiltersFromUser()
    {
        $ids = [1, 23, 42];
        $additionalFilters = [
            'additionalfilter' => $ids,
        ];

        $this->user = $this
            ->getMockBuilder('\Mado\QueryBundle\Interfaces\AdditionalFilterable')
            ->disableOriginalConstructor()
            ->getMock();
        $this->user->expects($this->once())
            ->method('getAdditionalFilters')
            ->will($this->returnValue($additionalFilters));

        $filter = \Mado\QueryBundle\Services\FilterExtractor::fromUser($this->user);

        $this->assertEquals(
            $ids,
            $filter->getFilters('additionalfilter')
        );
    }

    public function testExtractEmptyArray()
    {
        $this->user = $this
            ->getMockBuilder('\Mado\QueryBundle\Interfaces\AdditionalFilterable')
            ->disableOriginalConstructor()
            ->getMock();
        $this->user->expects($this->once())
            ->method('getAdditionalFilters')
            ->will($this->returnValue([]));

        $filter = \Mado\QueryBundle\Services\FilterExtractor::fromUser($this->user);

        $this->assertEquals('', $filter->getFilters('additionalfilter'));
    }
}
