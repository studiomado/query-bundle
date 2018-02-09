<?php

namespace Mado\QueryBundle\Services;

use Doctrine\ORM\EntityManager;
use Mado\QueryBundle\Interfaces\CustomFilter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

final class QueryBuilder
{
    private $customFilter;

    private $storage;

    private $token;

    private $user;

    private $request;

    private $logger;

    public function __construct(
        CustomFilter $customFilter,
        TokenStorage $storage,
        EntityManager $manager,
        RequestStack $requestStack,
        LoggerInterface $logger
    ) {
        $this->customFilter = $customFilter;
        $this->storage      = $storage;
        $this->manager      = $manager;
        $this->request      = $requestStack->getCurrentRequest();
        $this->logger       = $logger;

        $this->token = $this->storage->getToken();
        $this->user  = $this->token->getUser();
    }

    public function getRepository(string $rootEntity)
    {
        $this->rootEntity = $rootEntity;

        $additionalFilters = $this->customFilter
            ->setUser($this->user)
            ->allItemsTo($rootEntity);

        return $this->manager
            ->getRepository($this->rootEntity)
            ->setRequestWithFilter(
                $this->request,
                $additionalFilters
            );
    }
}
