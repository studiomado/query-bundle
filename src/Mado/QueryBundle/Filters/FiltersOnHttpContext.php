<?php

namespace Mado\QueryBundle\Filters;


use Mado\QueryBundle\Exceptions\InvalidFiltersException;
use Symfony\Component\HttpFoundation\Request;

class FiltersOnHttpContext extends Filters
{
    /**
     * @param Request $request
     *
     * @return FiltersOnHttpContext
     * @throws InvalidFiltersException
     */
    public static function fromRequest(Request $request): FiltersOnHttpContext
    {
        $filters = new self();

        $id          = $request->attributes->get('id');
        $route       = $request->attributes->get('_route');
        $routeParams = $request->attributes->get('_route_params', []);

        $rel         = $request->query->get('rel', '');
        $select      = $request->query->get('select', '');
        $printing    = $request->query->get('printing', []);

        $andFilters  = $request->query->get('filtering', []);
        $orFilters   = $request->query->get('filtering_or', []);
        $sorting     = $request->query->get('sorting', []);

        $page        = $request->query->get('page', '');
        $limit       = $request->query->get('limit', '');

        $filters->ensureFilterIsValid($printing);
        $filters->ensureFilterIsValid($andFilters);
        $filters->ensureFilterIsValid($orFilters);
        $filters->ensureFilterIsValid($sorting);

        // FIXME: what is it for? Can be removed?
        $filterOrCorrected = [];
        foreach ($orFilters as $key => $filterValue) {
            if (is_array($filterValue)) {
                foreach ($filterValue as $keyInternal => $internal) {
                    $filterOrCorrected[$keyInternal . '|' . $filters->getDiscriminator()] = $internal;
                }
            } else {
                $filterOrCorrected[$key] = $filterValue;
            }
        }

        $filters->setId($id);
        $filters->setRoute($route);
        $filters->setRouteParams($routeParams);

        $filters->setRel($rel);
        $filters->setSelect($select);
        $filters->setPrinting($printing);

        $filters->setAndFilters($andFilters);
        $filters->setOrFilters($filterOrCorrected);
        $filters->setSorting($sorting);

        $filters->setPage($page);
        $filters->setLimit($limit);

        return $filters;
    }

    /**
     * @param $filters
     *
     * @throws InvalidFiltersException
     */
    private function ensureFilterIsValid($filters)
    {
        if (!is_array($filters)) {

            $message = "Wrong query string exception: ";
            $message .= var_export($filters, true) . "\n";
            $message .= "Please check query string should be something like " .
                "http://127.0.0.1:8000/?filtering[status]=todo";

            throw new InvalidFiltersException($message);
        }
    }
}
