<?php

namespace Mado\QueryBundle\Filters;


use Mado\QueryBundle\Dictionary;
use Mado\QueryBundle\Queries\QueryBuilderOptions;

class Filters implements FiltersInterface
{
    private $discriminatorCounter = 1;

    private $route = '';
    private $routeParams = '';
    private $id = '';

    private $andFilters = [];
    private $orFilters = [];

    private $sorting = [];

    private $printing = [];
    private $rel = '';
    private $page = '';
    private $select = '';
    private $limit = '';

    protected function __construct()
    {
    }

    public static function emptyFilter()
    {
        return new self();
    }

    public function addAndFilter(string $field, string $operator, string $value, bool $replaceIfExist = false)
    {
        Dictionary::isValidOperator($operator);

        $key = $field . '|' . $operator;
        while ($replaceIfExist === false && isset($this->andFilters[$key])) {
            $key = $field . '|' . $operator . '|' . $this->getDiscriminator();
        }

        $this->andFilters[$key] = $value;

        return $this;
    }

    public function addOrFilter(string $field, string $operator, string $value, bool $replaceIfExist = false)
    {
        Dictionary::isValidOperator($operator);

        $key = $field . '|' . $operator;
        while ($replaceIfExist === false && isset($this->orFilters[$key])) {
            $key = $field . '|' . $operator . '|' . $this->getDiscriminator();
        }

        $this->orFilters[$key] = $value;

        return $this;
    }

    protected function getDiscriminator()
    {
        return $this->discriminatorCounter++;
    }

    protected function setId($id)
    {
        $this->id = $id;
    }

    protected function setRoute(string $route)
    {
        $this->route = $route;
    }

    protected function setRouteParams(array $routeParams)
    {
        $this->routeParams = $routeParams;
    }

    protected function setAndFilters(array $andFilters)
    {
        $this->andFilters = $andFilters;
    }

    protected function setOrFilters(array $orFilters)
    {
        $this->orFilters = $orFilters;
    }

    protected function setSorting(array $sorting)
    {
        $this->sorting = $sorting;
    }

    protected function setPrinting(array $printing)
    {
        $this->printing = $printing;
    }

    protected function setRel(string $rel)
    {
        $this->rel = $rel;
    }

    protected function setPage(string $page)
    {
        $this->page = $page;
    }

    protected function setSelect(string $select)
    {
        $this->select = $select;
    }

    protected function setLimit(string $limit)
    {
        $this->limit = $limit;
    }

    public function getQueryBuilderOptions(): QueryBuilderOptions
    {
        return QueryBuilderOptions::fromArray([
            '_route'        => $this->route,
            '_route_params' => $this->routeParams,
            'id'            => $this->id,
            'page'          => $this->page,
            'limit'         => $this->limit,
            'filters'       => $this->andFilters,
            'orFilters'     => $this->orFilters,
            'sorting'       => $this->sorting,
            'rel'           => $this->rel,
            'select'        => $this->select,
            'printing'      => $this->printing,
        ]);
    }
}
