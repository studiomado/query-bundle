<?php

use Mado\QueryBundle\Filters\FiltersOnHttpContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class FiltersOnHttpContextTest extends TestCase
{
    public function testConstructFromRequest()
    {
        $route = uniqid();
        $parameters = [
            'rel' => uniqid(),
            'select' => uniqid(),
            'printing' => [uniqid()],
            'filtering' => [uniqid()],
            'filtering_or' => [uniqid()],
            'sorting' => [uniqid()],
            'page' => uniqid(),
            'limit' => uniqid(),
        ];

        $request = Request::create(
            '/',
            Request::METHOD_GET,
            $parameters
        );

        $request->attributes->set('_route', $route);

        $filters = FiltersOnHttpContext::fromRequest($request);

        $qbOptions = $filters->getQueryBuilderOptions();

        $this->assertEquals($parameters['rel'], $qbOptions->getRel());
        $this->assertEquals($parameters['select'], $qbOptions->getSelect());
        $this->assertEquals($parameters['printing'], $qbOptions->getPrinting());

        $this->assertEquals($parameters['filtering'], $qbOptions->getAndFilters());
        $this->assertEquals($parameters['filtering_or'], $qbOptions->getOrFilters());
        $this->assertEquals($parameters['sorting'], $qbOptions->getSorting());

        $this->assertEquals($parameters['limit'], $qbOptions->get('limit'));
        $this->assertEquals($parameters['page'], $qbOptions->get('page'));
    }
}
