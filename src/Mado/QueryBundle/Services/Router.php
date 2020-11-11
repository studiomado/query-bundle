<?php

namespace Mado\QueryBundle\Services;

use Hateoas\Configuration\Route;
use Mado\QueryBundle\Queries\QueryBuilderOptions;

class Router
{
    public function createRouter(QueryBuilderOptions $queryOptions, $routeName): Route {
        $params = [];
        $list = [
            'filtering',
            'limit',
            'page',
            'sorting',
        ];

        foreach ($list as $itemKey => $itemValue) {
            $params[$itemValue] = $queryOptions->get($itemValue);
        }

        if (null != $queryOptions->get('_route_params')) {
            foreach (
                $queryOptions->get('_route_params') as $itemKey => $itemValue
            ) {
                $params[$itemKey] = $itemValue;
            }
        }

        if (!isset($routeName)) {
            $routeName = $queryOptions->get('_route');
        }

        return new Route($routeName, $params);
    }
}