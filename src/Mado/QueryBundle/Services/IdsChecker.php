<?php

namespace Mado\QueryBundle\Services;

use Mado\QueryBundle\Objects\Filter;
use Mado\QueryBundle\Exceptions\ForbiddenContentException;

class IdsChecker
{
    private $idsMustBeSubset = true;

    private $filtering;

    private $additionalFiltersIds;

    private $additionalFilter;

    private $filterKey;

    private $finalFilterIds;

    public function __construct()
    {
        $this->idsMustBeSubset = true;
    }

    public function setFiltering($filtering)
    {
        $this->filtering = $filtering;
    }

    public function setAdditionalFiltersIds($additionalFiltersIds)
    {
        $this->additionalFiltersIds = $additionalFiltersIds;
    }

    public function setObjectFilter(Filter $additionalFilter)
    {
        $this->additionalFilter = $additionalFilter;
    }

    public function setFilterKey($filterKey)
    {
        $this->filterKey = $filterKey;
    }

    public function validateIds()
    {
        $rawFilteredIds = $this->additionalFilter->getIds();

        foreach ($this->filtering as $key => $queryStringIds) {
            $querystringIds = explode(',', $queryStringIds);
            $additionalFiltersIds = explode(',', $this->additionalFiltersIds);
            foreach ($querystringIds as $requestedId) {
                if ($this->additionalFilter->getOperator() == 'list') {
                    if (!in_array($requestedId, $additionalFiltersIds)) {
                        throw new ForbiddenContentException(
                            'Oops! Forbidden requested id ' . $requestedId
                            . ' is not available. '
                            . 'Available are "' . join(', ', $additionalFiltersIds) . '"'
                        );
                    }
                }

                if ($this->additionalFilter->getOperator() == 'nlist') {
                    $queryStringOperator = explode('|', key($this->filtering));
                    if (array_intersect($querystringIds, $additionalFiltersIds) == []) {
                        $this->filterKey = str_replace('nlist', 'list', $this->additionalFilter->getRawFilter());
                        $rawFilteredIds = join(',', $querystringIds);
                        $this->idsMustBeSubset = false;
                    }
                }
            }
        }

        $this->finalFilterIds = $rawFilteredIds;

        if (true == $this->idsMustBeSubset) {
            foreach ($this->filtering as $key => $queryStringIds) {
                $querystringIds = explode(',', $queryStringIds);
                $additionalFiltersIds = explode(',', $this->additionalFiltersIds);
                $this->finalFilterIds = join(',', array_intersect($querystringIds, $additionalFiltersIds));
            }
        }
    }

    public function getFilterKey()
    {
        return $this->filterKey;
    }

    public function getFinalFilterIds()
    {
        return $this->finalFilterIds;
    }
}
