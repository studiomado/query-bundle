<?php

namespace Mado\QueryBundle\Objects;

class MetaDataAdapter
{
    private $metadata;

    private $entityName;

    public function setClassMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    public function getFields()
    {
        return array_keys($this->metadata->fieldMappings);
    }

    public function getEntityAlias()
    {
        $entityName = explode('\\', strtolower($this->entityName));

        $entityName = $entityName[count($entityName) - 1][0];

        return $entityName[0];
    }
}
