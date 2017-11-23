<?php

namespace Mado\QueryBundle\Services;

use Mado\QueryBundle\Component\ConfigProvider as ConfigProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ConfigProvider implements ConfigProviderInterface
{
    private $user;

    private $request;

    public function __construct(
        RequestStack $requestStack,
        TokenStorage $tokenStorage
    ) {
        $this->token   = $tokenStorage->getToken();
        $this->user    = $this->token->getUser();
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

    public function getUserRoles()
    {
        return $this->token->getRoles();
    }
}
