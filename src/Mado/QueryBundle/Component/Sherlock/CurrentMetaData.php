<?php

namespace Mado\QueryBundle\Component\Sherlock;

use Doctrine\ORM\EntityManagerInterface;
use Mado\QueryBundle\Dictionary;

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
        $this->currentMetadata = $this->manager->getClassMetadata($entityClass);

        return array_map(function ($item) {
            return Dictionary::getOperatorsFromDoctrineType($item['type']);
        }, $this->currentMetadata->fieldMappings);
    }

    public function haveRelations() : bool
    {
        return isset(
            $this->currentMetadata->associationMappings['members']
        );
    }

    public function getCurrentTargetEntity() : string
    {
        return $this->currentMetadata->associationMappings
            ['members']
            ['targetEntity'];
    }
}
