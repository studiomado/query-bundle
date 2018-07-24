<?php

namespace Mado\QueryBundle\Component\Meta;

/**
 * @since Class available since Release 2.1.0
 */
class Dijkstra
{
    private $map;

    private $distance;

    private $prev;

    private $visited;

    public function setMap($map)
    {
        foreach ($map as $nodeName => $metaData) {
            foreach ($metaData['relations'] as $itemKey => $itemValue) {
                $this->map[$nodeName][$itemValue] = 1;
            }
        }
    }

    private function processQueue(array $excluded)
    {
        $this->ensureMapIsDefined();

        $node = array_search(min($this->visited), $this->visited);

        if (!empty($this->map[$node]) && !in_array($node, $excluded)) {
            foreach ($this->map[$node] as $neighbor => $cost) {
                if (isset($this->distance[$neighbor])) {
                    if ($this->distance[$node] + $cost < $this->distance[$neighbor]) {
                        $this->distance[$neighbor] = $this->distance[$node] + $cost;
                        $this->prev[$neighbor] = [$node];
                        $this->visited[$neighbor] = $this->distance[$neighbor];
                    } elseif ($this->distance[$node] + $cost === $this->distance[$neighbor]) {
                        $this->prev[$neighbor][] = $node;
                        $this->visited[$neighbor] = $this->distance[$neighbor];
                    }
                }
            }
        }

        unset($this->visited[$node]);
    }

    private function extractPaths($target)
    {
        $paths = [[$target]];
        while (current($paths) !== false) {
            $key  = key($paths);
            $path = current($paths);
            next($paths);
            if (!empty($this->prev[$path[0]])) {
                foreach ($this->prev[$path[0]] as $prev) {
                    $copy = $path;
                    array_unshift($copy, $prev);
                    $paths[] = $copy;
                }
                unset($paths[$key]);
            }
        }
        return array_values($paths);
    }

    public function shortestPaths($source, $target, array $excluded = array())
    {
        $this->ensureMapIsDefined();

        $this->distance = array_fill_keys(array_keys($this->map), INF);
        $this->distance[$source] = 0;
        $this->prev = array_fill_keys(array_keys($this->map), []);
        $this->visited = [$source => 0];

        while (!empty($this->visited)) {
            $this->processQueue($excluded);
        }

        if ($source === $target) {
            return [[$source]];
        }

        if (empty($this->prev[$target])) {
            return [];
        }

        return $this->extractPaths($target);
    }

    public function ensureMapIsDefined()
    {
        if (!$this->map) {
            throw new \RuntimeException(
                'Oops! Map is not defined'
            );
        }
    }
}
