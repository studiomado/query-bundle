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

    private const AND_OPERATOR_LOGIC = 'AND';

    private const OR_OPERATOR_LOGIC = 'OR';

    protected $qBuilder;

    protected $fields;

    protected $andFilters;

    protected $orFilters;

    private $relationEntityAlias;

    protected $sorting;

    private $joins = [];

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

    /**
     * @param String $relation Nome della relazione semplice (groups.name) o con embedded (_embedded.groups.name)
     * @return $this
     */
    public function join(String $relation, $logicOperator = self::AND_OPERATOR_LOGIC)
    {
        $this->joinFactory->join($relation, $logicOperator);

        $innerJoins = $this->joinFactory->getInnerJoin();
        $leftJoins = $this->joinFactory->getLeftJoin();

        foreach ($innerJoins as $innerJoin) {
            $needle = $innerJoin['field'] . '_' . $innerJoin['relation'];
            if (!in_array($needle, $this->joins)) {
                $this->joins[] = $needle;
                $this->qBuilder->innerJoin($innerJoin['field'], $innerJoin['relation']);

            }
        }

        foreach ($leftJoins as $leftJoin) {
            $needle = $leftJoin['field'] . '_' . $leftJoin['relation'];
            if (!in_array($needle, $this->joins)) {
                $this->joins[] = $needle;
                $this->qBuilder->leftJoin($leftJoin['field'], $leftJoin['relation']);
            }
        }
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
            $andFilterFactory = new AndFilterFactory($this->entityAlias, $this->fields, $this->joinFactory);
            $andFilterFactory->createFilter($this->andFilters);

            $conditions = $andFilterFactory->getConditions();
            $parameters = $andFilterFactory->getParameters();
            $innerJoins = $andFilterFactory->getInnerJoin();

            foreach($conditions as $condition) {
                $this->qBuilder->andWhere($condition);
            }

            foreach($parameters as $parameter) {
                $this->qBuilder->setParameter($parameter['field'], $parameter['value']);
            }

            foreach ($innerJoins as $innerJoin) {
                $needle = $innerJoin['field'] . '_' . $innerJoin['relation'];
                if (!in_array($needle, $this->joins)) {
                    $this->joins[] = $needle;
                    $this->qBuilder->innerJoin($innerJoin['field'], $innerJoin['relation']);
                }
            }
        }

        if (null !== $this->orFilters) {
            $orFilterFactory = new OrFilterFactory($this->entityAlias, $this->fields, $this->joinFactory);
            $orFilterFactory->createFilter($this->orFilters);

            $conditions = $orFilterFactory->getConditions();
            $parameters = $orFilterFactory->getParameters();
            $leftJoins = $orFilterFactory->getLeftJoin();

            if ($conditions != '') {
                $this->qBuilder->andWhere($conditions);

                foreach ($parameters as $parameter) {
                    $this->qBuilder->setParameter($parameter['field'], $parameter['value']);
                }

                foreach ($leftJoins as $leftJoin) {
                    $needle = $leftJoin['field'] . '_' . $leftJoin['relation'];
                    if (!in_array($needle, $this->joins)) {
                        $this->joins[] = $needle;
                        $this->qBuilder->leftJoin($leftJoin['field'], $leftJoin['relation']);
                    }
                }
            }

            /*$orFilter = [];
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
            }*/
        }

        return $this;
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
                $relationEntityAlias = $this->joinFactory->getRelationEntityAlias();

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
