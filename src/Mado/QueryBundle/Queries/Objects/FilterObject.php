<?php

namespace Mado\QueryBundle\Queries\Objects;

use Mado\QueryBundle\Services\StringParser;
use Mado\QueryBundle\Dictionary;

final class FilterObject
{
    private $rawFilter;

    private $fieldName;

    private $operatorName;

    private function __construct(string $rawFilter)
    {
        $this->setRawFilter($rawFilter);

        $explodedFilter = explode('|', $rawFilter);
        if (!isset($explodedFilter[1])) {
            $explodedFilter[1] = 'eq';
        }

        $fieldName = $explodedFilter[0];
        $parser = new StringParser();
        $this->fieldName = $parser->camelize($fieldName);

        $this->operatorName = $explodedFilter[1];
    }

    public static function fromRawFilter(string $filter) : FilterObject
    {
        return new self($filter);
    }

    public function getFieldName() : string
    {
        return $this->fieldName;
    }

    public function getOperatorName() : string
    {
        return $this->operatorName;
    }

    public function isListType() : bool
    {
        return $this->getOperatorName() == 'list'
            || $this->getOperatorName() == 'nlist';
    }

    public function isFieldEqualityType()
    {
        return $this->getOperatorName() == 'field_eq';
    }

    public function getOperatorMeta() : string
    {
        return Dictionary::getOperators()[$this->getOperatorName()]['meta'];
    }

    public function haveOperatorSubstitutionPattern() : bool
    {
        $operator = Dictionary::getOperators()[$this->getOperatorName()];

        return isset($operator['substitution_pattern']);
    }

    public function getOperatorsSubstitutionPattern() : string
    {
        $operator = Dictionary::getOperators()[$this->getOperatorName()];

        return $operator['substitution_pattern'];
    }

    public function setRawFilter(string $rawFilter)
    {
        $this->rawFilter = $rawFilter;
    }

    public function getRawFilter() : string
    {
        return $this->rawFilter;
    }
}
