<?php

namespace Mado\QueryBundle\Services;

use Mado\QueryBundle\Exceptions\ForbiddenContentException;
use Mado\QueryBundle\Objects\Filter;
use Psr\Log\LoggerInterface;

class IdsChecker
{
    private $idsMustBeSubset = true;

    private $filtering;

    private $additionalFilter;

    private $filterKey;

    private $finalFilterIds;

    private $logger;

    public function __construct()
    {
        $this->idsMustBeSubset = true;
    }

    public function setFiltering($filtering)
    {
        $this->filtering = $filtering;
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

        // guard
        $chiave = key($this->filtering);
        $chiaveEsplosa = explode('|' , $chiave);
        if (!isset($chiaveEsplosa[1]) || !in_array($chiaveEsplosa[1], ['list', 'nlist'])) {
            $this->finalFilterIds = $this->additionalFilter->getIds();
            return;
        }

        // check
        foreach ($this->filtering as $key => $queryStringIds) {
            $querystringIds = explode(',', $queryStringIds);
            $additionalFiltersIds = explode(',', $this->additionalFilter->getIds());
            foreach ($querystringIds as $requestedId) {

                // list + list
                if ($this->additionalFilter->getOperator() == 'list') {
                    if (!in_array($requestedId, $additionalFiltersIds)) {
                        throw new ForbiddenContentException(
                            'Oops! Forbidden requested id ' . $requestedId
                            . ' is not available. '
                            . 'Available are ' . join(', ', $additionalFiltersIds) . ''
                        );
                    }
                }

                // list + nlist
                if ($this->additionalFilter->getOperator() == 'nlist') {
                    $queryStringOperator = explode('|', key($this->filtering));
                    if (array_intersect($querystringIds, $additionalFiltersIds) == []) {
                        $this->filterKey = str_replace('nlist', 'list', $this->additionalFilter->getFieldAndOperator());
                        $rawFilteredIds = join(',', $querystringIds);
                        $this->idsMustBeSubset = false;
                    }
                }


                // nlist + list

                // nlist + nlist

            }
        }

        $this->finalFilterIds = $rawFilteredIds;

        if (true == $this->idsMustBeSubset) {
            foreach ($this->filtering as $key => $queryStringIds) {
                $querystringIds = explode(',', $queryStringIds);
                $additionalFiltersIds = explode(',', $this->additionalFilter->getIds());
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

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function log($message)
    {
        if ($this->logger) {
            $this->logger->critical($message);
        }
    }
}
