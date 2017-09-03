<?php

namespace Mado\QueryBundle\Objects;

use Mado\QueryBundle\Vocabulary\Operators;

final class Operator
{
    private $operator;

    private function __construct(array $params)
    {
        $this->operator = $params['operator'];
    }

    public static function getDefault() : Operator
    {
        $operator = Operators::getDefaultOperator();

        return new self([
            'operator' => $operator,
        ]);
    }

    public function getMeta() : string
    {
        return $this->operator['meta'];
    }

    public static function fromRawValue(array $operator) : Operator
    {
        if (!isset($operator['meta'])) {
            throw new \RuntimeException(
                'Oops! Raw operator must contain `meta` parameter'
            );
        }

        return new self([
            'operator' => $operator,
        ]);
    }

    public static function fromFilteringObject(
        FilteringObject $filteringObject
    ) : Operator {
        if(true === $filteringObject->hasOperator()){
            $operatorName = $filteringObject->getOperator();
            $operator = Operators::get($operatorName);

            return new self([
                'operator' => $operator,
            ]);
        }

        return Operator::getDefault();
    }

    public function getRawValue() : array
    {
        return $this->operator;
    }

    public function haveSubstitutionPattern() : bool
    {
        return isset($this->operator['substitution_pattern']);
    }

    public function getSubstitutionPattern() : string
    {
        if ($this->haveSubstitutionPattern()) {
            return $this->operator['substitution_pattern'];
        }

        throw new \RuntimeException(
            'Oops! Current operator have not substitution pattern.'
        );
    }
}
