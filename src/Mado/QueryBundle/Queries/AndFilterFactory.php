<?php

namespace Mado\QueryBundle\Queries;

use Mado\QueryBundle\Services\StringParser;

class AndFilterFactory
{
    private $entityAlias;

    private $fields;

    private $conditions;

    private $parameters;

    private $relationEntityAlias;

    private $parser;

    private $joinFactory;

    public function __construct(string $entityAlias, array $fields, JoinFactory $joinFactory)
    {
        $this->entityAlias = $entityAlias;
        $this->fields = $fields;
        $this->conditions = [];
        $this->parameters = [];
        $this->parser  = new StringParser();
        $this->joinFactory = $joinFactory;
    }

    public function createFilter(array $andFilters)
    {
        foreach ($andFilters as $filter => $value) {
            $this->applyFilterAnd(
                Objects\FilterObject::fromRawFilter($filter),
                $value,
                Objects\Value::fromFilter($value)
            );
        }
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
            } elseif ($filterObject->isNullType()) {
                $whereCondition .= ' ';
            } else {
                $whereCondition .= ' :field_' . $filterObject->getFieldName() . $salt;
            }

            $this->conditions[] = $whereCondition;

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

            if (!$filterObject->isNullType()) {
                $param = [];
                $param['field'] = 'field_' . $filterObject->getFieldName() . $salt;
                $param['value'] = $value;
                $this->parameters[] = $param;
            }
        } else {
            if (strpos($filterObject->getFieldName(), 'Embedded.') === false) {
                $whereCondition .= ' ' . $this->entityAlias . '.' . $value;
                $this->conditions[] = $whereCondition;
            }
        }

        // controllo se il filtro si riferisce ad una relazione dell'entità quindi devo fare dei join
        // esempio per users: filtering[_embedded.groups.name|eq]=admin
        if (strstr($filterObject->getRawFilter(), '_embedded.')) {
            $this->joinFactory->join($filterObject->getRawFilter());
            $this->relationEntityAlias = $this->joinFactory->getRelationEntityAlias();

            $embeddedFields = explode('.', $filterObject->getFieldName());
            $embeddedFieldName = $this->parser->camelize($embeddedFields[count($embeddedFields) - 1]);

            $salt = '_' . random_int(111, 999);

            $whereCondition = $this->relationEntityAlias . '.' . $embeddedFieldName . ' '
                . $filterObject->getOperatorMeta();

            if ($filterObject->isListType()) {
                $whereCondition .= ' (:field_' . $embeddedFieldName . $salt . ')';
            } elseif ($filterObject->isNullType()) {
                $whereCondition .= ' ';
            } else {
                $whereCondition .= ' :field_' . $embeddedFieldName . $salt;
            }

            $this->conditions[] = $whereCondition;
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

            if (!$filterObject->isNullType()) {
                $param = [];
                $param['field'] = 'field_' . $embeddedFieldName . $salt;
                $param['value'] = $value;
                $this->parameters[] = $param;
            }
        }
    }

    public function getConditions() :array
    {
        return $this->conditions;
    }

    public function getParameters() :array
    {
        return $this->parameters;
    }

    public function getInnerJoin() :array
    {
        return $this->joinFactory->getInnerJoin();
    }

    public function getLeftJoin() :array
    {
        return $this->joinFactory->getLeftJoin();
    }
}