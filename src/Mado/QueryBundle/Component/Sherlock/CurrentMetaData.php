<?php

namespace Mado\QueryBundle\Component\Sherlock;

use Doctrine\ORM\EntityManagerInterface;
use Mado\QueryBundle\Dictionary;
use Mado\QueryBundle\Services\StringParser;

class CurrentMetaData
{
    private $metadata;

    private $manager;

    private $currentMetadata;

    private function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;

        $metadata = $this->manager
            ->getMetaDataFactory()
            ->getAllMetaData();

        $this->metadata = $metadata;
    }

    public static function fromEntityManager(EntityManagerInterface $manager) : CurrentMetaData
    {
        return new self($manager);
    }

    public function justEntitiesMetadata() : array
    {
        return array_map(function ($item) {
            return $item->rootEntityName;
        }, $this->metadata);
    }

    public function extractFields($entityClass) : array
    {
        return $this->extraction(
            $entityClass,
            function ($item) {
                return Dictionary::getOperatorsFromDoctrineType($item);
            }
        );
    }

    public function extractFieldsType($entityClass) : array
    {
        return $this->extraction(
            $entityClass,
            function ($item) {
                return $item;
            }
        );
    }

    private function extraction($entityClass, callable $step) : array
    {
        $this->currentMetadata = $this->manager->getClassMetadata($entityClass);

        return array_map(function ($item) use ($step) {
            return $step($item['type']);
        }, $this->currentMetadata->fieldMappings);
    }

    public function hasRelations() : bool
    {
        return count($this->getCurrentAssociationMapping()) > 0;
    }

    public function getCurrentAssociationMapping()
    {
        return $this->currentMetadata->associationMappings;
    }

    public function getRelations() : array
    {
        $relations = [];

        foreach ($this->getCurrentAssociationMapping() as $rel) {
            $relations[
                $rel['inversedBy']
            ] = StringParser::dotNotationFor($rel['targetEntity']);
        }

        return $relations;
    }
}
