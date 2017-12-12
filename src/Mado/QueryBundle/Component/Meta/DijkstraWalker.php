<?php

namespace Mado\QueryBundle\Component\Meta;

/**
 * @since Class available since Release 2.1.0
 */
final class DijkstraWalker implements
    GraphWalker
{
    private $builder;

    private $dijkstra;

    private $path;

    private $map;

    public function __construct(
        DataMapper $builder,
        Dijkstra $dijkstra
    ) {
        $this->builder = $builder;
        $this->dijkstra = $dijkstra;

        $this->init();
    }

    public function buildPathBetween($start, $end) : bool
    {
        $this->builder->rebuildRelationMap();

        $shortestPath = $this->dijkstra->shortestPaths($start, $end);
        $prevRelations = $this->map[$start]['relations'];

        $this->path = '_embedded';

        foreach ($shortestPath[0] as $meta) {
            if ($relationName = array_search($meta, $prevRelations)) {
                $this->path .= '.' . $relationName;
            }

            $prevRelations = $this->map[$meta]['relations'];
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

    public function init()
    {
        $this->dijkstra->setMap(
            $this->map = $this->builder->getMap()
        );
    }
}
