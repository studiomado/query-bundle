<?php

namespace Mado\QueryBundle\Queries\Objects;

use Mado\QueryBundle\Dictionary;
use Mado\QueryBundle\Services\StringParser;

final class Filter
{
    private $operatorName;

    private $rawOperator;

    private $fieldName;

    private $explodedRawFilter;

    public static function fromString(string $operatorName)
    {
        return new self($operatorName);
    }

    private function __construct(
        string $operatorName,
        array $rawOperator = [],
        string $fieldName = '',
        array $explodedRawFilter = []
    ) {
        $this->operatorName      = $operatorName;
        $this->rawOperator       = $rawOperator;
        $this->fieldName         = $fieldName;
        $this->explodedRawFilter = $explodedRawFilter;
    }

    public function isListOrNlist() : bool
    {
        $listFilters = ['list', 'nlist'];

        return in_array(
            $this->operatorName,
            $listFilters
        );
    }

    public static function fromQueryStringRawFilterExploded(array $explodedRawFilter)
    {
        $operators = Dictionary::getOperators();

        if(isset($explodedRawFilter[1])){
            $rawOperator = $operators[$explodedRawFilter[1]];
        } else {
            $rawOperator = $operators[QueryBuilderFactory::DEFAULT_OPERATOR];
        }

        $parser = new StringParser();
        $fieldName = $explodedRawFilter[0];
        $fieldName = $parser->camelize($fieldName);

        return new self(
            key($rawOperator),
            $rawOperator,
            $fieldName,
            $explodedRawFilter
        );
    }

    public function getRawOperator() : array
    {
        $operators = Dictionary::getOperators();

        $this->ensureRawOperatorIsDefined();

        return $this->rawOperator;
    }

    public function ensureRawOperatorIsDefined() : void
    {
        if (!$this->rawOperator) {
            throw new \RuntimeException(
                'Oops! Raw Filter is missing'
            );
        }
    }

    public static function fromRawFilter(string $filter) : Filter
    {
        $explodedRawFilter = explode('|',$filter);

        if (!isset($explodedRawFilter[1])) {
            $explodedRawFilter[1] = 'eq';
        }

        return Filter::fromQueryStringRawFilterExploded($explodedRawFilter);
    }

    public function getFieldName() : string
    {
        return $this->fieldName;
    }

    public function getExplodedRawFilter() : array
    {
        return $this->explodedRawFilter;
    }
}
