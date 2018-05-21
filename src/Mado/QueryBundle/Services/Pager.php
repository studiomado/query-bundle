<?php

namespace Mado\QueryBundle\Services;

use Mado\QueryBundle\Objects\PagerfantaBuilder;
use Mado\QueryBundle\Queries\QueryBuilderOptions;
use Pagerfanta\Adapter\DoctrineORMAdapter;

class Pager
{
    private const DEFAULT_LIMIT = 10;

    private const DEFAULT_PAGE = 1;

    private const DEFAULT_LIFETIME = 600;

    private $router;

    public function __construct()
    {
        $this->setRouter(new Router());
    }

    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    public function paginateResults (
        QueryBuilderOptions $queryOptions,
        DoctrineORMAdapter $ormAdapter,
        PagerfantaBuilder $pagerfantaBuilder,
        $routeName,
        $useResultCache
    ) {
        $limit = $queryOptions->get('limit', self::DEFAULT_LIMIT);
        $page = $queryOptions->get('page', self::DEFAULT_PAGE);

        $query = $ormAdapter->getQuery();
        if (isset($useResultCache) && $useResultCache) {
            $query->useResultCache(true, self::DEFAULT_LIFETIME);
        }

        $route = $this->router->createRouter($queryOptions, $routeName);

        return $pagerfantaBuilder->createRepresentation($route, $limit, $page);
    }
}