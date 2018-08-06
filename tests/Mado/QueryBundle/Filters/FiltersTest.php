<?php

use Mado\QueryBundle\Dictionary;
use Mado\QueryBundle\Filters\Filters;
use PHPUnit\Framework\TestCase;

class FiltersTest extends TestCase
{
    public function testAndFiltering()
    {
        $filter = Filters::emptyFilter();

        $field1 = uniqid();
        $operator1 = Dictionary::NUMBER_EQUAL;
        $value1 = uniqid();
        $filter->addAndFilter($field1, $operator1, $value1);

        $field2 = uniqid();
        $operator2 = Dictionary::NUMBER_EQUAL;
        $value2 = uniqid();
        $filter->addAndFilter($field2, $operator2, $value2);

        $qbOptions = $filter->getQueryBuilderOptions();

        $expected = [
            "$field1|$operator1" => "$value1",
            "$field2|$operator2" => "$value2",
        ];
        $this->assertEquals($expected, $qbOptions->getAndFilters());
    }

    public function testSameAndFilteringWithReplacing()
    {
        $filter = Filters::emptyFilter();

        $field = uniqid();
        $operator = Dictionary::NUMBER_EQUAL;
        $value = uniqid();

        $filter->addAndFilter($field, $operator, $value);
        $filter->addAndFilter($field, $operator, $value, true);

        $qbOptions = $filter->getQueryBuilderOptions();

        $expected = [
            "$field|$operator" => "$value",
        ];

        $this->assertEquals($expected, $qbOptions->getAndFilters());
    }

    public function testSameAndFilteringWithoutReplacing()
    {
        $filter = Filters::emptyFilter();

        $field = uniqid();
        $operator = Dictionary::NUMBER_EQUAL;
        $value = uniqid();

        $filter->addAndFilter($field, $operator, $value);
        $filter->addAndFilter($field, $operator, $value);

        $qbOptions = $filter->getQueryBuilderOptions();

        $expected = [
            "$field|$operator" => "$value",
            "$field|$operator|1" => "$value",
        ];

        $this->assertEquals($expected, $qbOptions->getAndFilters());
    }

    public function testOrFiltering()
    {
        $filter = Filters::emptyFilter();

        $field1 = uniqid();
        $operator1 = Dictionary::NUMBER_EQUAL;
        $value1 = uniqid();
        $filter->addOrFilter($field1, $operator1, $value1);

        $field2 = uniqid();
        $operator2 = Dictionary::NUMBER_EQUAL;
        $value2 = uniqid();
        $filter->addOrFilter($field2, $operator2, $value2);

        $qbOptions = $filter->getQueryBuilderOptions();

        $expected = [
            "$field1|$operator1" => "$value1",
            "$field2|$operator2" => "$value2",
        ];
        $this->assertEquals($expected, $qbOptions->getOrFilters());
    }

    public function testSameOrFilteringWithReplacing()
    {
        $filter = Filters::emptyFilter();

        $field = uniqid();
        $operator = Dictionary::NUMBER_EQUAL;
        $value = uniqid();

        $filter->addOrFilter($field, $operator, $value);
        $filter->addOrFilter($field, $operator, $value, true);

        $qbOptions = $filter->getQueryBuilderOptions();

        $expected = [
            "$field|$operator" => "$value",
        ];

        $this->assertEquals($expected, $qbOptions->getOrFilters());
    }

    public function testSameOrFilteringWithoutReplacing()
    {
        $filter = Filters::emptyFilter();

        $field = uniqid();
        $operator = Dictionary::NUMBER_EQUAL;
        $value = uniqid();

        $filter->addOrFilter($field, $operator, $value);
        $filter->addOrFilter($field, $operator, $value);

        $qbOptions = $filter->getQueryBuilderOptions();

        $expected = [
            "$field|$operator" => "$value",
            "$field|$operator|1" => "$value",
        ];

        $this->assertEquals($expected, $qbOptions->getOrFilters());
    }
}
