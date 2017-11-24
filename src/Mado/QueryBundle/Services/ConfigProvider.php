<?php
namespace Mado\QueryBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Mado\QueryBundle\Component\ConfigProvider as ConfigProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ConfigProvider implements ConfigProviderInterface
{
    private $user;

    private $request;

    private $manager;

    private $domainConfiguration;

    public function __construct(
        RequestStack $requestStack,
        TokenStorage $tokenStorage,
        EntityManager $manager
    ) {
        $this->token   = $tokenStorage->getToken();
        $this->user    = $this->token->getUser();
        $this->request = $requestStack->getCurrentRequest();
        $this->manager = $manager;
    }

    public function setDomainConfiguration(array $domainConfiguration = [])
    {
        $this->domainConfiguration = $domainConfiguration;
    }

    public function getConf()
    {
        return $this->domainConfiguration;
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

    public function filterRelation(QueryBuilder $qb)
    {
        $conf = $this->getConf();

        foreach ($conf['additional-filters'] as $entityClass => $entityIds) {
            if (isset($conf['entity-map'][$conf['root-entity']])) {
                $relationName = $conf['entity-map'][$conf['root-entity']];

                $qb->andWhere(
                    $qb->expr()->in(
                        $qb->getRootAlias() . '.' . $relationName[$entityClass],
                        $entityIds
                    )
                );

                var_dump($qb->getQuery()->getDql());
                die;
            }
        }

        return $qb;
    }
}
