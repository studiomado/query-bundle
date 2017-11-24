<?php

namespace Mado\QueryBundle\Component;

interface ConfigProvider
{
    public function setDomainConfiguration(array $domainConfiguration = []);

    public function getConf();

    public function getUser();

    public function getRequest();

    public function getUserRoles();
}
