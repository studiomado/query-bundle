<?php

use Mado\QueryBundle\Component\Meta\JsonPathFinder;
use PHPUnit\Framework\TestCase as TestCase;

class JsonPathFinderTest extends TestCase
{
    private $samepleJson;

    public function testRecognizeFirstChildOfAnEntity()
    {
        $this->samepleJson = [
            "AppBundle\\Entity\\Bar" => [
                "relations" => [
                    "fizz" => "AppBundle\\Entity\\Fizz",
                ]
            ],
        ];

        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\RelationDatamapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper->expects($this->once())
            ->method('getMap')
            ->will($this->returnValue(
                $this->samepleJson
            ));

        $this->pathFinder = new JsonPathFinder(
            $this->mapper
        );

        $this->assertEquals(
            "AppBundle\\Entity\\Fizz",
            $this->pathFinder->getFirstChildOf("AppBundle\\Entity\\Bar")
        );
    }

    public function testCatchRootEntityOfInnerOne()
    {
        $this->samepleJson = [
            "AppBundle\\Entity\\Bar" => [
                "relations" => [
                    "fizz" => "AppBundle\\Entity\\Fizz",
                ]
            ],
        ];

        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\RelationDatamapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper->expects($this->once())
            ->method('getMap')
            ->will($this->returnValue(
                $this->samepleJson
            ));

        $this->pathFinder = new JsonPathFinder(
            $this->mapper
        );

        $this->assertEquals(
            "AppBundle\\Entity\\Bar",
            $this->pathFinder->getFirstParentOf("AppBundle\\Entity\\Fizz")
        );
    }

    public function testCatchRelationNameToInnerEntity()
    {
        $this->samepleJson = [
            "AppBundle\\Entity\\Bar" => [
                "relations" => [
                    "fizz" => "AppBundle\\Entity\\Fizz",
                ]
            ],
        ];

        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\RelationDatamapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper->expects($this->once())
            ->method('getMap')
            ->will($this->returnValue(
                $this->samepleJson
            ));

        $this->pathFinder = new JsonPathFinder(
            $this->mapper
        );

        $this->assertEquals(
            "fizz",
            $this->pathFinder->getSourceRelation("AppBundle\\Entity\\Fizz")
        );
    }

    public function testLookForPathFromStartEntityToDestination()
    {
        $this->samepleJson = [
            "AppBundle\\Entity\\Root" => [
                "relations" => [
                    "foo" => "AppBundle\\Entity\\Foo",
                ]
            ],
            "AppBundle\\Entity\\Foo" => [
                "relations" => [
                    "bar" => "AppBundle\\Entity\\Bar",
                ]
            ],
            "AppBundle\\Entity\\Bar" => [
                "relations" => [
                    "fizz" => "AppBundle\\Entity\\Fizz",
                ]
            ],
        ];

        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\RelationDatamapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper->expects($this->once())
            ->method('getMap')
            ->will($this->returnValue(
                $this->samepleJson
            ));

        $this->pathFinder = new JsonPathFinder(
            $this->mapper
        );

        $this->pathFinder->setEntity("AppBundle\\Entity\\Root");

        $this->assertEquals(
            "foo.bar.fizz",
            $this->pathFinder->getPathTo("AppBundle\\Entity\\Fizz")
        );
    }

    public function testBuildPathBetweenTwoEntities()
    {
        $this->samepleJson = [
            "GammaBundle\\Entity\\Item" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
            "AppBundle\\Entity\\Foo" => [
                "relations" => [
                    "item" => "ZarroBundle\\Entity\\Item",
                ]
            ],
            "ZarroBundle\\Entity\\Item" => [
                "relations" => [
                    "family" => "AppBundle\\Entity\\Family",
                ]
            ],
        ];

        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\RelationDatamapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper->expects($this->once())
            ->method('getMap')
            ->will($this->returnValue(
                $this->samepleJson
            ));

        $this->pathFinder = new JsonPathFinder(
            $this->mapper
        );

        $this->pathFinder->setQueryStartEntity("GammaBundle\\Entity\\Item");

        $this->assertEquals(
            "_embedded.items.item.family",
            $this->pathFinder->getPathToEntity("AppBundle\\Entity\\Family")
        );
    }

    /**
     * @expectedException \Mado\QueryBundle\Component\Meta\Exceptions\UnreachablePathException
     */
    public function testThrowExceptionIfPathNotExists()
    {
        $this->samepleJson = [
            "GammaBundle\\Entity\\Merenghe" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
            "AppBundle\\Entity\\Foo" => [
                "relations" => [
                    "item" => "ZarroBundle\\Entity\\Item",
                ]
            ],
            "ZarroBundle\\Entity\\Item" => [
                "relations" => [
                    "family" => "AppBundle\\Entity\\Family",
                ]
            ],
        ];

        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\RelationDatamapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper->expects($this->once())
            ->method('getMap')
            ->will($this->returnValue(
                $this->samepleJson
            ));

        $this->pathFinder = new JsonPathFinder(
            $this->mapper
        );

        $this->pathFinder->setQueryStartEntity("GammaBundle\\Entity\\Item");
        $this->pathFinder->getPathToEntity("AppBundle\\Entity\\Family");
    }

    public function testCountNumberOfParentOfRelationEntity()
    {
        $this->samepleJson = [
            "GammaBundle\\Entity\\Zzz" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
            "GammaBundle\\Entity\\Item" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
        ];

        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\RelationDatamapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper->expects($this->once())
            ->method('getMap')
            ->will($this->returnValue(
                $this->samepleJson
            ));

        $this->pathFinder = new JsonPathFinder(
            $this->mapper
        );

        $this->assertEquals(
            2,
            $this->pathFinder->numberOfRelationsToEntity("AppBundle\\Entity\\Foo")
        );
    }

    public function testListParentOfInnerEntity()
    {
        $this->samepleJson = [
            "GammaBundle\\Entity\\Zzz" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
            "GammaBundle\\Entity\\Item" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
        ];

        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\RelationDatamapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper->expects($this->once())
            ->method('getMap')
            ->will($this->returnValue(
                $this->samepleJson
            ));

        $this->pathFinder = new JsonPathFinder(
            $this->mapper
        );

        $this->assertEquals(
            [
                "GammaBundle\\Entity\\Zzz",
                "GammaBundle\\Entity\\Item",
            ],
            $this->pathFinder->listOfParentsOf("AppBundle\\Entity\\Foo")
        );
    }

    public function testBuildListOfEntityReachedDuringTheWalk()
    {
        $this->samepleJson = [
            "GammaBundle\\Entity\\Zzz" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
            "GammaBundle\\Entity\\Item" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
            "AppBundle\\Entity\\Foo" => [
                "relations" => [
                    "item" => "ZarroBundle\\Entity\\Item",
                ]
            ],
            "ZarroBundle\\Entity\\Item" => [
                "relations" => [
                    "family" => "AppBundle\\Entity\\Family",
                ]
            ],
        ];

        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\RelationDatamapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper->expects($this->once())
            ->method('getMap')
            ->will($this->returnValue(
                $this->samepleJson
            ));

        $this->pathFinder = new JsonPathFinder(
            $this->mapper
        );

        $this->pathFinder->setQueryStartEntity("GammaBundle\\Entity\\Item");

        $this->assertEquals(
            "_embedded.items.item.family",
            $this->pathFinder->getPathToEntity("AppBundle\\Entity\\Family")
        );

        $this->assertEquals(
            [
                "AppBundle\\Entity\\Family",
                "ZarroBundle\\Entity\\Item",
                "AppBundle\\Entity\\Foo",
            ],
            $this->pathFinder->getEntitiesPath("AppBundle\\Entity\\Family")
        );
    }

    public function testBuildRightPathAlsoWhenAtTheEndThereIsAFork()
    {
        $this->samepleJson = [
            "GammaBundle\\Entity\\Zzz" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
            "GammaBundle\\Entity\\Item" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
            "AppBundle\\Entity\\Foo" => [
                "relations" => [
                    "item" => "ZarroBundle\\Entity\\Item",
                ]
            ],
            "ZarroBundle\\Entity\\Item" => [
                "relations" => [
                    "family" => "AppBundle\\Entity\\Family",
                ]
            ],
        ];

        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\RelationDatamapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper->expects($this->once())
            ->method('getMap')
            ->will($this->returnValue(
                $this->samepleJson
            ));

        $this->pathFinder = new JsonPathFinder(
            $this->mapper
        );

        $this->pathFinder->setQueryStartEntity("GammaBundle\\Entity\\Item");

        $this->assertEquals(
            "_embedded.items.item.family",
            $this->pathFinder->getPathToEntity("AppBundle\\Entity\\Family")
        );
    }

    public function testBuildRightPathAlsoWithForksIntPath()
    {
        $this->samepleJson = [
            "GammaBundle\\Entity\\Item" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
            "AppBundle\\Entity\\Wrong" => [
                "relations" => [
                    "item" => "ZarroBundle\\Entity\\Item",
                ]
            ],
            "AppBundle\\Entity\\Sbagliato" => [
                "relations" => [
                    "item" => "ZarroBundle\\Entity\\Item",
                ]
            ],
            "AppBundle\\Entity\\Foo" => [
                "relations" => [
                    "item" => "ZarroBundle\\Entity\\Item",
                ]
            ],
            "ZarroBundle\\Entity\\Item" => [
                "relations" => [
                    "family" => "AppBundle\\Entity\\Family",
                ]
            ],
        ];

        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\RelationDatamapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper->expects($this->once())
            ->method('getMap')
            ->will($this->returnValue(
                $this->samepleJson
            ));

        $this->pathFinder = new JsonPathFinder(
            $this->mapper
        );

        $this->pathFinder->setQueryStartEntity("GammaBundle\\Entity\\Item");

        $this->pathFinder->removeStep("AppBundle\\Entity\\Wrong");
        $this->pathFinder->removeStep("AppBundle\\Entity\\Sbagliato");

        $this->assertEquals(
            "_embedded.items.item.family",
            $this->pathFinder->getPathToEntity("AppBundle\\Entity\\Family")
        );
    }
}
