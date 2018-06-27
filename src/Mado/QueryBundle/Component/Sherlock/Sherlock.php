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
        $opList = [];

        foreach ($this->metadata->justEntitiesMetadata() as $entityClass) {
            $fields = $this->metadata->extractFields($entityClass);

            $entity = StringParser::dotNotationFor($entityClass);
            $opList[$entity]['fields'] = $fields;

            if ($this->metadata->haveRelations()) {
                $targetEntity = $this->metadata->getCurrentTargetEntity();
                $opList[$entity]['relations'] = [StringParser::dotNotationFor($targetEntity)];
            }
        }

        return $opList[$entityPath];
    }
}
