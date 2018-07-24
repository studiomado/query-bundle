<?php

namespace Mado\QueryBundle\Queries\Objects;

/**
 * @since class available since release 2.1.3
 */
final class Value
{
    private $filter;

    private function __construct($filter) {
        $this->filter = $filter;
    }

    public function getFilter()
    {
        if ($this->camesFromAdditionalFilters()) {
            return $this->filter[$this->getOperator()][0];
        }

        return $this->filter;
    }

    public function getValues()
    {
        return $this->filter[$this->getOperator()];
    }

    public function getOperator()
    {
        return key($this->filter);
    }

    public static function fromFilter($filter)
    {
        return new self($filter);
    }

    public function camesFromQueryString()
    {
        return is_string($this->filter);
    }

    public function camesFromAdditionalFilters()
    {
        return !$this->camesFromQueryString();
    }
}
