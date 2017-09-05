<?php

namespace Mado\QueryBundle\Objects;

use Mado\QueryBundle\Queries\QueryBuilderFactory;
use Mado\QueryBundle\Vocabulary\Operators;

class FilteringObject
{
    const INDEX_FIELD_NAME = 0;

    const INDEX_OPERATOR_NAME = 1;

    const KEY_FIELD_NAME = 'field_name';

    const KEY_OPERATOR = 'operator';

    private $properties;

    private function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    public static function fromFilter(string $filter)
    {
        $filterAsArray = explode('|', $filter);

        $properties = [];
        $properties[FilteringObject::KEY_FIELD_NAME] = $filterAsArray[FilteringObject::INDEX_FIELD_NAME];

        if (isset($filterAsArray[FilteringObject::INDEX_OPERATOR_NAME])) {
            $properties[FilteringObject::KEY_OPERATOR] = $filterAsArray[FilteringObject::INDEX_OPERATOR_NAME];
        }

        return new self($properties);
    }

    public function getFieldName()
    {
        return $this->properties[FilteringObject::KEY_FIELD_NAME];
    }

    public function getOperator()
    {
        if (!isset($this->properties[FilteringObject::KEY_OPERATOR])) {
            return Operators::getDefaultOperator();
        }

        return $this->properties[FilteringObject::KEY_OPERATOR];
    }

    public function hasOperator()
    {
        return isset($this->properties[FilteringObject::KEY_OPERATOR]);
    }

    public function getOperatorSign()
    {
        return $this->getOperator()['meta'];
    }
}
