<?php

namespace Mado\QueryBundle\Filters;


use Mado\QueryBundle\Queries\QueryBuilderOptions;

interface FiltersInterface
{
    public function getQueryBuilderOptions(): QueryBuilderOptions;
}

