<?php

namespace Mado\QueryBundle\Component\Meta;

/**
 * @since Interface available since Release 2.1.0
 */
interface DataMapper
{
    public function setMap(array $map) : bool;

    public function getMap() : array;

    public function rebuildRelationMap() : bool;
}
