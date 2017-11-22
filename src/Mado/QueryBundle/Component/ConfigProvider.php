<?php

namespace Mado\QueryBundle\Component;

interface ConfigProvider
{
    public function getUser();

    public function getRequest();
}
