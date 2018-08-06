<?php

namespace Mado\QueryBundle\Repositories;

use Mado\QueryBundle\Repositories\BaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class HttpRepository
{
    private $manager;

    private $stack;

    private $repo;

    public function __construct(
        EntityManagerInterface $manager,
        RequestStack $stack
    ) {
        $this->manager = $manager;
        $this->stack = $stack;
    }

    public function setRepo(BaseRepository $repo)
    {
        $this->repo = $repo;
    }

    public function buildForEntity(string $entityClassName)
    {
        if (!$this->repo) {
            $this->repo = new BaseRepository(
                $this->manager,
                $this->manager->getClassMetadata($entityClassName)
            );
        }

        $this->repo->setRequest($this->stack->getCurrentRequest());

        $this->query = $this->repo
            ->getQueryBuilderFactory()
            ->filter()
            ->sort()
            ->getQueryBuilder()
            ->getQuery();
    }

    public function getSql()
    {
        return $this->query->getSql();
    }

    public function getResult()
    {
        return $this->query->getResult();
    }
}
