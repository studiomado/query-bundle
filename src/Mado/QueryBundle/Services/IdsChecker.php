<?php

namespace Mado\QueryBundle\Services;

use Mado\QueryBundle\Objects\Filter;
use Mado\QueryBundle\Exceptions\ForbiddenContentException;

class IdsChecker
{
    private $idsMustBeSubset = true;

    private $filtering;

    private $additionalFiltersIds;

    private $objectFilter;

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

    public function setObjectFilter(Filter $objectFilter)
    {
        $this->objectFilter = $objectFilter;
    }

    public function setFilterKey($filterKey)
    {
        $this->filterKey = $filterKey;
    }

    public function validateIds()
    {
        $rawFilteredIds = $this->objectFilter->getIds();

        foreach ($this->filtering as $key => $queryStringIds) {
            $qsIds = explode(',', $queryStringIds);
            $addFilIds = explode(',', $this->additionalFiltersIds);
            foreach ($qsIds as $requestedId) {
                if ($this->objectFilter->getOperator() == 'list') {
                    if (!in_array($requestedId, $addFilIds)) {
                        throw new ForbiddenContentException(
                            'Oops! Forbidden requested id ' . $requestedId
                            . ' is not available. '
                            . 'Available are "' . join(', ', $addFilIds) . '"'
                        );
                    }
                }

                if ($this->objectFilter->getOperator() == 'nlist') {
                    $queryStringOperator = explode('|', key($this->filtering));
                    if (array_intersect($qsIds, $addFilIds) == []) {
                        $this->filterKey = str_replace('nlist', 'list', $this->objectFilter->getRawFilter());
                        $rawFilteredIds = join(',', $qsIds);
                        $this->idsMustBeSubset = false;
                    }
                }
            }
        }

        $this->finalFilterIds = $rawFilteredIds;

        if (true == $this->idsMustBeSubset) {
            foreach ($this->filtering as $key => $queryStringIds) {
                $qsIds = explode(',', $queryStringIds);
                $addFilIds = explode(',', $this->additionalFiltersIds);
                $this->finalFilterIds = join(',', array_intersect($qsIds, $addFilIds));
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
