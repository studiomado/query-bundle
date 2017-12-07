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
use Mado\QueryBundle\Meta\DataMapper;

class MyCustomMapper implements DataMapper
{
  public function getMap() : array
  {
    return [
      "FooBundle\\Entity\\Start" => [
        "relations" => [
          "end" => "AppBundle\\Entity\\End",
          "foo" => "AppBundle\\Entity\\Foo",
        ]
      ]
    ];
  }
};

$finder = new JsonPathFinder(new MyCustomMapper($entityManager));
$finder->setQueryStartEntity("FooBundle\\Entity\\Start");
$finder->getPathToEntity("AppBundle\\Entity\\End"); // _embedded.start.end
```

Dijkstra
--------

 * Navigate the graph to find the minimum spanning tree

```php
$dijkstra = new Dijkstra(new MyCustomMapper($entityManager));
$entities = $dijkstra->shortestPaths($from, $to)
```

DijkstraWalker
--------------

 * Use Dijkstra to find paths to use in querystring. For example to query all
   users with a group that is inside certain category with id 2, 3 or 5 we
   need this in query string.

   `filtering[_embedded.groups.category.id|list]=2,3,5`

   If we want to force this filter in queryBundle

```php
$walker = new DijkstraWalker(
  new \Mado\QueryBundle\Component\Meta\MapBuilder($manager)
  new Dijkstra()
);

$walker->buildPathBetween(
  \AppBundle\Entity\Start::class,
  \FooBundle\Entity\End:class
);

$filter = $walker->getPath();

$repository = $this->getDoctrine()
    ->getRepository('AppBundle:User')
    ->setRequestWithFilter($request, [
      $filter . '.id|list' => '2,3,5'
    ]);
```
