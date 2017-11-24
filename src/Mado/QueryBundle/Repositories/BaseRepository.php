<?php

namespace Mado\QueryBundle\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Mado\QueryBundle\Component\ConfigProvider;
use Mado\QueryBundle\Queries\QueryBuilderFactory;
use Mado\QueryBundle\Queries\QueryBuilderOptions;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

class BaseRepository extends EntityRepository
{
    protected $fields;

    protected $request;

    protected $use_result_cache = false;

    protected $entityAlias;

    protected $route_name;

    protected $currentEntityAlias;

    protected $embeddedFields;

    protected $joins = [];

    protected $queryBuilderFactory;

    protected $queryOptions;

    protected $configProvider;

    public function __construct($manager, $class)
    {
        parent::__construct($manager, $class);

        $this->fields = array_keys($this->getClassMetadata()->fieldMappings);

        $entityName = explode('\\', strtolower($this->getEntityName()) );
        $entityName = $entityName[count($entityName)-1];
        //$entityAlias = $entityName[0];
        $this->entityAlias = $entityName;

        $this->queryBuilderFactory = new QueryBuilderFactory($this->getEntityManager());
    }

    public function initFromQueryBuilderOptions(QueryBuilderOptions $options)
    {
        $this->queryBuilderFactory->createQueryBuilder($this->getEntityName(), $this->entityAlias);

        $fieldMappings = $this->getClassMetadata()->fieldMappings;
        $this->fields = array_keys($fieldMappings);

        $this->queryBuilderFactory->setFields($this->fields ?? []);
        $this->queryBuilderFactory->setFilters($options->getFilters());
        $this->queryBuilderFactory->setOrFilters($options->getOrFilters());
        $this->queryBuilderFactory->setSorting($options->getSorting());
        $this->queryBuilderFactory->setRel($options->getRel());
        $this->queryBuilderFactory->setPrinting($options->getPrinting());
        $this->queryBuilderFactory->setSelect($options->getSelect());
    }

    public function getQueryBuilderFactory()
    {
        $this->ensureQueryOptionIsDefined();

        $this->initFromQueryBuilderOptions($this->queryOptions);

        return $this->queryBuilderFactory;
    }

    public function useResultCache($bool)
    {
        $this->use_result_cache = $bool;
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
        $requestAttributes = [];
        foreach ($request->attributes->all() as $attributeName => $attributeValue) {
            $requestAttributes[$attributeName] = $request->attributes->get(
                $attributeName,
                $attributeValue
            );
        }

        $filters     = $request->query->get('filtering', []);
        $orFilters   = $request->query->get('filtering_or', []);
        $sorting     = $request->query->get('sorting', []);
        $printing    = $request->query->get('printing', []);
        $rel         = $request->query->get('rel', '');
        $page        = $request->query->get('page', '');
        $select      = $request->query->get('select', $this->entityAlias);
        $pageLength  = $request->query->get('limit', 666);
        $filtering   = $request->query->get('filtering', '');
        $limit       = $request->query->get('limit', '');

        $filterOrCorrected = [];

        $count = 0;
        foreach ($orFilters as $key => $filter) {
            if (is_array($filter)) {
                foreach ($filter as $keyInternal => $internal) {
                    $filterOrCorrected[$keyInternal .'|' . $count] = $internal;
                    $count = $count + 1;
                }
            } else {
                $filterOrCorrected[$key] = $filter;
            }
        }

        $requestProperties = [
            'filtering'   => $filtering,
            'orFiltering' => $filterOrCorrected,
            'limit'       => $limit,
            'page'        => $page,
            'filters'     => $filters,
            'orFilters'   => $filterOrCorrected,
            'sorting'     => $sorting,
            'rel'         => $rel,
            'printing'    => $printing,
            'select'      => $select,
        ];

        $options = array_merge(
            $requestAttributes,
            $requestProperties
        );

        $this->queryOptions = QueryBuilderOptions::fromArray($options);

        return $this;
    }

    public function setQueryOptionsFromRequestWithCustomFilter(Request $request = null, $filter)
    {
        $filters = $request->query->get('filtering', []);
        $orFilters = $request->query->get('filtering_or', []);
        $sorting = $request->query->get('sorting', []);
        $printing = $request->query->get('printing', []);
        $rel = $request->query->get('rel', '');
        $page = $request->query->get('page', '');
        $select = $request->query->get('select', $this->entityAlias);
        $pageLength = $request->query->get('limit', 666);
        $filtering = $request->query->get('filtering', '');
        $limit = $request->query->get('limit', '');

        $filters = array_merge($filters, $filter);

        $filterOrCorrected = [];

        $count = 0;
        foreach ($orFilters as $key => $filter) {
            if (is_array($filter)) {
                foreach ($filter as $keyInternal => $internal) {
                    $filterOrCorrected[$keyInternal .'|' . $count] = $internal;
                    $count = $count + 1;
                }
            } else {
                $filterOrCorrected[$key] = $filter;
            }
        }

        $this->queryOptions = QueryBuilderOptions::fromArray([
            '_route' => $request->attributes->get('_route'),
            'customer_id' => $request->attributes->get('customer_id'),
            'id' => $request->attributes->get('id'),
            'filtering' => $filtering,
            'limit' => $limit,
            'page' => $page,
            'filters' => $filters,
            'orFilters' => $filterOrCorrected,
            'sorting' => $sorting,
            'rel' => $rel,
            'printing' => $printing,
            'select' => $select,
        ]);

        return $this;
    }

    public function setQueryOptionsFromRequestWithCustomOrFilter(Request $request = null, $orFilter)
    {
        $filters = $request->query->get('filtering', []);
        $orFilters = $request->query->get('filtering_or', []);
        $sorting = $request->query->get('sorting', []);
        $printing = $request->query->get('printing', []);
        $rel = $request->query->get('rel', '');
        $page = $request->query->get('page', '');
        $select = $request->query->get('select', $this->entityAlias);
        $pageLength = $request->query->get('limit', 666);
        $filtering = $request->query->get('filtering', '');
        $limit = $request->query->get('limit', '');

        $orFilters = array_merge($orFilters, $orFilter);

        $filterOrCorrected = [];

        $count = 0;
        foreach ($orFilters as $key => $filter) {
            if (is_array($filter)) {
                foreach ($filter as $keyInternal => $internal) {
                    $filterOrCorrected[$keyInternal .'|' . $count] = $internal;
                    $count = $count + 1;
                }
            } else {
                $filterOrCorrected[$key] = $filter;
            }
        }

        $this->queryOptions = QueryBuilderOptions::fromArray([
            '_route' => $request->attributes->get('_route'),
            'customer_id' => $request->attributes->get('customer_id'),
            'id' => $request->attributes->get('id'),
            'filtering' => $filtering,
            'limit' => $limit,
            'page' => $page,
            'filters' => $filters,
            'orFilters' => $filterOrCorrected,
            'sorting' => $sorting,
            'rel' => $rel,
            'printing' => $printing,
            'select' => $select,
        ]);

        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setRouteName($route_name = '')
    {
        $this->route_name = $route_name;
        return $this;
    }

    public function findAllPaginated()
    {
        if ($this->configProvider) {
            $this->setRequest($this->configProvider->getRequest());
            $this->queryBuilderFactory->setConfigProvider($this->configProvider);
        }

        $this->ensureQueryOptionIsDefined();

        $this->initFromQueryBuilderOptions($this->queryOptions);

        $this->queryBuilderFactory->filter();
        $this->queryBuilderFactory->sort();

        $qb = $this->queryBuilderFactory->getQueryBuilder();

        if ($this->configProvider) {
            $qb = $this->configProvider->filterRelation($qb);
        }

        return $this->paginateResults($qb);
    }

    protected function paginateResults(
        \Doctrine\ORM\QueryBuilder $queryBuilder
    ) {
        $this->ensureQueryOptionIsDefined();

        $limit = $this->queryOptions->get('limit', 10);
        $page = $this->queryOptions->get('page', 1);


        $pagerAdapter = new DoctrineORMAdapter($queryBuilder);

        $query = $pagerAdapter->getQuery();
        if(isset($this->use_result_cache) and $this->use_result_cache){
            $query->useResultCache(true, 600);
        }

        $pager = new Pagerfanta($pagerAdapter);
        $pager->setNormalizeOutOfRangePages(true);
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        $pagerFactory = new PagerfantaFactory();

        $router = $this->createRouter();

        $results = $pagerFactory->createRepresentation($pager, $router);

        return $results;
    }

    protected function customQueryStringValues()
    {
        return [];
    }

    protected function createRouter()
    {
        $request = $this->getRequest();
        $params = [];

        $list = array_merge([
            'filtering',
            'limit',
            'page',
            'sorting',
        ], $this->customQueryStringValues());

        $this->ensureQueryOptionIsDefined();

        foreach ($list as $itemKey => $itemValue) {
            $params[$itemValue] = $this->queryOptions->get($itemValue);
        }

        if(!isset($this->route_name)){
            $this->route_name = $this->queryOptions->get('_route');
        }

        return new Route($this->route_name, $params);
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

    public function getEntityAlias(string $entityName) : string
    {
        $arrayEntityName = explode('\\', strtolower($entityName) );
        return $arrayEntityName[count($arrayEntityName)-1];
    }

    protected function relationship($queryBuilder)
    {
        return $queryBuilder;
    }

    public function getQueryBuilderFactoryWithoutInitialization()
    {
        return $this->queryBuilderFactory;
    }

    public function setConfigProvider(ConfigProvider $provider, array $domainConfiguration = [])
    {
        $this->configProvider = $provider;
        $this->configProvider->setDomainConfiguration($domainConfiguration);

        return $this;
    }

    public function ensureQueryOptionIsDefined()
    {
        if (!$this->queryOptions) {
            throw new \RuntimeException(
                'Oops! QueryBuilderOptions was never defined.'
            );
        }
    }
}
