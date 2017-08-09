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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class BaseRepository extends EntityRepository implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $fields;

    protected $request;

    protected $use_result_cache = false;

    protected $entityAlias;

    protected $route_name;

    protected $currentEntityAlias;

    protected $embeddedFields;

    protected $joins = [];

    /**
     * @var QueryBuilderFactory
     */
    protected $queryBuilderFactory;

    /**
     * @var QueryBuilderOptions
     */
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

        if(!isset($this->route_name)){
            $this->route_name = $this->queryOptions->get('_route');
        }

        return new Route($this->route_name, $params);
    }

    /** @deprecate use QueryBuilderFatory instead */
    public function noExistsJoin($prevEntityAlias, $currentEntityAlias)
    {
        $needle = $prevEntityAlias . "_" . $currentEntityAlias;
        return ! in_array($needle, $this->joins);
    }

    /** @deprecate use QueryBuilderFatory instead */
    public function storeJoin($prevEntityAlias, $currentEntityAlias)
    {
        $needle = $prevEntityAlias . "_" . $currentEntityAlias;
        $this->joins[$needle] = $needle;
    }

    /** @deprecate use QueryBuilderFatory instead */
    public function join($queryBuilder, $key, $val) 
    {
        if (strstr($key, '_embedded.')) {
            $embeddedFields = explode('.', $key);
            $numFields = count($embeddedFields);

            $prevEntityAlias = $this->entityAlias; // Stocksellouts
            $prevEntityName = $this->getEntityName(); // Stocksellouts

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

    /** @deprecate use QueryBuilderFatory component instead */
    //protected function sort($queryBuilder)
    //{
        //$request = $this->getRequest();
        //$sorting = $request->query->get('sorting', array());

        //foreach ($this->fields as $field) {
            //if (isset($sorting[$field])) {
                //$direction = ($sorting[$field] === 'asc') ? 'asc' : 'desc';
                //$queryBuilder->addOrderBy($this->entityAlias.'.'.$field, $direction);
            //}
        //}

        //// &sorting[_embedded.{{relazione}}.{{campo}}={{val}}
        //foreach ($sorting as $sort => $val) {
            //if (strstr($sort, '_embedded.')) {

                //$queryBuilder = $this->join($queryBuilder, $sort, $val);

                //$currentEntityAlias = $this->getCurrentEntityAlias();
                //$embeddedFields = $this->getEmbeddedFields();
                //$numFields = count($embeddedFields);

                //$fieldName = $embeddedFields[$numFields - 1];
                //$direction = ($val === 'asc') ? 'asc' : 'desc';
                //$queryBuilder->addOrderBy("$currentEntityAlias." . $fieldName, $direction);
            //}
        //}

        //return $queryBuilder;
    //}

    public function getEntityAlias(string $entityName) : string
    {
        $arrayEntityName = explode('\\', strtolower($entityName) );
        $entityAlias = $arrayEntityName[count($arrayEntityName)-1];
        return $entityAlias;
    }

    /** @deprecate use QueryBuilderFatory component instead */
    //protected function filter($queryBuilder)
    //{
        //$request = $this->getRequest();
        //$filtering = $request->query->get('filtering', array());

        //foreach ($this->fields as $field) {
            //if (isset($filtering[$field])) {
                //switch ($field) {
                //case 'id':
                //case 'year':
                //case 'week':                        
                    //$queryBuilder->andWhere($this->entityAlias.'.'.$field.' = :filter_'.$field)
                        //->setParameter('filter_'.$field, $filtering[$field]);                        
                    //break;
                //default:
                    //$queryBuilder->andWhere($this->entityAlias.'.'.$field.' LIKE :filter_'.$field)
                        //->setParameter('filter_'.$field, '%'.$filtering[$field].'%');
                //}
            //}
        //}

        //// &filtering[_embedded.{{relazione}}.{{campo}}]={{val}}
        //foreach ($filtering as $filter => $val) {
            //if (strstr($filter, '_embedded.')) {

                //$queryBuilder = $this->join($queryBuilder, $filter, $val);

                //$currentEntityAlias = $this->getCurrentEntityAlias();
                //$embeddedFields = $this->getEmbeddedFields();
                //$numFields = count($embeddedFields);
                //$fieldName = $embeddedFields[$numFields - 1];

                //$paramName = str_replace(".", "_", $filter);

                //switch ($fieldName) {
                //case 'id':
                //case 'codiceClienteFornitore':
                //case 'codiceFamily':
                //case 'year':
                //case 'week':
                    //$queryBuilder->andWhere("$currentEntityAlias." . $fieldName . ' = :filter_' . $paramName)
                        //->setParameter('filter_' . $paramName, $val);
                    //break;
                //default :
                    //$queryBuilder->andWhere("$currentEntityAlias." . $fieldName . ' LIKE :filter_' . $paramName)
                        //->setParameter('filter_' . $paramName, '%' . $val . '%');                        
                //}
            //}
        //}

        //return $queryBuilder;
    //}

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
        //---CHIAVI
        $array_keys = array_keys($insertFields);
        $list_keys = '`' . implode('`,`', $array_keys) . '`';

        //---VALORI
        $list_values = "'" . implode("', '", $insertFields) . "'";

        $table = $this->getEntityManager()->getClassMetadata($this->getEntityName())->getTableName();

        $sql = 'INSERT INTO '.$table;
        $sql .= '('. $list_keys . ') ';
        //$sql .= 'VALUES("'.implode('","', $insertFields).'") ';
        $sql .= "VALUES(". $list_values.") ";
        $sql .= 'ON DUPLICATE KEY UPDATE ';

        $c = 0;
        foreach($updateFields as $column => $value) {
            if($c>0)$sql .= ", ";
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
