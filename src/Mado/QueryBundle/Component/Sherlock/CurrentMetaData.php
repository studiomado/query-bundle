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

    public static function fromEntityManager(EntityManagerInterface $manager)
    {
        return new self($manager);
    }

    public function lightMetaData()
    {
        return array_map(function ($item) {
            return $item->rootEntityName;
        }, $this->metadata);
    }

    public function extractField($entityClass)
    {
        $this->currentMetadata = $this->manager->getClassMetadata($entityClass);

        return array_map(function ($item) {
            return Dictionary::getOperatorsFromDoctrineType($item['type']);
        }, $this->currentMetadata->fieldMappings);
    }

    public function haveCurrentMetadataRelations()
    {
        return isset(
            $this->currentMetadata->associationMappings['members']
        );
    }

    public function getCurrentTargetEntity()
    {
        return $this->currentMetadata->associationMappings
            ['members']
            ['targetEntity'];
    }
}
