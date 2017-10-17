<?php

namespace Mado\QueryBundle\Objects;

use Doctrine\ORM\QueryBuilder;

final class Salt
{
    private $qBuilder;

    private $salt;

    public function __construct(QueryBuilder $qBuilder)
    {
        $this->qBuilder = $qBuilder;
    }

    public function generateSaltForName(string $fieldName) : void
    {
        $this->salt = '';

        foreach ($this->qBuilder->getParameters() as $parameter) {
            if ($parameter->getName() == 'field_' . $fieldName) {
                $this->salt = '_' . rand(111, 999);
            }
        }
    }

    public function getSalt() : string
    {
        return $this->salt;
    }
}
