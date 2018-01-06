<?php

namespace Mado\QueryBundle\Tests\Objects;

use Mado\QueryBundle\Queries\QueryBuilderFactory;
use PHPUnit\Framework\TestCase;

class QueryBuilderFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->manager = \Doctrine\ORM\EntityManager::create(array(
            'driver' => 'pdo_sqlite',
            'path' => __DIR__ . '/../../data/db.sqlite',
        ),
        \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
            array(__DIR__."/src"),
            true
        ));
    }

    public function testProvideOneSingleResult()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setFilters([ 'id|eq' => 33 ]);
        $queryBuilderFactory->createQueryBuilder(MySimpleEntity::class, 'e');
        $queryBuilderFactory->filter();

        $doctrineQueryBuilder = $queryBuilderFactory->getQueryBuilder();
        $doctrineQueryBuilder->setMaxResults(1);

        $this->assertEquals(
            "SELECT m0_.id AS id_0 FROM MySimpleEntity m0_ WHERE m0_.id = ? LIMIT 1",
            $doctrineQueryBuilder->getQuery()->getSql()
        );
    }

    public function testSampleQueryMakedWithQueryBuilderFactory()
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

    public function testOneToManyQueryMakedHandly()
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

    public function testFiltersMustContainsAlsoFieldEquality()
    {
        $factory = new QueryBuilderFactory($this->manager);

        $validFilters = [
            'eq',
            'neq',
            'gt',
            'gte',
            'lt',
            'lte',
            'startswith',
            'contains',
            'notcontains',
            'endswith',
            'list',
            'nlist',
            'field_eq',
        ];

        $this->assertEquals(
            $validFilters,
            $factory->getAvailableFilters()
        );
    }

    public function testGetFields()
    {
        $fields = ['id'];
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields($fields);
        $fieldsReturned = $queryBuilderFactory->getFields();

        $this->assertEquals($fields, $fieldsReturned);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetFieldsThrowExceptionIfNull()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->getFields();
    }

    public function testSetOrFilters()
    {
        $fields = ['id'];
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setOrFilters($fields);

        $this->assertAttributeEquals($fields, 'orFiltering', $queryBuilderFactory);
    }

    public function testGetOrFilters()
    {
        $fields = ['id'];
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setOrFilters($fields);

        $fieldsReturned = $queryBuilderFactory->getOrFilters();

        $this->assertEquals($fields, $fieldsReturned);
    }

    public function testSetSorting()
    {
        $fields = ['id'];
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setSorting($fields);

        $this->assertAttributeEquals($fields, 'sorting', $queryBuilderFactory);
    }

    public function testGetFilters()
    {
        $fields = ['id'];
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFilters($fields);
        $fieldsReturned = $queryBuilderFactory->getFilters();

        $this->assertEquals($fields, $fieldsReturned);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetQueryBuilderThrowExceptionIfNull()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->getQueryBuilder();
    }

    public function testGetQueryBuilder()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->createQueryBuilder('foo', 'bar');
        $qb = $queryBuilderFactory->getQueryBuilder();

        $this->assertInstanceOf('Doctrine\ORM\QueryBuilder', $qb);
    }

    public function testGetRel()
    {
        $rel = 'foo';
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setRel($rel);
        $relReturned = $queryBuilderFactory->getRel();

        $this->assertEquals($rel, $relReturned);
    }

    public function testSetPrinting()
    {
        $print = 'foo';
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setPrinting($print);

        $this->assertAttributeEquals($print, 'printing', $queryBuilderFactory);
    }

    public function testGetPrinting()
    {
        $print = 'foo';
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setPrinting($print);
        $printReturned = $queryBuilderFactory->getPrinting();

        $this->assertEquals($print, $printReturned);
    }

    public function testSetPage()
    {
        $page = 'foo';
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setPage($page);

        $this->assertAttributeEquals($page, 'page', $queryBuilderFactory);
    }

    public function testGetPage()
    {
        $page = 'foo';
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setPage($page);
        $pageReturned = $queryBuilderFactory->getPage();

        $this->assertEquals($page, $pageReturned);
    }

    public function testSetPageLength()
    {
        $pageLength = 100;
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setPageLength($pageLength);

        $this->assertAttributeEquals($pageLength, 'pageLength', $queryBuilderFactory);
    }

    public function testGetPageLength()
    {
        $pageLength = 100;
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setPageLength($pageLength);
        $pageLengthReturned = $queryBuilderFactory->getPageLength();

        $this->assertEquals($pageLength, $pageLengthReturned);
    }

    public function testSetSelect()
    {
        $select = 'foo';
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setSelect($select);

        $this->assertAttributeEquals($select, 'select', $queryBuilderFactory);
    }

    public function testGetSelect()
    {
        $select = 'foo';
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setSelect($select);
        $selectReturned = $queryBuilderFactory->getSelect();

        $this->assertEquals($select, $selectReturned);
    }

    public function testGetEntityManager()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $emReturned = $queryBuilderFactory->getEntityManager();

        $this->assertEquals($this->manager, $emReturned);
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
