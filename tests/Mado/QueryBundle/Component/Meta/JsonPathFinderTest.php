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
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
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
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
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
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
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
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
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
            "FooBundle\\Entity\\Item" => [
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
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
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

        $this->pathFinder->setQueryStartEntity("FooBundle\\Entity\\Item");

        $this->assertEquals(
            "_embedded.items.item.family",
            $this->pathFinder->getPathToEntity("AppBundle\\Entity\\Family")
        );
    }

    /**
     * @expectedException \Mado\QueryBundle\Component\Meta\Exceptions\UnespectedValueException
     */
    public function testThrowExceptionIfPathNotExists()
    {
        $this->samepleJson = [
            "FooBundle\\Entity\\Merenghe" => [
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
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
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

        $this->pathFinder->setQueryStartEntity("FooBundle\\Entity\\Item");
        $this->pathFinder->getPathToEntity("AppBundle\\Entity\\Family");
    }

    public function testCountNumberOfParentOfRelationEntity()
    {
        $this->samepleJson = [
            "FooBundle\\Entity\\Zzz" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
            "FooBundle\\Entity\\Item" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
        ];

        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
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
            "FooBundle\\Entity\\Zzz" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
            "FooBundle\\Entity\\Item" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
        ];

        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
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
                "FooBundle\\Entity\\Zzz",
                "FooBundle\\Entity\\Item",
            ],
            $this->pathFinder->listOfParentsOf("AppBundle\\Entity\\Foo")
        );
    }

    public function testBuildListOfEntityReachedDuringTheWalk()
    {
        $this->samepleJson = [
            "FooBundle\\Entity\\Zzz" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
            "FooBundle\\Entity\\Item" => [
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
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
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

        $this->pathFinder->setQueryStartEntity("FooBundle\\Entity\\Item");

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
            "FooBundle\\Entity\\Zzz" => [
                "relations" => [
                    "items" => "AppBundle\\Entity\\Foo",
                ]
            ],
            "FooBundle\\Entity\\Item" => [
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
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
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

        $this->pathFinder->setQueryStartEntity("FooBundle\\Entity\\Item");

        $this->assertEquals(
            "_embedded.items.item.family",
            $this->pathFinder->getPathToEntity("AppBundle\\Entity\\Family")
        );
    }

    public function testBuildRightPathAlsoWithForksIntPath()
    {
        $this->samepleJson = [
            "FooBundle\\Entity\\Item" => [
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
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
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

        $this->pathFinder->setQueryStartEntity("FooBundle\\Entity\\Item");

        $this->pathFinder->removeStep("AppBundle\\Entity\\Wrong");
        $this->pathFinder->removeStep("AppBundle\\Entity\\Sbagliato");

        $this->assertEquals(
            "_embedded.items.item.family",
            $this->pathFinder->getPathToEntity("AppBundle\\Entity\\Family")
        );
    }

    public function testGenerateHasKeyFoRequest()
    {
        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper->expects($this->never())
            ->method('getMap');

        $this->pathFinder = new JsonPathFinder(
            $this->mapper
        );

        $startEntity = "FooBundle\\Entity\\Item";
        $endEntity = "AppBundle\\Entity\\Family";

        $this->pathFinder->setQueryStartEntity($startEntity);
        $hash = $this->pathFinder->getHashKeyForDestination($endEntity);

        $this->assertEquals(
            md5($startEntity . $endEntity),
            $hash
        );
    }

    public function testIncrementEntities()
    {
        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper->expects($this->never())
            ->method('getMap');

        $this->pathFinder = new JsonPathFinder($this->mapper);

        $startingCollection = [];

        $endCollection = $this->pathFinder->addEntity(
            $startingCollection,
            'ciaone'
        );

        $this->assertEquals(
            ['ciaone'],
            $endCollection
        );
    }

    /**
     * @expectedException \Mado\QueryBundle\Component\Meta\Exceptions\UndefinedPathException
     */
    public function testEntitiesPathCantExistsIfAnyPathWasLoaded()
    {
        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->pathFinder = new JsonPathFinder($this->mapper);

        $this->pathFinder->getEntitiesPath("AppBundle\\Entity\\Family");
    }

    /**
     * @expectedException \Mado\QueryBundle\Component\Meta\Exceptions\UnreachablePathException
     */
    public function testUnreachablePathExceptionIsThrownWheneverEntityIsMissed()
    {
        $this->samepleJson = [
            "" => [
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
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
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

    /**
     * @expectedException \Mado\QueryBundle\Component\Meta\Exceptions\NestingException
     */
    public function testCatchNesting()
    {
        $this->samepleJson = [
            "AppBundle\\Entity\\Fizz" => [
                "relations" => [
                    "fizz" => "AppBundle\\Entity\\Fizz",
                ]
            ],
        ];

        $this->mapper = $this
            ->getMockBuilder('Mado\QueryBundle\Component\Meta\DataMapper')
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

        $this->pathFinder->getPathTo("AppBundle\\Entity\\Fizz");
    }
}
