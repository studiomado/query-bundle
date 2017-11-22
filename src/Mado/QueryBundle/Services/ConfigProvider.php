<?php

namespace Mado\QueryBundle\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Mado\QueryBundle\Component\ConfigProvider as ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    private $user;

    private $request;

    public function __construct(
        RequestStack $requestStack,
        TokenStorage $tokenStorage
    ) {
        $this->user    = $tokenStorage->getToken()->getUser();
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
