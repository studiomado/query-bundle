<?php

namespace Mado\QueryBundle\Queries;

use Mado\QueryBundle\Services\StringParser;

class AndFilter
{
    private $entityAlias;

    private $fields;

    private $join;

    private $conditions;

    private $parameters;

    private $relationEntityAlias;

    private $parser;

    public function __construct(string $entityAlias, array $fields, Join $join)
    {
        $this->entityAlias = $entityAlias;
        $this->fields = $fields;
        $this->join = $join;

        $this->conditions = [];
        $this->parameters = [];
        $this->parser  = new StringParser();
    }

    public function createFilter(array $andFilters)
    {
        foreach ($andFilters as $filter => $value) {
            $this->applyFilter(
                Objects\FilterObject::fromRawFilter($filter),
                $value,
                Objects\Value::fromFilter($value)
            );
        }
    }

    private function applyFilter(
        Objects\FilterObject $filterObject,
        $value,
        Objects\Value $filterValue
    ) {
        $whereCondition = $this->entityAlias . '.' . $filterObject->getFieldName() . ' '
            . $filterObject->getOperatorMeta();

        if (in_array($filterObject->getFieldName(), $this->fields)) {
            $salt = '_' . random_int(111, 999);

            if ($filterObject->isListContainsType()) {
                $fieldName = $this->entityAlias . '.' . $filterObject->getFieldName();
                $whereCondition = $this->createWhereConditionForListContains($value, $fieldName, $filterObject->getFieldName(), $salt);
            } elseif ($filterObject->isListType()) {
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
                if ($filterObject->isListContainsType()) {
                    $value = $this->encapsulateValueForLike($value);
                } elseif ($filterObject->isListType()) {
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
                if ($filterObject->isListContainsType()) {
                    $this->addMultipleParameters($value, $filterObject->getFieldName(), $salt);
                } else {
                    $param = [];
                    $param['field'] = 'field_' . $filterObject->getFieldName() . $salt;
                    $param['value'] = $value;
                    $this->parameters[] = $param;
                }
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
            $this->join->join($filterObject->getRawFilter());
            $this->relationEntityAlias = $this->join->getRelationEntityAlias();

            $embeddedFields = explode('.', $filterObject->getFieldName());
            $embeddedFieldName = $this->parser->camelize($embeddedFields[count($embeddedFields) - 1]);

            $salt = '_' . random_int(111, 999);

            $whereCondition = $this->relationEntityAlias . '.' . $embeddedFieldName . ' '
                . $filterObject->getOperatorMeta();

            if ($filterObject->isListContainsType()) {
                $fieldName =  $this->relationEntityAlias . '.' . $embeddedFieldName;
                $whereCondition = $this->createWhereConditionForListContains($value, $fieldName, $embeddedFieldName, $salt);
            } elseif ($filterObject->isListType()) {
                $whereCondition .= ' (:field_' . $embeddedFieldName . $salt . ')';
            } elseif ($filterObject->isNullType()) {
                $whereCondition .= ' ';
            } else {
                $whereCondition .= ' :field_' . $embeddedFieldName . $salt;
            }

            $this->conditions[] = $whereCondition;
            if ($filterObject->haveOperatorSubstitutionPattern()) {
                if ($filterObject->isListContainsType()) {
                    $value = $this->encapsulateValueForLike($value);
                } elseif ($filterObject->isListType()) {
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
                if ($filterObject->isListContainsType()) {
                    $this->addMultipleParameters($value, $embeddedFieldName, $salt);
                } else {
                    $param = [];
                    $param['field'] = 'field_' . $embeddedFieldName . $salt;
                    $param['value'] = $value;
                    $this->parameters[] = $param;
                }
            }
        }
    }

    private function addMultipleParameters($value, $fieldName, $salt)
    {
        foreach ($value as $key => $val) {
            $param = [];
            $param['field'] = 'field_' . $fieldName . $salt . $key;
            $param['value'] = $val;
            $this->parameters[] = $param;
        }
    }

    private function createWhereConditionForListContains($value, $fieldName, $fieldNameWithoutAlias, $salt) :string
    {
        $whereCondition = '';
        $values = explode(',', $value);
        foreach ($values as $key => $val) {
            if ($whereCondition == '') {
                $whereCondition = ' (';
            } else {
                $whereCondition .=  ' OR ';
            }

            $whereCondition .= $fieldName .
                ' LIKE :field_' . str_replace('.', '_', $fieldNameWithoutAlias) . $salt . $key;
        }

        $whereCondition .= ')';

        return $whereCondition;
    }

    private function encapsulateValueForLike(string $value) : array
    {
        $values = explode(',', $value);
        foreach ($values as $key => $val) {
            $values[$key] = '%' . $val . '%';
        }

        return $values;
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
        return $this->join->getInnerJoin();
    }
}