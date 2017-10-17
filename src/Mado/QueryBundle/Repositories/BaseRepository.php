<?php

namespace Mado\QueryBundle\Repositories;

use Doctrine\ORM\EntityRepository;
use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Mado\QueryBundle\Queries\QueryBuilderOptions;
use Mado\QueryBundle\Queries\QueryBuilderFactory;

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

    public function __construct($manager, $class)
    {
        parent::__construct($manager, $class);

        $this->fields = array_keys($this->getClassMetadata()->fieldMappings);

        $entityName = explode('\\', strtolower($this->getEntityName()) );
        $entityName = $entityName[count($entityName)-1];
        $entityAlias = $entityName[0];
        $this->entityAlias = $entityAlias;

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

        return $this;
    }

    public function getQueryBuilderFactory()
    {
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

        return $this;
    }

    public function setQueryOptionsFromRequest(Request $request = null)
    {
        $requestAttributes = self::getRequestAttributes($request);

        $filters     = $request->query->get('filtering', []);
        $orFilters   = $request->query->get('filtering_or', []);
        $sorting     = $request->query->get('sorting', []);
        $printing    = $request->query->get('printing', []);
        $rel         = $request->query->get('rel', '');
        $page        = $request->query->get('page', '');
        $select      = $request->query->get('select', $this->entityAlias);
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

    public static function getRequestAttributes(Request $request)
    {
        $requestAttributes = [];

        foreach ($request->attributes->all() as $attributeName => $attributeValue) {
            $requestAttributes[$attributeName] = $request->attributes->get(
                $attributeName,
                $attributeValue
            );
        }

        return $requestAttributes;
    }

    public function setQueryOptionsFromRequestWithCustomFilter(Request $request = null, $filter)
    {
        $requestAttributes = self::getRequestAttributes($request);

        $filters = $request->query->get('filtering', []);
        $orFilters = $request->query->get('filtering_or', []);
        $sorting = $request->query->get('sorting', []);
        $printing = $request->query->get('printing', []);
        $rel = $request->query->get('rel', '');
        $page = $request->query->get('page', '');
        $select = $request->query->get('select', $this->entityAlias);
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

        $requestProperties = [
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
        ];

        $options = array_merge(
            $requestAttributes,
            $requestProperties
        );

        $this->queryOptions = QueryBuilderOptions::fromArray($options);

        return $this;
    }

    public function setQueryOptionsFromRequestWithCustomOrFilter(Request $request = null, $orFilter)
    {
        $requestAttributes = self::getRequestAttributes($request);

        $filters = $request->query->get('filtering', []);
        $orFilters = $request->query->get('filtering_or', []);
        $sorting = $request->query->get('sorting', []);
        $printing = $request->query->get('printing', []);
        $rel = $request->query->get('rel', '');
        $page = $request->query->get('page', '');
        $select = $request->query->get('select', $this->entityAlias);
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

        $requestProperties = [
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
        ];

        $options = array_merge(
            $requestAttributes,
            $requestProperties
        );

        $this->queryOptions = QueryBuilderOptions::fromArray($options);

        return $this;
    }

    public function getQueryBuilderOptions()
    {
        return $this->queryOptions;
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

        return $this->paginateResults($this->queryBuilderFactory->getQueryBuilder());
    }

    protected function paginateResults(
        \Doctrine\ORM\QueryBuilder $queryBuilder
    ) {
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

        if(!isset($this->route_name)){
            $this->route_name = $this->queryOptions->get('_route');
        }

        return new Route($this->route_name, $params);
    }

    /** @deprecate use QueryBuilderFactory instead */
    public function noExistsJoin($prevEntityAlias, $currentEntityAlias)
    {
        $needle = $prevEntityAlias . "_" . $currentEntityAlias;
        return ! in_array($needle, $this->joins);
    }

    /** @deprecate use QueryBuilderFactory instead */
    public function storeJoin($prevEntityAlias, $currentEntityAlias)
    {
        $needle = $prevEntityAlias . "_" . $currentEntityAlias;
        $this->joins[$needle] = $needle;
    }

    /** @deprecate use QueryBuilderFactory instead */
    public function join($queryBuilder, $key, $val) 
    {
        if (strstr($key, '_embedded.')) {
            $embeddedFields = explode('.', $key);
            $numFields = count($embeddedFields);

            $prevEntityAlias = $this->entityAlias;
            $prevEntityName = $this->getEntityName();

            for ($i = 1; $i < $numFields - 1; $i++) {
                $metadata = $this->getEntityManager()->getClassMetadata($prevEntityName);

                $currentRelation = $embeddedFields[$i];

                if ($metadata->hasAssociation($currentRelation)) {

                    $association = $metadata->getAssociationMapping($currentRelation);

                    $currentEntityAlias = $this->getEntityAlias($association['targetEntity']);

                    if ($this->noExistsJoin($prevEntityAlias, $currentRelation)) {
                        if ($association['isOwningSide']) {
                            $queryBuilder->join($association['targetEntity'], "$currentEntityAlias", "WITH", "$currentEntityAlias.id = " . "$prevEntityAlias.$currentRelation");
                        } else {
                            $mappedBy = $association['mappedBy'];
                            $queryBuilder->join($association['targetEntity'], "$currentEntityAlias", "WITH", "$currentEntityAlias.$mappedBy = " . "$prevEntityAlias.id");
                        }

                        $this->storeJoin($prevEntityAlias, $currentRelation);
                    }
                }

                $prevEntityAlias = $currentEntityAlias;
                $prevEntityName = $association['targetEntity'];
            }

            $this->setEmbeddedFields($embeddedFields);
            $this->setCurrentEntityAlias($currentEntityAlias);
        }

        return $queryBuilder;
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
        $entityAlias = $arrayEntityName[count($arrayEntityName)-1];
        return $entityAlias;
    }

    protected function relationship($queryBuilder)
    {
        return $queryBuilder;
    }

    /**
     *
     * @param type $insertFields
     * @param type $updateFields
     *
     * USE:
     *
     * $this->getEntityManager()
     *      ->getRepository('User')
     *      ->onDuplicateUpdate(['column1' => 'user_reminder_1', 'column2' => 235], ['column2' => 255]);
     */
    public function onDuplicateUpdate($insertFields, $updateFields)
    {
        $array_keys = array_keys($insertFields);
        $list_keys = '`' . implode('`,`', $array_keys) . '`';

        $list_values = "'" . implode("', '", $insertFields) . "'";

        $table = $this->getEntityManager()->getClassMetadata($this->getEntityName())->getTableName();

        $sql = 'INSERT INTO '.$table;
        $sql .= '('. $list_keys . ') ';
        $sql .= "VALUES(". $list_values.") ";
        $sql .= 'ON DUPLICATE KEY UPDATE ';

        $c = 0;
        foreach($updateFields as $column => $value) {
            if ($c > 0) {
                $sql .= ", ";
            }

            $sql .= '`'.$column . "` = '". $value."'";
            $c++;
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
    }

    public function getQueryBuilderFactoryWithoutInitialization()
    {
        return $this->queryBuilderFactory;
    }
}
