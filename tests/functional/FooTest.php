<?php

use Mado\QueryBundle\Action\CreateQuery;
use Mado\QueryBundle\Interfaces\EntityClass;
use Mado\QueryBundle\Queries\QueryBuilderFactory;
use Mado\QueryBundle\Queries\QueryBuilderOptions;
use Mado\QueryBundle\Repositories\BaseRepository;
use PHPUnit\Framework\Testcase;

/** @covers Mado\QueryBundle\Repositories\BaseRepository  */
final class FooTest extends Testcase
{
    public function setUp()
    {
        $this->manager = \Mado\QueryBundle\Common\Doctrine\EntityManager::getInstance();
    }

    public function testFilter()
    {
        $dql = new CreateQuery(
            $this->manager,
            QueryBuilderOptions::fromArray([
                'select' => 'f',
                'filters' => [
                    'name|contains|1' => 'foo',
                    'name|contains|2' => 'bar',
                ]
            ]),
            new Foo()
        );

        $this->assertEquals(
            "SELECT f0_.id AS id_0, f0_.name AS name_1 FROM Foo f0_ WHERE f0_.name LIKE ? AND f0_.name LIKE ?",
            $dql->getSql()
        );
    }

    public function testSelectAllResults()
    {
        $dql = new CreateQuery(
            $this->manager,
            QueryBuilderOptions::fromArray([
                'select' => 'f',
            ]),
            new Foo()
        );

        $this->assertEquals(
            "SELECT f FROM Foo f",
            $dql->getDql()
        );
    }

    public function testFooooo()
    {
        $expected = "SELECT f0_.id AS id_0, f0_.name AS name_1 FROM Foo f0_";

        $generatedSql = (new BaseRepository(
            $this->manager,
            $this->manager->getClassMetadata(Foo::class)
        ))
        ->setQueryOptions(QueryBuilderOptions::fromArray([
            'select' => 'f',
        ]))
        ->getQueryBuilderFactory()
        ->getQueryBuilder()
        ->getQuery()
        ->getSql();

        $this->assertEquals(
            $expected,
            $generatedSql
        );
    }

    public function testFiltering()
    {
        $expected = "SELECT f0_.id AS id_0, f0_.name AS name_1 " .
            "FROM Foo f0_ " .
            "WHERE f0_.name LIKE ? " .
            "AND f0_.name LIKE ?";

        $generatedSql = (new BaseRepository( $this->manager,
            $this->manager->getClassMetadata(Foo::class)
        ))
        ->setQueryOptions(QueryBuilderOptions::fromArray([
            'select' => 'f',
            'filters' => [
                'name|contains|1' => 'foo',
                'name|contains|2' => 'bar',
            ]
        ]))
        ->getQueryBuilderFactory()
        ->filter()
        ->sort()
        ->getQueryBuilder()
        ->getQuery()
        ->getSql();

        $this->assertEquals(
            $expected,
            $generatedSql
        );
    }
}

/** @Entity() */
class Foo implements EntityClass
{
    /** @Id @Column(type="integer") */
    private $id;

    /** @Column(type="string") */
    private $name;

    public function getFQNC()
    {
        return self::class;
    }
}
