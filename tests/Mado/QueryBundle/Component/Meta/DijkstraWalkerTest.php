<?php

use Mado\QueryBundle\Component\Meta\DijkstraWalker;
use PHPUnit\Framework\TestCase as TestCase;

class DijkstraWalkerTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testThrownAnExceptionWheneverPathIsRequestedBeforeBuild()
    {
        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dijkstra = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\Dijkstra')
            ->disableOriginalConstructor()
            ->getMock();

        $this->walker = new DijkstraWalker(
            $this->mapper,
            $this->dijkstra
        );

        $this->walker->getPath();
    }

    public function testFoo()
    {
        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mapper->expects($this->once())
            ->method('getMap')
            ->will($this->returnValue([
                'start' => [
                    'relations' => [
                        'fine' => 'end',
                    ]
                ],
                'end' => [
                    'relations' => [
                        'inizio' => 'start',
                    ]
                ]
            ]));

        $this->dijkstra = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\Dijkstra')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dijkstra->expects($this->once())
            ->method('shortestPaths')
            ->will($this->returnValue([[
                'start',
                'end'
            ]]));

        $this->walker = new DijkstraWalker(
            $this->mapper,
            $this->dijkstra
        );

        // complete test with map

        $this->walker->buildPathBetween('start', 'end');

        $this->walker->getPath();
    }
}
