<?php

namespace Mado\QueryBundle\Queries\Options;

class QueryOptionsBuilder
{
    private $entityAlias;

    public function setEntityAlias(string $entityAlias)
    {
        $this->entityAlias = $entityAlias;
    }

    public function builderFromRequest(Request $request = null)
    {
        $this->ensureEntityAliasIsDefined();

        $requestAttributes = [];
        foreach ($request->attributes->all() as $attributeName => $attributeValue) {
            $requestAttributes[$attributeName] = $request->attributes->get(
                $attributeName,
                $attributeValue
            );
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
}
