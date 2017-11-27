<?php

use Mado\QueryBundle\Component\Meta\MapBuilder;
use PHPUnit\Framework\TestCase as TestCase;

class MapBuilderTest extends TestCase
{
    public function testBuildEmptyMapWithoutEntities()
    {
        $expectedMap = [];

        $this->factory = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->factory->expects($this->once())
            ->method('getAllMetadata')
            ->will($this->returnValue($expectedMap));

        $this->manager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager->expects($this->once())
            ->method('getMetadataFactory')
            ->will($this->returnValue($this->factory));

        $mapBuilder = new MapBuilder(
            $this->manager
        );

        $map = $mapBuilder->getMap();

        $this->assertEquals(
            $expectedMap,
            $map
        );
    }

    public function testBuildMap()
    {
        $expectedMap = [];

        $this->factory = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->factory->expects($this->once())
            ->method('getAllMetadata')
            ->will($this->returnValue(function () {
                return [
                    'SomeBundle\EntityName' => [
                        'relations' => [
                            'relName' => 'Target\Entity\Name',
                        ]
                    ]
                ];
            }));

        $this->manager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager->expects($this->once())
            ->method('getMetadataFactory')
            ->will($this->returnValue($this->factory));

        $mapBuilder = new MapBuilder(
            $this->manager
        );

        $map = $mapBuilder->getMap();

        $this->assertEquals(
            $expectedMap,
            $map
        );
    }
}
