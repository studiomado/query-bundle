<?php

namespace Mado\QueryBundle\Component\Meta;

/**
 * @since Class available since Release 2.1.0
 */
final class DijkstraWalker
{
    private $builder;

    private $dijkstra;

    private $path;

    public function __construct(
        DataMapper $builder,
        Dijkstra $dijkstra
    ) {
        $this->builder = $builder;
        $this->dijkstra = $dijkstra;
    }

    public function buildPathBetween($start, $end) : bool
    {
        $this->builder->rebuildRelationMap();

        $map = $this->builder->getMap();

        $this->dijkstra->setMap($map);

        $percorso = $tutta = $this->dijkstra->shortestPaths($start, $end);
        $prevRelations = $map[$start]['relations'];

        $this->path = '_embedded';

        foreach ($percorso[0] as $nodo => $meta) {
            if ($relationName = array_search($meta, $prevRelations)) {
                $this->path .= '.' . $relationName;
            }

            $prevRelations = $map[$meta]['relations'];
        }

        return true;
    }

    public function getPath() : string
    {
        if (!$this->path) {
            throw new \RuntimeException(
                'Oops! path was never builded.'
            );
        }

        return $this->path;
    }
}
