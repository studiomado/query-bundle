<?php

namespace Mado\QueryBundle\Queries;

use Mado\QueryBundle\Objects\FilteringObject;
use Mado\QueryBundle\Objects\Operator;
use Mado\QueryBundle\Objects\Salt;
use Mado\QueryBundle\Vocabulary\Operators;

class QueryBuilderFactory extends AbstractQuery
{
    const DIRECTION_AZ = 'asc';

    const DIRECTION_ZA = 'desc';

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

    private function ensureFieldsDefined()
    {
        if (!$this->fields) {
            throw new \RuntimeException(
                'Oops! Fields are not defined'
            );
        }
    }

    private function ensureSortingIsDefined()
    {
        if (null === $this->sorting) {
            throw new \RuntimeException(
                'Oops! Sorting is not defined'
            );
        }
    }

    private function ensureFilteringIsDefined()
    {
        if (null === $this->filtering) {
            throw new \RuntimeException(
                'Oops! Filtering is not defined'
            );
        }
    }

    private function ensureQueryBuilderIsDefined()
    {
        if (!$this->qBuilder) {
            throw new \RuntimeException(
                "Oops! Query builder was never initialized! call ::createQueryBuilder('entityName', 'alias') to start."
            );
        }
    }

    public function getAvailableFilters()
    {
        return array_keys(Operators::getAll());
    }

    public function setFields(array $fields = [])
    {
        $this->fields = $fields;

        return $this;
    }

    public function getFields()
    {
        $this->ensureFieldsDefined();

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

        return !in_array($needle, $this->joins);
    }

    private function storeJoin($prevEntityAlias, $currentEntityAlias)
    {
        $needle = $prevEntityAlias . "_" . $currentEntityAlias;
        $this->joins[$needle] = $needle;
    }

    public function join(String $relation)
    {
        $relation = explode('|', $relation)[0];
        $relations = [$relation];

        if (strstr($relation, '_embedded.')) {
            $embeddedFields = explode('.', $relation);
            unset($embeddedFields[count($embeddedFields) - 1]);
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
        $this->ensureFilteringIsDefined();
        $this->ensureFieldsDefined();

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
        $filtering = FilteringObject::fromFilter($filter);
        $fieldName = $this->parser->camelize($filtering->getFieldName());

        $op = Operator::fromFilteringObject($filtering);

        $saltObj = new Salt($this->qBuilder);
        $saltObj->generateSaltForName($fieldName);

        if (in_array($fieldName, $this->fields)) {

            if ($filtering->hasOperator()) {
                if ($filtering->isListOperator()) {
                    $whereCondition =
                        $this->entityAlias . '.' . $fieldName . ' ' .
                        $op->getMeta() . ' ' .
                        '(:field_' . $fieldName . $saltObj->getSalt() . ')';
                } else if ($filtering->isFieldEqualsOperator()) {
                    $whereCondition =
                        $this->entityAlias . '.' . $fieldName . ' ' .
                        $op->getMeta() . ' ' .
                        $this->entityAlias . '.' . $value;
                } else {
                    $whereCondition =
                        $this->entityAlias . '.' . $fieldName . ' ' .
                        $op->getMeta() . ' ' .
                        ':field_' . $fieldName . $saltObj->getSalt();
                }
            } else {
                $whereCondition =
                    $this->entityAlias . '.' . $fieldName . ' ' .
                    $op->getMeta() . ' ' .
                    ':field_' . $fieldName . $saltObj->getSalt();
            }

            $this->qBuilder->andWhere($whereCondition);

            if ($op->haveSubstitutionPattern()) {
                if ($filtering->isListOperator()) {
                    $value = explode(',', $value);
                } else {
                    $value = str_replace(
                        '{string}',
                        $value,
                        $op->getSubstitutionPattern()
                    );
                }
            }

            $this->qBuilder->setParameter('field_' . $fieldName . $saltObj->getSalt(), $value);
        } else {
            $isNotARelation = 0 !== strpos($fieldName, 'Embedded.');
            if ($isNotARelation) {
                $whereCondition =
                    $this->entityAlias . '.' . $fieldName . ' ' .
                    $op->getMeta() . ' ' .
                    $this->entityAlias . '.' . $value;
                $this->qBuilder->andWhere($whereCondition);
            }
        }

        if (strstr($filter, '_embedded.')) {

            $this->join($filter);
            $relationEntityAlias = $this->getRelationEntityAlias();

            $embeddedFields = explode('.', $fieldName);
            $fieldName = $this->parser->camelize($embeddedFields[count($embeddedFields) - 1]);

            if ($filtering->isListOperator()) {
                $whereCondition =
                    $relationEntityAlias . '.' . $fieldName . ' ' .
                    $op->getMeta() . ' ' .
                    '(:field_' . $fieldName . $saltObj->getSalt() . ')';
            } else {
                $whereCondition =
                    $relationEntityAlias . '.' . $fieldName . ' ' .
                    $op->getMeta() . ' ' .
                    ':field_' . $fieldName . $saltObj->getSalt();
            }

            $this->qBuilder->andWhere($whereCondition);
            if ($op->haveSubstitutionPattern()) {
                if ($filtering->isListOperator()) {
                    $value = explode(',', $value);
                } else {
                    $value = str_replace(
                        '{string}',
                        $value,
                        $op->getSubstitutionPattern()
                    );
                }
            }

            $this->qBuilder->setParameter('field_' . $fieldName . $saltObj->getSalt(), $value);
        }
    }

    private function applyFilterOr($filter, $value, $orCondition)
    {
        $whereCondition = null;
        $filtering = FilteringObject::fromFilter($filter);

        $fieldName = $this->parser->camelize($filtering->getFieldName());

        $op = Operator::fromFilteringObject($filtering);

        $saltObj = new Salt($this->qBuilder);
        $saltObj->generateSaltForName($fieldName);

        if (in_array($fieldName, $this->fields)) {

            if ($filtering->hasOperator()) {
                if ($filtering->isListOperator()) {
                    $whereCondition =
                        $this->entityAlias . '.' . $fieldName . ' ' .
                        $op->getMeta()
                        .' (:field_' . $fieldName . $saltObj->getSalt() . ')';
                } else if ($filtering->isFieldEqualsOperator()) {
                    $whereCondition =
                        $this->entityAlias . '.' . $fieldName . ' ' .
                        $op->getMeta() . ' ' .
                        $this->entityAlias . '.' . $value
                    ;
                } else {
                    $whereCondition =
                        $this->entityAlias . '.' . $fieldName . ' ' .
                        $op->getMeta() . ' ' .
                        ':field_' . $fieldName . $saltObj->getSalt();
                }
            } else {
                $whereCondition =
                    $this->entityAlias . '.' . $fieldName . ' ' .
                    '=' . ' ' .
                    ':field_' . $fieldName . $saltObj->getSalt();
            }

            if ($orCondition['orCondition'] != null) {
                $orCondition['orCondition'] .= ' OR ' . $whereCondition;
            } else {
                $orCondition['orCondition'] = $whereCondition;
            }

            if ($op->haveSubstitutionPattern()) {
                if ($filtering->isListOperator()) {
                    $value = explode(',', $value);
                } else {
                    $value = str_replace(
                        '{string}',
                        $value,
                        $op->getSubstitutionPattern()
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
                $whereCondition =
                    $this->entityAlias . '.' . $fieldName . ' ' .
                    $op->getMeta() . ' ' .
                    $this->entityAlias . '.' . $value;
                if ($orCondition['orCondition'] != null) {
                    $orCondition['orCondition'] .= ' OR ' . $whereCondition;
                } else {
                    $orCondition['orCondition'] = $whereCondition;
                }
            }
        }

        if (strstr($filter, '_embedded.')) {

            $this->join($filter);
            $relationEntityAlias = $this->getRelationEntityAlias();

            $embeddedFields = explode('.', $fieldName);
            $fieldName = $this->parser->camelize($embeddedFields[count($embeddedFields) - 1]);

            if ($filtering->isListOperator()) {
                $whereCondition =
                    $relationEntityAlias . '.' . $fieldName . ' ' .
                    $op->getMeta() . ' ' .
                    '(:field_' . $fieldName . $saltObj->getSalt() . ')';
            } else {
                $whereCondition =
                    $relationEntityAlias . '.' . $fieldName . ' ' .
                    $op->getMeta() . ' ' .
                    ':field_' . $fieldName . $saltObj->getSalt();
            }

            if ($orCondition['orCondition'] != null) {
                $orCondition['orCondition'] .= ' OR ' . $whereCondition;
            } else {
                $orCondition['orCondition'] = $whereCondition;
            }

            if ($op->haveSubstitutionPattern()) {
                if ($filtering->isListOperator()) {
                    $value = explode(',', $value);
                } else {
                    $value = str_replace(
                        '{string}',
                        $value,
                        $op->getSubstitutionPattern()
                    );
                }
            }

            $orCondition['parameters'][] = [
                'field' => 'field_' . $fieldName . $saltObj->getSalt(),
                'value' => $value
            ];
        }

        return $orCondition;
    }

    public function sort()
    {
        $this->ensureFieldsDefined();
        $this->ensureSortingIsDefined();

        foreach ($this->sorting as $sort => $val) {
            $val = strtolower($val);

            $fieldName = $this->parser->camelize($sort);

            if (in_array($fieldName, $this->fields)) {
                $direction = ($val === self::DIRECTION_AZ) ? self::DIRECTION_AZ : self::DIRECTION_ZA;
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

    public function getQueryBuilder()
    {
        $this->ensureQueryBuilderIsDefined();

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
