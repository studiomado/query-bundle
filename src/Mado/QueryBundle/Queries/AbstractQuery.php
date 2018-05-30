<?php

namespace Mado\QueryBundle\Queries;

use Doctrine\ORM\EntityManager;
use Mado\QueryBundle\Objects\MetaDataAdapter;
use Mado\QueryBundle\Queries\QueryBuilderOptions;
use Mado\QueryBundle\Services\StringParser;

abstract class AbstractQuery
{
    protected $manager;

    protected $entityName;

    protected $entityAlias;

    protected $parser;

    protected $qBuilder;

    protected $joinFactory;

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

        $this->qBuilder = $this->manager->createQueryBuilder()
            ->select($select)
            ->groupBy($groupBy);

        $this->joinFactory = new Join($this->getEntityName(), $this->entityAlias, $this->manager);
    }

    public function createQueryBuilder($entityName, $alias)
    {
        $this->entityName = $entityName;
        $this->entityAlias = $alias;

        $this->qBuilder = $this->manager->createQueryBuilder()
            ->select($alias)
            ->from($this->entityName, $alias);

        $this->joinFactory = new Join($this->getEntityName(), $this->entityAlias, $this->manager);
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
        $this->setRel([$options->getRel()]);
        $this->setPrinting($options->getPrinting());
        $this->setSelect($options->getSelect());
    }

    abstract public function setFields(array $fields = []);
    abstract public function setAndFilters(array $andFilters = []);
    abstract public function setOrFilters(array $orFilters = []);
    abstract public function setSorting(array $sorting = []);
    abstract public function setRel(array $rel);
    abstract public function setPrinting($printing);
    abstract public function setSelect($select);
}
