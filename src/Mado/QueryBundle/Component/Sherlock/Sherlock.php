<?php

namespace Mado\QueryBundle\Component\Sherlock;

use Doctrine\ORM\EntityManagerInterface;
use Mado\QueryBundle\Dictionary;
use Mado\QueryBundle\Services\StringParser;

class Sherlock
{
    private $currentMetadata;

    private $manager;

    public function __construct(
        EntityManagerInterface $manager
    ) {
        $this->manager = $manager;
        $this->metadata = CurrentMetaData::fromEntityManager($this->manager);
    }

    public function getShortOpList($entityPath) : array
    {
        return $this->getAll([
            'show_just_type' => true,
            'show_embedded' => true,
        ])[$entityPath];
    }


    public function getOpList($entityPath) : array
    {
        return $this->getAll()[$entityPath];
    }

    public function getAll(array $options = []) : array
    {
        $opList = [];

        foreach ($this->metadata->justEntitiesMetadata() as $entityClass) {
            if (isset($options['show_just_type'])) {
                $fields = $this->metadata->extractFieldsType($entityClass);
            } else {
                $fields = $this->metadata->extractFields($entityClass);
            }

            $entity = StringParser::dotNotationFor($entityClass);
            $opList[$entity]['fields'] = $fields;

            if (isset($options['show_embedded'])) {
                $relations = $this->metadata->getRelations();
                $keys = array_keys($relations);
                $flip = array_flip($keys);
                foreach ($flip as $rel => $item) {
                    $relationPath = $relations[$rel];
                    $newSherlock = new Sherlock($this->manager);
                    $fields = $newSherlock->getFieldsType($relationPath)['fields'];
                    $flip[$rel] = $fields;
                }
                $opList[$entity]['_embedded'] = $flip;
            }

            if ($this->metadata->hasRelations()) {
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

    public function willCall(string $fullyQualifiedClassName) : array
    {
        return $this->getRelations($fullyQualifiedClassName);
    }

    public function getSearchable($entityPath) : array
    {
        $raw = $this->getShortOpList($entityPath);

        $ultimateListOfSearchableFields = $raw['fields'];

        foreach ($raw['_embedded'] as $em => $bedded) {
            foreach ($raw['_embedded'][$em] as $foo => $bar) {
                $ultimateListOfSearchableFields[$em .'.'. $foo] = $bar;
            }
        }

        return $ultimateListOfSearchableFields;
    }
}
