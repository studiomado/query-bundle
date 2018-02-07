<?php

namespace Mado\QueryBundle\Interfaces;

use Doctrine\ORM\EntityManagerInterface;
use Mado\QueryBundle\Component\Meta\GraphWalker;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;

interface CustomFilter
{
    public function __construct(
        EntityManagerInterface $manager,
        GraphWalker $walter,
        RequestStack $requestStack,
        LoggerInterface $logger
    );

    public function setUser(AdditionalFilterable $user);

    public function allItemsTo(string $entity);

    public function getPathTo(string $fullyqualifiedClassName);

    public static function getEntityFromFilter(string $filterName);

    public function setEntity(string $entity);
}
