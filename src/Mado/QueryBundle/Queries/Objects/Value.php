<?php

namespace Mado\QueryBundle\Queries\Objects;

/**
 * @since Interface available since Release 2.1.3
 */
final class Value
{
    private $filter;

    private function __construct($filter) {
        $this->filter = $filter;
    }

    public function getFilter()
    {
        $filterCameFromQueryString = is_string($this->filter);

        $isAdditionalFilter = !$filterCameFromQueryString;

        if ($isAdditionalFilter) {
            return $this->filter['list'][0];
        }

        return $this->filter;
    }

    public static function fromFilter($filter)
    {
        return new self($filter);
    }
}
