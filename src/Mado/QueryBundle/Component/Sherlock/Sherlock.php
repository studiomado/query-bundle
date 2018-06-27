<?php

namespace Mado\QueryBundle\Component\Sherlock;

use Doctrine\ORM\EntityManagerInterface;
use Mado\QueryBundle\Dictionary;
use Mado\QueryBundle\Services\StringParser;

class Sherlock
{
    private $currentMetadata;

    public function __construct(
        EntityManagerInterface $manager
    ) {
        $this->metadata = CurrentMetaData::fromEntityManager($manager);
    }

    public function getOpList($entityPath) : array
    {
        return $this->getAll()[$entityPath];
    }

    public function getAll() : array
    {
        $opList = [];

        foreach ($this->metadata->justEntitiesMetadata() as $entityClass) {
            $fields = $this->metadata->extractFields($entityClass);

            $entity = StringParser::dotNotationFor($entityClass);
            $opList[$entity]['fields'] = $fields;

            if ($this->metadata->haveRelations()) {
                $relations = $this->metadata->getRelations();
                $opList[$entity]['relations'][] = $relations;
            }
        }

        return $opList;
    }

    public function getRelations($entityPath) : array
    {
        return current($this->getOpList($entityPath)['relations']);
    }

    public function getFieldsType($entityPath) : array
    {
        $fieldTypes = [];

        foreach ($this->metadata->justEntitiesMetadata() as $entityClass) {
            $fields = $this->metadata->extractFieldsType($entityClass);

            $entity = StringParser::dotNotationFor($entityClass);
            $fieldTypes[$entity]['fields'] = $fields;
        }

        return $fieldTypes[$entityPath];
    }
}
