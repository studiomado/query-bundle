<?php

namespace Mado\QueryBundle\Queries;

use Doctrine\ORM\EntityManager;
use Mado\QueryBundle\Services\StringParser;

class AbstractQuery
{
    protected $manager;

    protected $entityName;

    protected $entityAlias;

    protected $parser;

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

        return $this;
    }

    public function createQueryBuilder($entityName, $alias)
    {
        $this->entityName = $entityName;
        $this->entityAlias = $alias;

        $this->qBuilder = $this->manager->createQueryBuilder($this->entityName)
            ->select($alias)
            ->from($this->entityName, $alias);

        return $this;
    }

    public function getEntityName()
    {
        return $this->entityName;
    }
}
