<?php

namespace Mado\QueryBundle\Queries;

use Doctrine\ORM\QueryBuilder;
use Mado\QueryBundle\Dictionary\Operators;

class QueryBuilderFactory extends AbstractQuery
{
    const DIRECTION_AZ = 'asc';

    const DIRECTION_ZA = 'desc';

    const DEFAULT_OPERATOR = 'eq';

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

    public function getAvailableFilters()
    {
        return array_keys($this->getValueAvailableFilters());
    }

    public function getValueAvailableFilters()
    {
        return Operators::getOperators();
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
    public function join(String $relation)
    {
        $relation = explode('|', $relation)[0];
        $relations = [$relation];

        if (strstr($relation, '_embedded.')) {
            $embeddedFields = explode('.', $relation);
            $relation = $this->parser->camelize($embeddedFields[1]);

            // elimino l'ultimo elemento che dovrebbe essere il nome del campo
            unset($embeddedFields[count($embeddedFields) - 1]);

            // elimino il primo elemento _embedded
            unset($embeddedFields[0]);

            $relations = $embeddedFields;
        }

        $entityName = $this->getEntityName();
        $entityAlias = $this->entityAlias;

        foreach ($relations as $relation) {

            $relation = $this->parser->camelize($relation);
            $relationEntityAlias = 'table_' . $relation;

            $metadata = $this->manager->getClassMetadata($entityName);

            if ($metadata->hasAssociation($relation)) {

                $association = $metadata->getAssociationMapping($relation);

                $fieldName = $this->parser->camelize($association['fieldName']);

                if ($this->noExistsJoin($relationEntityAlias, $relation)) {
                    $this->qBuilder->join($entityAlias . "." . $fieldName, $relationEntityAlias);
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

        foreach ($this->filtering as $filter => $value) {
            $this->applyFilterAnd($filter, $value);
        }

        if (null !== $this->orFiltering) {
            $orFilter = [];
            $orFilter['orCondition'] = null;
            $orFilter['parameters'] = [];

            foreach ($this->orFiltering as $filter => $value) {
                $orFilter = $this->applyFilterOr($filter, $value, $orFilter);
            }

            if ((count($orFilter) > 0) && ($orFilter['orCondition'] != null)) {
                $this->qBuilder->andWhere($orFilter['orCondition']);

                foreach ($orFilter['parameters'] as $parameter) {
                    $this->qBuilder->setParameter($parameter['field'], $parameter['value']);
                }
            }
        }

        return $this;
    }

    private function applyFilterAnd($filter, $value)
    {
        $whereCondition = null;
        $filterAndOperator = explode('|',$filter);

        $fieldName = $filterAndOperator[0];
        $fieldName = $this->parser->camelize($fieldName);

        $operator = $this->getValueAvailableFilters()[self::DEFAULT_OPERATOR];
        if(isset($filterAndOperator[1])){
            $operator = $this->getValueAvailableFilters()[$filterAndOperator[1]];
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

            // filtering[foo|bar]
            // $filterAndOperator[0] = 'foo'
            // $filterAndOperator[1] = 'bar'
            if (isset($filterAndOperator[1])) {
                if ('list' == $filterAndOperator[1]) {
                    $whereCondition = $this->entityAlias.'.'.$fieldName.' '.$operator['meta'].' (:field_'.$fieldName . $salt . ')';
                } else if ('field_eq' == $filterAndOperator[1]) {
                    $whereCondition =
                        $this->entityAlias . '.' . $fieldName . ' '.
                        $operator['meta'] . '' .
                        $this->entityAlias . '.' . $value
                        ;
                    //} else {
                    //throw new \RuntimeException(
                    //'Oops! Eccezzione'
                    //);
                } else {
                    $whereCondition = $this->entityAlias.'.'.$fieldName.' '.$operator['meta'].' :field_'.$fieldName . $salt;
                }
            } else {
                $whereCondition = $this->entityAlias.'.'.$fieldName.' = :field_'.$fieldName . $salt;
            }

            $this->qBuilder->andWhere($whereCondition);

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
                $this->qBuilder->andWhere($whereCondition);
            }
        }

        // controllo se il filtro si riferisce ad una relazione dell'entità quindi devo fare dei join
        // esempio per users: filtering[_embedded.groups.name|eq]=admin
        if (strstr($filter, '_embedded.')) {

            $this->join($filter);
            $relationEntityAlias = $this->getRelationEntityAlias();

            $embeddedFields = explode('.', $fieldName);
            $fieldName = $this->parser->camelize($embeddedFields[count($embeddedFields)-1]);

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

            $this->qBuilder->andWhere($whereCondition);
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

    private function applyFilterOr($filter, $value, $orCondition)
    {
        $whereCondition = null;
        $filterAndOperator = explode('|',$filter);

        $fieldName = $filterAndOperator[0];
        $fieldName = $this->parser->camelize($fieldName);

        $operator = $this->getValueAvailableFilters()[self::DEFAULT_OPERATOR];
        if(isset($filterAndOperator[1])){
            $operator = $this->getValueAvailableFilters()[$filterAndOperator[1]];
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

            if ($salt == '') {
                $salt = '_' . rand(111, 999);
            }

            // filtering[foo|bar]
            // $filterAndOperator[0] = 'foo'
            // $filterAndOperator[1] = 'bar'
            if (isset($filterAndOperator[1])) {
                if ('list' == $filterAndOperator[1]) {
                    $whereCondition = $this->entityAlias.'.'.$fieldName.' '.$operator['meta'].' (:field_'.$fieldName . $salt . ')';
                } else if ('field_eq' == $filterAndOperator[1]) {
                    $whereCondition =
                        $this->entityAlias . '.' . $fieldName . ' '.
                        $operator['meta'] . '' .
                        $this->entityAlias . '.' . $value
                    ;
                    //} else {
                    //throw new \RuntimeException(
                    //'Oops! Eccezzione'
                    //);
                } else {
                    $whereCondition = $this->entityAlias.'.'.$fieldName.' '.$operator['meta'].' :field_'.$fieldName . $salt;
                }
            } else {
                $whereCondition = $this->entityAlias.'.'.$fieldName.' = :field_'.$fieldName . $salt;
            }

            if ($orCondition['orCondition'] != null) {
                $orCondition['orCondition'] .= ' OR ' . $whereCondition;
            } else {
                $orCondition['orCondition'] = $whereCondition;
            }

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

            $orCondition['parameters'][] = [
                'field' => 'field_' . $fieldName . $salt,
                'value' => $value
            ];
        } else {
            $isNotARelation = 0 !== strpos($fieldName, 'Embedded.');
            if ($isNotARelation) {
                $whereCondition = $this->entityAlias.'.'.$fieldName.' '.$operator['meta'].' ' . $this->entityAlias . '.' . $value;
                if ($orCondition['orCondition'] != null) {
                    $orCondition['orCondition'] .= ' OR ' . $whereCondition;
                } else {
                    $orCondition['orCondition'] = $whereCondition;
                }
            }
        }

        // controllo se il filtro si riferisce ad una relazione dell'entità quindi devo fare dei join
        // esempio per users: filtering[_embedded.groups.name|eq]=admin
        if (strstr($filter, '_embedded.')) {

            $this->join($filter);
            $relationEntityAlias = $this->getRelationEntityAlias();

            $embeddedFields = explode('.', $fieldName);
            $fieldName = $this->parser->camelize($embeddedFields[count($embeddedFields)-1]);

            $salt = '';
            foreach ($this->qBuilder->getParameters() as $parameter) {
                if ($parameter->getName() == 'field_' . $fieldName) {
                    $salt = '_' . rand(111, 999);
                }
            }

            if ($salt == '') {
                $salt = '_' . rand(111, 999);
            }

            if (isset($filterAndOperator[1]) && 'list' == $filterAndOperator[1]) {
                $whereCondition = $relationEntityAlias.'.'.$fieldName.' '.$operator['meta'].' (:field_'.$fieldName . $salt . ')';
            } else {
                $whereCondition = $relationEntityAlias.'.'.$fieldName.' '.$operator['meta'].' :field_'.$fieldName . $salt;
            }

            if ($orCondition['orCondition'] != null) {
                $orCondition['orCondition'] .= ' OR ' . $whereCondition;
            } else {
                $orCondition['orCondition'] = $whereCondition;
            }

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

            $orCondition['parameters'][] = [
                'field' => 'field_' . $fieldName . $salt,
                'value' => $value
            ];
        }

        return $orCondition;
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

        foreach ($this->sorting as $sort => $val) {
            $val = strtolower($val);

            $fieldName = $this->parser->camelize($sort);

            if (in_array($fieldName, $this->fields)) {
                $direction = ($val === self::DIRECTION_AZ) ? self::DIRECTION_AZ : self::DIRECTION_ZA;
                $this->qBuilder->addOrderBy($this->entityAlias .'.'. $fieldName, $direction);
            }

            if (strstr($sort, '_embedded.')) {
                $this->join($sort);
                $relationEntityAlias = $this->getRelationEntityAlias();

                $embeddedFields = explode('.', $sort);
                $fieldName = $this->parser->camelize($embeddedFields[2]);
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

    public function setSelect( $select) : QueryBuilderFactory
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
