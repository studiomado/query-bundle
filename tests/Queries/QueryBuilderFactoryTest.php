<?php

namespace Mado\QueryBundle\Tests\Objects;

use Mado\QueryBundle\Queries\QueryBuilderFactory;
use Mado\QueryBundle\Vocabulary\Operators;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mado\QueryBundle\Queries\QueryBuilderFactory
 */
class QueryBuilderFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->manager = \Doctrine\ORM\EntityManager::create(array(
            'driver' => 'pdo_sqlite',
            'path' => __DIR__ . '/data/db.sqlite',
        ),
        \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
            array(__DIR__."/src"),
            true
        ));
    }

    /**
     * @covers \Mado\QueryBundle\Queries\QueryBuilderFactory::__construct
     * @covers \Mado\QueryBundle\Queries\QueryBuilderFactory::getAvailableFilters
     */
    public function testCanFilterThanksToOperators()
    {
        $this->manager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilderFactory = new QueryBuilderFactory($this->manager);

        $this->assertEquals(
            array_keys(Operators::getAll()),
            $queryBuilderFactory->getAvailableFilters()
        );
    }

    /**
     * @covers \Mado\QueryBundle\Queries\QueryBuilderFactory::__construct
     * @covers \Mado\QueryBundle\Queries\QueryBuilderFactory::getAvailableFilters
     * @expectedException \RuntimeException
     */
    public function testThrowExceptionWheneverUsedWithoutFields()
    {
        $this->manager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->ensureFieldsDefinedPublic();
    }

    /**
     * @covers \Mado\QueryBundle\Queries\QueryBuilderFactory::__construct
     * @covers \Mado\QueryBundle\Queries\QueryBuilderFactory::getAvailableFilters
     */
    public function testBuildQueryHandly()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setFilters([ 'id|eq' => 33 ]);
        $queryBuilderFactory->createQueryBuilder(MySimpleEntity::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT m0_.id AS id_0 FROM MySimpleEntity m0_ WHERE m0_.id = ?",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );

        $this->assertEquals(
            "SELECT e FROM Mado\QueryBundle\Tests\Objects\MySimpleEntity e WHERE e.id = :field_id",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getDql()
        );
    }

    public function testOneToMany()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setRel([ 'group' ]);
        $queryBuilderFactory->setFilters([
            '_embedded.group.name|contains|1' => 'ad',
            '_embedded.group.name|contains|2' => 'ns',
            '_embedded.group.name|contains|3' => 'dm',
            '_embedded.group.name|contains|4' => 'mi',
        ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT" .
            " u0_.id AS id_0," .
            " u0_.username AS username_1," .
            " u0_.group_id AS group_id_2 " .
            "FROM User u0_ " .
            "INNER JOIN Group g1_ ON u0_.group_id = g1_.id " .
            "WHERE g1_.name LIKE ? " .
            "AND g1_.name LIKE ? " .
            "AND g1_.name LIKE ? " .
            "AND g1_.name LIKE ?",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }
}

/** @Entity() */
class MySimpleEntity
{
    /** @Id @Column(type="integer") */
    private $id;
}

/** @Entity() */
class User
{
    /** @Id @Column(type="integer") */
    private $id;

    /** @Column(type="string") */
    private $username;

    /** @ManyToOne(targetEntity="Group", inversedBy="member") */
    private $group;
}

/** @Entity() */
class Group
{
    /** @Id @Column(type="integer") */
    private $id;

    /** @Column(type="string") */
    private $name;

    /** @OneToMany(targetEntity="User", mappedBy="member") */
    private $members;
}
