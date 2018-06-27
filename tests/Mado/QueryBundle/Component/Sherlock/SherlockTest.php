<?php

namespace Mado\QueryBundle\Tests\Objects;

use Mado\QueryBundle\Component\Sherlock\Sherlock;
use PHPUnit\Framework\TestCase;

class SherlockTest extends TestCase
{
    public function setUp()
    {
        $this->manager = \Doctrine\ORM\EntityManager::create(array(
            'driver' => 'pdo_sqlite',
            'path' => __DIR__ . '/../../data/db.sqlite',
        ),
        \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
            array(__DIR__),
            true
        ));

        $this->sherlock = new Sherlock(
            $this->manager
        );
    }

    /** @dataProvider startingPoints */
    public function testExtractJustTypeAndRelations(
        $metadataAsJson,
        $startingEntity,
        $compressed
    ) {
        $this->assertEquals(
            $metadataAsJson,
            $this->sherlock->getShortOpList($startingEntity)
        );

        $this->assertEquals(
            $compressed,
            $foo = $this->sherlock->getSearchable($startingEntity)
        );
    }

    public function startingPoints()
    {
        return [
            [
                [
                    'fields' => [ 'id' => 'integer', 'username' => 'string', ],
                    '_embedded' => [
                        'middle' => [
                            'id' => 'integer',
                            'name' => 'string',
                        ],
                        'tooMany' => [
                            'id' => 'integer',
                            'stringa' => 'string',
                        ],
                    ],
                    'relations' => [
                        [
                            'middle' => 'mado.querybundle.tests.objects.middleentity',
                            'tooMany' => 'mado.querybundle.tests.objects.toomany',
                        ]
                    ]
                ], "mado.querybundle.tests.objects.startingentity", [
                    'id' => 'integer',
                    'username' => 'string',
                    'middle.id' => 'integer',
                    'middle.name' => 'string',
                    'tooMany.id' => 'integer',
                    'tooMany.stringa' => 'string',
                ]
            ],
            [
                [
                    'fields' => [ 'id' => 'integer', 'name' => 'string', ],
                    '_embedded' => [
                        'oneToMany' => [
                            'id' => 'integer',
                            'username' => 'string',
                        ],
                    ],
                    'relations' => [
                        [
                            'oneToMany' => 'mado.querybundle.tests.objects.startingentity'
                        ]
                    ]
                ], "mado.querybundle.tests.objects.middleentity", [
                    'id' => 'integer',
                    'name' => 'string',
                    'oneToMany.id' => 'integer',
                    'oneToMany.username' => 'string',
                ]
            ],
            [
                [
                    'fields' => [ 'id' => 'integer', 'stringa' => 'string', ],
                    '_embedded' => [
                        'starting' => [
                            'id' => 'integer',
                            'username' => 'string',
                        ],
                    ],
                    'relations' => [
                        [
                            'starting' => 'mado.querybundle.tests.objects.startingentity'
                        ]
                    ]
                ], "mado.querybundle.tests.objects.toomany", [
                    'id' => 'integer',
                    'stringa' => 'string',
                    'starting.id' => 'integer',
                    'starting.username' => 'string',
                ]
            ],
        ];
    }

    public function test()
    {
        $relations = $this
            ->sherlock
            ->willCall("mado.querybundle.tests.objects.startingentity");

        $this->assertEquals(
            [
                'middle' => 'mado.querybundle.tests.objects.middleentity',
                'tooMany' => 'mado.querybundle.tests.objects.toomany',
            ],
            $relations
        );
    }

    public function testShouldProvideAvailableOperatorsForASpecificEntity()
    {
        $this->assertEquals(
            [
                'fields' => [
                    'id' => [
                        'eq',
                        'neq',
                        'gt',
                        'gte',
                        'lt',
                        'lte',
                    ],
                    'username' => [
                        'startswith',
                        'contains',
                        'notcontains',
                        'endswith',
                    ],
                ],
                'relations' => [
                    [
                        'middle' => 'mado.querybundle.tests.objects.middleentity',
                        'tooMany' => 'mado.querybundle.tests.objects.toomany',
                    ]
                ]
            ],
            $this->sherlock->getOpList("mado.querybundle.tests.objects.startingentity")
        );
    }

    public function testExtractJustFieldTypeAndNotOperatorList()
    {
        $this->assertEquals(
            [
                'fields' => [
                    'id' => 'integer',
                    'username' => 'string',
                ],
            ],
            $this->sherlock->getFieldsType("mado.querybundle.tests.objects.startingentity")
        );
    }

    public function testExtractRelations()
    {
        $this->assertEquals(
            [
                'middle' => 'mado.querybundle.tests.objects.middleentity',
                'tooMany' => 'mado.querybundle.tests.objects.toomany',
            ],
            $this->sherlock->getRelations("mado.querybundle.tests.objects.startingentity")
        );
    }
}

/** @Entity() */
class DeepetstEntity
{
    /** @Id @Column(type="integer") */
    private $id;
}

/** @Entity() */
class StartingEntity
{
    /** @Id @Column(type="integer") */
    private $id;
    /** @Column(type="string") */
    private $username;
    /** @ManyToOne(targetEntity="MiddleEntity", inversedBy="middle") */
    private $one;
    /** @ManyToOne(targetEntity="TooMany", inversedBy="tooMany") */
    private $tooMany;
}

/** @Entity() */
class TooMany
{
    /** @Id @Column(type="integer") */
    private $id;
    /** @Column(type="string") */
    private $stringa;
    /** @OneToMany(targetEntity="StartingEntity", mappedBy="starting") */
    private $starting;
}

/** @Entity() */
class MiddleEntity
{
    /** @Id @Column(type="integer") */
    private $id;
    /** @Column(type="string") */
    private $name;
    /** @OneToMany(targetEntity="StartingEntity", mappedBy="oneToMany") */
    private $oneToMany;
}
