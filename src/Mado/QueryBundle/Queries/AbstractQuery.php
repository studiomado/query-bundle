<?php

namespace Mado\QueryBundle\Queries;

use Doctrine\ORM\EntityManager;
use Mado\QueryBundle\Objects\MetaDataAdapter;
use Mado\QueryBundle\Queries\QueryBuilderOptions;
use Mado\QueryBundle\Services\StringParser;

class AbstractQuery
{
    protected $manager;

    protected $entityName;

    protected $entityAlias;

    protected $parser;

    protected $qBuilder;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
        $this->parser  = new StringParser();
    }

    public function createSelectAndGroupBy($entityName, $alias, $groupByField)
    {
        $select = $alias . '.' . $groupByField . ', count(' . $alias . '.id) as num';
        $groupBy = $alias . '.' . $groupByField . '';

        $this->entityName = $entityName;
        $this->entityAlias = $alias;

        $this->qBuilder = $this->manager->createQueryBuilder($this->entityName)
            ->select($select)
            ->groupBy($groupBy);
    }

    public function createQueryBuilder($entityName, $alias)
    {
        $this->entityName = $entityName;
        $this->entityAlias = $alias;

        $this->qBuilder = $this->manager->createQueryBuilder($this->entityName)
            ->select($alias)
            ->from($this->entityName, $alias);
    }

    public function getEntityName()
    {
        return $this->entityName;
    }

    public function loadMetadataAndOptions(
        MetaDataAdapter $metadata,
        QueryBuilderOptions $options
    ) {
        $this->setFields($metadata->getFields());

        $this->setAndFilters($options->getAndFilters());
        $this->setOrFilters($options->getOrFilters());
        $this->setSorting($options->getSorting());
        $this->setRel($options->getRel());
        $this->setPrinting($options->getPrinting());
        $this->setSelect($options->getSelect());
    }
}
