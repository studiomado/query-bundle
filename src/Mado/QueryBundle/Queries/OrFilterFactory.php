<?php

namespace Mado\QueryBundle\Queries;

use Mado\QueryBundle\Services\StringParser;

class OrFilterFactory
{
    private const OR_OPERATOR_LOGIC = 'OR';

    private $entityAlias;

    private $fields;

    private $joinFactory;

    private $parser;

    private $conditions;

    private $parameters;

    private $relationEntityAlias;

    public function __construct(string $entityAlias, array $fields, JoinFactory $joinFactory)
    {
        $this->entityAlias = $entityAlias;
        $this->joinFactory = $joinFactory;
        $this->fields = $fields;
        $this->conditions = '';
        $this->parameters = [];
        $this->parser  = new StringParser();
    }

    public function createFilter(array $orFilters)
    {
        foreach ($orFilters as $filter => $value) {
            $this->applyFilterOr(
                Objects\FilterObject::fromRawFilter($filter),
                $value
            );
        }
    }

    private function applyFilterOr(Objects\FilterObject $filterObject, $value)
    {
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
            } elseif ($filterObject->isNullType()) {
                $whereCondition .= ' ';
            } else {
                $whereCondition .= ' :field_' . $filterObject->getFieldName() . $salt;
            }

            if ('' != $this->conditions) {
                $this->conditions .= ' OR ' . $whereCondition;
            } else {
                $this->conditions = $whereCondition;
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

            if (!$filterObject->isNullType()) {
                $this->parameters[] = [
                    'field' => 'field_' . $filterObject->getFieldName() . $salt,
                    'value' => $value
                ];
            }
        } else {
            $isNotARelation = 0 !== strpos($filterObject->getFieldName(), 'Embedded.');
            if ($isNotARelation) {
                $whereCondition .= ' ' . $this->entityAlias . '.' . $value;
                if ('' != $this->conditions) {
                    $this->conditions .= ' OR ' . $whereCondition;
                } else {
                    $this->conditions = $whereCondition;
                }
            }
        }

        // controllo se il filtro si riferisce ad una relazione dell'entità quindi devo fare dei join
        // esempio per users: filtering[_embedded.groups.name|eq]=admin
        if (strstr($filterObject->getRawFilter(), '_embedded.')) {
            $this->joinFactory->join($filterObject->getRawFilter(), self::OR_OPERATOR_LOGIC);
            $this->relationEntityAlias = $this->joinFactory->getRelationEntityAlias();

            $embeddedFields = explode('.', $filterObject->getFieldName());
            $embeddableFieldName = $this->parser->camelize($embeddedFields[count($embeddedFields) - 1]);

            $salt = '_' . random_int(111, 999);

            $whereCondition = $this->relationEntityAlias . '.' . $embeddableFieldName . ' '
                . $filterObject->getOperatorMeta();

            if ($filterObject->isListType()) {
                $whereCondition .= ' (:field_' . $embeddableFieldName . $salt . ')';
            } elseif ($filterObject->isNullType()) {
                $whereCondition .= ' ';
            } else {
                $whereCondition .= ' :field_' . $embeddableFieldName . $salt;
            }

            if ('' != $this->conditions) {
                $this->conditions .= ' OR ' . $whereCondition;
            } else {
                $this->conditions = $whereCondition;
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

            if (!$filterObject->isNullType()) {
                $this->parameters[] = [
                    'field' => 'field_' . $embeddableFieldName . $salt,
                    'value' => $value
                ];
            }
        }
    }

    public function getConditions() :string
    {
        return $this->conditions;
    }

    public function getParameters() :array
    {
        return $this->parameters;
    }

    public function getLeftJoin() :array
    {
        return $this->joinFactory->getLeftJoin();
    }
}