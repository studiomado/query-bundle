<?php

use Mado\QueryBundle\Component\Meta\DijkstraWalker;
use PHPUnit\Framework\TestCase as TestCase;

class DijkstraWalkerTest extends TestCase
:
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

    public function testBuildPathUsingDijkstra()
    {
        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mapper->expects($this->once())
            ->method('getMap')
            ->will($this->returnValue($laMappa = [
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
            ->method('setMap')
            ->with($laMappa);
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

        $this->walker->buildPathBetween('start', 'end');

        $pathFound = $this->walker->getPath();

        $this->assertEquals(
            '_embedded.fine',
            $pathFound
        );
    }
}
