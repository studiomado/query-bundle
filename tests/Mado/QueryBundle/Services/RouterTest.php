<?php

use Mado\QueryBundle\Queries\QueryBuilderOptions;
use Mado\QueryBundle\Services\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function testCreateRouterWithoutRouteParams()
    {
        $queryBuilderOptions = QueryBuilderOptions::fromArray([
            'filters' => 'foo',
            'orFilters' => 'bar'
        ]);

        $this->router = new Router();
        $route = $this->router->createRouter($queryBuilderOptions, '');

        $this->assertInstanceOf('Hateoas\Configuration\Route', $route);
    }

    public function testCreateRouterWithRouteParams()
    {
        $routeName = 'route';
        $routeParamsKey = 'foo';
        $routeParamsValue = 'bar';
        $queryBuilderOptions = QueryBuilderOptions::fromArray([
            '_route_params' => [$routeParamsKey => $routeParamsValue]
        ]);

        $this->router = new Router();
        $route = $this->router->createRouter($queryBuilderOptions, $routeName);
        $routeParams = $route->getParameters();

        $this->assertInstanceOf('Hateoas\Configuration\Route', $route);
        $this->assertEquals($routeName, $route->getName());
        $this->assertTrue(array_key_exists($routeParamsKey, $routeParams));
    }
}
