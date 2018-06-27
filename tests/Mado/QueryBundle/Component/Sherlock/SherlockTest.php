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

    public function testShouldProvideFieldsAndRelationsOfGivenEntityPath()
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
                    'name' => [
                        'startswith',
                        'contains',
                        'notcontains',
                        'endswith',
                    ],
                ],
                'relations' => [
                    'mado.querybundle.tests.objects.startingentity',
                ]
            ],
            $this->sherlock->getOpList("mado.querybundle.tests.objects.middleentity")
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
    /** @ManyToOne(targetEntity="MiddleEntity", inversedBy="member") */
    private $middle;
}

/** @Entity() */
class MiddleEntity
{
    /** @Id @Column(type="integer") */
    private $id;
    /** @Column(type="string") */
    private $name;
    /** @OneToMany(targetEntity="StartingEntity", mappedBy="member") */
    private $members;
}
