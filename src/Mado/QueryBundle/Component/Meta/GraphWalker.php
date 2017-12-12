<?php

namespace Mado\QueryBundle\Component\Meta;

/**
 * @since Class available since Release 2.1.1
 */
interface GraphWalker
{
    public function buildPathBetween($firstNode, $lastNode) : bool;

    public function getPath() : string;

    public function init();
}
