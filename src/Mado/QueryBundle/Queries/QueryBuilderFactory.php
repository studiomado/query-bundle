<?php

namespace Mado\QueryBundle\Queries;

use Mado\Strings\CamelCaseParser;
use Doctrine\ORM\QueryBuilder;

class QueryBuilderFactory extends AbstractQuery
{
    const DIRECTION_AZ = 'asc';

    const DIRECTION_ZA = 'desc';

    const DEFAULT_OPERATOR = 'eq';

    /**
     * @var QueryBuilder
     */
    protected $qBuilder;

    protected $fields;

    protected $filtering;

    protected $orFiltering;

    protected $relationEntityAlias;

    protected $sorting;

    protected $joins;

    protected $rel;

    protected $printing;

    protected $page;

    protected $pageLength;

    protected $select;

    /** @todo move this file in configuration */
    /** @todo type number|text */
    private static $operatorMap = [
        //'eq' => [
            //'filtro' => '=',
            //'tipo' => 'data|numero|stringa',
            //'meta' => '%{foo}%'
        //],
        'eq' => [
            'meta' => '=',
        ],
        'neq' => [
            'meta' => '!=',
        ],
        'gt' => [
            'meta' => '>',
        ],
        'gte' => [
            'meta' => '>=',
        ],
        'lt' => [
            'meta' => '<',
        ],
        'lte' => [
            'meta' => '<=',
        ],
        'startswith' => [
            'meta' => 'LIKE',
            'substitution_pattern' => '{string}%'
        ],
        'contains' => [
            'meta' => 'LIKE',
            'substitution_pattern' => '%{string}%'
        ],
        'endswith' => [
            'meta' => 'LIKE',
            'substitution_pattern' => '%{string}'
        ],
        'list' => [
            'meta' => 'IN',
            'substitution_pattern' => '({string})',
        ],
        'field_eq' => [
            'meta' => '=',
        ],
    ];

    public function getAvailableFilters()
    {
        return array_keys(static::$operatorMap);
    }

    public function setFields(array $fields = [])
    {
        $this->fields = $fields;

        return $this;
    }

    public function getFields()
    {
        if (null === $this->fields) {
            throw new \RuntimeException(
                'Oops! Fields are not defined'
            );
        }

        return $this->fields;
    }

    public function setFilters(array $filtering = [])
    {
        $this->filtering = $filtering;

        return $this;
    }

    public function setOrFilters(array $orFiltering = [])
    {
        $this->orFiltering = $orFiltering;

        return $this;
    }

    public function setSorting(array $sorting = [])
    {
        $this->sorting = $sorting;

        return $this;
    }

    public function getFilters()
    {
        return $this->filtering;
    }

    public function getOrFilters()
    {
        return $this->orFiltering;
    }

    private function noExistsJoin($prevEntityAlias, $currentEntityAlias)
    {
        if (null === $this->joins) {
            $this->joins = [];
        }

        $needle = $prevEntityAlias . "_" . $currentEntityAlias;

        return ! in_array($needle, $this->joins);
    }

    private function storeJoin($prevEntityAlias, $currentEntityAlias)
    {
        $needle = $prevEntityAlias . "_" . $currentEntityAlias;
        $this->joins[$needle] = $needle;
    }

    /**
     * @param String $relation Nome della relazione semplice (groups.name) o con embedded (_embedded.groups.name)
     * @return $this
     */
    public function join(String $relation){

        $parser = new CamelCaseParser();
        $relation = explode('|',$relation)[0];
        $relations = [$relation];

        if (strstr($relation, '_embedded.')) {
            $embeddedFields = explode('.', $relation);
            $parser = new CamelCaseParser();
            $relation = $parser->trans($embeddedFields[1]);

            // elimino l'ultimo elemento che dovrebbe essere il nome del campo
            unset($embeddedFields[count($embeddedFields)-1]);

            // elimino il primo elemento _embedded
            unset($embeddedFields[0]);

            $relations = $embeddedFields;
        }

        $entityName = $this->getEntityName();
        $entityAlias = $this->entityAlias;

        foreach ($relations as $relation) {

            $relation = $parser->trans($relation);
            $relationEntityAlias = 'table_' . $relation;

            $metadata = $this->manager->getClassMetadata($entityName);

            if ($metadata->hasAssociation($relation)) {

                $association = $metadata->getAssociationMapping($relation);

                $fieldName = $parser->trans($association['fieldName']);

                if ($this->noExistsJoin($relationEntityAlias, $relation)) {

                    $this->qBuilder
                        ->join($entityAlias . "." . $fieldName, $relationEntityAlias);

                    $this->storeJoin($relationEntityAlias, $relation);
                }

                $entityName = $association['targetEntity'];
                $entityAlias = $relationEntityAlias;
            }

            $this->setRelationEntityAlias($relationEntityAlias);
        }

        return $this;
    }

    public function filter()
    {

        if (null === $this->filtering) {
            throw new \RuntimeException(
                'Oops! Filtering is not defined'
            );
        }

        if (!$this->fields) {
            throw new \RuntimeException(
                'Oops! Fields are not defined'
            );
        }

        $parser = new CamelCaseParser();

        foreach ($this->filtering as $filter => $value) {
            $this->applyFilter('andWhere', $filter, $parser, $value);
        }

        if ($this->orFiltering) {
            foreach ($this->orFiltering as $filter => $value) {
                $this->applyFilter('orWhere', $filter, $parser, $value);
            }
        }

        return $this;
    }

    private function applyFilter($filterMethod, $filter, $parser, $value)
    {
        $whereCondition = null;
        $filterAndOperator = explode('|',$filter);

        $fieldName = $filterAndOperator[0];
        $fieldName = $parser->trans($fieldName);

        $operator = self::$operatorMap[self::DEFAULT_OPERATOR];
        if(isset($filterAndOperator[1])){
            $operator = self::$operatorMap[$filterAndOperator[1]];
        }

        // controllo se il filtro che mi arriva dalla richiesta è una proprietà di questa entità
        // esempio per users: filtering[username|contains]=mado
        if (in_array($fieldName, $this->fields)) {

            $salt = '';
            foreach ($this->qBuilder->getParameters() as $parameter) {
                if ($parameter->getName() == 'field_' . $fieldName) {
                    $salt = '_' . rand(111, 999);
                }
            }

            if (isset($filterAndOperator[1])) {
                if ('list' == $filterAndOperator[1]) {
                    $whereCondition = $this->entityAlias.'.'.$fieldName.' '.$operator['meta'].' (:field_'.$fieldName . $salt . ')';
                } else if ('field_eq' == $filterAndOperator[1]) {
                    $whereCondition =
                        $this->entityAlias . '.' . $fieldName . ' '.
                        $operator['meta'] . '' .
                        $this->entityAlias . '.' . $value
                    ;
                } else {
                    $whereCondition = $this->entityAlias.'.'.$fieldName.' '.$operator['meta'].' :field_'.$fieldName . $salt;
                }
            } else {
                $whereCondition = $this->entityAlias.'.'.$fieldName.' = :field_'.$fieldName . $salt;
            }

            $this->qBuilder->$filterMethod($whereCondition);

            if (isset($operator['substitution_pattern'])) {
                if (isset($filterAndOperator[1]) && 'list' == $filterAndOperator[1]) {
                    $value = explode(',', $value);
                } else {
                    $value = str_replace(
                        '{string}',
                        $value,
                        $operator['substitution_pattern']
                    );
                }
            }

            $this->qBuilder->setParameter('field_' . $fieldName . $salt, $value);
        } else {
            $isNotARelation = 0 !== strpos($fieldName, 'Embedded.');
            if ($isNotARelation) {
                $whereCondition = $this->entityAlias.'.'.$fieldName.' '.$operator['meta'].' ' . $this->entityAlias . '.' . $value;
                $this->qBuilder->$filterMethod($whereCondition);
            }
        }

        // controllo se il filtro si riferisce ad una relazione dell'entità quindi devo fare dei join
        // esempio per users: filtering[_embedded.groups.name|eq]=admin
        if (strstr($filter, '_embedded.')) {

            $this->join($filter);
            $relationEntityAlias = $this->getRelationEntityAlias();

            $embeddedFields = explode('.', $fieldName);
            $fieldName = $parser->trans($embeddedFields[count($embeddedFields)-1]);

            $salt = '';
            foreach ($this->qBuilder->getParameters() as $parameter) {
                if ($parameter->getName() == 'field_' . $fieldName) {
                    $salt = '_' . rand(111, 999);
                }
            }

            if (isset($filterAndOperator[1]) && 'list' == $filterAndOperator[1]) {
                $whereCondition = $relationEntityAlias.'.'.$fieldName.' '.$operator['meta'].' (:field_'.$fieldName . $salt . ')';
            } else {
                $whereCondition = $relationEntityAlias.'.'.$fieldName.' '.$operator['meta'].' :field_'.$fieldName . $salt;
            }

            $this->qBuilder->$filterMethod($whereCondition);
            if (isset($operator['substitution_pattern'])) {
                if (isset($filterAndOperator[1]) && 'list' == $filterAndOperator[1]) {
                    $value = explode(',', $value);
                } else {
                    $value = str_replace(
                        '{string}',
                        $value,
                        $operator['substitution_pattern']
                    );
                }
            }
            $this->qBuilder->setParameter('field_' . $fieldName . $salt, $value);
        }
    }

    public function sort()
    {
        if (!$this->fields) {
            throw new \RuntimeException(
                'Oops! Fields are not defined'
            );
        }

        if (null === $this->sorting) {
            throw new \RuntimeException(
                'Oops! Sorting is not defined'
            );
        }

        $parser = new CamelCaseParser();

        foreach ($this->sorting as $sort => $val) {
            $val = strtolower($val);

            $fieldName = $parser->trans($sort);

            if (in_array($fieldName, $this->fields)) {
                $direction = ($val === self::DIRECTION_AZ) ? self::DIRECTION_AZ : self::DIRECTION_ZA;
                $this->qBuilder->addOrderBy($this->entityAlias .'.'. $fieldName, $direction);
            }

            if (strstr($sort, '_embedded.')) {
                $this->join($sort);
                $relationEntityAlias = $this->getRelationEntityAlias();

                $embeddedFields = explode('.', $sort);
                $fieldName = $parser->trans($embeddedFields[2]);
                $direction = ($val === self::DIRECTION_AZ) ? self::DIRECTION_AZ : self::DIRECTION_ZA;

                $this->qBuilder->addOrderBy($relationEntityAlias.'.'.$fieldName, $direction);
            }

        }

        return $this;
    }

    public function getQueryBuilder()
    {
        if (!$this->qBuilder) {
            throw new \RuntimeException(
                "Oops! Query builder was never initialized! call ::createQueryBuilder('entityName', 'alias') to start."
            );
        }

        return $this->qBuilder;
    }

    public function buildSelectValue() : string
    {
        if ("" == $this->getSelect()) {
            return $this->getEntityAlias(
                $this->getEntityName()
            );
        }

        return $this->getSelect();
    }

    public function createQuery()
    {
        if (null == $this->getEntityName()) {
            throw new \RuntimeException(
                'Oops! Entity name is missing'
            );
        }

        $alias = $this->getEntityAlias(
            $this->getEntityName()
        );

        $this->qBuilder->select('s')
            ->from('SvaBundle\Entity\Sva', 's');

        return $this->qBuilder->getQuery();
    }

    private function setRelationEntityAlias(string $relationEntityAlias)
    {
        $this->relationEntityAlias = $relationEntityAlias;
    }

    private function getRelationEntityAlias()
    {
        return $this->relationEntityAlias;
    }

    public function setRel($rel)
    {
        $this->rel = $rel;

        return $this;
    }

    public function getRel()
    {
        return $this->rel;
    }

    public function setPrinting($printing)
    {
        $this->printing = $printing;

        return $this;
    }

    public function getPrinting()
    {
        return $this->printing;
    }

    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setPageLength($pageLength)
    {
        $this->pageLength = $pageLength;

        return $this;
    }

    public function getPageLength()
    {
        return $this->pageLength;
    }

    public function setSelect(string $select) : QueryBuilderFactory
    {
        $this->select = $select;

        return $this;
    }

    public function getSelect()
    {
        return $this->select;
    }

    public function getEntityManager()
    {
        return $this->manager;
    }
}
