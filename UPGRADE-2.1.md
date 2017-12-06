UPGRADE FROM 2.0 to 2.1
=======================

NestingException
----------------

 * Notice if some loop is nested too many times.

UnespectedValueException
------------------------

 * Trhowed whenever parent root entity of a relation not exists.

UnreachablePathException
------------------------

 * An exception that's throwed when is not possibile to get the path.

UndefinedPathException
----------------------

 * An exception that's throwed when entities path is requested too early.

MapBuilder
----------

 * Build a map based on Doctrine's DataMapper.

DataMapper
----------

 * An interface to build maps of relations of database entities.

JsonPathFinder
--------------

 * Build the path of relations between entities.

```php
$finder = new JsonPathFinder($this->mapper);
$finder->setQueryStartEntity("FooBundle\\Entity\\Start");
$finder->getPathToEntity("AppBundle\\Entity\\End"); // _embedded.start.end
```

Dijkstra
--------

 * Navigate the graph to find the minimum spanning tree

```php
$dijkstra = new Dijkstra($this->mapper);
$entities = $dijkstra->shortestPaths($from, $to)
```

DijkstraWalker
--------------

 * Use Dijkstra to find paths

