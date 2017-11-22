<?php

namespace Mado\QueryBundle\Action;

use Mado\QueryBundle\Interfaces\EntityClass;
use Mado\QueryBundle\Queries\QueryBuilderOptions;
use Mado\QueryBundle\Repositories\BaseRepository;

final class CreateQuery
{
    private $manager;

    private $options;

    private $entityClass;

    public function __construct(
        \Doctrine\ORM\EntityManager $manager,
        QueryBuilderOptions $options,
        EntityClass $class
    ) {
        $this->manager = $manager;
        $this->options = $options;
        $this->class   = $class;
    }

    private function getQuery()
    {
        return (new BaseRepository($this->manager,
            $this->manager->getClassMetadata($this->class->getFullyQualifiedNameClass())
        ))
        ->setQueryOptions($this->options)
        ->getQueryBuilderFactory()
        ->filter()
        ->sort()
        ->getQueryBuilder()
        ->getQuery();
    }

    public function getDql()
    {
        return $this->getQuery()->getDql();
    }

    public function getSql()
    {
        return $this->getQuery()->getSql();
    }
}
