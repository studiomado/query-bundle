<?php

namespace Mado\QueryBundle\Queries\Objects;

use Mado\QueryBundle\Services\StringParser;

class WhereCondition
{
    private $parameters;

    private function __construct(
        array $parameters
    ) {
        $this->parameters = $parameters;
    }

    public static function fromFilterObject(
        StringParser $parser,
        FilterObject $filterObject,
        $relationEntityAlias
    ) {
        $embeddedFields = explode('.', $filterObject->getFieldName());
        $embeddedFieldName = $parser->camelize($embeddedFields[count($embeddedFields) - 1]);

        $field = $relationEntityAlias . '.' . $embeddedFieldName;

        $salt = '_' . random_int(111, 999);

        return new self([
            'embeddedFieldName' => $embeddedFieldName,
            'filterObject'      => $filterObject,
            'field'             => $field,
            'salt'              => $salt,
        ]);
    }

    public function getWhereCondition()
    {
        $embeddedFieldName = $this->parameters['embeddedFieldName'];
        $salt              = $this->parameters['salt'];

        $whereCondition = $this->parameters['field'] . ' ' .
            $this->parameters['filterObject']->getOperatorMeta();

        if ($this->parameters['filterObject']->isListType()) {
            $whereCondition .= ' (:field_' . $embeddedFieldName . $salt . ')';
        } else {
            $whereCondition .= ' :field_' . $embeddedFieldName . $salt;
        }

        return $whereCondition;
    }

    public function getParameterName()
    {
        $this->ensureSaltIsDefined();

        $embeddedFieldName = $this->parameters['embeddedFieldName'];

        return 'field_' . $embeddedFieldName . $this->parameters['salt'];
    }

    public function ensureSaltIsDefined()
    {
        if (!isset($this->parameters['salt'])) {
            throw new \RuntimeException(
                'Oops! Salt is not defined'
            );
        }
    }
}
