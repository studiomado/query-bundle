<?php

namespace Mado\QueryBundle\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Mado\QueryBundle\Objects\MetaDataAdapter;
use Mado\QueryBundle\Objects\PagerfantaBuilder;
use Mado\QueryBundle\Queries\QueryBuilderFactory;
use Mado\QueryBundle\Queries\QueryBuilderOptions;
use Mado\QueryBundle\Queries\Options\QueryOptionsBuilder;
use Mado\QueryBundle\Services\Pager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Component\HttpFoundation\Request;

/** @codeCoverageIgnore */
class BaseRepository extends EntityRepository
{
    protected $request;

    protected $useResultCache = false;

    protected $routeName;

    protected $currentEntityAlias;

    protected $embeddedFields;

    protected $joins = [];

    protected $queryBuilderFactory;

    protected $queryOptions;

    protected $metadata;

    private $lastQuery;
    
    private $lastParameters;

    public function __construct($manager, $class)
    {
        parent::__construct($manager, $class);

        $this->metadata = new MetaDataAdapter();
        $this->metadata->setClassMetadata($this->getClassMetadata());
        $this->metadata->setEntityName($this->getEntityName());

        $this->queryBuilderFactory = new QueryBuilderFactory($this->getEntityManager());

        $this->qoBuilder = new QueryOptionsBuilder();
        $entityAlias = $this->metadata->getEntityAlias();
        $this->qoBuilder->setEntityAlias($entityAlias);
    }

    public function initFromQueryBuilderOptions(QueryBuilderOptions $options)
    {
        $this->queryBuilderFactory->createQueryBuilder(
            $this->getEntityName(),
            $this->metadata->getEntityAlias()
        );

        $this->queryBuilderFactory->loadMetadataAndOptions(
            $this->metadata,
            $options
        );
    }

    public function getQueryBuilderFactory()
    {
        $this->initFromQueryBuilderOptions($this->queryOptions);

        return $this->queryBuilderFactory;
    }

    public function useResultCache($bool)
    {
        $this->useResultCache = $bool;
    }

    public function setRequest(Request $request)
    {
        return $this->setQueryOptionsFromRequest($request);
    }

    public function setRequestWithFilter(Request $request, $filter)
    {
        return $this->setQueryOptionsFromRequestWithCustomFilter($request, $filter);
    }

    public function setRequestWithOrFilter(Request $request, $orFilter)
    {
        return $this->setQueryOptionsFromRequestWithCustomOrFilter($request, $orFilter);
    }

    public function setQueryOptions(QueryBuilderOptions $options)
    {
        $this->queryOptions = $options;
    }

    public function setQueryOptionsFromRequest(Request $request = null)
    {
        $this->queryOptions = $this->qoBuilder->builderFromRequest($request);

        return $this;
    }

    public function setQueryOptionsFromRequestWithCustomFilter(Request $request = null, $filter)
    {
        $this->queryOptions = $this->qoBuilder->buildFromRequestAndCustomFilter($request, $filter);

        return $this;
    }

    public function setQueryOptionsFromRequestWithCustomOrFilter(Request $request = null, $orFilter)
    {
        $this->queryOptions = $this->qoBuilder->buildForOrFilter($request);

        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setRouteName($routeName = '')
    {
        $this->routeName = $routeName;
        return $this;
    }

    public function findAllNoPaginated()
    {
        $queryBuilderFactory = $this->getQueryBuilderFactory()
            ->filter()
            ->sort();

        $doctrineQueryBuilder = $queryBuilderFactory->getQueryBuilder();

        return $doctrineQueryBuilder->getQuery()->getResult();
    }
    
    public function findAllPaginated()
    {
        $this->initFromQueryBuilderOptions($this->queryOptions);

        $this->queryBuilderFactory->filter();
        $this->queryBuilderFactory->sort();

        $queryBuilder = $this->queryBuilderFactory->getQueryBuilder();

        if ($this->queryOptions->requireJustCount()) {
            $metadata = $this->metadata;
            $rootEntityAlias = $metadata->getEntityAlias();
            $select = 'count(' . $rootEntityAlias . '.id)';

            $count = $queryBuilder
                ->select($select)
                ->getQuery()
                ->getSingleScalarResult();

            return [ 'count' => $count ];
        }

        $this->lastQuery = $queryBuilder->getQuery()->getSql();
        $this->lastParameters = $queryBuilder->getQuery()->getParameters();

        return $this->paginateResults($queryBuilder);
    }

    public function getLastQuery()
    {
        return [
            'query' => $this->lastQuery,
            'params' =>  $this->lastParameters,
        ];
    }

    protected function paginateResults(QueryBuilder $queryBuilder)
    {
        $ormAdapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfantaBuilder = new PagerfantaBuilder(new PagerfantaFactory(), $ormAdapter);
        $pager = new Pager();
        return $pager->paginateResults(
            $this->queryOptions,
            $ormAdapter,
            $pagerfantaBuilder,
            $this->routeName,
            $this->useResultCache
        );
    }

    protected function getCurrentEntityAlias() : string
    {
        return $this->currentEntityAlias;
    }

    protected function setCurrentEntityAlias(string $currentEntityAlias)
    {
        $this->currentEntityAlias = $currentEntityAlias;
    }

    protected function getEmbeddedFields() : array
    {
        return $this->embeddedFields;
    }

    protected function setEmbeddedFields(array $embeddedFields)
    {
        $this->embeddedFields = $embeddedFields;
    }

    public function getEntityAlias() : string
    {
        return $this->metadata->getEntityAlias();
    }

    protected function relationship($queryBuilder)
    {
        return $queryBuilder;
    }

    public function getQueryBuilderFactoryWithoutInitialization()
    {
        return $this->queryBuilderFactory;
    }
}
