<?php

namespace Mado\QueryBundle\Tests\Objects;

use Mado\QueryBundle\Dictionary;
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

    public function testExposeEntityManager()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $this->assertSame(
            $this->manager,
            $queryBuilderFactory->getEntityManager()
        );
    }

    public function testProvideOneSingleResult()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setAndFilters([ 'id|eq' => 33 ]);
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
        $queryBuilderFactory->setAndFilters([ 'id|eq' => 33 ]);
        $queryBuilderFactory->createQueryBuilder(MySimpleEntity::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT m0_.id AS id_0 FROM MySimpleEntity m0_ WHERE m0_.id = ?",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );

        $this->assertContains(
            "SELECT e FROM Mado\QueryBundle\Tests\Objects\MySimpleEntity e WHERE e.id = :field_id",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getDql()
        );
    }

    public function testFilterWithListType()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setAndFilters([ 'id|list' => '42, 33' ]);
        $queryBuilderFactory->createQueryBuilder(MySimpleEntity::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT m0_.id AS id_0 FROM MySimpleEntity m0_ WHERE m0_.id IN (?)",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testFilterWithListTypeOnEmbeddedField()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setAndFilters([ '_embedded.group.id|list' => '42, 33']);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT "
            . "u0_.id AS id_0, "
            . "u0_.username AS username_1, "
            . "u0_.group_id AS group_id_2 "
            . "FROM User u0_ "
            . "INNER JOIN Group g1_ ON u0_.group_id = g1_.id "
            . "WHERE g1_.id IN (?)",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testFilterWithContainsOperator()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'username', 'group' ]);
        $queryBuilderFactory->setAndFilters([ 'username|contains' => 'orar' ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT "
            . "u0_.id AS id_0, "
            . "u0_.username AS username_1, "
            . "u0_.group_id AS group_id_2 "
            . "FROM User u0_ "
            . "WHERE u0_.username LIKE ?",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testFilterWithFieldEqualityOperator()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'username', 'group' ]);
        $queryBuilderFactory->setAndFilters([ 'username|field_eq' => 'group' ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT "
            . "u0_.id AS id_0, "
            . "u0_.username AS username_1, "
            . "u0_.group_id AS group_id_2 "
            . "FROM User u0_ "
            . "WHERE u0_.username = u0_.group_id",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testOneToManyQueryMakedHandly()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setRel([ 'group' ]);
        $queryBuilderFactory->setAndFilters([
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
            'isnull',
            'isnotnull',
            'listcontains',
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
        $filters = ['id'];
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setOrFilters($filters);

        $this->assertAttributeEquals($filters, 'orFilters', $queryBuilderFactory);
    }

    public function testGetOrFilters()
    {
        $filters = ['id'];
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setOrFilters($filters);
        $fieldsReturned = $queryBuilderFactory->getOrFilters();

        $this->assertEquals($filters, $fieldsReturned);
    }

    public function testSetSorting()
    {
        $sorting = ['id'];
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setSorting($sorting);

        $this->assertAttributeEquals($sorting, 'sorting', $queryBuilderFactory);
    }

    public function testGetFilters()
    {
        $filters = ['id'];
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setAndFilters($filters);
        $fieldsReturned = $queryBuilderFactory->getAndFilters();

        $this->assertEquals($filters, $fieldsReturned);
    }

    /**
     * @expectedException Mado\QueryBundle\Component\Meta\Exceptions\UnInitializedQueryBuilderException
     */
    public function testGetQueryBuilderThrowExceptionIfNull()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->getQueryBuilder();
    }

    public function testGetRel()
    {
        $rel = 'foo';
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setRel([$rel]);
        $relReturned = $queryBuilderFactory->getRel();

        $this->assertEquals([$rel], $relReturned);
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
        $page = 100;
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setPage($page);

        $this->assertAttributeEquals($page, 'page', $queryBuilderFactory);
    }

    public function testGetPage()
    {
        $page = 100;
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

    public function testListOnEmbeddedOrFilter()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setRel([ 'group' ]);
        $queryBuilderFactory->setOrFilters([
            '_embedded.group.id|list' => '1,2,3',
        ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT "
            . "u0_.id AS id_0, "
            . "u0_.username AS username_1, "
            . "u0_.group_id AS group_id_2 "
            . "FROM User u0_ "
            . "LEFT JOIN Group g1_ ON u0_.group_id = g1_.id "
            . "WHERE g1_.id IN (?)",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testFieldsArentInEntity()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setRel([ 'group' ]);
        $queryBuilderFactory->setOrFilters([
            'id|list' => '1,2,3',
        ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT" .
            " u0_.id AS id_0," .
            " u0_.username AS username_1," .
            " u0_.group_id AS group_id_2 " .
            "FROM User u0_ " .
            "WHERE u0_.id IN (?)",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testCheckFieldsEqualitiWithOrOperator()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'username', 'group' ]);
        $queryBuilderFactory->setOrFilters([ 'username|field_eq' => 'group' ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT "
            . "u0_.id AS id_0, "
            . "u0_.username AS username_1, "
            . "u0_.group_id AS group_id_2 "
            . "FROM User u0_ "
            . "WHERE u0_.username = u0_.group_id",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testCheckFieldsGreaterThan()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id', 'group' ]);
        $queryBuilderFactory->setOrFilters([ 'id|gte' => '42' ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT "
            . "u0_.id AS id_0, "
            . "u0_.username AS username_1, "
            . "u0_.group_id AS group_id_2 "
            . "FROM User u0_ "
            . "WHERE u0_.id >= ?",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testFilteringOrWithContains()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'username', 'group' ]);
        $queryBuilderFactory->setOrFilters([ 'username|contains' => 'sorar' ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT "
            . "u0_.id AS id_0, "
            . "u0_.username AS username_1, "
            . "u0_.group_id AS group_id_2 "
            . "FROM User u0_ "
            . "WHERE u0_.username LIKE ?",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testCanBuildQueriesUsingOrOperator()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setRel([ 'group' ]);
        $queryBuilderFactory->setOrFilters([
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
            "LEFT JOIN Group g1_ ON u0_.group_id = g1_.id " .
            "WHERE g1_.name LIKE ? " .
            "OR g1_.name LIKE ? " .
            "OR g1_.name LIKE ? " .
            "OR g1_.name LIKE ?",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    /** @expectedException \Mado\QueryBundle\Exceptions\MissingFiltersException */
    public function testThrowMissingFiltersExceptionsWheneverFiltersAreMissing()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->filter();
    }

    /** @expectedException \Mado\QueryBundle\Exceptions\MissingFieldsException */
    public function testThrowExceptionWheneverFieldsWereNotDefined()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setAndFilters([ 'foo|eq' => 'bar' ]);
        $queryBuilderFactory->filter();
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Oops! Fields are not defined
     */
    public function testCantSortWithoutFields()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->sort();
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Oops! Sorting is not defined
     */
    public function testThrowExceptionWheneverSortIsRequestedWithoutSorting()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields(['foo', 'bar']);
        $queryBuilderFactory->sort();
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Oops! QueryBuilder was never initialized
     */
    public function testThrowExceptionWhenQueryBuilderIsNotInitialized()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields(['foo', 'bar']);
        $queryBuilderFactory->setSorting(['foo' => 'bar']);
        $queryBuilderFactory->sort();
    }

    public function testApplySortingJustForEntityFields()
    {
        $this->queryBuilder = $this
            ->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryBuilder->expects($this->once())
            ->method('select')
            ->with('alias')
            ->willReturn($this->queryBuilder);
        $this->queryBuilder->expects($this->once())
            ->method('from')
            ->with('EntityName')
            ->willReturn($this->queryBuilder);

        $this->manager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilder);

        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields(['foo', 'bar']);
        $queryBuilderFactory->setSorting(['foo' => 'bar']);
        $queryBuilderFactory->createQueryBuilder('EntityName', 'alias');
        $queryBuilderFactory->sort();
    }

    public function testApplySortingAlsoOnRelationsField()
    {
        $this->queryBuilder = $this
            ->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryBuilder->expects($this->once())
            ->method('select')
            ->with('alias')
            ->willReturn($this->queryBuilder);
        $this->queryBuilder->expects($this->once())
            ->method('from')
            ->with('EntityName')
            ->willReturn($this->queryBuilder);
        $this->queryBuilder->expects($this->once())
            ->method('innerJoin')
            ->with('alias.ciao', 'table_fizz');

        $this->metadata = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadata->expects($this->once())
            ->method('hasAssociation')
            ->with('fizz')
            ->willReturn(true);
        $this->metadata->expects($this->once())
            ->method('getAssociationMapping')
            ->with('fizz')
            ->willReturn([
                'fieldName'    => 'ciao',
                'targetEntity' => 'someEntityName',
            ]);

        $this->manager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilder);
        $this->manager->expects($this->once())
            ->method('getClassMetadata')
            ->with('EntityName')
            ->willReturn($this->metadata);

        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields(['foo', 'bar']);
        $queryBuilderFactory->setSorting(['_embedded.fizz.buzz' => 'bar']);
        $queryBuilderFactory->createQueryBuilder('EntityName', 'alias');
        $queryBuilderFactory->sort();
    }

    public function testGetAvailableFilters()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);

        $expectedFilters = Dictionary::getOperators();

        $availableFilters = $queryBuilderFactory->getValueAvailableFilters();

        $this->assertEquals($expectedFilters, $availableFilters);
    }

    public function testAcceptRelationsToAdd()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setRel([ 'group' ]);
        $queryBuilderFactory->addRel('foo');

        $this->assertEquals(
            ['group', 'foo'],
            $queryBuilderFactory->getRel()
        );
    }

    public function testAndFilterUseInnerJoin()
    {
        $expectJoinType = 'innerJoin';

        $this->queryBuilder = $this
            ->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryBuilder
            ->method('select')
            ->willReturn($this->queryBuilder);
        $this->queryBuilder
            ->method('from')
            ->willReturn($this->queryBuilder);
        $this->queryBuilder->expects($this->once())
            ->method($expectJoinType)
            ->with('alias.baz', 'table_foo');

        $this->prepareDataForFilter();

        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->createQueryBuilder('EntityName', 'alias');
        $queryBuilderFactory->setAndFilters([ '_embedded.foo.baz|eq' => 'bar' ]);
        $queryBuilderFactory->filter();
    }

    public function testOrFilterUseLeftJoin()
    {
        $expectJoinType = 'leftJoin';

        $this->queryBuilder = $this
            ->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryBuilder
            ->method('select')
            ->willReturn($this->queryBuilder);
        $this->queryBuilder
            ->method('from')
            ->willReturn($this->queryBuilder);
        $this->queryBuilder->expects($this->once())
            ->method($expectJoinType)
            ->with('alias.baz', 'table_foo');

        $this->prepareDataForFilter();

        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->createQueryBuilder('EntityName', 'alias');
        $queryBuilderFactory->setOrFilters([ '_embedded.foo.baz|eq' => 'bar' ]);
        $queryBuilderFactory->filter();
    }

    private function prepareDataForFilter()
    {
        $this->metadata = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadata
            ->method('hasAssociation')
            ->with('foo')
            ->willReturn(true);
        $this->metadata
            ->method('getAssociationMapping')
            ->with('foo')
            ->willReturn([
                'fieldName'    => 'baz',
                'targetEntity' => 'someEntityName',
            ]);

        $this->manager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilder);
        $this->manager
            ->method('getClassMetadata')
            ->with('EntityName')
            ->willReturn($this->metadata);
    }

    public function testFilteringWithIsNull()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'username', 'group' ]);
        $queryBuilderFactory->setAndFilters([ 'username|isnull' => '' ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT "
            . "u0_.id AS id_0, "
            . "u0_.username AS username_1, "
            . "u0_.group_id AS group_id_2 "
            . "FROM User u0_ "
            . "WHERE u0_.username IS NULL",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testFilteringOrWithIsNull()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'username', 'group' ]);
        $queryBuilderFactory->setOrFilters([ 'username|isnull' => '' ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT "
            . "u0_.id AS id_0, "
            . "u0_.username AS username_1, "
            . "u0_.group_id AS group_id_2 "
            . "FROM User u0_ "
            . "WHERE u0_.username IS NULL",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testFilteringAndWithIsNullIntoEmbedded()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setRel([ 'group' ]);
        $queryBuilderFactory->setAndFilters([
            '_embedded.group.name|isnull' => ''
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
            "WHERE g1_.name IS NULL",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testFilteringOrWithIsNullIntoEmbedded()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setRel([ 'group' ]);
        $queryBuilderFactory->setOrFilters([
            '_embedded.group.name|isnull' => ''
        ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT" .
            " u0_.id AS id_0," .
            " u0_.username AS username_1," .
            " u0_.group_id AS group_id_2 " .
            "FROM User u0_ " .
            "LEFT JOIN Group g1_ ON u0_.group_id = g1_.id " .
            "WHERE g1_.name IS NULL",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testFilteringWithIsNotNull()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'username', 'group' ]);
        $queryBuilderFactory->setAndFilters([ 'username|isnotnull' => '' ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT "
            . "u0_.id AS id_0, "
            . "u0_.username AS username_1, "
            . "u0_.group_id AS group_id_2 "
            . "FROM User u0_ "
            . "WHERE u0_.username IS NOT NULL",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testFilteringOrWithIsNotNull()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'username', 'group' ]);
        $queryBuilderFactory->setOrFilters([ 'username|isnotnull' => '' ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT "
            . "u0_.id AS id_0, "
            . "u0_.username AS username_1, "
            . "u0_.group_id AS group_id_2 "
            . "FROM User u0_ "
            . "WHERE u0_.username IS NOT NULL",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testFilteringAndWithIsNotNullIntoEmbedded()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setRel([ 'group' ]);
        $queryBuilderFactory->setAndFilters([
            '_embedded.group.name|isnotnull' => ''
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
            "WHERE g1_.name IS NOT NULL",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testFilteringOrWithIsNotNullIntoEmbedded()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setRel([ 'group' ]);
        $queryBuilderFactory->setOrFilters([
            '_embedded.group.name|isnotnull' => ''
        ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT" .
            " u0_.id AS id_0," .
            " u0_.username AS username_1," .
            " u0_.group_id AS group_id_2 " .
            "FROM User u0_ " .
            "LEFT JOIN Group g1_ ON u0_.group_id = g1_.id " .
            "WHERE g1_.name IS NOT NULL",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testFilteringListContains()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id', 'username' ]);
        $queryBuilderFactory->setRel([ 'group' ]);
        $queryBuilderFactory->setAndFilters([
            'username|listcontains' => 'a,b',
            '_embedded.group.name|listcontains' => 'c,d'
        ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->filter();

        $this->assertEquals(
            "SELECT u0_.id AS id_0, u0_.username AS username_1, u0_.group_id AS group_id_2" .
            " FROM User u0_" .
            " INNER JOIN Group g1_ ON u0_.group_id = g1_.id " .
            "WHERE ((u0_.username LIKE ? OR u0_.username LIKE ?)) " .
            "AND ((g1_.name LIKE ? OR g1_.name LIKE ?))",
            $queryBuilderFactory->getQueryBuilder()->getQuery()->getSql()
        );
    }

    public function testSortingIntoTwoLevelsEmbedded()
    {
        $queryBuilderFactory = new QueryBuilderFactory($this->manager);
        $queryBuilderFactory->setFields([ 'id' ]);
        $queryBuilderFactory->setRel([ 'group', 'company' ]);
        $queryBuilderFactory->setSorting([
            '_embedded.group.company.id' => 'asc'
        ]);
        $queryBuilderFactory->createQueryBuilder(User::class, 'e');
        $queryBuilderFactory->sort();

        $this->assertEquals(
            "SELECT" .
            " u0_.id AS id_0," .
            " u0_.username AS username_1," .
            " u0_.group_id AS group_id_2 " .
            "FROM User u0_ " .
            "INNER JOIN Group g1_ ON u0_.group_id = g1_.id " .
            "INNER JOIN Company c2_ ON g1_.company_id = c2_.id " .
            "ORDER BY c2_.id ASC",
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
    /** @ManyToOne(targetEntity="Company", inversedBy="groups") */
    private $company;
}

/** @Entity() */
class Company
{
    /** @Id @Column(type="integer") */
    private $id;
    /** @Column(type="string") */
    private $name;
    /** @OneToMany(targetEntity="Group", mappedBy="company") */
    private $group;
}
