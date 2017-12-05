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
