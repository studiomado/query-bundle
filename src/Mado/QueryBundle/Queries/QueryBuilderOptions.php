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
        $this->validateOption($option, $defaultValue);

        if (
            !isset($this->options[$option])
            || empty($this->options[$option])
        ) {
            return $defaultValue;
        }

        return $this->options[$option];
    }

    public function getAndFilters()
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
        return $this->get('rel', []);
    }

    public function getPrinting()
    {
        return $this->get('printing', []);
    }

    public function getSelect()
    {
        return $this->get('select');
    }

    public function validateOption($option, $defaultValue)
    {
        $optionIsDefinedNegativeAndNotNull = (
            !isset($this->options[$option])
            || $this->options[$option] < 0
        ) && $defaultValue == null;

        if ('limit' == $option && $optionIsDefinedNegativeAndNotNull) {
            $this->options[$option] = PHP_INT_MAX;
        }
    }
}
