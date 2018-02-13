<?php

namespace Mado\QueryBundle\Interfaces;

use Doctrine\ORM\EntityManagerInterface;
use Mado\QueryBundle\Component\Meta\GraphWalker;
use Mado\QueryBundle\Services\IdsChecker;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

interface CustomFilter
{
    public function __construct(
        EntityManagerInterface $manager,
        GraphWalker $walter,
        RequestStack $requestStack,
        IdsChecker $idsChecker,
        LoggerInterface $logger
    );

    public function setUser(AdditionalFilterable $user);

    public function allItemsTo(string $entity);

    public function getPathTo(string $fullyQualifiedClassName);

    public static function getEntityFromFilter(string $filterName);

    public function setEntity(string $entity);
}
