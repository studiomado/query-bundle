<?php

namespace Mado\QueryBundle\Repositories;

use Doctrine\ORM\EntityRepository;
use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Mado\QueryBundle\Objects\MetaDataAdapter;
use Mado\QueryBundle\Queries\QueryBuilderFactory;
use Mado\QueryBundle\Queries\QueryBuilderOptions;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

class BaseRepository extends EntityRepository
{
    protected $request;

    protected $use_result_cache = false;

    protected $route_name;

    protected $currentEntityAlias;

    protected $embeddedFields;

    protected $joins = [];

    protected $queryBuilderFactory;

    protected $queryOptions;

    protected $metadata;

    public function __construct($manager, $classMetadata)
    {
        parent::__construct($manager, $classMetadata);

        $this->metadata = $this(new MetaDataAdapter(), $this);

        $this->queryBuilderFactory = new QueryBuilderFactory(
            $this->getEntityManager()
        );
    }

    /** @since version 2.2 */
    public function __invoke(
        MetaDataAdapter $metadata,
        BaseRepository $repository
    ) {
        $metadata->setClassMetadata($repository->getClassMetadata());
        $metadata->setEntityName($repository->getEntityName());

        return $metadata;
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
        if (!$this->queryOptions) {
            throw new \RuntimeException(
                'Oops! QueryBuilderOptions is missing'
            );
        }

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
        $select      = $request->query->get('select', $this->metadata->getEntityAlias());
        $pageLength  = $request->query->get('limit', 666);
        $filtering   = $request->query->get('filtering', '');
        $limit       = $request->query->get('limit', '');

        $filterOrCorrected = [];

        $count = 0;
        foreach ($orFilters as $key => $filter) {
            if (is_array($filter)) {
                foreach ($filter as $keyInternal => $internal) {
                    $filterOrCorrected[$keyInternal . '|' . $count] = $internal;
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
        $select = $request->query->get('select', $this->metadata->getEntityAlias());
        $pageLength = $request->query->get('limit', 666);
        $filtering = $request->query->get('filtering', '');
        $limit = $request->query->get('limit', '');

        $filters = array_merge($filters, $filter);

        $filterOrCorrected = [];

        $count = 0;
        foreach ($orFilters as $key => $filter) {
            if (is_array($filter)) {
                foreach ($filter as $keyInternal => $internal) {
                    $filterOrCorrected[$keyInternal . '|' . $count] = $internal;
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
        $select = $request->query->get('select', $this->metadata->getEntityAlias());
        $pageLength = $request->query->get('limit', 666);
        $filtering = $request->query->get('filtering', '');
        $limit = $request->query->get('limit', '');

        $orFilters = array_merge($orFilters, $orFilter);

        $filterOrCorrected = [];

        $count = 0;
        foreach ($orFilters as $key => $filter) {
            if (is_array($filter)) {
                foreach ($filter as $keyInternal => $internal) {
                    $filterOrCorrected[$keyInternal . '|' . $count] = $internal;
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
        $this->initFromQueryBuilderOptions($this->queryOptions);

        $this->queryBuilderFactory->filter();
        $this->queryBuilderFactory->sort();

        $queryBuilder = $this->queryBuilderFactory->getQueryBuilder();

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

    protected function paginateResults(
        \Doctrine\ORM\QueryBuilder $queryBuilder
    ) {
        $limit = $this->queryOptions->get('limit', 10);
        $page = $this->queryOptions->get('page', 1);


        $pagerAdapter = new DoctrineORMAdapter($queryBuilder);

        $query = $pagerAdapter->getQuery();
        if (isset($this->use_result_cache) and $this->use_result_cache) {
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

        foreach ($list as $itemKey => $itemValue) {
            $params[$itemValue] = $this->queryOptions->get($itemValue);
        }

        if (!isset($this->route_name)) {
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
