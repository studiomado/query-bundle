<?php

namespace Mado\QueryBundle\Queries;

class QueryBuilderOptions
{
    private $options;

    private function __construct(array $options)
    {
        $this->options = $options;
    }

    public static function fromArray(array $options)
    {
        return new self($options);
    }

    public function get($option, $defaultValue = null)
    {
        if (!isset($this->options[$option]) || empty($this->options[$option])) {
            return $defaultValue;
        }

        return $this->options[$option];
    }

    public function getFilters()
    {
        return $this->get('filters', []);
    }

    public function getOrFilters()
    {
        return $this->get('orFilters', []);
    }

    public function getSorting()
    {
        return $this->get('sorting', []);
    }

    public function getRel()
    {
        return $this->get('rel');
    }

    public function getPrinting()
    {
        return $this->get('printing', []);
    }

    public function getSelect()
    {
        return $this->get('select');
    }
}
