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
