<?php

namespace Mado\QueryBundle\Objects;

use Mado\QueryBundle\Objects\Operator;
use Mado\QueryBundle\Objects\Salt;

class WhereCondition
{
    private $entityAlias;

    private $operator;

    private $salt;

    private $filtering;

    private $value;

    private $fieldName;

    private $relationEntityAlias;

    public function setEntityAlias(string $entityAlias)
    {
        $this->entityAlias = $entityAlias;
    }

    public function setOperator(Operator $operator)
    {
        $this->operator = $operator;
    }

    public function setSalt(Salt $salt)
    {
        $this->salt = $salt;
    }

    public function setFiltering(FilteringObject $filtering)
    {
        $this->filtering = $filtering;
    }

    public function setValue(string $value)
    {
        $this->value = $value;
    }

    public function setFieldName(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    private function isListOperator() : bool
    {
        return $this->filtering->isListOperator();
    }

    public function getCondition() : string
    {
        if ($this->filtering->hasOperator()) {
            if ($this->isListOperator()) {
                return $this->entityAlias . '.' . $this->fieldName . ' ' .
                    $this->operator->getMeta() . ' ' .
                    $this->getListFieldName();
            }

            if ($this->filtering->isFieldEqualsOperator()) {
                return $this->entityAlias . '.' . $this->fieldName . ' ' .
                    $this->operator->getMeta() . ' ' .
                    $this->entityAlias . '.' . $this->value;
            }
        }

        return $this->entityAlias . '.' . $this->fieldName . ' ' .
            $this->operator->getMeta() . ' ' .
            $this->getFieldName();
    }

    public function setRelationEntityAlias(string $relationEntityAlias)
    {
        $this->relationEntityAlias = $relationEntityAlias;
    }

    public function getEmbeddedCondition() : string
    {
        $fieldName = $this->isListOperator()
            ? $this->getListFieldName()
            : $this->getFieldName();

        return $this->relationEntityAlias . '.' . $this->fieldName .
            ' ' . $this->operator->getMeta() .
            ' ' . $fieldName;
    }

    private function getFieldName() : string
    {
        return ':field_' . $this->fieldName . $this->salt->getSalt();
    }

    private function getListFieldName() : string
    {
        return '(' . $this->getFieldName() . ')';
    }
}
