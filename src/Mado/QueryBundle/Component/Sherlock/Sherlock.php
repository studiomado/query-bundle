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

    public function getOpList($entityPath)
    {
        $opList = [];

        foreach ($this->metadata->lightMetaData() as $entityClass) {
            $fields = $this->metadata->extractField($entityClass);

            $node = StringParser::dotNotationFor($entityClass);
            $opList[$node]['fields'] = $fields;

            if ($this->metadata->haveCurrentMetadataRelations()) {
                $targetEntity = $this->metadata->getCurrentTargetEntity();
                $opList[$node]['relations'] = [StringParser::dotNotationFor($targetEntity)];
            }
        }

        return $opList[$entityPath];
    }
}
