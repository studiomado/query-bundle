<?php

namespace Mado\QueryBundle\Queries;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Mado\QueryBundle\Component\Meta\Exceptions\UnInitializedQueryBuilderException;
use Mado\QueryBundle\Dictionary;
use Mado\QueryBundle\Exceptions;
use Mado\QueryBundle\Queries\Objects\FilterObject;

class QueryBuilderFactory extends AbstractQuery
{
    const DIRECTION_AZ = 'asc';

    const DIRECTION_ZA = 'desc';

    const DEFAULT_OPERATOR = 'eq';

    protected $qBuilder;

    protected $fields;

    protected $andFilters;

    protected $orFilters;

    private $relationEntityAlias;

    protected $sorting;

    private $joins;

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
        return Dictionary::getOperators();
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

    /** @since version 2.2 */
    public function setAndFilters(array $andFilters = [])
    {
        $this->andFilters = $andFilters;

        return $this;
    }

    public function setOrFilters(array $orFilters = [])
    {
        $this->orFilters = $orFilters;

        return $this;
    }

    public function setSorting(array $sorting = [])
    {
        $this->sorting = $sorting;

        return $this;
    }

    public function getAndFilters()
    {
        return $this->andFilters;
    }

    public function getOrFilters()
    {
        return $this->orFilters;
    }

    private function noExistsJoin($prevEntityAlias, $currentEntityAlias)
    {
        if (null === $this->joins) {
            $this->joins = [];
        }

        $needle = $prevEntityAlias . '_' . $currentEntityAlias;

        return !in_array($needle, $this->joins);
    }

    private function storeJoin($prevEntityAlias, $currentEntityAlias)
    {
        $needle = $prevEntityAlias . '_' . $currentEntityAlias;
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
            $this->parser->camelize($embeddedFields[1]);

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
        if (null === $this->andFilters && null === $this->orFilters) {
            throw new Exceptions\MissingFiltersException();
        }

        if (!$this->fields) {
            throw new Exceptions\MissingFieldsException();
        }

        if (null !== $this->andFilters) {
            foreach ($this->andFilters as $filter => $value) {
                $this->applyFilterAnd(
                    Objects\FilterObject::fromRawFilter($filter),
                    $value,
                    Objects\Value::fromFilter($value)
                );
            }
        }

        if (null !== $this->orFilters) {
            $orFilter = [];
            $orFilter['orCondition'] = null;
            $orFilter['parameters'] = [];

            foreach ($this->orFilters as $filter => $value) {
                $orFilter = $this->applyFilterOr(
                    Objects\FilterObject::fromRawFilter($filter),
                    $value,
                    $orFilter
                );
            }

            if ((count($orFilter) > 0) && (null != $orFilter['orCondition'])) {
                $this->qBuilder->andWhere($orFilter['orCondition']);

                foreach ($orFilter['parameters'] as $parameter) {
                    $this->qBuilder->setParameter($parameter['field'], $parameter['value']);
                }
            }
        }

        return $this;
    }

    private function applyFilterAnd(
        Objects\FilterObject $filterObject,
        $value,
        Objects\Value $filterValue
    ) {
        $whereCondition = $this->entityAlias . '.' . $filterObject->getFieldName() . ' '
            . $filterObject->getOperatorMeta();

        if (in_array($filterObject->getFieldName(), $this->fields)) {
            $salt = '_' . random_int(111, 999);

            if ($filterObject->isListType()) {
                $whereCondition .= ' (:field_' . $filterObject->getFieldName() . $salt . ')';
            } elseif ($filterObject->isFieldEqualityType()) {
                $whereCondition .= ' ' . $this->entityAlias . '.' . $value;
            } else {
                $whereCondition .= ' :field_' . $filterObject->getFieldName() . $salt;
            }

            $this->qBuilder->andWhere($whereCondition);

            if ($filterObject->haveOperatorSubstitutionPattern()) {
                if ($filterObject->isListType()) {
                    $value = explode(',', $value);
                } else {
                    $value = str_replace(
                        '{string}',
                        $value,
                        $filterObject->getOperatorsSubstitutionPattern()
                    );
                }
            }

            $this->qBuilder->setParameter('field_' . $filterObject->getFieldName() . $salt, $value);
        } else {
            if (strpos($filterObject->getFieldName(), 'Embedded.') === false) {
                $whereCondition .= ' ' . $this->entityAlias . '.' . $value;
                $this->qBuilder->andWhere($whereCondition);
            }
        }

        // controllo se il filtro si riferisce ad una relazione dell'entità quindi devo fare dei join
        // esempio per users: filtering[_embedded.groups.name|eq]=admin
        if (strstr($filterObject->getRawFilter(), '_embedded.')) {

            $this->join($filterObject->getRawFilter());
            $relationEntityAlias = $this->getRelationEntityAlias();

            $whereConditionObject = Objects\WhereCondition::fromFilterObject(
                $this->parser,
                $filterObject,
                $relationEntityAlias
            );

            $whereCondition = $whereConditionObject->getWhereCondition();

            $this->qBuilder->andWhere($whereCondition);
            if ($filterObject->haveOperatorSubstitutionPattern()) {
                if ($filterObject->isListType()) {
                    $value = explode(',', $filterValue->getFilter());
                } else {
                    $value = str_replace(
                        '{string}',
                        $value,
                        $filterObject->getOperatorsSubstitutionPattern()
                    );
                }
            }

            $this->qBuilder->setParameter(
                $whereConditionObject->getParameterName(),
                $value
            );
        }
    }

    private function applyFilterOr(
        Objects\FilterObject $filterObject,
        $value,
        $orCondition
    ) {
        $whereCondition = $this->entityAlias . '.' . $filterObject->getFieldName() . ' '
            . $filterObject->getOperatorMeta();

        // controllo se il filtro che mi arriva dalla richiesta è una proprietà di questa entità
        // esempio per users: filtering[username|contains]=mado
        if (in_array($filterObject->getFieldName(), $this->fields)) {
            $salt = '_' . random_int(111, 999);

            if ($filterObject->isListType()) {
                $whereCondition .= ' (:field_' . $filterObject->getFieldName() . $salt . ')';
            } else if ($filterObject->isFieldEqualityType()) {
                $whereCondition .= $this->entityAlias . '.' . $value;
            } else {
                $whereCondition .= ' :field_' . $filterObject->getFieldName() . $salt;
            }

            if (null != $orCondition['orCondition']) {
                $orCondition['orCondition'] .= ' OR ' . $whereCondition;
            } else {
                $orCondition['orCondition'] = $whereCondition;
            }

            if ($filterObject->haveOperatorSubstitutionPattern()) {
                if ($filterObject->isListType()) {
                    $value = explode(',', $value);
                } else {
                    $value = str_replace(
                        '{string}',
                        $value,
                        $filterObject->getOperatorsSubstitutionPattern()
                    );
                }
            }

            $orCondition['parameters'][] = [
                'field' => 'field_' . $filterObject->getFieldName() . $salt,
                'value' => $value
            ];
        } else {
            $isNotARelation = 0 !== strpos($filterObject->getFieldName(), 'Embedded.');
            if ($isNotARelation) {
                    $whereCondition .= ' ' . $this->entityAlias . '.' . $value;
                if (null != $orCondition['orCondition']) {
                    $orCondition['orCondition'] .= ' OR ' . $whereCondition;
                } else {
                    $orCondition['orCondition'] = $whereCondition;
                }
            }
        }

        // controllo se il filtro si riferisce ad una relazione dell'entità quindi devo fare dei join
        // esempio per users: filtering[_embedded.groups.name|eq]=admin
        if (strstr($filterObject->getRawFilter(), '_embedded.')) {

            $this->join($filterObject->getRawFilter());
            $relationEntityAlias = $this->getRelationEntityAlias();

            $embeddedFields = explode('.', $filterObject->getFieldName());
            $embeddableFieldName = $this->parser->camelize($embeddedFields[count($embeddedFields) - 1]);

            $salt = '_' . random_int(111, 999);

            $whereCondition = $relationEntityAlias . '.' . $embeddableFieldName . ' '
                . $filterObject->getOperatorMeta();

            if ($filterObject->isListType()) {
                $whereCondition .= ' (:field_' . $embeddableFieldName . $salt . ')';
            } else {
                $whereCondition .= ' :field_' . $embeddableFieldName . $salt;
            }

            if (null != $orCondition['orCondition']) {
                $orCondition['orCondition'] .= ' OR ' . $whereCondition;
            } else {
                $orCondition['orCondition'] = $whereCondition;
            }

            if ($filterObject->haveOperatorSubstitutionPattern()) {
                if ($filterObject->isListType()) {
                    $value = explode(',', $value);
                } else {
                    $value = str_replace(
                        '{string}',
                        $value,
                        $filterObject->getOperatorsSubstitutionPattern()
                    );
                }
            }

            $orCondition['parameters'][] = [
                'field' => 'field_' . $embeddableFieldName . $salt,
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
                $this->ensureQueryBuilderIsDefined();
                $this->qBuilder->addOrderBy($this->entityAlias . '.' . $fieldName, $direction);
            }

            if (strstr($sort, '_embedded.')) {
                $this->join($sort);
                $relationEntityAlias = $this->getRelationEntityAlias();

                $embeddedFields = explode('.', $sort);
                $fieldName = $this->parser->camelize($embeddedFields[2]);
                $direction = ($val === self::DIRECTION_AZ) ? self::DIRECTION_AZ : self::DIRECTION_ZA;

                $this->qBuilder->addOrderBy($relationEntityAlias . '.' . $fieldName, $direction);
            }

        }

        return $this;
    }

    public function getQueryBuilder() :QueryBuilder
    {
        if (!$this->qBuilder) {
            throw new UnInitializedQueryBuilderException();
        }

        return $this->qBuilder;
    }

    private function setRelationEntityAlias(string $relationEntityAlias)
    {
        $this->relationEntityAlias = $relationEntityAlias;
    }

    private function getRelationEntityAlias()
    {
        return $this->relationEntityAlias;
    }

    public function setRel(array $rel)
    {
        $this->rel = $rel;

        return $this;
    }

    public function getRel() : array
    {
        return $this->rel;
    }

    public function addRel($relation)
    {
        array_push($this->rel, $relation);
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

    public function setPage(int $page)
    {
        $this->page = $page;

        return $this;
    }

    public function getPage() :int
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

    public function setSelect($select) : QueryBuilderFactory
    {
        $this->select = $select;

        return $this;
    }

    public function getSelect()
    {
        return $this->select;
    }

    public function getEntityManager() : EntityManager
    {
        return $this->manager;
    }

    public function ensureQueryBuilderIsDefined()
    {
        if (!$this->qBuilder) {
            throw new \RuntimeException(
                'Oops! QueryBuilder was never initialized. '
                . "\n" . 'QueryBuilderFactory::createQueryBuilder()'
                . "\n" . 'QueryBuilderFactory::createSelectAndGroupBy()'
            );
        }
    }
}
