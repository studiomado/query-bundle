<?php

namespace Mado\QueryBundle\Services;

use Mado\QueryBundle\Exceptions\ForbiddenContentException;
use Mado\QueryBundle\Objects\Filter;
use Psr\Log\LoggerInterface;

class IdsChecker
{
    private $idsMustBeSubset = true;

    private $filtering;

    private $additionalFiltersIds;

    private $objectFilter;

    private $filterKey;

    private $finalFilterIds;

    private $logger;

    public function __construct()
    {
        $this->idsMustBeSubset = true;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        $this->log('Start to check IDS');

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

        $this->log(var_export([
            'filterKey'      => $this->getFilterKey(),
            'finalFilterIds' => $this->getFinalFilterIds(),
        ], true));
    }

    public function getFilterKey()
    {
        return $this->filterKey;
    }

    public function getFinalFilterIds()
    {
        return $this->finalFilterIds;
    }

    public function log($message)
    {
        if ($this->logger) {
            $this->logger->debug('[IdsChecker] - ' . $message);
        }
    }
}
