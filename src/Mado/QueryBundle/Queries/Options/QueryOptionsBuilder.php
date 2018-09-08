<?php

namespace Mado\QueryBundle\Queries\Options;

use Mado\QueryBundle\Exceptions\InvalidFiltersException;
use Mado\QueryBundle\Queries\QueryBuilderOptions;
use Symfony\Component\HttpFoundation\Request;

class QueryOptionsBuilder
{
    private $entityAlias;

    public function setEntityAlias(string $entityAlias)
    {
        $this->entityAlias = $entityAlias;
    }

    public function getEntityAlias()
    {
        return $this->entityAlias;
    }

    public function fromRequest(Request $request = null)
    {
        $this->ensureEntityAliasIsDefined();

        $requestAttributes = [];
        foreach ($request->attributes->all() as $attributeName => $attributeValue) {
            $requestAttributes[$attributeName] = $request->attributes->get(
                $attributeName,
                $attributeValue);
        }

        $filters     = $request->query->get('filtering', []);
        $orFilters   = $request->query->get('filtering_or', []);
        $sorting     = $request->query->get('sorting', []);
        $printing    = $request->query->get('printing', []);
        $rel         = $request->query->get('rel', '');
        $page        = $request->query->get('page', '');
        $select      = $request->query->get('select', $this->getEntityAlias());
        $filtering   = $request->query->get('filtering', '');
        $limit       = $request->query->get('limit', '');

        $filterOrCorrected = [];

        $count = 0;
        foreach ($orFilters as $key => $filter) {
            if (is_array($filter)) {
                foreach ($filter as $keyInternal => $internal) {
                    $filterOrCorrected[$keyInternal . '|' . $count] = $internal;
                    $count += 1;
                }
            } else {
                $filterOrCorrected[$key] = $filter;
            }
        }

        $requestProperties = [
            'filtering'   => $filtering,
            'orFiltering' => $filterOrCorrected,
            'limit'       => $limit,
            'page'        => $page,
            'filters'     => $filters,
            'orFilters'   => $filterOrCorrected,
            'sorting'     => $sorting,
            'rel'         => $rel,
            'printing'    => $printing,
            'select'      => $select,
        ];

        $options = array_merge(
            $requestAttributes,
            $requestProperties
        );

        return QueryBuilderOptions::fromArray($options);
    }

    public function ensureEntityAliasIsDefined()
    {
        if (!$this->entityAlias) {
            throw new \RuntimeException(
                'Oops! Entity alias is missing'
            );
        }
    }

    public function buildFromRequestAndCustomFilter(Request $request, $filter)
    {
        $this->ensureEntityAliasIsDefined();

        $filters   = $request->query->get('filtering', []);
        $orFilters = $request->query->get('filtering_or', []);
        $sorting   = $request->query->get('sorting', []);
        $printing  = $request->query->get('printing', []);
        $rel       = $request->query->get('rel', '');
        $page      = $request->query->get('page', '');
        $select    = $request->query->get('select', $this->getEntityAlias());
        $filtering = $request->query->get('filtering', '');
        $limit     = $request->query->get('limit', '');
        $justCount = $request->query->get('justCount', 'false');

        $this->ensureFilterIsValid($filters);

        $filters = array_merge($filters, $filter);

        $filterOrCorrected = [];

        $count = 0;
        foreach ($orFilters as $key => $filterValue) {
            if (is_array($filterValue)) {
                foreach ($filterValue as $keyInternal => $internal) {
                    $filterOrCorrected[$keyInternal . '|' . $count] = $internal;
                    $count += 1;
                }
            } else {
                $filterOrCorrected[$key] = $filterValue;
            }
        }

        return QueryBuilderOptions::fromArray([
            '_route'        => $request->attributes->get('_route'),
            '_route_params' => $request->attributes->get('_route_params', []),
            'id'            => $request->attributes->get('id'),
            'filtering'     => $filtering,
            'limit'         => $limit,
            'page'          => $page,
            'filters'       => $filters,
            'orFilters'     => $filterOrCorrected,
            'sorting'       => $sorting,
            'rel'           => $rel,
            'printing'      => $printing,
            'select'        => $select,
            'justCount'     => $justCount,
        ]);
    }

    private function ensureFilterIsValid($filters)
    {
        if (!is_array($filters)) {
            throw new InvalidFiltersException(
                "Wrong query string exception: "
                . var_export($filters, true) . "\n\n"
                . "Please check query string should be something like "
                . "http://<host>:<port>/?filtering[<field>|<operator>]=<value>"
            );
        }
    }

    public function buildForOrFilter(Request $request, array $orFilter)
    {
        $this->ensureEntityAliasIsDefined();

        $filters   = $request->query->get('filtering', []);
        $orFilters = $request->query->get('filtering_or', []);
        $sorting   = $request->query->get('sorting', []);
        $printing  = $request->query->get('printing', []);
        $rel       = $request->query->get('rel', '');
        $page      = $request->query->get('page', '');
        $select    = $request->query->get('select', $this->getEntityAlias());
        $filtering = $request->query->get('filtering', '');
        $limit     = $request->query->get('limit', '');

        $orFilters = array_merge($orFilters, $orFilter);

        $filterOrCorrected = [];

        $count = 0;
        foreach ($orFilters as $key => $filter) {
            if (is_array($filter)) {
                foreach ($filter as $keyInternal => $internal) {
                    $filterOrCorrected[$keyInternal . '|' . $count] = $internal;
                    $count += 1;
                }
            } else {
                $filterOrCorrected[$key] = $filter;
            }
        }

        return QueryBuilderOptions::fromArray([
            '_route'        => $request->attributes->get('_route'),
            '_route_params' => $request->attributes->get('_route_params', []),
            'id'            => $request->attributes->get('id'),
            'filtering'     => $filtering,
            'limit'         => $limit,
            'page'          => $page,
            'filters'       => $filters,
            'orFilters'     => $filterOrCorrected,
            'sorting'       => $sorting,
            'rel'           => $rel,
            'printing'      => $printing,
            'select'        => $select,
        ]);
    }
}
